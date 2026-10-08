<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule recurring invoice generation daily at 6:00 AM IST
Schedule::command('invoices:generate-recurring')
    ->dailyAt('06:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping(5) // Lock expires after 5 minutes
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('✅ Recurring invoices generated successfully', [
            'timestamp' => now(),
            'schedule' => 'daily_6am'
        ]);
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('❌ Recurring invoice generation failed', [
            'timestamp' => now(),
            'schedule' => 'daily_6am',
            'action_required' => 'Check logs and run manually: php artisan invoices:generate-recurring'
        ]);
        // TODO: Send email notification to admin
    });

// Follow-up reminders: notify 30/15/10/5 minutes before each pending follow-up
Schedule::call(function () {
    app(\App\Services\FollowupReminderService::class)->dispatchDueReminders();
})->everyMinute()->name('followup-reminders')->withoutOverlapping();

// Schedule overdue invoice reminders daily at 9:00 AM IST
Schedule::command('invoices:send-overdue-reminders')
    ->dailyAt('09:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping(15)
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('✅ Overdue invoice reminders sent successfully', [
            'timestamp' => now(),
            'schedule' => 'daily_9am'
        ]);
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('❌ Overdue invoice reminders failed', [
            'timestamp' => now(),
            'schedule' => 'daily_9am',
            'action_required' => 'Check logs and run manually: php artisan invoices:send-overdue-reminders'
        ]);
    });
