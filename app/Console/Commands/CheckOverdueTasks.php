<?php

namespace App\Console\Commands;

use App\Services\TaskOverdueService;
use Illuminate\Console\Command;

class CheckOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Scan for tasks that passed their deadline, send portal notifications, and email employees (with CC to manager) having 5+ missed tasks';

    /**
     * Execute the console command.
     */
    public function handle(TaskOverdueService $service): int
    {
        $this->info('Checking overdue tasks and dispatching notifications...');

        $stats = $service->checkAndDispatchOverdueNotifications();

        $this->line("Overdue incomplete tasks found : {$stats['overdue_tasks']}");
        $this->line("New portal notifications sent   : {$stats['portal_notifications']}");
        $this->line("Threshold alert emails sent     : {$stats['emails_sent']}");

        $this->info('Overdue task check completed successfully.');

        return self::SUCCESS;
    }
}

