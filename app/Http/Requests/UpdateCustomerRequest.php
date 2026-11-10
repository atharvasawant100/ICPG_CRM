<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'brands' => 'nullable|string|max:1000',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'designated_person' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ];

        return $rules;
    }
}
