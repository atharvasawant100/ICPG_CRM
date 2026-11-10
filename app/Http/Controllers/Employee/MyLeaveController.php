<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyLeaveController extends Controller
{
    /**
     * Display personal leave balances and history of applied leaves.
     */
    public function index(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.leaves.index')->with('info', 'Admin accounts do not have personal leave allocations.');
        }

        abort_unless(Gate::allows('my_leaves.view'), 403);

        $user = $request->user();

        // 1. Employee's leave balances
        $balances = EmployeeLeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();

        // If balances don't exist yet for this user, populate initial default balances
        if ($balances->isEmpty()) {
            $types = LeaveType::where('is_active', true)->get();
            foreach ($types as $type) {
                EmployeeLeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $type->id, 'year' => now()->year],
                    [
                        'total_allocated' => $type->days_allowed_per_year,
                        'used_days' => 0,
                        'remaining_days' => $type->days_allowed_per_year,
                    ]
                );
            }
            $balances = EmployeeLeaveBalance::where('user_id', $user->id)
                ->where('year', now()->year)
                ->with('leaveType')
                ->get();
        }

        // 2. Employee's own applied leave requests
        $myLeaves = LeaveRequest::where('user_id', $user->id)
            ->with(['leaveType', 'approver'])
            ->latest('created_at')
            ->paginate(15);

        // Active leave types for application form
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('employee.leaves.index', [
            'user' => $user,
            'balances' => $balances,
            'myLeaves' => $myLeaves,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Submit a new leave application.
     */
    public function store(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.leaves.index')->with('info', 'Admin accounts do not apply for leaves.');
        }

        abort_unless(Gate::allows('my_leaves.create'), 403);

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day' => ['sometimes', 'boolean'],
            'half_day_type' => ['nullable', 'string', 'in:first_half,second_half'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $isHalfDay = $request->boolean('is_half_day');

        if ($isHalfDay) {
            $totalDays = 0.5;
            $end = $start->copy(); // Half-day is on a single day
        } else {
            // Count working days (excluding Sundays)
            $totalDays = 0;
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                if ($date->dayOfWeek !== Carbon::SUNDAY) {
                    $totalDays++;
                }
            }
            if ($totalDays == 0) {
                $totalDays = 1;
            }
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_days' => $totalDays,
            'is_half_day' => $isHalfDay,
            'half_day_type' => $isHalfDay ? ($validated['half_day_type'] ?? 'first_half') : null,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'action_by' => null,
            'action_at' => null,
        ]);

        // The approvers need to know a request is waiting for them.
        app(\App\Services\NotificationDispatcher::class)
            ->leaveRequested($leaveRequest);

        return redirect()->route('my-leaves.index')
            ->with('success', 'Your leave request has been submitted successfully to your manager for review.');
    }
}
