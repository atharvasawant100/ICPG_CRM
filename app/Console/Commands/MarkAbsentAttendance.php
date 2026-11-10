<?php

namespace App\Console\Commands;

use App\Services\AttendanceAutoAbsentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absents
                            {--date= : The date to process (YYYY-MM-DD), defaults to today}
                            {--from= : Start date for date range}
                            {--to= : End date for date range}
                            {--month= : Process all past days of a month (1-12 or YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark active employees without a punch-in as absent, holiday, or on-leave';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceAutoAbsentService $service): int
    {
        $this->info('Starting attendance absent/holiday verification...');

        if ($this->option('month')) {
            $monthInput = (string) $this->option('month');
            if (str_contains($monthInput, '-')) {
                [$year, $month] = explode('-', $monthInput);
            } else {
                $year = now()->year;
                $month = (int) $monthInput;
            }

            $startDate = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth()->min(now()->startOfDay());

            $this->info("Processing entire month: {$startDate->format('M Y')} ({$startDate->toDateString()} to {$endDate->toDateString()})...");
            $result = $service->markAbsentsForRange($startDate, $endDate);

            $this->info("✓ Processed {$result['days_processed']} day(s):");
            $this->line("   - Marked Absent: {$result['marked_absent']}");
            $this->line("   - Marked On Leave: {$result['marked_leave']}");
            $this->line("   - Marked Holiday: {$result['marked_holiday']}");

            return Command::SUCCESS;
        }

        if ($this->option('from')) {
            $from = $this->option('from');
            $to = $this->option('to') ?: now()->toDateString();

            $this->info("Processing date range: {$from} to {$to}...");
            $result = $service->markAbsentsForRange($from, $to);

            $this->info("✓ Processed {$result['days_processed']} day(s):");
            $this->line("   - Marked Absent: {$result['marked_absent']}");
            $this->line("   - Marked On Leave: {$result['marked_leave']}");
            $this->line("   - Marked Holiday: {$result['marked_holiday']}");

            return Command::SUCCESS;
        }

        $date = $this->option('date') ?: now()->toDateString();
        $this->info("Processing date: {$date}...");

        $result = $service->markAbsentsForDate($date);

        if (! empty($result['status']) && $result['status'] === 'future_date_skipped') {
            $this->warn("Date {$date} is in the future. Skipped.");
            return Command::SUCCESS;
        }

        $this->info("✓ Completed for {$date}:");
        if ($result['is_sunday']) {
            $this->comment("   - Weekly Off (Sunday).");
        } elseif ($result['is_holiday']) {
            $this->comment("   - Official Holiday ({$result['holiday_name']}): {$result['marked_holiday']} employee(s) marked as holiday.");
        } else {
            $this->line("   - Marked Absent: {$result['marked_absent']}");
            $this->line("   - Marked On Leave: {$result['marked_leave']}");
        }

        return Command::SUCCESS;
    }
}
