<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Shared plumbing for every outgoing email: the queue name, the local
 * development fallback, and the hand-off to the mail transport.
 *
 * The per-domain services (InvoiceEmailService, PayrollEmailService) decide
 * *what* to send and build the log row; this one only decides *how* it leaves
 * the application, so the two cannot drift apart on retry or transport rules.
 */
class EmailLogService
{
    /**
     * The mailer actually used for a send.
     *
     * Local development defaults to the log driver so a half-configured SMTP
     * host cannot turn a demo into a wall of auth failures. MAIL_FORCE_SMTP=true
     * opts back into real delivery.
     */
    public function mailer(?Mailable $mailable = null): string
    {
        $forceSmtp = filter_var(env('MAIL_FORCE_SMTP', false), FILTER_VALIDATE_BOOL);

        if (app()->environment('local') && ! $forceSmtp) {
            return 'log';
        }

        if ($mailable instanceof \App\Mail\PayslipMail && config('mail.mailers.payroll_smtp')) {
            return 'payroll_smtp';
        }

        if ($mailable instanceof \App\Mail\InvoiceMail && config('mail.mailers.billing_smtp')) {
            return 'billing_smtp';
        }

        if ($mailable instanceof \App\Mail\LeaveApprovedMail && config('mail.mailers.hr_smtp')) {
            return 'hr_smtp';
        }

        return config('mail.default');
    }

    /**
     * Hand a built message to the transport.
     *
     * Called only from a queued job, which owns the log bookkeeping.
     */
    public function deliver(Mailable $mailable): void
    {
        Mail::mailer($this->mailer($mailable))->send($mailable);
    }
}
