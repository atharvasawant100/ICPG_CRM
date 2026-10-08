<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== Quick Email Test ===\n\n";

$testEmail = $argv[1] ?? 'hiteshbhagwat97@gmail.com';

echo "Sending to: $testEmail\n";
echo "From: " . Config::get('mail.from.address') . "\n\n";

try {
    Mail::raw('This is a test email sent at ' . date('Y-m-d H:i:s') . ' from ICPG Office CRM.', function ($message) use ($testEmail) {
        $message->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->to($testEmail)
            ->subject('Test Email - ' . date('H:i:s'));
    });

    echo "✅ Email sent successfully!\n";
    echo "Check: $testEmail (including spam folder)\n";
} catch (\Exception $e) {
    echo "❌ Email failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}
