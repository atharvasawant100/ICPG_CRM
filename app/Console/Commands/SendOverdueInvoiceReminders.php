<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceEmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders {--dry-run : Do not send, only show counts}';

    protected $description = 'Send one overdue reminder email per invoice per day until it is paid';

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

            if ($invoice->overdue_reminder_last_sent_at?->isSameDay($now)) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $sent++;
                continue;
            }

            try {
                $service->sendOverdueReminder($invoice, true);

                $invoice->forceFill([
                    'overdue_reminder_last_sent_at' => $now,
                    'overdue_reminder_sent_count' => (int) ($invoice->overdue_reminder_sent_count ?? 0) + 1,
                ])->save();

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Overdue reminder email failed', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_email' => $customerEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Overdue reminders: candidates={$totalCandidates}, sent={$sent}, skipped={$skipped}, failed={$failed}" . ($dryRun ? ' (dry-run)' : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

