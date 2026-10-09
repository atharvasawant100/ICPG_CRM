<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use AuthorizesByPermission;

    /**
     * List departments with their manager and headcount.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('departments.view');

        $departments = Department::query()
            ->with(['manager:id,name,email'])
            ->withCount('users')
            ->orderBy('name')
            ->get();

        // The manager dropdown is built here and reused by both the add form
        // and the per-row change-manager control.
        $managerCandidates = Department::managerCandidates();

        return view('admin.departments.index', [
            'departments' => $departments,
            'managers' => $managerCandidates,
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

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Change a department's manager, description or active flag.
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

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$department->name}' updated successfully.");
    }
}