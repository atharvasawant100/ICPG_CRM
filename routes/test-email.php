<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from ICPG Office CRM.', function ($message) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->to('hiteshbhagwat97@gmail.com')
                ->subject('Test Email - ICPG Office');
        });

        return 'Test email sent successfully! Check hiteshbhagwat97@gmail.com';
    } catch (\Exception $e) {
        return 'Email failed: ' . $e->getMessage();
    }
});
