<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
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
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,follow_up,interested,qualified,converted,won,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'extra_fields' => 'nullable|array',
            'extra_fields.*' => 'nullable|string|max:1000',
        ];
    }
}
