<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $brand = $this->route('brand');
        return $this->user()->can('update', $brand);
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('client_id') && ! $this->filled('customer_id')) {
            $this->merge(['customer_id' => $this->input('client_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'client_id'   => 'nullable|exists:customers,id',
            'name'        => 'required|string|max:150',
            'slug'        => 'nullable|string|max:150',
            'website'     => 'nullable|url|max:255',
            'industry'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:50',
            'primary_color' => 'nullable|string|max:50',
            'is_active'   => 'nullable|boolean',
            'logo'        => 'nullable|image|max:5120|mimes:png,jpg,jpeg,webp,svg',
        ];
    }
}
