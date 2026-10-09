<?php

namespace App\Http\Requests\Concerns;

use App\Models\Department;
use App\Rbac\Rbac;
use Illuminate\Validation\Rule;

/**
 * Department rules shared by the create and edit user requests.
 *
 * The allowed values come from the departments table, not a hard-coded list, so
 * an admin can add a department on the Departments page and it becomes
 * immediately assignable here.
 */
trait ValidatesUserDepartment
{
    /**
     * Validation rules for the department field.
     *
     * @return array<int, mixed>
     */
    protected function departmentRules(): array
    {
        // Fall back to the original four if the table is somehow unavailable,
        // so a request never hard-fails with a database error.
        $names = $this->departmentNames();

        return [
            'nullable',
            'string',
            Rule::in($names),
        ];
    }

    /**
     * Every department name, active or not. A user may keep a department that
     * was later deactivated, so validation must not filter on is_active.
     *
     * @return list<string>
     */
    protected function departmentNames(): array
    {
        try {
            return Department::allNames();
        } catch (\Throwable) {
            return ['sales', 'marketing', 'development', 'hr'];
        }
    }

    /**
     * Enforce the two department/role rules:
     *  - employee roles must have one
     *  - admin and manager must not have one
     */
    protected function applyDepartmentRules(
        \Illuminate\Validation\Validator $validator,
        ?string $role,
        mixed $department
    ): void {
        $isLeadershipRole = in_array($role, [Rbac::ADMIN_ROLE, 'manager'], true);

        if (! $isLeadershipRole && blank($department)) {
            $validator->errors()->add(
                'department',
                'A department is required for the '.str_replace('_', ' ', (string) $role).' role.'
            );
        }

        if ($isLeadershipRole && filled($department)) {
            $validator->errors()->add(
                'department',
                'Admin and manager roles do not use a department. Leave it unset.'
            );
        }
    }
}