<?php

namespace App\Services;

use App\Jobs\SendInvoiceEmail;
use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Turns "this invoice should reach the customer" into a queued job plus a log row.
 *
 * Every caller — the send button, the recurring generator, the daily overdue
 * reminder — goes through queueInvoice() or queueOverdueReminder() rather than
 * sending inline, so a slow or unreachable SMTP host never blocks a web
 * request or a scheduled command.
 *
 * A row in email_logs is written before the push. If no worker is running the
 * row simply stays `queued`, which is how a stuck queue becomes visible
 * instead of an invoice that silently never got emailed.
 */
class InvoiceEmailService
{
    /**
     * Queue the invoice email.
     *
     * @return EmailLog|null null when the customer has no address to send to
     */
    public function queueInvoice(Invoice $invoice, bool $attachPdf = true, ?int $userId = null): ?EmailLog
    {
        return $this->queue($invoice, InvoiceMail::TYPE_INVOICE, $attachPdf, $userId);
    }

    /**
     * Dispatch and send the invoice email immediately (synchronously).
     *
     * @return EmailLog|null null when customer has no email address
     */
    public function sendInvoiceDirectly(Invoice $invoice, bool $attachPdf = true, ?int $userId = null): ?EmailLog
    {
        $log = $this->queue($invoice, InvoiceMail::TYPE_INVOICE, $attachPdf, $userId);
        if ($log) {
            try {
                SendInvoiceEmail::dispatchSync($log->id);
            } catch (\Throwable $e) {
                Log::warning('Direct invoice email delivery encountered an issue; job remains in queue: ' . $e->getMessage(), [
                    'invoice_id' => $invoice->id,
                    'email_log_id' => $log->id,
                ]);
            }
        }

        return $log;
    }

    /**
     * Queue the daily overdue nudge.
     *
     * @return EmailLog|null null when the customer has no address to send to
     */
    public function queueOverdueReminder(Invoice $invoice, bool $attachPdf = true, ?int $userId = null): ?EmailLog
    {
        return $this->queue($invoice, InvoiceMail::TYPE_OVERDUE_REMINDER, $attachPdf, $userId);
    }

    /**
     * Write the log row, then push the job.
     *
     * The log row is created first on purpose: a failure to enqueue must still
     * leave a trace, otherwise the invoice looks emailed when it is not.
     */
    private function queue(Invoice $invoice, string $type, bool $attachPdf, ?int $userId): ?EmailLog
    {
        $customer = $invoice->customer;

        if (! $customer?->email) {
            Log::warning('Invoice email skipped: customer has no email address', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $customer?->id,
            ]);

            return null;
        }

        $log = EmailLog::queue(
            mailable: InvoiceMail::class,
            type: $type,
            recipientEmail: $customer->email,
            recipientName: $customer->name,
            // The subject is rendered now so the log shows what was actually
            // sent, even if the invoice is edited before the worker runs.
            subject: $this->subjectFor($type, $invoice),
            context: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $customer->id,
                'attach_pdf' => $attachPdf,
            ],
            userId: $userId,
        );

        SendInvoiceEmail::dispatch($log->id)->onQueue(EmailLog::QUEUE);

        return $log;
    }

    private function subjectFor(string $type, Invoice $invoice): string
    {
        return $type === InvoiceMail::TYPE_OVERDUE_REMINDER
            ? 'Overdue Invoice ' . $invoice->invoice_number . ' from Iconicpages'
            : 'Invoice ' . $invoice->invoice_number . ' from Iconicpages';
    }
}
