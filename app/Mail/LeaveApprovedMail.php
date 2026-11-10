<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Leave approval notification email sent to the employee from hr@iconicpages.com.
 */
class LeaveApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
    ) {
    }

    public function build(): self
    {
        $fromAddress = config('mail.hr_from.address') ?: 'hr@iconicpages.com';
        $fromName = config('mail.hr_from.name', 'ICPG HR Department');
        $replyTo = config('mail.hr_from.reply_to', 'hr@iconicpages.com');

        $this->subject($this->subjectLine())
            ->from($fromAddress, $fromName)
            ->to($this->leaveRequest->user->email, $this->leaveRequest->user->name);

        if ($replyTo) {
            $this->replyTo($replyTo, $fromName);
        }

        return $this->view('emails.leave-approved', [
            'leave' => $this->leaveRequest,
            'user' => $this->leaveRequest->user,
            'approver' => $this->leaveRequest->approver,
            'leaveType' => $this->leaveRequest->leaveType,
        ]);
    }

    public function subjectLine(): string
    {
        $typeName = $this->leaveRequest->leaveType?->name ?? 'Leave';
        $startDate = $this->leaveRequest->start_date?->format('M d, Y');
        $endDate = $this->leaveRequest->end_date?->format('M d, Y');

        $dates = ($this->leaveRequest->start_date && $this->leaveRequest->end_date && $this->leaveRequest->start_date->ne($this->leaveRequest->end_date))
            ? "{$startDate} - {$endDate}"
            : $startDate;

        return "Leave Request Approved: {$typeName} ({$dates})";
    }
}
