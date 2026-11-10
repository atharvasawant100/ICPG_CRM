<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to an employee when HR or a manager approves their leave request.
 *
 * One of the two notification types an employee is allowed to receive.
 */
class LeaveApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected LeaveRequest $leave,
        protected ?string $approvedByName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $leave = $this->leave;
        $approver = $this->approvedByName ?: ($leave->approver?->name ?? 'Your approver');
        $leaveType = $leave->leaveType?->name ?? 'Leave';

        $days = (float) $leave->total_days;
        $dayLabel = $days == 1 ? '1 day' : rtrim(rtrim(number_format($days, 1), '0'), '.') . ' days';

        return [
            'type' => 'leave_approved',
            'leave_id' => $leave->id,
            'message' => "Your {$leaveType} request ({$dayLabel}, {$leave->start_date->format('d M Y')}) has been approved by {$approver}.",
            'url' => route('my-leaves.index'),
        ];
    }
}
