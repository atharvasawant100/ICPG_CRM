<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendancePunchService
{
    /**
     * Record employee check-in (punch-in).
     */
    public function punchIn(User $user, ?string $notes = null): ?Attendance
    {
        // Admins are not part of staff attendance tracking
        if ($user->isAdmin()) {
            return null;
        }

        $today = Carbon::today();
        $currentTime = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // If already punched in today, keep the original punch-in record
        if ($attendance && $attendance->check_in) {
            return $attendance;
        }

        $settings = \App\Models\AttendanceSetting::get();

        // Do not allow punch-in after official office shift hours
        if (! $settings->isPunchInAllowed($currentTime)) {
            $formattedEnd = $settings->formattedShiftEndTime();
            throw new \DomainException("Office shift ended at {$formattedEnd}. Punch-in is closed for today.");
        }

        if ($attendance) {
            // If a blank record was present, record check-in
            $attendance->check_in = $currentTime->format('H:i:s');
            if ($attendance->status === 'absent' || empty($attendance->status)) {
                $attendance->status = $settings->determinePunchInStatus($currentTime, false);
            }
            if ($notes && empty($attendance->notes)) {
                $attendance->notes = $notes;
            }
            $attendance->save();

            return $attendance;
        }

        // Check if employee is on an approved leave today
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        $status = $settings->determinePunchInStatus($currentTime, $isOnLeave);

        return Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $currentTime->format('H:i:s'),
            'check_out' => null,
            'total_minutes' => 0,
            'status' => $status,
            'notes' => $notes ?? 'Manual punch-in',
            'marked_by' => $user->id,
        ]);
    }

    /**
     * Record employee check-out (punch-out) with optional task logs.
     *
     * @param array<int|string> $workedTaskIds
     * @param array<int|string> $completedTaskIds
     */
    public function punchOut(
        User $user,
        ?string $notes = null,
        array $workedTaskIds = [],
        array $completedTaskIds = [],
        ?string $workNotes = null
    ): ?Attendance {
        if ($user->isAdmin()) {
            return null;
        }

        $today = Carbon::today();
        $currentTime = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return null;
        }

        // Record check-out time and compute total working minutes
        $attendance->check_out = $currentTime->format('H:i:s');
        $attendance->computeTotalMinutes();

        // Check if short work hours rule applies (e.g. less than half_day_min_hours)
        $settings = \App\Models\AttendanceSetting::get();
        $halfDayMinutes = (int) ($settings->half_day_min_hours * 60);
        if ($settings->auto_half_day_on_short_hours && $attendance->total_minutes < $halfDayMinutes) {
            if ($attendance->status === 'present' || $attendance->status === 'late') {
                $attendance->status = 'half_day';
            }
        }

        if ($notes && (empty($attendance->notes) || in_array($attendance->notes, ['Auto punch-in on login', 'Manual punch-in'], true))) {
            $attendance->notes = $notes;
        }

        $attendance->save();

        // Ensure any tasks ticked as completed are also processed in worked tasks
        $workedTaskIds = array_values(array_unique(array_merge($workedTaskIds, $completedTaskIds)));

        // Process Task Pool work logs if tasks were checked
        if (! empty($workedTaskIds)) {
            $tasks = \App\Models\Task::whereIn('id', $workedTaskIds)
                ->where('assigned_to', $user->id)
                ->get();

            $loggedTitles = [];
            foreach ($tasks as $task) {
                $statusBefore = $task->status;
                $isMarkedCompleted = in_array((string) $task->id, array_map('strval', $completedTaskIds), true);

                if ($isMarkedCompleted) {
                    $task->status = 'completed';
                    $task->completed_at = Carbon::now();
                } elseif ($task->status === 'pending') {
                    $task->status = 'in_progress';
                }
                $task->save();

                if ($isMarkedCompleted && $statusBefore !== 'completed') {
                    app(\App\Services\NotificationDispatcher::class)
                        ->taskCompleted($task, $user);
                }

                \App\Models\TaskWorkLog::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'worked_date' => $today,
                    'status_before' => $statusBefore,
                    'status_after' => $task->status,
                    'notes' => $workNotes ?: 'Logged on punch-out',
                ]);

                $loggedTitles[] = $task->title . ($isMarkedCompleted ? ' (Completed)' : '');
            }

            if (! empty($loggedTitles)) {
                $taskSummary = 'Tasks Worked: ' . implode(', ', $loggedTitles);
                if ($workNotes) {
                    $taskSummary .= ' | Note: ' . $workNotes;
                }
                $attendance->notes = $attendance->notes && $attendance->notes !== 'Manual punch-out'
                    ? $attendance->notes . "\n" . $taskSummary
                    : $taskSummary;
                $attendance->save();
            }
        }

        return $attendance;
    }
}
