<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rate' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one-time,weekly,monthly,quarterly,yearly,custom'],
            'custom_interval_days' => ['nullable', 'required_if:frequency,custom', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],

        ];
    }
}
