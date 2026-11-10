<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Invoice;
use App\Services\InvoiceEmailService;

echo "=== Invoice Email Test ===\n\n";

// Get invoice #3
$invoice = Invoice::with(['customer', 'items.service'])->find(3);

if (!$invoice) {
    echo "❌ Invoice #3 not found!\n";
    exit(1);
}

echo "Invoice: {$invoice->invoice_number}\n";
echo "Customer: {$invoice->customer->name}\n";
echo "Email: {$invoice->customer->email}\n";
echo "Amount: ₹" . number_format($invoice->total_amount, 2) . "\n\n";

if (!$invoice->customer->email) {
    echo "❌ Customer has no email address!\n";
    exit(1);
}

echo "=== Queueing Email ===\n";

try {
    $emailService = new InvoiceEmailService();
    $log = $emailService->queueInvoice($invoice, true);

    if ($log === null) {
        echo "⚠️  Nothing queued - the customer has no email address.\n";
        exit(1);
    }

    echo "✅ Email queued successfully!\n";
    echo "Recipient: {$log->recipient_email}\n";
    echo "Subject:   {$log->subject}\n";
    echo "Log id:    {$log->id}\n\n";
    echo "Deliver it with:\n";
    echo "  php artisan queue:work --queue=" . InvoiceEmailService::QUEUE . "\n\n";
    echo "Then check the result at /admin/email-logs\n";
} catch (\Exception $e) {
    echo "❌ Queueing failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
