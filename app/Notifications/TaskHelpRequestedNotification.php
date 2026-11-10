<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskHelpRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $requester,
        protected string $helpMessage
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_help_requested',
            'task_id' => $this->task->id,
            'message' => "{$this->requester->name} requested your help on task \"{$this->task->title}\": \"{$this->helpMessage}\"",
            'url' => route('tasks.show', $this->task),
        ];
    }
}

