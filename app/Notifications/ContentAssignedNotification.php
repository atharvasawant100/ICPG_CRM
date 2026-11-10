<?php

namespace App\Notifications;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ContentPost $post,
        protected ?User $assigner = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->post;
        $assignerName = $this->assigner ? $this->assigner->name : 'Management';
        $brandName = $post->brand?->name ?? 'Social Media';

        $msg = "{$assignerName} assigned you content creation for [{$brandName}] \"{$post->title}\"";
        if ($post->scheduled_at) {
            $msg .= ' scheduled for ' . $post->scheduled_at->format('d M Y, h:i A');
        }

        return [
            'type'            => 'content_assigned',
            'content_post_id' => $post->id,
            'message'         => $msg . '.',
            'url'             => route('content.posts.show', $post),
        ];
    }
}

