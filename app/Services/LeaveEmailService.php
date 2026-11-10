<?php

namespace App\Services;

use App\Jobs\SendLeaveApprovedEmail;
use App\Mail\LeaveApprovedMail;
use App\Models\EmailLog;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;

/**
 * Handles emailing leave approval notifications to employees from hr@iconicpages.com.
 */
class LeaveEmailService
{
    /**
     * Send or queue approval email to the employee.
     *
     * @return EmailLog|null null when employee has no email address
     */
    public function sendApprovalEmail(LeaveRequest $leave, ?int $approverUserId = null): ?EmailLog
    {
        $employee = $leave->user;

        if (! $employee?->email) {
            Log::warning('Leave approval email skipped: employee has no email address', [
                'leave_id' => $leave->id,
                'user_id' => $employee?->id,
            ]);

            return null;
        }

        $mailable = new LeaveApprovedMail($leave->loadMissing(['user', 'leaveType', 'approver']));

        $log = EmailLog::queue(
            mailable: LeaveApprovedMail::class,
            type: EmailLog::TYPE_LEAVE_APPROVED,
            recipientEmail: $employee->email,
            recipientName: $employee->name,
            subject: $mailable->subjectLine(),
            context: [
                'leave_request_id' => $leave->id,
                'user_id' => $employee->id,
                'leave_type' => $leave->leaveType?->name,
                'start_date' => $leave->start_date?->toDateString(),
                'end_date' => $leave->end_date?->toDateString(),
                'total_days' => (float) $leave->total_days,
                'approved_by' => $approverUserId ?? auth()->id(),
            ],
            userId: $approverUserId ?? auth()->id(),
        );

        try {
            SendLeaveApprovedEmail::dispatch($log->id)->onQueue(EmailLog::QUEUE);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch SendLeaveApprovedEmail: ' . $e->getMessage(), [
                'log_id' => $log->id,
                'leave_id' => $leave->id,
            ]);
        }

        return $log;
    }
}
