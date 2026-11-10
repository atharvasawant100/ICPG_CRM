<?php

namespace App\Services;

use App\Mail\MissedTasksAlertMail;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TaskOverdueService
{
    public const MISSED_TASKS_EMAIL_THRESHOLD = 5;

    /**
     * Check for overdue incomplete tasks and dispatch:
     * 1. In-portal notification on employee portal for each missed task deadline.
     * 2. Email alert to employee (with department manager CC'd) once threshold of 5+ missed tasks is reached.
     *
     * @return array{overdue_tasks: int, portal_notifications: int, emails_sent: int}
     */
    public function checkAndDispatchOverdueNotifications(): array
    {
        $today = Carbon::today();

        // 1. Fetch all overdue tasks that are still incomplete
        $overdueTasks = Task::query()
            ->with(['assignee', 'project'])
            ->overdue()
            ->whereNotNull('assigned_to')
            ->get();

        $portalNotificationsSent = 0;
        $emailsSent = 0;

        // 2. Dispatch portal notification for each overdue task (once per task)
        foreach ($overdueTasks as $task) {
            $assignee = $task->assignee;
            if (! $assignee) {
                continue;
            }

            $alreadyNotified = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $assignee->id)
                ->where('type', TaskOverdueNotification::class)
                ->where(function ($q) use ($task) {
                    $q->where('data', 'like', '%"task_id":' . $task->id . '%')
                      ->orWhere('data', 'like', '%"task_id": "' . $task->id . '"%');
                })
                ->exists();

            if (! $alreadyNotified) {
                try {
                    $assignee->notify(new TaskOverdueNotification($task));
                    $portalNotificationsSent++;
                } catch (\Throwable $e) {
                    Log::error("Failed to dispatch TaskOverdueNotification for task #{$task->id}: " . $e->getMessage());
                }
            }
        }

        // 3. Check for employees with 5 or more missed tasks and trigger email
        $tasksByEmployee = $overdueTasks->groupBy('assigned_to');

        foreach ($tasksByEmployee as $employeeId => $tasks) {
            if ($tasks->count() < self::MISSED_TASKS_EMAIL_THRESHOLD) {
                continue;
            }

            $employee = $tasks->first()->assignee;
            if (! $employee || ! $employee->email) {
                continue;
            }

            $cacheKey = "missed_tasks_mail_{$employee->id}_{$today->toDateString()}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            $alreadySentToday = EmailLog::query()
                ->where('recipient_email', $employee->email)
                ->where('type', 'missed_tasks_alert')
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadySentToday) {
                Cache::put($cacheKey, true, now()->endOfDay());
                continue;
            }

            $manager = $employee->departmentManager();

            try {
                $mailable = new MissedTasksAlertMail($employee, $tasks, $manager);
                $mailable->build();

                // In test environment, use default Mail facade so Mail::fake() captures it cleanly
                if (app()->environment('testing')) {
                    Mail::send($mailable);
                } else {
                    $mailerName = app(EmailLogService::class)->mailer($mailable);
                    Mail::mailer($mailerName)->send($mailable);
                }

                EmailLog::create([
                    'mailable' => MissedTasksAlertMail::class,
                    'type' => 'missed_tasks_alert',
                    'recipient_email' => $employee->email,
                    'recipient_name' => $employee->name,
                    'subject' => $mailable->subjectLine(),
                    'status' => EmailLog::STATUS_SENT,
                    'sent_at' => now(),
                    'context' => [
                        'employee_id' => $employee->id,
                        'manager_id' => $manager?->id,
                        'manager_email' => $manager?->email,
                        'manager_name' => $manager?->name,
                        'overdue_count' => $tasks->count(),
                        'task_ids' => $tasks->pluck('id')->all(),
                    ],
                ]);

                Cache::put($cacheKey, true, now()->endOfDay());
                $emailsSent++;

                Log::info("Sent missed tasks threshold email to {$employee->email} with CC to " . ($manager?->email ?? 'none'), [
                    'employee_id' => $employee->id,
                    'overdue_count' => $tasks->count(),
                    'manager_id' => $manager?->id,
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to send MissedTasksAlertMail to {$employee->email}: " . $e->getMessage(), [
                    'employee_id' => $employee->id,
                    'exception' => $e,
                ]);
            }
        }

        return [
            'overdue_tasks' => $overdueTasks->count(),
            'portal_notifications' => $portalNotificationsSent,
            'emails_sent' => $emailsSent,
        ];
    }
}

