<?php

/*
|--------------------------------------------------------------------------
| Notification feed policy
|--------------------------------------------------------------------------
|
| The notification bell is not one feed for everybody. Each role only ever
| sees the notifications that are about its own work:
|
|   admin             : follow-up reminders and upcoming recurring invoices
|   hr                : leave requests from anywhere in the company
|   manager           : leave requests from their own team, their own leave
|                       being approved
|   manager_with_tasks: a task they assigned was completed, leave requests
|                       from their own team, their own leave being approved
|   employee          : a task was assigned to them, their leave was approved
|
| A manager applies for leave like anybody else, so a manager feed carries
| Leave Approved as well. Who may approve is a separate rule, enforced by the
| leave controller: a manager's leave is approved by HR, an employee's leave
| by HR or by their manager.
|
| `manager_with_tasks` is only applied to the departments listed in
| `manager_scoped_departments`. It is a list because some departments simply
| do not receive task assignments: a Sales manager has no tasks to be told
| about, so their feed does not need a task slot.
|
| `recurring_invoices` controls the separate "upcoming invoice" block in the
| dropdown. It is not a stored notification, it is a live preview, so it is
| configured separately from the notification types.
|
| To change what a role receives, edit the `feeds` map below. No code change
| is needed.
|
*/

return [

    /*
    | Departments whose managers also get task-completion notifications.
    | Add a department here only if its employees genuinely receive tasks.
    */
    'manager_scoped_departments' => ['development', 'marketing'],

    /*
    | The feed for each tier: which stored notifications a role may receive,
    | and whether they see the upcoming recurring invoice preview.
    */
    'feeds' => [

        'admin' => [
            'types' => [
                \App\Notifications\FollowupReminderNotification::class,
                \App\Notifications\NewInternalTicketAdminNotification::class,
                \App\Notifications\ContentReviewSubmittedNotification::class,
                \App\Notifications\ContentPublishMissedNotification::class,
            ],
            'recurring_invoices' => true,
        ],

        'hr' => [
            'types' => [
                \App\Notifications\LeaveRequestNotification::class,
            ],
            'recurring_invoices' => false,
        ],

        'manager' => [
            'types' => [
                \App\Notifications\LeaveRequestNotification::class,
                \App\Notifications\LeaveApprovedNotification::class,
            ],
            'recurring_invoices' => false,
        ],

        'manager_with_tasks' => [
            'types' => [
                \App\Notifications\TaskCompletedNotification::class,
                \App\Notifications\LeaveRequestNotification::class,
                \App\Notifications\LeaveApprovedNotification::class,
                \App\Notifications\TaskOverdueNotification::class,
                \App\Notifications\TaskEscalatedNotification::class,
                \App\Notifications\InternalTicketResolvedNotification::class,
                \App\Notifications\TaskReworkNotification::class,
                \App\Notifications\ContentAssignedNotification::class,
                \App\Notifications\ContentReviewSubmittedNotification::class,
                \App\Notifications\ContentApprovalDecisionNotification::class,
                \App\Notifications\ContentPublishMissedNotification::class,
            ],
            'recurring_invoices' => false,
        ],

        'employee' => [
            'types' => [
                \App\Notifications\TaskAssignedNotification::class,
                \App\Notifications\LeaveApprovedNotification::class,
                \App\Notifications\TaskOverdueNotification::class,
                \App\Notifications\TaskEscalatedNotification::class,
                \App\Notifications\InternalTicketResolvedNotification::class,
                \App\Notifications\TaskReworkNotification::class,
                \App\Notifications\ContentAssignedNotification::class,
                \App\Notifications\ContentReviewSubmittedNotification::class,
                \App\Notifications\ContentApprovalDecisionNotification::class,
                \App\Notifications\ContentPublishMissedNotification::class,
            ],
            'recurring_invoices' => false,
        ],

    ],

];
