<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the approver(s) when somebody submits a leave request.
 *
 * A department manager receives this for their own team only. HR receives it
 * for the entire company.
 */
class LeaveRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected LeaveRequest $leave,
        protected ?string $applicantName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $leave = $this->leave;
        $applicant = $this->applicantName ?: ($leave->user?->name ?? 'An employee');
        $leaveType = $leave->leaveType?->name ?? 'Leave';

        $days = (float) $leave->total_days;
        $dayLabel = $days == 1 ? '1 day' : rtrim(rtrim(number_format($days, 1), '0'), '.') . ' days';

        return [
            'type' => 'leave_requested',
            'leave_id' => $leave->id,
            'message' => "{$applicant} submitted a {$leaveType} request for {$dayLabel} ({$leave->start_date->format('d M Y')}).",
            'url' => route('admin.leaves.index'),
        ];
    }
}
