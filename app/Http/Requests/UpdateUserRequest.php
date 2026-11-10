<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\User;
use App\Rbac\Designation;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for updating a user.
 *
 * Like StoreUserRequest there is no Role field: the role is derived from the
 * chosen (department, designation) pair. The self/last-admin protections still
 * apply, but they now run against the derived role.
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
            'emp_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'emp_id')->ignore($user?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            // Optional on edit: leave blank to keep the current password.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'designation' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'in:'.implode(',', $this->departmentNames())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'designation' => Designation::slug($this->input('designation')) ?: null,
        ];

        if ($this->has('emp_id')) {
            $merge['emp_id'] = ($raw = trim((string) $this->input('emp_id'))) !== '' ? strtoupper($raw) : null;
        }

        $this->merge($merge);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $target */
            $target = $this->route('user');
            /** @var User|null $actor */
            $actor = $this->user();
            $service = app(UserManagementService::class);

            $department = $this->input('department') ?: null;
            $designation = $this->input('designation') ?: null;

            $newRole = $this->resolvedRole();
            $staysActive = $this->boolean('is_active', true);

            // Rule 1: an admin may not demote or deactivate themselves.
            if ($actor && $actor->id === $target->id) {
                if ($newRole !== Rbac::ADMIN_ROLE) {
                    $validator->errors()->add(
                        'department',
                        'You cannot change your own role.'
                    );
                }

                if (! $staysActive) {
                    $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
                }
            }

            // Rule 2: never orphan the last active admin.
            $stillAdmin = $target->hasRole(Rbac::ADMIN_ROLE) && $newRole === Rbac::ADMIN_ROLE;

            if ($target->hasRole(Rbac::ADMIN_ROLE) && (! $stillAdmin || ! $staysActive)) {
                if ($service->wouldOrphanAdmins($target, false)) {
                    $validator->errors()->add(
                        'department',
                        'This is the last active admin. Promote another admin first.'
                    );
                }
            }

            // A designation needs a department to resolve against.
            if ($designation !== null && $department === null) {
                $validator->errors()->add(
                    'department',
                    'Choose a department for this designation.'
                );

                return;
            }

            if ($designation !== null && $department !== null) {
                if (! in_array($designation, array_keys(Designation::forDepartment($department)), true)) {
                    $validator->errors()->add(
                        'designation',
                        'That designation is not offered for this department.'
                    );
                }
            }

            // Rule 3: the manager is derived from the department, so the only
            // way a loop can appear is if the department's manager already sits
            // below this user in the reporting chain.
            $managerId = $this->resolvedManagerId();

            if ($managerId !== null && (int) $managerId === (int) $target->id) {
                $validator->errors()->add(
                    'department',
                    'This user manages that department, so they would report to themselves.'
                );
            }

            if ($managerId !== null && $service->wouldCreateManagerLoop($target, $managerId)) {
                $validator->errors()->add(
                    'department',
                    'This department\'s manager reports to this user, which would create a loop.'
                );
            }
        });
    }

    /**
     * The Spatie role this edit resolves to.
     *
     * Falls back to the role the user already holds when the form left both
     * fields blank, so an unrelated edit (a name typo, say) never silently
     * strips somebody's access.
     */
    public function resolvedRole(): ?string
    {
        $department = $this->input('department') ?: null;
        $designation = $this->input('designation') ?: null;

        if ($department === null && $designation === null) {
            return $this->route('user')?->getRoleNames()->first();
        }

        return Designation::roleFor($department, $designation);
    }

    /**
     * The manager derived from the department, since the form has no Manager
     * field.
     *
     * Raw on purpose: the self-reporting and loop checks live in
     * withValidator(), where every other reporting rule is enforced.
     */
    public function resolvedManagerId(): ?int
    {
        $department = $this->input('department') ?: null;

        if ($department === null) {
            return null;
        }

        // A manager leads the department and does not report to the previous manager
        if (Rbac::isManagerRole($this->resolvedRole())) {
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
            'emp_id.unique' => 'That Employee ID is already in use.',
            'name.required' => 'Please enter a name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'That email address is already in use.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
            'department.in' => 'That department does not exist.',
        ];
    }

    /**
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