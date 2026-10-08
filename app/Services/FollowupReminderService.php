<?php

namespace App\Services;

use App\Models\LeadFollowup;
use App\Models\User;
use App\Notifications\FollowupReminderNotification;

class FollowupReminderService
{
    /** Reminder thresholds in minutes before the follow-up time. */
    public const THRESHOLDS = [30, 15, 10, 5];

    /**
     * Send reminders for pending follow-ups whose 30/15/10/5 minute
     * marks have been crossed and were not already notified.
     *
     * @return int number of reminders sent
     */
    public function dispatchDueReminders(): int
    {
        $followups = LeadFollowup::with('lead')
            ->where('status', 'pending')
            ->whereBetween('followup_date', [now(), now()->addMinutes(max(self::THRESHOLDS) + 1)])
            ->get();

        if ($followups->isEmpty()) {
            return 0;
        }

        $admins = User::where('role', 'admin')->get();
        $sent = 0;

        foreach ($followups as $followup) {
            $minutesUntil = (int) ceil(now()->diffInSeconds($followup->followup_date, false) / 60);
            $already = $followup->reminders_sent ?? [];
            $crossed = array_values(array_filter(self::THRESHOLDS, fn ($t) => $minutesUntil <= $t));
            $unsent = array_values(array_diff($crossed, $already));

            if (empty($unsent)) {
                continue;
            }

            // Notify once per poll with the actual minutes remaining, then mark
            // every crossed threshold as sent so a late poll doesn't spam
            // (e.g. first check at 12 min sends one alert, not 30+15 alerts).
            foreach ($admins as $admin) {
                $admin->notify(new FollowupReminderNotification($followup, max($minutesUntil, 1)));
            }
            $sent++;

            $followup->reminders_sent = array_values(array_unique(array_merge($already, $crossed)));
            $followup->save();
        }

        return $sent;
    }
}
