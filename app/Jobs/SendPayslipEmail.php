<?php

namespace App\Jobs;

use App\Mail\PayslipMail;
use App\Models\EmailLog;
use App\Models\Payroll;
use Illuminate\Mail\Mailable;

/**
 * Delivers a payslip to an employee once their payroll is marked paid.
 */
class SendPayslipEmail extends SendQueuedEmail
{
    protected function buildMailable(EmailLog $log): Mailable
    {
        $payroll = $this->resolveRecord($log, Payroll::class, 'payroll_id');

        return new PayslipMail(
            $payroll->loadMissing(['user.salary', 'creator']),
            (bool) ($log->context['attach_pdf'] ?? true),
        );
    }

    /**
     * Stamped only after the transport accepted the message, so the payslip page
     * never claims the employee was emailed when the mail bounced.
     */
    protected function afterSent(EmailLog $log): void
    {
        $payroll = $this->resolveRecord($log, Payroll::class, 'payroll_id');

        $payroll->forceFill([
            'payslip_sent_at' => now(),
            'payslip_sent_count' => (int) ($payroll->payslip_sent_count ?? 0) + 1,
        ])->save();
    }
}
