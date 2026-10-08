<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    /**
     * Generate a unique invoice number
     * Format: INV-YYYYMMDD-XXXXXX
     *
     * The YYYYMMDD part is the date the invoice was generated.
     * The XXXXXX part is a continuous sequence that does NOT reset
     * when the date changes - it keeps counting up across all dates.
     *
     * @return string
     */
    public static function generate(): string
    {
        return DB::transaction(function () {
            $datePrefix = Carbon::now()->format('Ymd');
            $pattern = 'INV-%';

            // Continuous counter: find the highest sequence used so far across
            // every invoice regardless of its date segment, so the counter keeps
            // incrementing instead of restarting at 000001 each new day.
            $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
                ->lockForUpdate()
                ->orderByRaw('RIGHT(invoice_number, 6) DESC')
                ->first();

            if ($lastInvoice) {
                // Extract the counter part (last 6 digits to support more than 9999)
                $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
                $counter = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                // Very first invoice ever
                $counter = '000001';
            }

            $invoiceNumber = "INV-{$datePrefix}-{$counter}";

            // Double check uniqueness (safety net in case of a collision)
            $maxAttempts = 1000; // Prevent infinite loop
            $attempts = 0;
            while (Invoice::where('invoice_number', $invoiceNumber)->exists() && $attempts < $maxAttempts) {
                $counter = str_pad((int) $counter + 1, 6, '0', STR_PAD_LEFT);
                $invoiceNumber = "INV-{$datePrefix}-{$counter}";
                $attempts++;
            }

            if ($attempts >= $maxAttempts) {
                throw new \Exception('Unable to generate unique invoice number after ' . $maxAttempts . ' attempts.');
            }

            return $invoiceNumber;
        });
    }
}