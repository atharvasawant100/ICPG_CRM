<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringInvoiceTemplateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'frequency' => 'required|in:weekly,monthly,yearly,custom',
            'custom_interval_days' => 'required_if:frequency,custom|integer|min:1|max:365',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'items' => 'required|array|min:1|max:1000',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.1|max:999999',
            'items.*.unit_price' => 'required|numeric|min:0.01|max:999999999',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            
            // Ensure each item has either service_id or description
            foreach ($items as $index => $item) {
                if (empty($item['service_id']) && empty($item['description'])) {
                    $validator->errors()->add("items.{$index}.description", 'Either service or description is required for each item.');
                }
            }
        });
    }
}
