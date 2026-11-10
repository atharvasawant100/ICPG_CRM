<?php

namespace App\Console\Commands;

use App\Services\AttendanceAutoPunchOutService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoPunchOutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-punch-out
                            {--date= : The date to process (YYYY-MM-DD), defaults to today}
                            {--all-past : Auto-close all pending forgotten punch-outs up to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out employees who forgot to punch out';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceAutoPunchOutService $service): int
    {
        $this->info('Starting auto punch-out processor for open shifts...');

        if ($this->option('all-past')) {
            $this->info('Scanning all past unclosed attendance shifts...');
            $res = $service->autoPunchOutPastUnclosedDays();
            $this->info("✓ Completed: {$res['total_processed']} open shift(s) closed across {$res['days_processed']} day(s).");
            return Command::SUCCESS;
        }

        $date = $this->option('date') ?: Carbon::today()->toDateString();
        $this->info("Processing date: {$date}...");

        $res = $service->autoPunchOutForDate($date);

        if ($res['status'] === 'disabled') {
            $this->warn('Auto punch-out is currently disabled in Attendance Settings.');
            return Command::SUCCESS;
        }

        if ($res['status'] === 'none_pending') {
            $this->info("✓ No open shifts pending punch-out on {$date}.");
            return Command::SUCCESS;
        }

        $this->info("✓ Successfully auto-clocked out {$res['processed']} employee(s):");
        foreach ($res['employees'] as $emp) {
            $this->line("   - {$emp}");
        }

        return Command::SUCCESS;
    }
}
