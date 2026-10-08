<?php

namespace App\Notifications;

use App\Models\LeadFollowup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowupReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected LeadFollowup $followup,
        protected int $minutesBefore
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $lead = $this->followup->lead;
        $type = ucfirst($this->followup->followup_type);

        return [
            'type' => 'followup_reminder',
            'followup_id' => $this->followup->id,
            'lead_id' => $lead?->id,
            'minutes_before' => $this->minutesBefore,
            'message' => "{$type} with " . ($lead?->name ?? 'Lead') . " in {$this->minutesBefore} min ({$this->followup->followup_date->format('h:i A')})",
            'url' => $lead ? route('leads.show', $lead) : null,
        ];
    }
}
