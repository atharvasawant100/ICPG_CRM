<?php

namespace App\Notifications;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentReviewSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ContentPost $post,
        protected ?User $submitter = null,
        protected ?string $notes = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->post;
        $name = $this->submitter ? $this->submitter->name : 'An employee';
        $brandName = $post->brand?->name ?? 'Brand';

        $msg = "{$name} submitted content for [{$brandName}] \"{$post->title}\" for review";
        if ($this->notes) {
            $msg .= ': ' . substr($this->notes, 0, 80);
        }

        return [
            'type'            => 'content_review_submitted',
            'content_post_id' => $post->id,
            'message'         => $msg . '.',
            'url'             => route('content.posts.show', $post),
        ];
    }
}

