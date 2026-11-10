<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadFollowupRequest extends FormRequest
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
            'followup_date' => 'required|date',
            'followup_type' => 'required|in:call,email,meeting,visit,other',
            'description' => 'nullable|string|max:1000',
            'agenda' => 'nullable|string|max:2000',
            'meeting_link' => 'nullable|string|max:500',
            'meeting_platform' => 'nullable|string|in:jitsi,google_meet,zoom,custom',
            'status' => 'nullable|in:pending,completed,missed,cancelled',
            'cancellation_reason' => 'nullable|string|max:500',
            'send_email_to_lead' => 'nullable|boolean',
        ];
    }
}
