<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Task;
use App\Models\User;
use App\Rbac\Designation;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesByPermission;
    public function __construct(private readonly UserManagementService $users) {}

    /**
     * List users with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('users.view');

        $query = User::query()->with(['manager', 'roles'])->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->trim()->value()) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($department = $request->string('department')->trim()->value()) {
            $query->where('department', $department);
        }

        if ($status = $request->string('status')->trim()->value()) {
            $status === 'active'
                ? $query->where('is_active', true)
                : $query->where('is_active', false);
        }

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            // Includes inactive departments so the filter can still find them.
            'departments' => Department::names(activeOnly: false),
        ]);
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        $this->authorizePermission('users.create');

        return view('admin.users.create', $this->formOptions(null));
    }

    /**
     * Create a user.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorizePermission('users.create');

        $data = $request->validated();
        $role = $request->resolvedRole();
        $isManager = Rbac::isManagerRole($role);

        $user = User::create([
            'emp_id' => ! empty($data['emp_id']) ? $data['emp_id'] : User::nextEmpId(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            // A manager does not report to the department manager; other employees inherit it.
            'manager_id' => $isManager ? null : $request->resolvedManagerId(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        /*
         * The role is derived from (department, designation) by the request,
         * never taken from the browser. A user with neither field filled in is
         * created with no role, which means no access until an admin sets it.
         */
        if ($role !== null) {
            // Role goes through the single sync wrapper, never users.role directly.
            $this->users->assignRole($user, $role);
        }

        // When a manager is created for a department, directly assign them as the department's manager
        if ($isManager && ! empty($data['department'])) {
            $department = Department::where('name', $data['department'])->first();
            if ($department) {
                $oldManagerId = $department->manager_id;
                $department->update(['manager_id' => $user->id]);

                // Update department members whose manager was the old manager or unassigned
                User::where('department', $data['department'])
                    ->where('id', '!=', $user->id)
                    ->where(function ($q) use ($oldManagerId) {
                        $q->whereNull('manager_id')
                            ->when($oldManagerId, fn ($sub) => $sub->orWhere('manager_id', $oldManagerId));
                    })
                    ->update(['manager_id' => $user->id]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    /**
     * Display the specified employee profile.
     */
    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Admin and HR have global visibility; managers can view their department/direct reports; employees can view own profile; users with users.view or departments.view can view
        if (! $currentUser->isAdmin() && ! $currentUser->isHr() && ! $currentUser->can('users.view') && ! $currentUser->can('departments.view')) {
            $isDirectReport = ((int) $user->manager_id === (int) $currentUser->id);
            $isSameDept = ($currentUser->department && $user->department === $currentUser->department);

            if (! $isDirectReport && ! $isSameDept && (int) $currentUser->id !== (int) $user->id) {
                abort(403, 'You do not have permission to view this employee profile.');
            }
        }

        $user->load([
            'roles',
            'manager',
            'subordinates',
            'salary.submittedBy',
            'salary.approvedBy',
            'leaveBalances.leaveType',
        ]);

        $currentMonth = Carbon::today()->startOfMonth();
        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->orderBy('date', 'desc')
            ->get();

        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(15)
            ->get();

        $recentLeaves = LeaveRequest::where('user_id', $user->id)
            ->with(['leaveType', 'approver'])
            ->latest()
            ->limit(10)
            ->get();

        $tasks = Task::where('assigned_to', $user->id)
            ->with(['creator', 'workLogs'])
            ->orderByRaw("CASE 
                WHEN status = 'in_progress' THEN 1 
                WHEN status = 'pending' THEN 2 
                WHEN status = 'completed' THEN 3 
                ELSE 4 END")
            ->latest('id')
            ->limit(10)
            ->get();

        // Monthly Attendance KPI
        $presentDays = $monthlyAttendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $lateDays = $monthlyAttendances->where('status', 'late')->count();
        $absentDays = $monthlyAttendances->where('status', 'absent')->count();
        $totalMinutes = $monthlyAttendances->sum('total_minutes');
        $totalWorkedHours = sprintf('%dh %02dm', floor($totalMinutes / 60), $totalMinutes % 60);

        return view('admin.users.show', [
            'user' => $user,
            'salary' => $user->salary,
            'monthlyAttendances' => $monthlyAttendances,
            'recentAttendances' => $recentAttendances,
            'recentLeaves' => $recentLeaves,
            'tasks' => $tasks,
            'presentDays' => $presentDays,
            'lateDays' => $lateDays,
            'absentDays' => $absentDays,
            'totalWorkedHours' => $totalWorkedHours,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(User $user)
    {
        $this->authorizePermission('users.edit');

        return view('admin.users.edit', $this->formOptions($user));
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $data = $request->validated();
        $newRole = $request->resolvedRole();
        $isManager = Rbac::isManagerRole($newRole);

        $managerId = $isManager ? null : $request->resolvedManagerId();
        if ($managerId !== null && (int) $managerId === (int) $user->id) {
            $managerId = null;
        }

        $user->fill([
            'emp_id' => array_key_exists('emp_id', $data) ? ($data['emp_id'] ?: null) : $user->emp_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            'manager_id' => $managerId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Only touch the password when one was actually supplied.
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $wasActive = (bool) $user->getOriginal('is_active');
        $previousRole = $user->getRoleNames()->first();

        $user->save();

        // Role is derived from (department, designation), and always goes
        // through the wrapper, which keeps users.role in sync. Comparing
        // against the pre-update value avoids a pointless write.
        if ($newRole !== null && $previousRole !== $newRole) {
            $this->users->assignRole($user, $newRole);
        }

        // When a user is updated to manager in a department, directly assign them as the department's manager
        if ($isManager && ! empty($data['department'])) {
            $department = Department::where('name', $data['department'])->first();
            if ($department && (int) $department->manager_id !== (int) $user->id) {
                $oldManagerId = $department->manager_id;
                $department->update(['manager_id' => $user->id]);

                User::where('department', $data['department'])
                    ->where('id', '!=', $user->id)
                    ->where(function ($q) use ($oldManagerId) {
                        $q->whereNull('manager_id')
                            ->when($oldManagerId, fn ($sub) => $sub->orWhere('manager_id', $oldManagerId));
                    })
                    ->update(['manager_id' => $user->id]);
            }
        }

        // Deactivating must also end any active session.
        if ($wasActive && ! $user->is_active) {
            $this->users->revokeSessions($user);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle a user's active state.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $activate = $request->boolean('activate');

        // Rule 1: cannot deactivate yourself.
        if (! $activate && $request->user()->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Rule 2: never remove the last active admin.
        if (! $activate && $this->users->wouldOrphanAdmins($user, false)) {
            return back()->with('error', 'This is the last active admin. Promote another admin first.');
        }

        $this->users->setActive($user, $activate);

        return back()->with('success', $activate
            ? "{$user->name} reactivated successfully."
            : "{$user->name} deactivated and signed out.");
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'must_change' => ['sometimes', 'boolean'],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $this->users->resetPassword(
            $user,
            $validated['password'],
            $request->boolean('must_change')
        );

        return back()->with('success', "Password reset for {$user->name}. They have been signed out.");
    }

    /**
     * Shared select options for the create and edit forms.
     *
     * There is deliberately no roles or managers here: the form has no Role
     * field and no Manager field. Designations arrive as one map keyed by
     * department so the form can narrow the list without another request.
     */
    private function formOptions(?User $user): array
    {
        return [
            'user' => $user,
            'nextEmpId' => $user?->emp_id ?: User::nextEmpId(),
            'departments' => Department::names(),
            'designations' => Designation::map(),
            'defaultDesignations' => Designation::forDepartment(null),
        ];
    }
}
