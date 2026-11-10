<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1|max:1000',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.1|max:999999',
            'items.*.unit_price' => 'required|numeric|min:0.01|max:999999999',
            'subtotal' => 'nullable|numeric|min:0|max:999999999',
            'tax_amount' => 'nullable|numeric|min:0|max:999999999',
            'discount_amount' => 'nullable|numeric|min:0|max:999999999',
            'total_amount' => 'nullable|numeric|min:0|max:999999999',
            'status' => 'nullable|in:draft,sent,partial,paid,overdue,cancelled',
            'payment_terms' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:5000',
            'department_id' => 'nullable|exists:departments,id',
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
            
            // Validate tax/discount don't exceed subtotal
            $subtotal = $this->input('subtotal', 0);
            $taxAmount = $this->input('tax_amount', 0);
            $discountAmount = $this->input('discount_amount', 0);
            
            if ($discountAmount > $subtotal) {
                $validator->errors()->add('discount_amount', 'Discount amount cannot exceed subtotal.');
            }
        });
    }
}
