<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to an employee when a task passes its deadline without being completed.
 */
class TaskOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $task = $this->task;
        $dueStr = $task->due_date ? $task->due_date->format('d M Y') : 'the deadline';

        return [
            'type' => 'task_overdue',
            'task_id' => $task->id,
            'message' => "Deadline Missed: Task \"{$task->title}\" was due on {$dueStr} and remains incomplete.",
            'url' => route('tasks.index', ['tab' => 'my']),
        ];
    }
}

