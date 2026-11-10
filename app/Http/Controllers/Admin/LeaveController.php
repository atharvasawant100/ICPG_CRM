<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Rbac\Rbac;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    use AuthorizesByPermission;

    /**
     * List all leave requests with filters & KPI metrics.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('leaves.view');

        $statusFilter = $request->string('status')->trim()->value();
        $leaveTypeId = $request->input('leave_type_id');
        $employeeId = $request->input('user_id');
        $department = $request->string('department')->trim()->value();
        $search = $request->string('search')->trim()->value();

        $query = LeaveRequest::with(['user', 'leaveType', 'approver'])
            ->latest('created_at');

        // Managers only see leave requests from their supervised team; Admin and HR see all departments and employees/managers
        if (! $request->user()->isAdmin() && ! $request->user()->isHr()) {
            $currentUser = $request->user();
            $query->whereHas('user', function ($q) use ($currentUser) {
                $q->where('manager_id', $currentUser->id);
                if ($currentUser->department) {
                    $q->orWhere(function ($sub) use ($currentUser) {
                        $sub->where('department', $currentUser->department)
                            ->where('id', '!=', $currentUser->id);
                    });
                }
            });
        }

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected', 'cancelled'])) {
            $query->where('status', $statusFilter);
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        if ($department) {
            $query->whereHas('user', fn ($q) => $q->where('department', $department));
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        // KPI Summary
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $pendingCount = LeaveRequest::where('status', 'pending')->count();
        $approvedThisMonth = LeaveRequest::where('status', 'approved')
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->count();

        $todayOnLeave = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $totalEmployees = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->count();

        $summary = [
            'pending' => $pendingCount,
            'approved_this_month' => $approvedThisMonth,
            'today_on_leave' => $todayOnLeave,
            'total_employees' => $totalEmployees,
        ];

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name')
            ->get();

        return view('admin.leaves.index', [
            'requests' => $requests,
            'summary' => $summary,
            'leaveTypes' => $leaveTypes,
            'employees' => $employees,
            'departments' => Department::names(activeOnly: false),
            'selectedStatus' => $statusFilter,
            'selectedLeaveType' => $leaveTypeId,
            'selectedEmployee' => $employeeId,
            'selectedDepartment' => $department,
            'search' => $search,
        ]);
    }

    /**
     * Show form to record or apply leave.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('leaves.create');

        $employeesQuery = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name');

        $currentUser = $request->user();
        if (! $currentUser->isAdmin() && ! $currentUser->isHr()) {
            $managedDepts = Department::where('manager_id', $currentUser->id)->pluck('name')->all();
            if ($currentUser->department && ! in_array($currentUser->department, $managedDepts, true)) {
                $managedDepts[] = $currentUser->department;
            }
            $employeesQuery->where(function ($q) use ($managedDepts, $currentUser) {
                if (! empty($managedDepts)) {
                    $q->where(function ($sub) use ($managedDepts) {
                        foreach ($managedDepts as $idx => $d) {
                            if ($idx === 0) {
                                $sub->whereRaw('LOWER(department) = ?', [strtolower($d)]);
                            } else {
                                $sub->orWhereRaw('LOWER(department) = ?', [strtolower($d)]);
                            }
                        }
                    });
                } else {
                    $q->where('manager_id', $currentUser->id);
                }
            });
        }

        $employees = $employeesQuery->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $preselectedUser = $request->input('user_id');

        return view('admin.leaves.create', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'preselectedUser' => $preselectedUser,
        ]);
    }

    /**
     * Store new leave request.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('leaves.create');

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day' => ['nullable', 'boolean'],
            'half_day_type' => ['nullable', 'in:first_half,second_half'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:pending,approved'],
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $isHalfDay = ! empty($validated['is_half_day']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        if ($isHalfDay) {
            $totalDays = 0.5;
            $endDate = $startDate; // half day must be same date
        } else {
            $totalDays = $startDate->diffInDays($endDate) + 1;
        }

        $year = $startDate->year;
        $balance = EmployeeLeaveBalance::getOrCreateForUser($validated['user_id'], $validated['leave_type_id'], $year);

        DB::transaction(function () use ($validated, $totalDays, $isHalfDay, $startDate, $endDate, $balance) {
            $status = $validated['status'];

            $leaveRequest = LeaveRequest::create([
                'user_id' => $validated['user_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'is_half_day' => $isHalfDay,
                'half_day_type' => $isHalfDay ? ($validated['half_day_type'] ?? 'first_half') : null,
                'reason' => $validated['reason'] ?? null,
                'status' => $status,
                'action_by' => $status === 'approved' ? auth()->id() : null,
                'action_at' => $status === 'approved' ? now() : null,
                'admin_remarks' => $validated['admin_remarks'] ?? null,
            ]);

            if ($status === 'approved') {
                $balance->used_days = (float) $balance->used_days + $totalDays;
                $balance->recompute()->save();
                $leaveRequest->syncToAttendance();
            } else {
                $balance->pending_days = (float) $balance->pending_days + $totalDays;
                $balance->save();

                // A request recorded on someone's behalf still needs approval,
                // so the approvers are told about it.
                app(\App\Services\NotificationDispatcher::class)
                    ->leaveRequested($leaveRequest);
            }
        });

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave record created successfully.');
    }

    /**
     * Show form to edit leave request.
     */
    public function edit(LeaveRequest $leave)
    {
        $this->authorizePermission('leaves.edit');

        $employees = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name')
            ->get();

        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('admin.leaves.edit', [
            'leave' => $leave,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Update leave request.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $this->authorizePermission('leaves.edit');

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update($validated);

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->authorizePermission('leaves.approve');

        // Managers can only approve their supervised team; Admin and HR can approve any department, manager, or employee
        if (! $request->user()->isAdmin() && ! $request->user()->isHr()) {
            $currentUser = $request->user();
            $applicant = $leave->user;

            // A manager's leave is HR's to approve. A peer manager must never
            // approve it, however close they are.
            abort_if(
                $applicant?->isManager(),
                403,
                'A manager\'s leave can only be approved by HR.'
            );

            $isSupervised = $applicant->manager_id === $currentUser->id
                || ($currentUser->department && $applicant->department === $currentUser->department && $applicant->id !== $currentUser->id);
            abort_unless($isSupervised, 403, 'You can only approve leaves for your department or supervised team members.');
        }

        if ($leave->status === 'approved') {
            return redirect()->back()->with('info', 'Leave request is already approved.');
        }

        $remarks = $request->string('remarks')->trim()->value();
        $year = Carbon::parse($leave->start_date)->year;
        $balance = EmployeeLeaveBalance::getOrCreateForUser($leave->user_id, $leave->leave_type_id, $year);

        DB::transaction(function () use ($leave, $balance, $remarks) {
            $totalDays = (float) $leave->total_days;

            if ($leave->status === 'pending') {
                $balance->pending_days = max(0, (float) $balance->pending_days - $totalDays);
            }

            $balance->used_days = (float) $balance->used_days + $totalDays;
            $balance->recompute()->save();

            $leave->update([
                'status' => 'approved',
                'action_by' => auth()->id(),
                'action_at' => now(),
                'admin_remarks' => $remarks ?: $leave->admin_remarks,
            ]);

            $leave->syncToAttendance();
        });

        // Send approval notification email to the employee from hr@iconicpages.com
        app(\App\Services\LeaveEmailService::class)->sendApprovalEmail($leave->fresh(), auth()->id());

        // Notify the applicant in the app. This is one of the two notifications
        // an employee receives, so it must not be skipped.
        app(\App\Services\NotificationDispatcher::class)
            ->leaveApproved($leave->fresh(), auth()->user());

        return redirect()->back()->with('success', "Leave request for {$leave->user->name} has been approved.");
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizePermission('leaves.approve');

        // Managers can only reject their supervised team; Admin and HR can reject any department, manager, or employee
        if (! $request->user()->isAdmin() && ! $request->user()->isHr()) {
            $currentUser = $request->user();
            $applicant = $leave->user;

            // A manager's leave is HR's to decide on, same as approval.
            abort_if(
                $applicant?->isManager(),
                403,
                'A manager\'s leave can only be approved by HR.'
            );

            $isSupervised = $applicant->manager_id === $currentUser->id
                || ($currentUser->department && $applicant->department === $currentUser->department && $applicant->id !== $currentUser->id);
            abort_unless($isSupervised, 403, 'You can only reject leaves for your department or supervised team members.');
        }

        if ($leave->status === 'rejected') {
            return redirect()->back()->with('info', 'Leave request is already rejected.');
        }

        $remarks = $request->string('remarks')->trim()->value();
        $year = Carbon::parse($leave->start_date)->year;
        $balance = EmployeeLeaveBalance::getOrCreateForUser($leave->user_id, $leave->leave_type_id, $year);

        DB::transaction(function () use ($leave, $balance, $remarks) {
            $totalDays = (float) $leave->total_days;

            if ($leave->status === 'approved') {
                $balance->used_days = max(0, (float) $balance->used_days - $totalDays);
                $balance->recompute()->save();
                $leave->revertAttendance();
            } elseif ($leave->status === 'pending') {
                $balance->pending_days = max(0, (float) $balance->pending_days - $totalDays);
                $balance->save();
            }

            $leave->update([
                'status' => 'rejected',
                'action_by' => auth()->id(),
                'action_at' => now(),
                'admin_remarks' => $remarks ?: $leave->admin_remarks,
            ]);
        });

        return redirect()->back()->with('success', "Leave request for {$leave->user->name} has been rejected.");
    }

    /**
     * Delete a leave request.
     */
    public function destroy(LeaveRequest $leave)
    {
        $this->authorizePermission('leaves.delete');

        $year = Carbon::parse($leave->start_date)->year;
        $balance = EmployeeLeaveBalance::getOrCreateForUser($leave->user_id, $leave->leave_type_id, $year);

        DB::transaction(function () use ($leave, $balance) {
            $totalDays = (float) $leave->total_days;

            if ($leave->status === 'approved') {
                $balance->used_days = max(0, (float) $balance->used_days - $totalDays);
                $balance->recompute()->save();
                $leave->revertAttendance();
            } elseif ($leave->status === 'pending') {
                $balance->pending_days = max(0, (float) $balance->pending_days - $totalDays);
                $balance->save();
            }

            $leave->delete();
        });

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave record removed successfully.');
    }

    /**
     * Leave balances grid for all employees.
     */
    public function balances(Request $request)
    {
        $this->authorizePermission('leaves.view');

        $year = (int) ($request->input('year') ?: now()->year);
        $department = $request->string('department')->trim()->value();
        $search = $request->string('search')->trim()->value();

        // Ensure balances exist for current year
        EmployeeLeaveBalance::initializeForYear($year);

        $employeesQuery = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->with(['leaveBalances' => fn ($q) => $q->where('year', $year)->with('leaveType')])
            ->orderBy('name');

        $currentUser = $request->user();
        $isManager = ! $currentUser->isAdmin() && ! $currentUser->isHr();

        if ($isManager) {
            // Managers can only see the leave balances of their own department
            $managedDepts = Department::where('manager_id', $currentUser->id)->pluck('name')->all();
            if ($currentUser->department && ! in_array($currentUser->department, $managedDepts, true)) {
                $managedDepts[] = $currentUser->department;
            }

            $employeesQuery->where(function ($q) use ($managedDepts, $currentUser) {
                if (! empty($managedDepts)) {
                    $q->where(function ($sub) use ($managedDepts) {
                        foreach ($managedDepts as $idx => $d) {
                            if ($idx === 0) {
                                $sub->whereRaw('LOWER(department) = ?', [strtolower($d)]);
                            } else {
                                $sub->orWhereRaw('LOWER(department) = ?', [strtolower($d)]);
                            }
                        }
                    });
                } else {
                    $q->where('manager_id', $currentUser->id);
                }
            });

            // Restrict department options to manager's department(s)
            $departments = ! empty($managedDepts) ? $managedDepts : [];
            $selectedDepartment = count($departments) === 1
                ? $departments[0]
                : ($department && in_array(strtolower($department), array_map('strtolower', $departments), true) ? $department : ($departments[0] ?? ''));

            if ($selectedDepartment && count($departments) > 1) {
                $employeesQuery->whereRaw('LOWER(department) = ?', [strtolower($selectedDepartment)]);
            }
        } else {
            if ($department) {
                $employeesQuery->where('department', $department);
            }
            $selectedDepartment = $department;
            $departments = Department::names(activeOnly: false);
        }

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('admin.leaves.balances', [
            'year' => $year,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
            'isManager' => $isManager,
            'search' => $search,
        ]);
    }

    /**
     * Update leave balance allocation for an employee.
     */
    public function updateBalance(Request $request, EmployeeLeaveBalance $balance)
    {
        $this->authorizePermission('leaves.settings');

        $currentUser = $request->user();
        if (! $currentUser->isAdmin() && ! $currentUser->isHr()) {
            $managedDepts = Department::where('manager_id', $currentUser->id)->pluck('name')->all();
            if ($currentUser->department) {
                $managedDepts[] = $currentUser->department;
            }
            $managedDepts = array_map('strtolower', $managedDepts);
            $targetDept = strtolower((string) $balance->user->department);

            if (! in_array($targetDept, $managedDepts, true) && $balance->user->manager_id !== $currentUser->id) {
                abort(403, 'You can only update leave balances for employees in your department.');
            }
        }

        $validated = $request->validate([
            'allocated_days' => ['required', 'numeric', 'min:0', 'max:365'],
        ]);

        $balance->allocated_days = $validated['allocated_days'];
        $balance->recompute()->save();

        return redirect()->back()->with('success', "Leave balance updated for {$balance->user->name}.");
    }

    /**
     * Manage leave types.
     */
    public function types()
    {
        $this->authorizePermission('leaves.settings');

        $leaveTypes = LeaveType::withCount('requests')->get();

        return view('admin.leaves.types', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Store new leave type.
     */
    public function storeType(Request $request)
    {
        $this->authorizePermission('leaves.settings');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:leave_types,code'],
            'days_allowed_per_year' => ['required', 'numeric', 'min:0', 'max:365'],
            'is_paid' => ['required', 'boolean'],
        ]);

        LeaveType::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'days_allowed_per_year' => $validated['days_allowed_per_year'],
            'is_paid' => (bool) $validated['is_paid'],
            'requires_approval' => true,
            'is_active' => true,
        ]);

        return redirect()->route('admin.leaves.types')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Update existing leave type and optionally sync current year's employee balances.
     */
    public function updateType(Request $request, LeaveType $type)
    {
        $this->authorizePermission('leaves.settings');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'days_allowed_per_year' => ['required', 'numeric', 'min:0', 'max:365'],
            'is_paid' => ['required', 'boolean'],
            'update_existing_balances' => ['nullable', 'boolean'],
        ]);

        $newDays = (float) $validated['days_allowed_per_year'];

        $type->update([
            'name' => $validated['name'],
            'days_allowed_per_year' => $newDays,
            'is_paid' => (bool) $validated['is_paid'],
        ]);

        // If requested, update current year's employee balances with the new quota
        if (! empty($validated['update_existing_balances'])) {
            $year = now()->year;
            $balances = EmployeeLeaveBalance::where('leave_type_id', $type->id)
                ->where('year', $year)
                ->get();

            foreach ($balances as $balance) {
                $balance->allocated_days = $newDays;
                $balance->recompute()->save();
            }
        }

        return redirect()->route('admin.leaves.types')
            ->with('success', "Leave type {$type->name} ({$type->code}) updated to {$newDays} days/year.");
    }
}
