<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== Email Configuration ===\n";
echo "MAIL_MAILER: " . Config::get('mail.default') . "\n";
echo "MAIL_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . Config::get('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . Config::get('mail.from.name') . "\n";
echo "\n=== Sending Test Email ===\n";

try {
    Mail::raw('This is a test email from ICPG Office CRM - ' . date('Y-m-d H:i:s'), function ($message) {
        $message->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->to('hiteshbhagwat97@gmail.com')
            ->subject('Test Email - ICPG Office - ' . date('H:i:s'));
    });

    echo "✅ Email sent successfully!\n";
    echo "Check: hiteshbhagwat97@gmail.com\n";
} catch (\Exception $e) {
    echo "❌ Email failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
