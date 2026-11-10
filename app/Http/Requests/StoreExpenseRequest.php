<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'category_id' => 'required|exists:expense_categories,id',
            'ai_tool' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // 5MB max, supports images and PDF
        ];
    }
}
