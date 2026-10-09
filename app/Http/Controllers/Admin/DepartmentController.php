<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Rbac\Designation;
use Illuminate\Http\Request;

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

        $departments = Department::query()
            ->with(['manager:id,name,email'])
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.departments.index', [
            'departments' => $departments,
            // Only needed for the create panel, and only for someone who can
            // create. A read-only page never needs the candidate list.
            'managers' => $this->canCreate() ? Department::managerCandidates() : collect(),
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
     * Create a department.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $this->authorizePermission('departments.create');

        $data = $request->validated();

        $department = Department::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Change a department's manager, description or active flag.
     *
     * The name is not editable: users.department stores it as a string, so
     * renaming would strand every member of the department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->authorizePermission('departments.edit');

        $data = $request->validated();

        $department->fill([
            'description' => $data['description'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department '{$department->name}' updated successfully.");
    }

    private function canCreate(): bool
    {
        return auth()->user()?->can('departments.create') ?? false;
    }
}