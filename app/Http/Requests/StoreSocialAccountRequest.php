<?php

namespace App\Http\Requests;

use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocialAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SocialAccount::class);
    }

    public function rules(): array
    {
        return [
            'brand_id'            => 'required|exists:brands,id',
            'platform'            => ['required', 'string', Rule::in(array_keys(SocialAccount::PLATFORMS))],
            'account_name'        => 'required|string|max:150',
            'username'            => 'nullable|string|max:150',
            'platform_account_id' => 'nullable|string|max:150',
            'profile_url'         => 'nullable|url|max:255',
            'connection_status'   => 'nullable|in:connected,disconnected,pending',
            'credentials'         => 'nullable|string|max:2000',
            'notes'               => 'nullable|string|max:1000',
            'is_active'           => 'nullable|boolean',
        ];
    }
}

