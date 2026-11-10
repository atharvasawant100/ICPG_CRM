<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to an assignee when their completed (or submitted) task is sent back for rework.
 */
class TaskReworkNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $requester,
        protected string $reworkNotes
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $task = $this->task;
        $name = $this->requester->name ?? 'Management';

        return [
            'type' => 'task_rework',
            'task_id' => $task->id,
            'title' => '🔄 Re-work Requested',
            'message' => "Re-work requested on task \"{$task->title}\" by {$name}. Notes: \"{$this->reworkNotes}\"",
            'rework_notes' => $this->reworkNotes,
            'requester_name' => $name,
            'status' => 'rework',
            'url' => route('tasks.show', $task),
        ];
    }
}

