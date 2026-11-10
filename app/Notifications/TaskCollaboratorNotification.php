<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCollaboratorNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $actor,
        protected string $action // added or removed
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actionVerb = $this->action === 'added' ? 'added you as a collaborator on' : 'removed you as a collaborator from';

        return [
            'type' => 'task_collaborator_' . $this->action,
            'task_id' => $this->task->id,
            'message' => "{$this->actor->name} {$actionVerb} task \"{$this->task->title}\".",
            'url' => route('tasks.show', $this->task),
        ];
    }
}

