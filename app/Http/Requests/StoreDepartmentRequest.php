<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rbac\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for creating a department.
 */
class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('departments.create') ?? false;
    }

    public function rules(): array
    {
        return [
            // Free text, not a fixed list: admins can add departments. Stored
            // lower-cased by prepareForValidation so casing never causes a
            // duplicate or a mismatch against users.department.
            'name' => [
                'required', 'string', 'min:2', 'max:100',
                'regex:/^[A-Za-z][A-Za-z0-9 _\&\-]*$/',
                Rule::unique('departments', 'name'),
            ],
            'description' => ['nullable', 'string', 'max:255'],

            /*
             | Required: every department has a manager. The dropdown offers only
             | eligible users, so this catches a forged or empty submission
             | rather than ordinary use.
             */
            'manager_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'name' => Str::of($this->input('name'))->trim()->squish()->lower()->value(),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $managerId = $this->input('manager_id');

            if ($managerId === null) {
                // Already failed the "required" rule; nothing more to say.
                return;
            }

            $manager = User::find($managerId);

            if (! $manager) {
                return;
            }

            // Same rule as the Users page Manager dropdown: only active admins
            // and managers may head a department.
            $isEligible = $manager->is_active
                && ($manager->hasRole(Rbac::ADMIN_ROLE) || $manager->hasRole('manager'));

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
            'name.required' => 'Please enter a department name.',
            'name.unique' => 'That department already exists.',
            'name.regex' => 'Use letters, numbers, spaces, & or - only.',
            'manager_id.required' => 'Every department needs a manager.',
            'manager_id.exists' => 'The selected manager does not exist.',
        ];
    }
}