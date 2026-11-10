<?php

namespace App\Console\Commands;

use App\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices from active recurring invoice templates and dispatch emails';

    /**
     * Execute the console command.
     */
    public function handle(RecurringInvoiceService $service)
    {
        $this->info('Starting recurring invoice generation...');

        $result = $service->processDueTemplates();

        $generatedCount = $result['generated'];
        $errors = $result['errors'];

        if ($generatedCount === 0 && empty($errors)) {
            $this->info('No recurring invoices to generate today.');
            return 0;
        }

        $this->info("Generated {$generatedCount} invoice(s).");

        foreach ($result['invoices'] as $invoice) {
            $this->line("  ✓ Invoice {$invoice->invoice_number} created for customer ID {$invoice->customer_id}.");
        }

        if (!empty($errors)) {
            $this->error("Errors occurred:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        return 0;
    }
}
