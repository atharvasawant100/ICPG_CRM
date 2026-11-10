<?php

namespace App\Services;

use App\Jobs\SendPayslipEmail;
use App\Mail\PayslipMail;
use App\Models\EmailLog;
use App\Models\Payroll;
use Illuminate\Support\Facades\Log;

/**
 * Queues the payslip an employee receives when their payroll is marked paid.
 *
 * Writing the log row before the push is deliberate: if no worker is running
 * the payroll still says the payslip went out, and the Email Log page shows
 * the row sitting in `queued` instead of the employee quietly never getting it.
 */
class PayrollEmailService
{
    /**
     * Queue the payslip for a payroll record.
     *
     * @return EmailLog|null null when the employee has no address to send to
     */
    public function queuePayslip(Payroll $payroll, bool $attachPdf = true, ?int $userId = null): ?EmailLog
    {
        $employee = $payroll->user;

        if (! $employee?->email) {
            Log::warning('Payslip email skipped: employee has no email address', [
                'payroll_id' => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'user_id' => $employee?->id,
            ]);

            return null;
        }

        $log = EmailLog::queue(
            mailable: PayslipMail::class,
            type: EmailLog::TYPE_PAYSLIP,
            recipientEmail: $employee->email,
            recipientName: $employee->name,
            subject: (new PayslipMail($payroll, $attachPdf))->subjectLine(),
            context: [
                'payroll_id' => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'user_id' => $employee->id,
                'period' => $payroll->period_label,
                'net_salary' => (float) $payroll->net_salary,
                'attach_pdf' => $attachPdf,
            ],
            userId: $userId,
        );

        SendPayslipEmail::dispatch($log->id)->onQueue(EmailLog::QUEUE);

        return $log;
    }
}
