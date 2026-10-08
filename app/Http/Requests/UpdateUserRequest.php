<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for updating a user.
 *
 * Carries the four self/last-admin protections (rules 1-3 of the spec).
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.edit') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            // Optional on edit: leave blank to keep the current password.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(Rbac::ROLES)],
            'department' => ['nullable', 'string', Rule::in(['sales', 'marketing', 'development', 'hr'])],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $target */
            $target = $this->route('user');
            /** @var User|null $actor */
            $actor = $this->user();
            $service = app(UserManagementService::class);

            $newRole = (string) $this->input('role');
            $stillAdmin = $target->hasRole(Rbac::ADMIN_ROLE) && $newRole === Rbac::ADMIN_ROLE;
            $staysActive = $this->boolean('is_active', true);

            // Rule 1: an admin may not demote, deactivate or delete themselves.
            if ($actor && $actor->id === $target->id) {
                if ($newRole !== Rbac::ADMIN_ROLE) {
                    $validator->errors()->add('role', 'You cannot change your own role.');
                }

                if (! $staysActive) {
                    $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
                }
            }

            // Rule 2: never orphan the last active admin.
            if ($target->hasRole(Rbac::ADMIN_ROLE) && (! $stillAdmin || ! $staysActive)) {
                if ($service->wouldOrphanAdmins($target, false)) {
                    $validator->errors()->add(
                        'role',
                        'This is the last active admin. Promote another admin first.'
                    );
                }
            }

            // Rule 4: department required for employee departments.
            $requiresDepartment = ! in_array($newRole, [Rbac::ADMIN_ROLE, 'manager'], true);

            if ($requiresDepartment && blank($this->input('department'))) {
                $validator->errors()->add(
                    'department',
                    'A department is required for the '.str_replace('_', ' ', $newRole).' role.'
                );
            }

            if (in_array($this->input('department'), ['sales', 'marketing', 'development', 'hr'], true)
                && in_array($newRole, [Rbac::ADMIN_ROLE, 'manager'], true)) {
                $validator->errors()->add(
                    'department',
                    'Admin and manager roles do not use a department. Leave it unset.'
                );
            }

            // Rule 3: self-management and reporting loops.
            $managerId = $this->input('manager_id');

            if ($managerId !== null && (int) $managerId === (int) $target->id) {
                $validator->errors()->add('manager_id', 'A user cannot be their own manager.');
            }

            if ($managerId !== null && $service->wouldCreateManagerLoop($target, (int) $managerId)) {
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
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
            'role.required' => 'Please choose a role.',
            'role.in' => 'That role does not exist.',
            'manager_id.exists' => 'The selected manager does not exist.',
        ];
    }
}