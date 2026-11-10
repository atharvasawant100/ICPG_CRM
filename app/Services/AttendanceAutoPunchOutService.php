<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceAutoPunchOutService
{
    /**
     * Auto punch-out employees who forgot to clock out for a specific date.
     *
     * @return array{date: string, processed: int, skipped: int, employees: array<string>}
     */
    public function autoPunchOutForDate(Carbon|string $date): array
    {
        $d = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $d->toDateString();
        $settings = AttendanceSetting::get();

        if (! $settings->enable_auto_punch_out) {
            return [
                'date' => $dateStr,
                'status' => 'disabled',
                'processed' => 0,
                'skipped' => 0,
                'employees' => [],
            ];
        }

        // Fetch unclosed attendance records for this date
        $openAttendances = Attendance::with('user')
            ->whereDate('date', $dateStr)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        if ($openAttendances->isEmpty()) {
            return [
                'date' => $dateStr,
                'status' => 'none_pending',
                'processed' => 0,
                'skipped' => 0,
                'employees' => [],
            ];
        }

        $processedCount = 0;
        $employeeNames = [];
        $checkoutTimeFormatted = substr($settings->auto_punch_out_time, 0, 5) . ':00';

        DB::transaction(function () use ($openAttendances, $settings, $checkoutTimeFormatted, &$processedCount, &$employeeNames) {
            foreach ($openAttendances as $attendance) {
                // Determine checkout time based on configured mode
                if ($settings->auto_punch_out_mode === 'shift_duration' && $settings->full_day_min_hours > 0) {
                    $in = Carbon::parse($attendance->check_in);
                    $calculatedOut = $in->copy()->addMinutes((int) ($settings->full_day_min_hours * 60));
                    $assignedCheckOut = $calculatedOut->format('H:i:s');
                } else {
                    $assignedCheckOut = $checkoutTimeFormatted;
                }

                $attendance->check_out = $assignedCheckOut;
                $attendance->computeTotalMinutes();

                // Check if short work hours rule applies
                $halfDayMinutes = (int) ($settings->half_day_min_hours * 60);
                if ($settings->auto_half_day_on_short_hours && $attendance->total_minutes < $halfDayMinutes) {
                    if ($attendance->status === 'present' || $attendance->status === 'late') {
                        $attendance->status = 'half_day';
                    }
                }

                $autoNote = "Auto clocked out by system (Forgot punch-out, recorded at " . Carbon::parse($assignedCheckOut)->format('h:i A') . ")";
                $attendance->notes = $attendance->notes
                    ? $attendance->notes . "\n" . $autoNote
                    : $autoNote;

                $attendance->save();

                $processedCount++;
                $employeeNames[] = $attendance->user?->name ?? "User #{$attendance->user_id}";
            }
        });

        Log::info("✅ Auto punch-out processed for {$dateStr}: {$processedCount} employee(s) auto-closed.", [
            'employees' => $employeeNames,
            'checkout_time' => $checkoutTimeFormatted,
        ]);

        return [
            'date' => $dateStr,
            'status' => 'success',
            'processed' => $processedCount,
            'skipped' => 0,
            'employees' => $employeeNames,
        ];
    }

    /**
     * Auto punch-out all forgotten checkouts from past days up to today.
     */
    public function autoPunchOutPastUnclosedDays(): array
    {
        $settings = AttendanceSetting::get();
        if (! $settings->enable_auto_punch_out) {
            return ['total_processed' => 0, 'days_processed' => 0];
        }

        $dates = Attendance::whereNotNull('check_in')
            ->whereNull('check_out')
            ->whereDate('date', '<=', Carbon::today())
            ->distinct()
            ->pluck('date');

        $totalProcessed = 0;
        $daysCount = 0;

        foreach ($dates as $date) {
            $res = $this->autoPunchOutForDate($date);
            $totalProcessed += $res['processed'];
            $daysCount++;
        }

        return [
            'total_processed' => $totalProcessed,
            'days_processed' => $daysCount,
        ];
    }
}
