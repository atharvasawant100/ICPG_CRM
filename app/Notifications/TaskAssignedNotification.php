<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to an employee when a task is assigned to them.
 *
 * One of the two notification types an employee is allowed to receive.
 */
class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected ?string $assignedByName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $task = $this->task;
        $assigner = $this->assignedByName ?: ($task->creator?->name ?? 'Your manager');

        $message = "{$assigner} assigned you the task \"{$task->title}\"";

        if ($task->priority) {
            $message .= ' (' . ucfirst((string) $task->priority) . ' priority)';
        }

        if ($task->due_date) {
            $message .= ' due ' . $task->due_date->format('d M Y');
        }

        return [
            'type' => 'task_assigned',
            'task_id' => $task->id,
            'message' => $message . '.',
            'url' => route('tasks.show', $task),
        ];
    }
}
