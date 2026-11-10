<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $submitter,
        protected ?string $notes = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $msg = "{$this->submitter->name} submitted task \"{$this->task->title}\" for your review.";
        if ($this->notes) {
            $msg .= " Note: \"{$this->notes}\"";
        }

        return [
            'type' => 'task_review_submitted',
            'task_id' => $this->task->id,
            'message' => $msg,
            'url' => route('tasks.show', $this->task),
        ];
    }
}

