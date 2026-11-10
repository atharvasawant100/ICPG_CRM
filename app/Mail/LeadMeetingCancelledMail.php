<?php

namespace App\Mail;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadMeetingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public LeadFollowup $followup,
        public User $host,
        public ?string $reason = null,
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

        return $this->view('emails.leads.cancelled', [
            'lead' => $this->lead,
            'followup' => $this->followup,
            'host' => $this->host,
            'reason' => $this->reason,
            'dateFormatted' => $this->followup->followup_date?->format('l, F j, Y \a\t g:i A') ?? 'Scheduled Time',
        ]);
    }

    public function subjectLine(): string
    {
        return "Notice: Meeting Cancelled with {$this->host->name}";
    }
}
