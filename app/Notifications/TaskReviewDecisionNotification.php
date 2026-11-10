<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $reviewer,
        protected string $decision, // approved or changes_requested
        protected ?string $notes = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $action = $this->decision === 'approved' ? 'approved' : 'requested changes on';
        $msg = "{$this->reviewer->name} {$action} task \"{$this->task->title}\".";
        if ($this->notes) {
            $msg .= " Notes: \"{$this->notes}\"";
        }

        return [
            'type' => 'task_review_' . $this->decision,
            'task_id' => $this->task->id,
            'message' => $msg,
            'url' => route('tasks.show', $this->task),
        ];
    }
}

