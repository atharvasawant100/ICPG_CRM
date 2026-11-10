<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use App\Rbac\Rbac;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ManagerPerformanceService
{
    /**
     * Get managers available for reporting.
     * Admin gets all active managers. A manager only gets themselves.
     */
    public function getReportingManagers(User $currentUser): Collection
    {
        if ($currentUser->isAdmin()) {
            return User::active()
                ->where(function ($q) {
                    $q->whereHas('roles', function ($rq) {
                        $rq->where('name', Rbac::MANAGER_ROLE)
                            ->orWhere('name', 'like', '%_manager');
                    })
                    ->orWhere('role', 'manager')
                    ->orWhere('role', 'like', '%_manager')
                    ->orWhere('designation', 'like', '%manager%');
                })
                ->orWhereIn('id', Department::whereNotNull('manager_id')->pluck('manager_id'))
                ->orderBy('name')
                ->get()
                ->filter(fn ($u) => ! $u->isAdmin())
                ->values();
        }

        return collect([$currentUser]);
    }

    /**
     * Generate monthly performance metrics for a specific manager.
     */
    public function getMonthlyReport(User $manager, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $now = Carbon::now();

        // Resolve team members under this manager (including the manager themselves)
        $teamMemberIds = User::active()
            ->where(function ($q) use ($manager) {
                $q->where('manager_id', $manager->id)
                    ->orWhere('id', $manager->id);
                if ($manager->department) {
                    $q->orWhere('department', $manager->department);
                }
            })
            ->pluck('id')
            ->all();

        // Tasks assigned to this manager, their team members, assigned by manager, or in their department
        $tasks = Task::with(['project', 'creator', 'workLogs', 'assignee'])
            ->where(function ($q) use ($manager, $teamMemberIds) {
                $q->whereIn('assigned_to', $teamMemberIds)
                    ->orWhere('assigned_by', $manager->id);
                if ($manager->department) {
                    $q->orWhere('department', $manager->department);
                }
            })
            ->where(function ($q) use ($startDate, $endDate) {
                // 1. Created/assigned during this month
                $q->whereBetween('created_at', [$startDate, $endDate])
                    // 2. Due during this month
                    ->orWhereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
                    // 3. Completed during this month
                    ->orWhereBetween('completed_at', [$startDate, $endDate])
                    // 4. In progress or pending during this month
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('created_at', '<=', $endDate)
                            ->where(function ($inner) use ($startDate) {
                                $inner->whereNull('completed_at')
                                    ->orWhere('completed_at', '>=', $startDate);
                            });
                    });
            })
            ->latest('id')
            ->get();

        $totalTasksCount = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed');
        $completedCount = $completedTasks->count();
        $inProgressTasks = $tasks->where('status', 'in_progress');
        $inProgressCount = $inProgressTasks->count();
        $pendingTasks = $tasks->where('status', 'pending');
        $pendingCount = $pendingTasks->count();
        $cancelledTasks = $tasks->where('status', 'cancelled');
        $cancelledCount = $cancelledTasks->count();

        // Active workload (non-completed and non-cancelled)
        $activeCount = $inProgressCount + $pendingCount;

        // On-Time vs Delayed completions
        $onTimeCompletedCount = 0;
        $delayedCompletedCount = 0;
        $totalTurnaroundHours = 0;

        foreach ($completedTasks as $task) {
            $isDelayed = false;
            if ($task->due_date && $task->completed_at) {
                $isDelayed = $task->completed_at->startOfDay()->gt($task->due_date->endOfDay());
            }

            if ($isDelayed) {
                $delayedCompletedCount++;
            } else {
                $onTimeCompletedCount++;
            }

            if ($task->created_at && $task->completed_at) {
                $totalTurnaroundHours += max(0, $task->created_at->diffInHours($task->completed_at));
            }
        }

        // Overdue tasks: due_date passed and not completed
        $overdueTasksCount = $tasks->filter(function ($t) use ($endDate, $now) {
            if (in_array($t->status, ['completed', 'cancelled'], true) || ! $t->due_date) {
                return false;
            }
            $targetDate = $endDate->isPast() ? $endDate : $now;
            return $t->due_date->startOfDay()->lt($targetDate->startOfDay());
        })->count();

        // Rates
        $completionRate = $totalTasksCount > 0
            ? round(($completedCount / $totalTasksCount) * 100, 1)
            : 0;

        $onTimeRate = $completedCount > 0
            ? round(($onTimeCompletedCount / $completedCount) * 100, 1)
            : ($totalTasksCount === 0 ? 100 : 0);

        // Turnaround time
        $avgTurnaroundHours = $completedCount > 0
            ? round($totalTurnaroundHours / $completedCount, 1)
            : 0;

        $avgTurnaroundDays = round($avgTurnaroundHours / 24, 1);

        // Time tracked (total seconds from task tracking in month across all tasks)
        $effectiveSeconds = (int) $tasks->sum(fn ($t) => (int) $t->currentSecondsSpent());

        $trackedHours = floor($effectiveSeconds / 3600);
        $trackedMinutes = floor(($effectiveSeconds % 3600) / 60);
        $formattedTimeSpent = sprintf('%dh %02dm', $trackedHours, $trackedMinutes);

        // Priority breakdown
        $priorities = [
            'urgent' => [
                'total' => $tasks->where('priority', 'urgent')->count(),
                'completed' => $tasks->where('priority', 'urgent')->where('status', 'completed')->count(),
            ],
            'high' => [
                'total' => $tasks->where('priority', 'high')->count(),
                'completed' => $tasks->where('priority', 'high')->where('status', 'completed')->count(),
            ],
            'medium' => [
                'total' => $tasks->where('priority', 'medium')->count(),
                'completed' => $tasks->where('priority', 'medium')->where('status', 'completed')->count(),
            ],
            'low' => [
                'total' => $tasks->where('priority', 'low')->count(),
                'completed' => $tasks->where('priority', 'low')->where('status', 'completed')->count(),
            ],
        ];

        // Overall Performance Score calculation (0 to 100)
        // 50% Completion Rate + 35% On-Time Delivery + 15% Overdue/Activity Factor
        if ($totalTasksCount === 0) {
            $performanceScore = 100;
            $hasData = false;
        } else {
            $hasData = true;
            $completionComponent = ($completionRate / 100) * 50;
            $onTimeComponent = ($onTimeRate / 100) * 35;
            $overduePenalty = min(15, ($overdueTasksCount * 5));
            $overdueComponent = max(0, 15 - $overduePenalty);

            $performanceScore = (int) round($completionComponent + $onTimeComponent + $overdueComponent);
        }

        // Performance Grade
        if (! $hasData) {
            $grade = 'N/A';
            $gradeLabel = 'No Tasks Assigned';
            $gradeColor = 'gray';
        } elseif ($performanceScore >= 90) {
            $grade = 'A+';
            $gradeLabel = 'Outstanding';
            $gradeColor = 'emerald';
        } elseif ($performanceScore >= 80) {
            $grade = 'A';
            $gradeLabel = 'Exceeds Expectations';
            $gradeColor = 'blue';
        } elseif ($performanceScore >= 70) {
            $grade = 'B';
            $gradeLabel = 'Meets Expectations';
            $gradeColor = 'indigo';
        } elseif ($performanceScore >= 55) {
            $grade = 'C';
            $gradeLabel = 'Needs Improvement';
            $gradeColor = 'amber';
        } else {
            $grade = 'D';
            $gradeLabel = 'Action Required';
            $gradeColor = 'rose';
        }

        // Team / Department metrics (if manager heads a department)
        $teamSummary = null;
        if ($manager->department) {
            $teamMembers = User::active()->where('department', $manager->department)->get();
            $teamMemberIds = $teamMembers->pluck('id')->all();

            $teamTasks = Task::whereIn('assigned_to', $teamMemberIds)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate])
                        ->orWhereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->orWhereBetween('completed_at', [$startDate, $endDate]);
                })
                ->get();

            $teamTotal = $teamTasks->count();
            $teamCompleted = $teamTasks->where('status', 'completed')->count();
            $teamRate = $teamTotal > 0 ? round(($teamCompleted / $teamTotal) * 100, 1) : 0;

            $teamSummary = [
                'department' => ucfirst($manager->department),
                'member_count' => count($teamMemberIds),
                'total_tasks' => $teamTotal,
                'completed_tasks' => $teamCompleted,
                'completion_rate' => $teamRate,
            ];
        }

        return [
            'manager' => $manager,
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'month_name' => $startDate->format('F Y'),
                'is_current_month' => $startDate->isCurrentMonth(),
            ],
            'has_data' => $hasData,
            'summary' => [
                'total_tasks' => $totalTasksCount,
                'completed_tasks' => $completedCount,
                'in_progress_tasks' => $inProgressCount,
                'pending_tasks' => $pendingCount,
                'cancelled_tasks' => $cancelledCount,
                'active_tasks' => $activeCount,
                'on_time_completed' => $onTimeCompletedCount,
                'delayed_completed' => $delayedCompletedCount,
                'overdue_tasks' => $overdueTasksCount,
                'completion_rate' => $completionRate,
                'on_time_rate' => $onTimeRate,
                'avg_turnaround_days' => $avgTurnaroundDays,
                'avg_turnaround_hours' => $avgTurnaroundHours,
                'formatted_time_spent' => $formattedTimeSpent,
                'total_seconds_spent' => $effectiveSeconds,
                'performance_score' => $performanceScore,
                'grade' => $grade,
                'grade_label' => $gradeLabel,
                'grade_color' => $gradeColor,
            ],
            'priorities' => $priorities,
            'tasks' => $tasks,
            'team_summary' => $teamSummary,
        ];
    }

    /**
     * Get summary metrics for all managers for a specific month (for executive comparison view).
     */
    public function getAllManagersSummary(int $year, int $month): array
    {
        $managers = User::active()
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->where('name', Rbac::MANAGER_ROLE)
                        ->orWhere('name', 'like', '%_manager');
                })
                ->orWhere('role', 'manager')
                ->orWhere('role', 'like', '%_manager')
                ->orWhere('designation', 'like', '%manager%');
            })
            ->orWhereIn('id', Department::whereNotNull('manager_id')->pluck('manager_id'))
            ->orderBy('name')
            ->get()
            ->filter(fn ($u) => ! $u->isAdmin())
            ->values();

        $summaries = [];
        foreach ($managers as $manager) {
            $report = $this->getMonthlyReport($manager, $year, $month);
            $summaries[] = [
                'manager' => $manager,
                'department' => $manager->department ? ucfirst($manager->department) : 'General',
                'summary' => $report['summary'],
                'has_data' => $report['has_data'],
            ];
        }

        return $summaries;
    }
}

