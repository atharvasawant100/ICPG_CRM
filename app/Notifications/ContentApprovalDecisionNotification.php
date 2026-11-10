<?php

namespace App\Notifications;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentApprovalDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ContentPost $post,
        protected ?User $reviewer = null,
        protected string $action = 'approved',
        protected ?string $comment = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->post;
        $name = $this->reviewer ? $this->reviewer->name : 'Reviewer';
        $brandName = $post->brand?->name ?? 'Brand';

        $actionText = $this->action === 'approved' ? 'approved' : 'requested revisions on';
        $msg = "{$name} {$actionText} your content for [{$brandName}] \"{$post->title}\"";
        if ($this->comment) {
            $msg .= ' ("' . substr($this->comment, 0, 70) . '")';
        }

        return [
            'type'            => 'content_approval_decision',
            'action'          => $this->action,
            'content_post_id' => $post->id,
            'message'         => $msg . '.',
            'url'             => route('content.posts.show', $post),
        ];
    }
}

