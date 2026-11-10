<?php

namespace App\Mail;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadMeetingRescheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public LeadFollowup $followup,
        public User $host,
        public ?string $previousDate = null,
    ) {
    }

    public function build(): self
    {
        $fromAddress = config('mail.from.address', 'sales@iconicpages.com');
        $fromName = config('mail.from.name', config('app.name', 'Iconic Pages Sales'));

        $this->subject($this->subjectLine())
            ->from($fromAddress, $fromName)
            ->to($this->lead->email, $this->lead->name);

        if (! empty($this->host->email)) {
            $this->replyTo($this->host->email, $this->host->name);
        }

        return $this->view('emails.leads.rescheduled', [
            'lead' => $this->lead,
            'followup' => $this->followup,
            'host' => $this->host,
            'previousDate' => $this->previousDate,
            'meetingLink' => $this->followup->meeting_link,
            'dateFormatted' => $this->followup->followup_date?->format('l, F j, Y \a\t g:i A') ?? 'New Scheduled Time',
        ]);
    }

    public function subjectLine(): string
    {
        $date = $this->followup->followup_date?->format('M d, Y') ?? '';
        return "Updated: Meeting Rescheduled with {$this->host->name}" . ($date ? " ({$date})" : '');
    }
}
