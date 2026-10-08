<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoicePaymentRequest extends FormRequest
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
        $invoice = $this->route('invoice');
        $remainingAmount = $invoice ? max(0, $invoice->total_amount - $invoice->getPaidAmount()) : 999999999;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $remainingAmount,
            ],
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) use ($invoice) {
                    if ($invoice && $value < $invoice->invoice_date->toDateString()) {
                        $fail('Payment date cannot be before invoice date.');
                    }
                },
            ],
            'payment_method' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
