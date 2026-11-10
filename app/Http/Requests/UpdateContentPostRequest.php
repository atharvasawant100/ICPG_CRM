<?php

namespace App\Http\Requests;

use App\Models\ContentPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');
        return $this->user()->can('update', $post);
    }

    public function rules(): array
    {
        return [
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

