<?php

namespace App\Http\Requests;

use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocialAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('socialAccount') ?? $this->route('social_account');
        return $this->user()->can('update', $account ?? SocialAccount::class);
    }

    public function rules(): array
    {
        return [
            'platform'            => ['nullable', 'string', Rule::in(array_keys(SocialAccount::PLATFORMS))],
            'account_name'        => 'sometimes|required|string|max:150',
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
