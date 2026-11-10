<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use Illuminate\Mail\Mailable;

/**
 * Delivers an invoice or an overdue reminder to a customer.
 */
class SendInvoiceEmail extends SendQueuedEmail
{
    protected function buildMailable(EmailLog $log): Mailable
    {
        $invoice = $this->resolveRecord($log, Invoice::class, 'invoice_id');

        return new InvoiceMail(
            $invoice->loadMissing(['customer', 'payments', 'items.service']),
            $log->type,
            (bool) ($log->context['attach_pdf'] ?? true),
        );
    }

    /**
     * The counters mirror the log rows; they exist only so the invoice list can
     * show "sent N times" without a join.
     */
    protected function afterSent(EmailLog $log): void
    {
        $invoice = $this->resolveRecord($log, Invoice::class, 'invoice_id');

        $invoice->forceFill($this->countersFor($log->type, $invoice))->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function countersFor(string $type, Invoice $invoice): array
    {
        if ($type === InvoiceMail::TYPE_OVERDUE_REMINDER) {
            return [
                'overdue_reminder_last_sent_at' => now(),
                'overdue_reminder_sent_count' => (int) ($invoice->overdue_reminder_sent_count ?? 0) + 1,
            ];
        }

        return [
            'email_sent_at' => now(),
            'email_sent_count' => (int) ($invoice->email_sent_count ?? 0) + 1,
        ];
    }
}
