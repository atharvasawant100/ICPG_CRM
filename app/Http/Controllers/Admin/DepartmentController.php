<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Rbac\Designation;
use App\Rbac\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Departments: list, view the team, edit, and create.
 *
 * The list deliberately has no Manager column and no inline dropdown. Editing
 * happens on its own page so the table stays scannable, and the index only
 * needs the read permission.
 */
class DepartmentController extends Controller
{
    use AuthorizesByPermission;

    /**
     * List departments.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('departments.view');

        $selectedDept = $request->string('dept')->trim()->value();

        $departments = Department::query()
            ->with([
                'manager:id,name,email,emp_id,is_active,designation',
                'users' => fn ($q) => $q->with(['roles:id,name', 'manager:id,name'])->orderBy('name'),
            ])
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $totalMembers = $departments->sum('users_count');
        $departmentsWithoutManager = $departments->whereNull('manager_id')->count();

        return view('admin.departments.index', [
            'departments' => $departments,
            'selectedDept' => $selectedDept,
            'totalMembers' => $totalMembers,
            'departmentsWithoutManager' => $departmentsWithoutManager,
            // Only needed for the create panel, and only for someone who can
            // create. A read-only page never needs the candidate list.
            'managers' => $this->canCreate() ? Department::managerCandidates() : collect(),
        ]);
    }

    /**
     * Show the department create form.
     */
    public function create()
    {
        $this->authorizePermission('departments.create');

        return view('admin.departments.create', [
            'managers' => Department::managerCandidates(),
        ]);
    }

    /**
     * Show one department and the team in it.
     */
    public function show(Department $department)
    {
        $this->authorizePermission('departments.view');

        $department->load('manager:id,name,email,is_active');

        /*
         | The team is everyone whose users.department matches this department's
         | name. Ordered with the manager first so the page reads top-down, and
         | with inactive users kept but visibly marked rather than hidden: a
         | deactivated colleague is still part of the department.
         */
        $team = $department->users()
            ->with('roles:id,name')
            ->orderByRaw('case when id = ? then 0 else 1 end', [$department->manager_id])
            ->orderBy('name')
            ->get();

        return view('admin.departments.show', [
            'department' => $department,
            'team' => $team,
            'teamCount' => $team->count(),
            'activeCount' => $team->where('is_active', true)->count(),
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(Department $department)
    {
        $this->authorizePermission('departments.edit');

        return view('admin.departments.edit', [
            'department' => $department,
            'managers' => Department::managerCandidates(),
        ]);
    }

    /**
     * Create a department and its corresponding Spatie role and designations.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $this->authorizePermission('departments.create');

        $data = $request->validated();

        $designations = $this->parseDesignationsInput($request->input('designations'));

        $department = Department::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'designations' => $designations,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Automate role creation in Spatie Permission
        $role = $this->ensureDepartmentRoleExists($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department '{$department->name}' created successfully with security role '{$role->name}'.");
    }

    /**
     * Change a department's manager, description, designations, or active flag.
     *
     * The name is not editable: users.department stores it as a string, so
     * renaming would strand every member of the department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->authorizePermission('departments.edit');

        $data = $request->validated();

        $updateData = [
            'description' => $data['description'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->has('designations')) {
            $updateData['designations'] = $this->parseDesignationsInput($request->input('designations'));
        }

        $department->fill($updateData)->save();

        // Ensure the matching role exists in the roles table
        $this->ensureDepartmentRoleExists($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department '{$department->name}' updated successfully.");
    }

    /**
     * Ensure a Spatie role matching the department exists and has base employee permissions.
     */
    private function ensureDepartmentRoleExists(Department $department): Role
    {
        $roleName = strtolower(trim($department->name));

        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => Rbac::GUARD,
        ]);

        // Give base permissions same as Development and Marketing
        $basePermissions = [
            'dashboard.view',
            'tasks.view',
            'my_attendance.view',
            'my_leaves.view',
            'my_leaves.create',
            'my_expenses.view',
            'my_expenses.create',
        ];

        $devRole = Role::where('name', 'development')->where('guard_name', Rbac::GUARD)->first();
        if ($devRole && $devRole->permissions->isNotEmpty()) {
            $permissions = array_unique(array_merge($basePermissions, $devRole->permissions->pluck('name')->all()));
        } else {
            $permissions = $basePermissions;
        }

        // Only assign if the role has no permissions yet, so we don't overwrite admin custom edits
        if ($role->permissions()->count() === 0) {
            $validPermissions = array_values(array_intersect($permissions, Rbac::permissions()));
            $role->syncPermissions($validPermissions);
        }

        // Seed default data scopes for this role if role_module_scopes table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('role_module_scopes')) {
            $scopedModules = array_keys(config('rbac.owner_columns', []));
            foreach ($scopedModules as $module) {
                DB::table('role_module_scopes')->updateOrInsert(
                    ['role_name' => $roleName, 'module' => $module],
                    ['scope' => 'own', 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * Parse comma-separated designations into a slug => label array.
     * Always ensures 'manager' is included.
     *
     * @return array<string, string>
     */
    private function parseDesignationsInput(?string $input): array
    {
        $result = [];
        if ($input !== null && trim($input) !== '') {
            $parts = explode(',', $input);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $slug = strtolower($trimmed);
                    $result[$slug] = $trimmed;
                }
            }
        }

        if (empty($result)) {
            $result = [
                'manager' => 'Manager',
                'team leader' => 'Team Leader',
                'executive' => 'Executive',
                'intern' => 'Intern',
            ];
        }

        if (! isset($result['manager'])) {
            $result = array_merge(['manager' => 'Manager'], $result);
        }

        return $result;
    }

    private function canCreate(): bool
    {
        return auth()->user()?->can('departments.create') ?? false;
    }
}