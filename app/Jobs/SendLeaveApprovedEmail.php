<?php

namespace App\Jobs;

use App\Mail\LeaveApprovedMail;
use App\Models\EmailLog;
use App\Models\LeaveRequest;
use Illuminate\Mail\Mailable;

/**
 * Delivers a leave approval confirmation email to an employee when HR approves their leave.
 */
class SendLeaveApprovedEmail extends SendQueuedEmail
{
    protected function buildMailable(EmailLog $log): Mailable
    {
        $leave = $this->resolveRecord($log, LeaveRequest::class, 'leave_request_id');

        return new LeaveApprovedMail(
            $leave->loadMissing(['user', 'leaveType', 'approver'])
        );
    }
}
