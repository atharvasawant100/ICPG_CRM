<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoiceTemplate;
use App\Services\InvoiceEmailService;
use App\Services\InvoiceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringInvoiceService
{
    public function __construct(
        protected InvoiceEmailService $emailService
    ) {}

    /**
     * Process all active templates that have reached their generation date.
     *
     * @return array{generated: int, errors: array<string>, invoices: array<Invoice>}
     */
    public function processDueTemplates(): array
    {
        $today = Carbon::today()->toDateString();

        $templates = RecurringInvoiceTemplate::active()
            ->where('next_generation_date', '<=', $today)
            ->with(['customer', 'items'])
            ->get();

        if ($templates->isEmpty()) {
            return [
                'generated' => 0,
                'errors' => [],
                'invoices' => [],
            ];
        }

        $generatedCount = 0;
        $invoices = [];
        $errors = [];

        foreach ($templates as $template) {
            try {
                if (!$template->shouldGenerate()) {
                    continue;
                }

                $invoice = $this->generateFromTemplate($template);
                if ($invoice) {
                    $generatedCount++;
                    $invoices[] = $invoice;
                }
            } catch (\Throwable $e) {
                $errorMsg = "Template #{$template->id} ('{$template->name}'): " . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error("Recurring generation error: " . $errorMsg, [
                    'exception' => $e,
                ]);
            }
        }

        return [
            'generated' => $generatedCount,
            'errors' => $errors,
            'invoices' => $invoices,
        ];
    }

    /**
     * Generate an invoice for a specific recurring template.
     */
    public function generateFromTemplate(RecurringInvoiceTemplate $template): Invoice
    {
        return DB::transaction(function () use ($template) {
            // Lock row to prevent duplicate generation from concurrent triggers
            $lockedTemplate = RecurringInvoiceTemplate::where('id', $template->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedTemplate || !$lockedTemplate->is_active) {
                throw new \RuntimeException("Template is inactive or no longer exists.");
            }

            // Generate invoice number
            $invoiceNumber = InvoiceNumberGenerator::generate();

            // Set dates
            $invoiceDate = Carbon::today()->toDateString();
            $dueDate = Carbon::today()->addDays(30)->toDateString();

            if ($lockedTemplate->payment_terms && preg_match('/\b(\d+)\b/', $lockedTemplate->payment_terms, $matches)) {
                $dueDate = Carbon::today()->addDays((int) $matches[1])->toDateString();
            }

            $total = (float) $lockedTemplate->subtotal + (float) $lockedTemplate->tax_amount - (float) $lockedTemplate->discount_amount;

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $lockedTemplate->customer_id,
                'brand' => $lockedTemplate->brand,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'subtotal' => $lockedTemplate->subtotal,
                'tax_amount' => $lockedTemplate->tax_amount,
                'discount_amount' => $lockedTemplate->discount_amount,
                'total_amount' => $total,
                'status' => 'sent',
                'payment_terms' => $lockedTemplate->payment_terms,
                'notes' => $lockedTemplate->notes,
                'is_recurring' => true,
                'recurring_template_id' => $lockedTemplate->id,
                'created_by' => $lockedTemplate->created_by,
            ]);

            // Copy items
            foreach ($lockedTemplate->items as $templateItem) {
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

            // Calculate next generation date:
            // Advance by frequency from the template's previous scheduled date so the billing anchor day of month is maintained
            $baseDate = $lockedTemplate->next_generation_date ? Carbon::parse($lockedTemplate->next_generation_date) : Carbon::today();
            $nextDate = $lockedTemplate->calculateNextGenerationDate($baseDate);

            // If template was severely overdue, ensure next date lands in the future
            while ($nextDate->lte(Carbon::today())) {
                $nextDate = $lockedTemplate->calculateNextGenerationDate($nextDate);
            }

            // Check if template reached end date
            if ($lockedTemplate->end_date && $nextDate->toDateString() > $lockedTemplate->end_date->toDateString()) {
                $lockedTemplate->is_active = false;
                Log::info("Recurring template reached end date and was deactivated", [
                    'template_id' => $lockedTemplate->id,
                    'end_date' => $lockedTemplate->end_date->toDateString(),
                ]);
            }

            $lockedTemplate->next_generation_date = $nextDate->toDateString();
            $lockedTemplate->save();

            // Fire email immediately to customer
            try {
                $this->emailService->sendInvoiceDirectly($invoice, true);
                Log::info("Recurring invoice auto-generated and email dispatched", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $invoice->customer_id,
                    'customer_email' => $invoice->customer?->email,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Invoice {$invoiceNumber} was created, but email dispatch had an error: " . $e->getMessage(), [
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $invoice;
        });
    }
}
