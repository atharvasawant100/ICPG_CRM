<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Display team members under the current manager.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('team.view'), 403);

        $currentUser = $request->user();
        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();

        // 1. Build team query
        $query = User::query()
            ->where('is_active', true)
            ->with(['roles']);

        if (! $currentUser->isAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('manager_id', $currentUser->id);
                if ($currentUser->department) {
                    $q->orWhere(function ($sub) use ($currentUser) {
                        $sub->where('department', $currentUser->department)
                            ->where('id', '!=', $currentUser->id);
                    });
                }
            });
        } else {
            // Admins are employers/bosses, not subordinate team members
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', \App\Rbac\Rbac::ADMIN_ROLE));
        }

        // Apply search
        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        // Apply designation filter
        if ($designation = $request->string('designation')->trim()->value()) {
            $query->where('designation', $designation);
        }

        $allTeamMembers = $query->orderBy('name')->get();

        // Fetch today's attendances for these members
        $memberIds = $allTeamMembers->pluck('id')->all();

        $todayAttendances = Attendance::whereIn('user_id', $memberIds)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('user_id');

        // Fetch active leaves for today
        $todayLeaves = LeaveRequest::whereIn('user_id', $memberIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('leaveType')
            ->get()
            ->keyBy('user_id');

        // Pending leave requests count for the team
        $pendingLeaves = LeaveRequest::whereIn('user_id', $memberIds)
            ->where('status', 'pending')
            ->with(['user', 'leaveType'])
            ->latest()
            ->get();

        // Monthly attendance aggregations
        $monthlyAttendances = Attendance::whereIn('user_id', $memberIds)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('user_id');

        // Daily tasks completed today by team members (vanishes tomorrow)
        $todayCompletedTasks = \App\Models\Task::whereIn('assigned_to', $memberIds)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->get()
            ->groupBy('assigned_to');

        $todayTeamTasksCompletedCount = $todayCompletedTasks->flatten(1)->count();

        // Enrich members with computed status
        $enrichedMembers = $allTeamMembers->map(function ($member) use ($todayAttendances, $todayLeaves, $monthlyAttendances, $todayCompletedTasks) {
            $att = $todayAttendances->get($member->id);
            $leave = $todayLeaves->get($member->id);

            $status = 'not_clocked_in';
            if ($leave) {
                $status = 'on_leave';
            } elseif ($att) {
                $status = $att->status; // present, late, half_day, absent
            }

            // Monthly stats
            $monthRecords = $monthlyAttendances->get($member->id, collect());
            $presentDays = $monthRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
            $lateDays = $monthRecords->where('status', 'late')->count();
            $totalMinutes = $monthRecords->sum('total_minutes');
            $hours = floor($totalMinutes / 60);
            $mins = $totalMinutes % 60;

            $member->today_attendance = $att;
            $member->today_leave = $leave;
            $member->computed_status = $status;
            $member->month_present_days = $presentDays;
            $member->month_late_days = $lateDays;
            $member->month_total_hours = "{$hours}h {$mins}m";
            $member->today_tasks_completed_count = $todayCompletedTasks->get($member->id, collect())->count();

            return $member;
        });

        // Attendance status filter
        if ($statusFilter = $request->string('status')->trim()->value()) {
            $enrichedMembers = $enrichedMembers->filter(function ($m) use ($statusFilter) {
                if ($statusFilter === 'present') {
                    return in_array($m->computed_status, ['present', 'late']);
                }
                if ($statusFilter === 'on_leave') {
                    return $m->computed_status === 'on_leave';
                }
                if ($statusFilter === 'not_clocked_in') {
                    return in_array($m->computed_status, ['not_clocked_in', 'absent']);
                }
                return true;
            });
        }

        // Metrics
        $totalCount = $allTeamMembers->count();
        $presentTodayCount = $allTeamMembers->filter(fn ($m) => in_array($m->computed_status ?? '', ['present', 'late']))->count();
        $onLeaveTodayCount = $todayLeaves->count();
        $notClockedInCount = max(0, $totalCount - $presentTodayCount - $onLeaveTodayCount);
        $pendingLeavesCount = $pendingLeaves->count();

        // Available designations in current team for filter dropdown
        $designations = $allTeamMembers->pluck('designation')->filter()->unique()->values();

        return view('team.index', [
            'members' => $enrichedMembers,
            'totalCount' => $totalCount,
            'presentTodayCount' => $presentTodayCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'notClockedInCount' => $notClockedInCount,
            'pendingLeavesCount' => $pendingLeavesCount,
            'pendingLeaves' => $pendingLeaves,
            'todayTeamTasksCompletedCount' => $todayTeamTasksCompletedCount,
            'designations' => $designations,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Display a team member's profile, attendance history, and leave balances.
     */
    public function show(Request $request, User $user)
    {
        abort_unless(Gate::allows('team.view'), 403);

        $currentUser = $request->user();

        // Check if current user is allowed to view this member
        if (! $currentUser->isAdmin()) {
            $isDirectReport = ((int) $user->manager_id === (int) $currentUser->id);
            $isSameDept = ($currentUser->department && $user->department === $currentUser->department);

            if (! $isDirectReport && ! $isSameDept) {
                abort(403, 'You do not have permission to view this team member.');
            }
        }

        return redirect()->route('admin.users.show', $user);
    }
}
