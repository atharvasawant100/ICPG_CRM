<?php

namespace App\Http\Requests;

use App\Models\ContentPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ContentPost::class);
    }

    public function rules(): array
    {
        return [
            'brand_id'          => 'required|exists:brands,id',
            'client_id'         => 'nullable|exists:customers,id',
            'title'             => 'required|string|max:200',
            'post_type'         => ['required', 'string', Rule::in(array_keys(ContentPost::POST_TYPES))],
            'caption'           => 'nullable|string',
            'hashtags'          => 'nullable|string|max:1000',
            'scheduled_at'      => 'nullable|date',
            'timezone'          => 'nullable|string|max:60',
            'assigned_to'       => 'nullable|exists:users,id',
            'reviewer_id'       => 'nullable|exists:users,id',
            'internal_notes'    => 'nullable|string|max:2000',
            'approval_required' => 'nullable|boolean',
            'status'            => ['nullable', 'string', Rule::in(array_keys(ContentPost::STATUSES))],
            'platforms'         => 'nullable|array',
            'platforms.*'       => 'string',
            'media'             => 'nullable|array',
            'media.*'           => 'file|max:51200|mimes:png,jpg,jpeg,webp,svg,gif,mp4,mov,avi,webm,pdf',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('assigned_to')) {
                $target = \App\Models\User::find($this->input('assigned_to'));
                if ($target) {
                    $isMarketing = strtolower((string) $target->department) === 'marketing'
                        || $target->hasRole('marketing')
                        || $target->hasRole('marketing_manager')
                        || str_contains(strtolower((string) $target->designation), 'marketing');

                    if (! $isMarketing) {
                        $validator->errors()->add('assigned_to', 'The assigned employee must belong to the Marketing department.');
                    }

                    if (! $this->user()->isManager() && ! $this->user()->isAdmin()) {
                        if ($target->isManager() || $target->isAdmin()) {
                            $validator->errors()->add('assigned_to', 'Employees cannot assign content posts or tasks to managers. Please assign to yourself or a fellow team member.');
                        }
                    }
                }
            }
        });
    }
}

