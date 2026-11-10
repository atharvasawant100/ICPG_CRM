<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskHelpRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $helper,
        protected string $status, // accepted or rejected
        protected ?string $notes = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $action = $this->status === 'accepted' ? 'accepted' : 'declined';
        $msg = "{$this->helper->name} {$action} your help request for task \"{$this->task->title}\".";
        if ($this->notes) {
            $msg .= " Note: \"{$this->notes}\"";
        }

        return [
            'type' => 'task_help_responded',
            'task_id' => $this->task->id,
            'message' => $msg,
            'url' => route('tasks.show', $this->task),
        ];
    }
}

