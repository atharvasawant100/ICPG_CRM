<?php

namespace App\Console\Commands;

use App\Models\ContentActivityLog;
use App\Models\ContentPost;
use App\Models\User;
use App\Notifications\ContentPublishMissedNotification;
use Illuminate\Console\Command;

class CheckMissedContentSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:check-missed-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flag scheduled social media posts that passed their scheduled time without being published.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $missedPosts = ContentPost::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<', $now)
            ->where('is_missed', false)
            ->get();

        if ($missedPosts->isEmpty()) {
            $this->info('No missed scheduled posts found.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($missedPosts as $post) {
            $post->update(['is_missed' => true]);

            $post->platformSchedules()
                ->where('status', 'scheduled')
                ->where('scheduled_at', '<', $now)
                ->where('is_missed', false)
                ->update(['is_missed' => true]);

            // Record activity log
            ContentActivityLog::create([
                'content_post_id' => $post->id,
                'user_id'         => null,
                'action'          => 'missed',
                'field_name'      => 'is_missed',
                'old_value'       => '0',
                'new_value'       => '1',
                'description'     => "Scheduled publishing time ({$post->scheduled_at->format('d M Y, h:i A')}) passed without being marked as published.",
                'created_at'      => now(),
            ]);

            // Notify responsible employee
            $recipient = $post->assignee ?: $post->contentCreator;
            if ($recipient) {
                try {
                    $recipient->notify(new ContentPublishMissedNotification($post));
                } catch (\Throwable) {}
            }

            $count++;
        }

        $this->info("Flagged {$count} overdue scheduled post(s) as missed.");
        return self::SUCCESS;
    }
}
