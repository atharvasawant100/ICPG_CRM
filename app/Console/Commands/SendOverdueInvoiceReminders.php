<?php

namespace App\Console\Commands;

use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Services\InvoiceEmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders {--dry-run : Do not send, only show counts}';

    protected $description = 'Queue one overdue reminder email per invoice per day until it is paid';

    public function handle(): int
    {
        $now = now();
        $dryRun = (bool) $this->option('dry-run');

        $query = Invoice::query()
            ->with(['customer', 'payments'])
            ->whereDate('due_date', '<', $now->toDateString())
            ->whereNotIn('status', ['paid', 'cancelled']);

        $totalCandidates = (clone $query)->count();

        $invoices = $query->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $service = new InvoiceEmailService();

        /*
         | Reminders already queued or delivered today, keyed by invoice.
         |
         | Read from the email log rather than invoices.overdue_reminder_last_sent_at:
         | that column is only stamped once the worker delivers, so a backed-up
         | queue would otherwise let a second run mail the same customer twice.
         */
        $alreadyHandledToday = EmailLog::query()
            ->where('type', InvoiceMail::TYPE_OVERDUE_REMINDER)
            ->whereIn('status', [EmailLog::STATUS_QUEUED, EmailLog::STATUS_SENT])
            ->where('created_at', '>=', $now->startOfDay())
            ->get(['context', 'id'])
            ->map(fn (EmailLog $log) => $log->context['invoice_id'] ?? null)
            ->filter()
            ->flip()
            ->all();

        foreach ($invoices as $invoice) {
            $customerEmail = $invoice->customer?->email;
            if (!$customerEmail) {
                $skipped++;
                continue;
            }

            // Only keep sending while some amount is due
            if ($invoice->getRemainingAmount() <= 0) {
                $skipped++;
                continue;
            }

            if (isset($alreadyHandledToday[$invoice->id])) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $sent++;
                continue;
            }

            try {
                $log = $service->queueOverdueReminder($invoice, true);

                if ($log === null) {
                    $skipped++;
                    continue;
                }

                // Claim the slot now so a second run today cannot re-queue.
                $alreadyHandledToday[$invoice->id] = $log->id;

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Failed to queue overdue reminder email', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_email' => $customerEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Overdue reminders: candidates={$totalCandidates}, queued={$sent}, skipped={$skipped}, failed={$failed}" . ($dryRun ? ' (dry-run)' : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

