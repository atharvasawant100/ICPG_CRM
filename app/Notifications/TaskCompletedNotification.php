<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the manager who assigned a task, when the assignee completes it.
 *
 * One of the two notification types a department manager receives.
 */
class TaskCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected ?string $completedByName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $task = $this->task;
        $completedBy = $this->completedByName ?: ($task->assignee?->name ?? 'The assignee');

        return [
            'type' => 'task_completed',
            'task_id' => $task->id,
            'message' => "{$completedBy} completed the task \"{$task->title}\".",
            'url' => route('tasks.show', $task),
        ];
    }
}
