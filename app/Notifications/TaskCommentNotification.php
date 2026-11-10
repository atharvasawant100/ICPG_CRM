<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TaskCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $commenter,
        protected string $commentText
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $preview = Str::limit($this->commentText, 60);

        return [
            'type' => 'task_comment',
            'task_id' => $this->task->id,
            'message' => "{$this->commenter->name} commented on \"{$this->task->title}\": \"{$preview}\"",
            'url' => route('tasks.show', $this->task),
        ];
    }
}

