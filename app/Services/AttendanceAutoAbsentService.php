<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Rbac\Rbac;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceAutoAbsentService
{
    /**
     * Mark unpunched employees as absent or on_leave/holiday for a single date.
     */
    public function markAbsentsForDate(Carbon|string $date): array
    {
        $d = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $d->toDateString();

        // Do not process future dates
        if ($d->isFuture()) {
            return [
                'date' => $dateStr,
                'status' => 'future_date_skipped',
                'marked_absent' => 0,
                'marked_leave' => 0,
                'marked_holiday' => 0,
            ];
        }

        // First, auto punch-out any employees who clocked in today but forgot to punch out
        app(AttendanceAutoPunchOutService::class)->autoPunchOutForDate($dateStr);

        $isSunday = $d->isSunday();
        $holiday = Holiday::whereDate('date', $dateStr)->first();

        // Active non-admin employees
        $employees = User::query()
            ->active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->get();

        $markedAbsent = 0;
        $markedLeave = 0;
        $markedHoliday = 0;

        DB::transaction(function () use ($employees, $dateStr, $isSunday, $holiday, &$markedAbsent, &$markedLeave, &$markedHoliday) {
            foreach ($employees as $employee) {
                // If user was created after this date, skip past dates before their account existed
                if ($employee->created_at && Carbon::parse($employee->created_at)->startOfDay()->gt(Carbon::parse($dateStr)->endOfDay())) {
                    continue;
                }

                $attendance = Attendance::where('user_id', $employee->id)
                    ->whereDate('date', $dateStr)
                    ->first();

                // If employee already punched in, do not overwrite
                if ($attendance && ! empty($attendance->check_in)) {
                    continue;
                }

                // If today is a weekly off (Sunday)
                if ($isSunday) {
                    continue;
                }

                // If today is an official Holiday: mark as holiday so employee is exempt
                if ($holiday) {
                    if (! $attendance) {
                        Attendance::create([
                            'user_id' => $employee->id,
                            'date' => $dateStr,
                            'status' => 'holiday',
                            'notes' => "Official Holiday: {$holiday->name}",
                        ]);
                        $markedHoliday++;
                    } elseif ($attendance->status !== 'present' && $attendance->status !== 'holiday') {
                        $attendance->status = 'holiday';
                        $attendance->notes = "Official Holiday: {$holiday->name}";
                        $attendance->save();
                        $markedHoliday++;
                    }
                    continue;
                }

                // Check if employee has an approved leave for this date
                $leave = LeaveRequest::where('user_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $dateStr)
                    ->whereDate('end_date', '>=', $dateStr)
                    ->first();

                if ($leave) {
                    $leaveTypeName = $leave->leaveType?->name ?? 'Leave';
                    $leaveStatus = $leave->is_half_day ? 'half_day' : 'on_leave';
                    $notes = ($leave->is_half_day ? 'Half Day ' : '') . "Approved Leave: {$leaveTypeName}" . ($leave->reason ? " ({$leave->reason})" : '');

                    if (! $attendance) {
                        Attendance::create([
                            'user_id' => $employee->id,
                            'date' => $dateStr,
                            'status' => $leaveStatus,
                            'notes' => $notes,
                            'marked_by' => $leave->action_by,
                        ]);
                        $markedLeave++;
                    } elseif ($attendance->status !== $leaveStatus) {
                        $attendance->status = $leaveStatus;
                        $attendance->notes = $notes;
                        $attendance->save();
                        $markedLeave++;
                    }
                    continue;
                }

                // If no punch in, mark directly as ABSENT
                if (! $attendance) {
                    Attendance::create([
                        'user_id' => $employee->id,
                        'date' => $dateStr,
                        'status' => 'absent',
                        'notes' => 'Auto-marked absent: No punch-in recorded',
                    ]);
                    $markedAbsent++;
                } elseif (empty($attendance->status) || $attendance->status === 'unmarked') {
                    $attendance->status = 'absent';
                    if (empty($attendance->notes)) {
                        $attendance->notes = 'Auto-marked absent: No punch-in recorded';
                    }
                    $attendance->save();
                    $markedAbsent++;
                }
            }
        });

        return [
            'date' => $dateStr,
            'is_sunday' => $isSunday,
            'is_holiday' => (bool) $holiday,
            'holiday_name' => $holiday?->name,
            'marked_absent' => $markedAbsent,
            'marked_leave' => $markedLeave,
            'marked_holiday' => $markedHoliday,
        ];
    }

    /**
     * Mark unpunched attendance across a date range.
     */
    public function markAbsentsForRange(Carbon|string $from, Carbon|string $to): array
    {
        $startDate = Carbon::parse($from)->startOfDay();
        $endDate = Carbon::parse($to)->min(now())->startOfDay();

        $period = CarbonPeriod::create($startDate, $endDate);
        $totalAbsent = 0;
        $totalLeave = 0;
        $totalHoliday = 0;
        $daysProcessed = 0;

        foreach ($period as $date) {
            $result = $this->markAbsentsForDate($date);
            $totalAbsent += $result['marked_absent'];
            $totalLeave += $result['marked_leave'];
            $totalHoliday += $result['marked_holiday'];
            $daysProcessed++;
        }

        return [
            'days_processed' => $daysProcessed,
            'marked_absent' => $totalAbsent,
            'marked_leave' => $totalLeave,
            'marked_holiday' => $totalHoliday,
        ];
    }
}
