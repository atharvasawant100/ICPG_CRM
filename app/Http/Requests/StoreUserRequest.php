<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating a user.
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
            'role' => ['required', 'string', Rule::in(Rbac::ROLES)],
            'department' => ['nullable', 'string', Rule::in(['sales', 'marketing', 'development', 'hr'])],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = app(UserManagementService::class);
            $role = $this->input('role');
            $department = $this->input('department');
            $managerId = $this->input('manager_id');

            // Rule 4: department required for the employee departments, optional
            // for admin and manager.
            $requiresDepartment = ! in_array($role, [Rbac::ADMIN_ROLE, 'manager'], true);

            if ($requiresDepartment && blank($department)) {
                $validator->errors()->add(
                    'department',
                    'A department is required for the '.str_replace('_', ' ', (string) $role).' role.'
                );
            }

            if (in_array($department, ['sales', 'marketing', 'development', 'hr'], true)
                && in_array($role, [Rbac::ADMIN_ROLE, 'manager'], true)) {
                $validator->errors()->add(
                    'department',
                    'Admin and manager roles do not use a department. Leave it unset.'
                );
            }

            // Rule 3: no self-management, no loops.
            if ($managerId !== null && (int) $managerId === (int) optional($this->user())->id) {
                $validator->errors()->add('manager_id', 'You cannot be your own manager.');
            }

            if ($managerId !== null && $service->wouldCreateManagerLoop(
                $this->route('user') ?? new User,
                (int) $managerId
            )) {
                $validator->errors()->add('manager_id', 'That choice would create a reporting loop.');
            }
        });
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
            'role.required' => 'Please choose a role.',
            'role.in' => 'That role does not exist.',
            'manager_id.exists' => 'The selected manager does not exist.',
        ];
    }
}