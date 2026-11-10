<?php

namespace App\Notifications;

use App\Models\ContentPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentPublishMissedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ContentPost $post
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->post;
        $brandName = $post->brand?->name ?? 'Brand';
        $time = $post->scheduled_at ? $post->scheduled_at->format('d M, h:i A') : 'scheduled time';

        return [
            'type'            => 'content_publish_missed',
            'content_post_id' => $post->id,
            'message'         => "Scheduled publishing missed for [{$brandName}] \"{$post->title}\" ({$time}). Please publish or reschedule.",
            'url'             => route('content.posts.show', $post),
        ];
    }
}

