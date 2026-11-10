<?php

namespace App\Services;

use App\Models\RecurringInvoiceTemplate;
use App\Models\User;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Support\Collection;

/**
 * Single source of truth for what a user sees in the notification bell.
 *
 * The bell is not one feed for everybody. Each role only ever sees the
 * notifications that are about its own work:
 *
 *   admin              : follow-up reminders, upcoming recurring invoices
 *   hr                 : leave requests from anywhere in the company
 *   manager            : leave requests from their own team
 *   manager_with_tasks : a task they assigned was completed, leave requests
 *                        from their own team
 *   employee           : a task was assigned to them, their leave was approved
 *
 * The bell markup, the badge counter and the polling endpoint all read from
 * here, so the three can never drift apart. Which notifications a role gets
 * is data, not code: see config/notifications.php.
 */
class NotificationFeed
{
    /**
     * How many days ahead upcoming recurring invoices are previewed.
     */
    public const UPCOMING_WINDOW_DAYS = 3;

    /**
     * The tier a user belongs to.
     *
     * Order matters: a person who is both HR and a manager (an HR manager)
     * is treated as HR, because HR approves leave for the whole company.
     */
    public function tier(?User $user): string
    {
        if (! $user) {
            return 'employee';
        }

        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->isHr()) {
            return 'hr';
        }

        if ($user->isManager()) {
            return $this->isScopedManagerDepartment($user)
                ? 'manager_with_tasks'
                : 'manager';
        }

        return 'employee';
    }

    /**
     * Notification classes the user is allowed to receive.
     *
     * @return array<int, string>
     */
    public function allowedClasses(?User $user): array
    {
        $tier = $this->tier($user);
        $feeds = (array) config('notifications.feeds', []);

        return (array) ($feeds[$tier]['types'] ?? []);
    }

    /**
     * Whether the user sees the upcoming recurring invoice preview.
     *
     * This is not a stored notification but a live preview, so it is
     * configured separately from the notification types.
     */
    public function showsRecurringInvoices(?User $user): bool
    {
        $tier = $this->tier($user);
        $feeds = (array) config('notifications.feeds', []);

        return (bool) ($feeds[$tier]['recurring_invoices'] ?? false);
    }

    /**
     * Whether this manager's department gets the task-completion slot.
     *
     * Only departments whose employees actually receive task assignments get
     * it: a Sales manager has no tasks to be told about, and a task slot in
     * their feed would only ever sit empty.
     */
    protected function isScopedManagerDepartment(User $user): bool
    {
        $departments = (array) config('notifications.manager_scoped_departments', []);

        return in_array(strtolower((string) $user->department), $departments, true);
    }

    /**
     * Unread notifications visible to the user.
     */
    public function unread(User $user, int $limit = 10): Collection
    {
        $classes = $this->allowedClasses($user);

        $query = $user->unreadNotifications()->latest();

        if ($classes !== []) {
            $query->whereIn('type', $classes);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Number of unread notifications visible to the user.
     */
    public function unreadCount(User $user): int
    {
        $classes = $this->allowedClasses($user);

        $query = $user->unreadNotifications();

        if ($classes !== []) {
            $query->whereIn('type', $classes);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->count();
    }

    /**
     * Query for all notifications (read and unread) visible to the user.
     */
    public function allQuery(User $user)
    {
        $classes = $this->allowedClasses($user);

        $query = $user->notifications()->latest();

        if ($classes !== []) {
            $query->whereIn('type', $classes);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Mark all unread notifications visible to the user as read.
     */
    public function markAllAsRead(User $user): int
    {
        $classes = $this->allowedClasses($user);

        if ($classes === []) {
            return 0;
        }

        $unread = $user->unreadNotifications()->whereIn('type', $classes)->get();
        $count = $unread->count();

        $unread->markAsRead();

        return $count;
    }

    /**
     * Recurring invoice templates that are about to be generated.
     *
     * Only the admin sees these: billing is not everybody's concern.
     */
    public function upcomingTemplates(User $user, int $limit = 10): Collection
    {
        if (! $this->showsRecurringInvoices($user)) {
            return collect();
        }

        return RecurringInvoiceTemplate::with('customer')
            ->active()
            ->where('next_generation_date', '<=', now()->addDays(self::UPCOMING_WINDOW_DAYS))
            ->orderBy('next_generation_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Number of upcoming recurring invoice templates.
     */
    public function upcomingCount(User $user): int
    {
        if (! $this->showsRecurringInvoices($user)) {
            return 0;
        }

        return RecurringInvoiceTemplate::active()
            ->where('next_generation_date', '<=', now()->addDays(self::UPCOMING_WINDOW_DAYS))
            ->count();
    }
}
