<?php

namespace App\Services;

use App\Mail\LeadMeetingCancelledMail;
use App\Mail\LeadMeetingInviteMail;
use App\Mail\LeadMeetingRescheduledMail;
use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadFollowupNotificationService
{
    /**
     * Send meeting invite to lead and log result.
     */
    public static function sendMeetingInvite(LeadFollowup $followup, Lead $lead, User $host): void
    {
        if (empty($lead->email) || ! filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $mailable = new LeadMeetingInviteMail($lead, $followup, $host);
        $subject = $mailable->subjectLine();

        $log = EmailLog::queue(
            mailable: LeadMeetingInviteMail::class,
            type: EmailLog::TYPE_MEETING_INVITE,
            recipientEmail: $lead->email,
            recipientName: $lead->name,
            subject: $subject,
            context: [
                'lead_id' => $lead->id,
                'followup_id' => $followup->id,
                'host_id' => $host->id,
                'meeting_link' => $followup->meeting_link,
            ],
            userId: $host->id,
        );

        try {
            Mail::to($lead->email, $lead->name)->send($mailable);
            $log->markSent();
        } catch (Throwable $e) {
            Log::warning("Failed to send meeting invite email to {$lead->email}: " . $e->getMessage());
            $log->markFailed($e);
        }
    }

    /**
     * Send reschedule notification to lead.
     */
    public static function sendMeetingRescheduled(LeadFollowup $followup, Lead $lead, User $host, ?string $previousDate = null): void
    {
        if (empty($lead->email) || ! filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $mailable = new LeadMeetingRescheduledMail($lead, $followup, $host, $previousDate);
        $subject = $mailable->subjectLine();

        $log = EmailLog::queue(
            mailable: LeadMeetingRescheduledMail::class,
            type: EmailLog::TYPE_MEETING_UPDATE,
            recipientEmail: $lead->email,
            recipientName: $lead->name,
            subject: $subject,
            context: [
                'lead_id' => $lead->id,
                'followup_id' => $followup->id,
                'host_id' => $host->id,
                'meeting_link' => $followup->meeting_link,
                'previous_date' => $previousDate,
            ],
            userId: $host->id,
        );

        try {
            Mail::to($lead->email, $lead->name)->send($mailable);
            $log->markSent();
        } catch (Throwable $e) {
            Log::warning("Failed to send meeting reschedule email to {$lead->email}: " . $e->getMessage());
            $log->markFailed($e);
        }
    }

    /**
     * Send cancellation notice to lead.
     */
    public static function sendMeetingCancelled(LeadFollowup $followup, Lead $lead, User $host, ?string $reason = null): void
    {
        if (empty($lead->email) || ! filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $mailable = new LeadMeetingCancelledMail($lead, $followup, $host, $reason);
        $subject = $mailable->subjectLine();

        $log = EmailLog::queue(
            mailable: LeadMeetingCancelledMail::class,
            type: EmailLog::TYPE_MEETING_CANCEL,
            recipientEmail: $lead->email,
            recipientName: $lead->name,
            subject: $subject,
            context: [
                'lead_id' => $lead->id,
                'followup_id' => $followup->id,
                'host_id' => $host->id,
                'reason' => $reason,
            ],
            userId: $host->id,
        );

        try {
            Mail::to($lead->email, $lead->name)->send($mailable);
            $log->markSent();
        } catch (Throwable $e) {
            Log::warning("Failed to send meeting cancellation email to {$lead->email}: " . $e->getMessage());
            $log->markFailed($e);
        }
    }
}
