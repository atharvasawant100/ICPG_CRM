<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Rbac\Designation;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for creating a user.
 *
 * The form has no Role field. Access is derived: Designation::roleFor() turns
 * the chosen (department, designation) pair into the Spatie role, so this
 * request never accepts a role from the browser.
 *
 * All rules are server-side. The UI mirrors them with friendly messages, but
 * nothing here trusts the browser.
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')?->id),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'designation' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'in:'.implode(',', $this->departmentNames())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'designation' => Designation::slug($this->input('designation')) ?: null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $department = $this->input('department') ?: null;
            $designation = $this->input('designation') ?: null;

            // A designation without a department cannot resolve to a role,
            // because the department is what decides the employee access level.
            if ($designation !== null && $department === null) {
                $validator->errors()->add(
                    'department',
                    'Choose a department for this designation.'
                );

                return;
            }

            // A designation from another department's list must not slip
            // through just because it happens to exist somewhere in the config.
            if ($designation !== null && $department !== null) {
                $allowed = array_keys(Designation::forDepartment($department));

                if (! in_array($designation, $allowed, true)) {
                    $validator->errors()->add(
                        'designation',
                        'That designation is not offered for this department.'
                    );
                }
            }
        });
    }

    /**
     * The Spatie role this submission resolves to, or null when the user should
     * be created without one.
     *
     * Null happens when neither field was filled in: an unconfigured user is
     * safer than one silently given a department's access.
     */
    public function resolvedRole(): ?string
    {
        $department = $this->input('department') ?: null;
        $designation = $this->input('designation') ?: null;

        if ($department === null && $designation === null) {
            return null;
        }

        return Designation::roleFor($department, $designation);
    }

    /**
     * The manager derived from the department, since the form has no Manager
     * field. Keeps the "team" data scope working.
     */
    public function resolvedManagerId(): ?int
    {
        $department = $this->input('department') ?: null;

        if ($department === null) {
            return null;
        }

        return Department::query()
            ->where('name', $department)
            ->first()
            ?->managerUserId();
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'That email address is already in use.',
            'password.required' => 'A password is required for a new user.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
            'department.in' => 'That department does not exist.',
        ];
    }

    /**
     * Every department name, active or not. A user may keep a department that
     * was later deactivated, so validation must not filter on is_active.
     *
     * @return list<string>
     */
    private function departmentNames(): array
    {
        try {
            return Department::allNames();
        } catch (\Throwable) {
            return array_keys(Rbac::EMPLOYEE_ROLES);
        }
    }
}