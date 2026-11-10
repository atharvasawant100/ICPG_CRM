<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to an employee / assignee when an Administrator escalates their task to Urgent.
 */
class TaskEscalatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $escalatedBy,
        protected string $escalationReason
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $task = $this->task;
        $adminName = $this->escalatedBy->name ?? 'Administrator';

        return [
            'type' => 'task_escalated',
            'task_id' => $task->id,
            'title' => '⚠️ Task Escalated to Urgent',
            'message' => "🚨 ESCALATION: Task \"{$task->title}\" has been escalated to URGENT by {$adminName}. Directive: \"{$this->escalationReason}\"",
            'escalation_reason' => $this->escalationReason,
            'escalated_by_id' => $this->escalatedBy->id,
            'escalated_by_name' => $adminName,
            'priority' => 'urgent',
            'url' => route('tasks.show', $task),
        ];
    }
}

