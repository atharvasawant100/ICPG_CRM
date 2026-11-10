<?php

namespace App\Http\Requests;

use App\Rbac\Rbac;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for changing a department, currently its manager.
 */
class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('departments.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:255'],

            /*
             | Required even on edit: a department must never be left without a
             | manager. The form pre-selects the current one, so this is a
             | no-op for a department that already has one.
             */
            'manager_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'designations' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $managerId = $this->input('manager_id');

            if ($managerId === null) {
                return;
            }

            $manager = User::find($managerId);

            if (! $manager) {
                return;
            }

            $isEligible = $manager->is_active
                && ($manager->hasRole(Rbac::ADMIN_ROLE) || $manager->isManager());

            if (! $isEligible) {
                $validator->errors()->add(
                    'manager_id',
                    'Choose an active user who holds the admin or manager role.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'manager_id.required' => 'Every department needs a manager.',
            'manager_id.exists' => 'The selected manager does not exist.',
        ];
    }
}