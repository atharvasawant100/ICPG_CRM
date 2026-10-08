<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoiceTemplate;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceEmailService;

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
    protected $description = 'Generate invoices from active recurring invoice templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recurring invoice generation...');

        // Find all active templates that should generate today
        $templates = RecurringInvoiceTemplate::active()
            ->where('next_generation_date', '<=', Carbon::now()->toDateString())
            ->with(['customer', 'items'])
            ->get();

        if ($templates->isEmpty()) {
            $this->info('No recurring invoices to generate today.');
            return 0;
        }

        $generatedCount = 0;
        $errors = [];

        foreach ($templates as $template) {
            try {
                // Check if template should generate
                if (!$template->shouldGenerate()) {
                    continue;
                }

                DB::beginTransaction();

                // Generate invoice number
                $invoiceNumber = InvoiceNumberGenerator::generate();

                // Calculate due date (default to 30 days from invoice date, or use payment terms)
                $invoiceDate = Carbon::now()->toDateString();
                $dueDate = Carbon::now()->addDays(30)->toDateString();
                
                // Parse payment terms if available (e.g., "Net 30" = 30 days)
                if ($template->payment_terms) {
                    if (preg_match('/\b(\d+)\b/', $template->payment_terms, $matches)) {
                        $days = (int) $matches[1];
                        $dueDate = Carbon::now()->addDays($days)->toDateString();
                    }
                }

                // Create invoice
                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $template->customer_id,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $template->subtotal,
                    'tax_amount' => $template->tax_amount,
                    'discount_amount' => $template->discount_amount,
                    'total_amount' => $template->subtotal + $template->tax_amount - $template->discount_amount,
                    'status' => 'sent', // Auto-send generated invoices
                    'payment_terms' => $template->payment_terms,
                    'notes' => $template->notes,
                    'is_recurring' => true,
                    'recurring_template_id' => $template->id,
                    'created_by' => $template->created_by,
                ]);

                // Copy items from template
                foreach ($template->items as $templateItem) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $templateItem->service_id ?? null,
                        'description' => $templateItem->description,
                        'quantity' => $templateItem->quantity,
                        'unit_price' => $templateItem->unit_price,
                        'total' => $templateItem->total,
                        'item_order' => $templateItem->item_order,
                    ]);
                }
                
                // Send email notification for auto-generated invoices
                try {
                    $emailService = new InvoiceEmailService();
                    $emailService->sendInvoice($invoice, true);
                    Log::info("Recurring invoice email sent", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoiceNumber,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the generation
                    Log::warning("Failed to send email for recurring invoice", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoiceNumber,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Calculate next generation date
                $nextGenerationDate = $template->calculateNextGenerationDate(Carbon::now());

                // Check if template has reached end date
                if ($template->end_date && $nextGenerationDate->toDateString() > $template->end_date->toDateString()) {
                    // Deactivate template
                    $template->is_active = false;
                    $this->info("Template '{$template->name}' reached end date and has been deactivated.");
                }

                // Update template's next generation date
                $template->next_generation_date = $nextGenerationDate->toDateString();
                $template->save();

                DB::commit();

                $generatedCount++;
                $this->info("Generated invoice {$invoiceNumber} from template '{$template->name}'");
                Log::info("Recurring invoice generated", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'template_id' => $template->id,
                    'next_generation_date' => $template->next_generation_date,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $error = "Failed to generate invoice from template '{$template->name}': " . $e->getMessage();
                $errors[] = $error;
                $this->error($error);
                Log::error("Failed to generate recurring invoice", [
                    'template_id' => $template->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Generated {$generatedCount} invoice(s).");

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
