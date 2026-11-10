<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ContentActivityLog;
use App\Models\ContentApproval;
use App\Models\ContentMedia;
use App\Models\ContentPlatformSchedule;
use App\Models\ContentPost;
use App\Models\SocialAccount;
use App\Models\User;
use App\Notifications\ContentApprovalDecisionNotification;
use App\Notifications\ContentAssignedNotification;
use App\Notifications\ContentReviewSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ContentCalendarService
{
    /**
     * Create a new content post with optional media and multi-platform schedule.
     */
    public function createPost(array $data, User $actor, array $mediaFiles = [], array $platforms = []): ContentPost
    {
        return DB::transaction(function () use ($data, $actor, $mediaFiles, $platforms) {
            $brand = Brand::findOrFail($data['brand_id']);

            $scheduledAt = ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
            $status = $data['status'] ?? 'idea';

            if ($status === 'scheduled' && empty($scheduledAt)) {
                $status = 'planned';
            }

            $post = ContentPost::create([
                'client_id'          => $brand->customer_id ?? ($data['client_id'] ?? null),
                'brand_id'           => $brand->id,
                'title'              => trim($data['title']),
                'post_type'          => $data['post_type'] ?? 'single_image',
                'caption'            => $data['caption'] ?? null,
                'hashtags'           => $data['hashtags'] ?? null,
                'scheduled_at'       => $scheduledAt,
                'timezone'           => $data['timezone'] ?? config('app.timezone', 'Asia/Kolkata'),
                'content_creator_id' => $data['content_creator_id'] ?? $actor->id,
                'assigned_to'        => $data['assigned_to'] ?? null,
                'reviewer_id'        => $data['reviewer_id'] ?? null,
                'internal_notes'     => $data['internal_notes'] ?? null,
                'approval_required'  => isset($data['approval_required']) ? (bool) $data['approval_required'] : true,
                'status'             => $status,
                'created_by'         => $actor->id,
            ]);

            // Handle multi-platform schedules
            if (! empty($platforms)) {
                foreach ($platforms as $plat) {
                    $platName = is_array($plat) ? ($plat['platform'] ?? null) : $plat;
                    $accountId = is_array($plat) ? ($plat['social_account_id'] ?? null) : null;
                    if ($platName) {
                        $post->platformSchedules()->create([
                            'social_account_id' => $accountId,
                            'platform'          => $platName,
                            'custom_caption'    => is_array($plat) ? ($plat['custom_caption'] ?? null) : null,
                            'custom_hashtags'   => is_array($plat) ? ($plat['custom_hashtags'] ?? null) : null,
                            'scheduled_at'      => $scheduledAt,
                            'status'            => $status === 'scheduled' ? 'scheduled' : 'planned',
                        ]);
                    }
                }
            }

            // Upload media files
            if (! empty($mediaFiles)) {
                $sort = 0;
                foreach ($mediaFiles as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->uploadMedia($post, $file, $actor, null, $sort++);
                    }
                }
            }

            $this->logActivity($post, $actor, 'created', null, null, null, "Post \"{$post->title}\" created with status {$post->statusLabel()}.");

            // Notify assigned employee if assigned
            if ($post->assigned_to && (int) $post->assigned_to !== (int) $actor->id) {
                $assignee = User::find($post->assigned_to);
                if ($assignee) {
                    try {
                        $assignee->notify(new ContentAssignedNotification($post, $actor));
                    } catch (\Throwable) {}
                }
            }

            return $post;
        });
    }

    /**
     * Update an existing content post.
     */
    public function updatePost(ContentPost $post, array $data, User $actor): ContentPost
    {
        return DB::transaction(function () use ($post, $data, $actor) {
            $oldStatus = $post->status;
            $oldScheduledAt = $post->scheduled_at;
            $oldAssignedTo = $post->assigned_to;

            $scheduledAt = ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;

            $post->update([
                'title'             => isset($data['title']) ? trim($data['title']) : $post->title,
                'post_type'         => $data['post_type'] ?? $post->post_type,
                'caption'           => $data['caption'] ?? $post->caption,
                'hashtags'          => $data['hashtags'] ?? $post->hashtags,
                'scheduled_at'      => $scheduledAt,
                'timezone'          => $data['timezone'] ?? $post->timezone,
                'assigned_to'       => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : $post->assigned_to,
                'reviewer_id'       => array_key_exists('reviewer_id', $data) ? $data['reviewer_id'] : $post->reviewer_id,
                'internal_notes'    => $data['internal_notes'] ?? $post->internal_notes,
                'approval_required' => isset($data['approval_required']) ? (bool) $data['approval_required'] : $post->approval_required,
                'status'            => $data['status'] ?? $post->status,
            ]);

            // Sync platform schedules if provided
            if (isset($data['platforms']) && is_array($data['platforms'])) {
                $post->platformSchedules()->delete();
                foreach ($data['platforms'] as $plat) {
                    $platName = is_array($plat) ? ($plat['platform'] ?? null) : $plat;
                    $accountId = is_array($plat) ? ($plat['social_account_id'] ?? null) : null;
                    if ($platName) {
                        $post->platformSchedules()->create([
                            'social_account_id' => $accountId,
                            'platform'          => $platName,
                            'custom_caption'    => is_array($plat) ? ($plat['custom_caption'] ?? null) : null,
                            'custom_hashtags'   => is_array($plat) ? ($plat['custom_hashtags'] ?? null) : null,
                            'scheduled_at'      => $scheduledAt,
                            'status'            => $post->status === 'scheduled' ? 'scheduled' : 'planned',
                        ]);
                    }
                }
            }

            $this->logActivity($post, $actor, 'updated', null, null, null, 'Post content and details updated.');

            // Notify newly assigned employee if assignment changed
            if ($post->assigned_to && (int) $post->assigned_to !== (int) $oldAssignedTo && (int) $post->assigned_to !== (int) $actor->id) {
                $assignee = User::find($post->assigned_to);
                if ($assignee) {
                    try {
                        $assignee->notify(new ContentAssignedNotification($post, $actor));
                    } catch (\Throwable) {}
                }
                $this->logActivity($post, $actor, 'assigned', 'assigned_to', (string) $oldAssignedTo, (string) $post->assigned_to, "Assigned to {$assignee?->name}.");
            }

            return $post;
        });
    }

    /**
     * Upload and attach media to a content post.
     */
    public function uploadMedia(ContentPost $post, UploadedFile $file, User $actor, ?string $caption = null, int $sortOrder = 0): ContentMedia
    {
        $mime = $file->getClientMimeType() ?: $file->getMimeType();
        $mediaType = 'image';
        if (str_starts_with((string) $mime, 'video/')) {
            $mediaType = 'video';
        } elseif (str_starts_with((string) $mime, 'application/pdf')) {
            $mediaType = 'document';
        }

        $path = $file->store('content_media/' . $post->id, 'public');

        $media = $post->media()->create([
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_size'   => $file->getSize(),
            'mime_type'   => $mime ?: 'application/octet-stream',
            'media_type'  => $mediaType,
            'sort_order'  => $sortOrder,
            'caption'     => $caption,
            'uploaded_by' => $actor->id,
        ]);

        $this->logActivity($post, $actor, 'media_uploaded', 'media', null, $media->file_name, "Uploaded media \"{$media->file_name}\".");

        return $media;
    }

    /**
     * Delete an attached media file.
     */
    public function deleteMedia(ContentMedia $media, User $actor): void
    {
        $post = $media->post;
        $fileName = $media->file_name;

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        $this->logActivity($post, $actor, 'media_removed', 'media', $fileName, null, "Removed media \"{$fileName}\".");
    }

    /**
     * Submit content for review.
     */
    public function submitForReview(ContentPost $post, User $actor, ?string $notes = null): ContentPost
    {
        $post->update([
            'status' => 'pending_review',
        ]);

        $this->logActivity($post, $actor, 'review_submitted', 'status', $post->getOriginal('status'), 'pending_review', $notes ?: 'Submitted for review.');

        // Notify reviewer or managers
        if ($post->reviewer) {
            try {
                $post->reviewer->notify(new ContentReviewSubmittedNotification($post, $actor, $notes));
            } catch (\Throwable) {}
        } else {
            // Notify marketing managers
            $managers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'marketing_manager']))->get();
            foreach ($managers as $mgr) {
                if ($mgr->id !== $actor->id) {
                    try {
                        $mgr->notify(new ContentReviewSubmittedNotification($post, $actor, $notes));
                    } catch (\Throwable) {}
                }
            }
        }

        return $post;
    }

    /**
     * Reviewer decides (approve or request changes).
     */
    public function decideReview(ContentPost $post, User $actor, string $action, ?string $comment = null): ContentPost
    {
        if (! in_array($action, ['approved', 'changes_requested'], true)) {
            throw ValidationException::withMessages(['action' => 'Invalid review decision.']);
        }

        return DB::transaction(function () use ($post, $actor, $action, $comment) {
            $oldStatus = $post->status;
            $newStatus = $action === 'approved' ? 'approved' : 'changes_requested';

            $post->update(['status' => $newStatus]);

            ContentApproval::create([
                'content_post_id' => $post->id,
                'reviewer_id'     => $actor->id,
                'action'          => $action,
                'comment'         => $comment,
                'created_at'      => now(),
            ]);

            $label = $action === 'approved' ? 'approved' : 'requested changes on';
            $this->logActivity($post, $actor, $action, 'status', $oldStatus, $newStatus, "{$actor->name} {$label} this post. Note: " . ($comment ?: 'No comments provided.'));

            // Notify assignee or author
            $targetUser = $post->assignee ?: $post->contentCreator;
            if ($targetUser && (int) $targetUser->id !== (int) $actor->id) {
                try {
                    $targetUser->notify(new ContentApprovalDecisionNotification($post, $actor, $action, $comment));
                } catch (\Throwable) {}
            }

            return $post;
        });
    }

    /**
     * Schedule an approved content post onto the calendar.
     */
    public function schedulePost(ContentPost $post, User $actor, string|Carbon $scheduledAt, ?string $timezone = null): ContentPost
    {
        $dt = $scheduledAt instanceof Carbon ? $scheduledAt : Carbon::parse($scheduledAt);

        $oldStatus = $post->status;
        $post->update([
            'scheduled_at' => $dt,
            'timezone'     => $timezone ?: $post->timezone,
            'status'       => 'scheduled',
            'is_missed'    => false,
        ]);

        // Also update platform schedules
        $post->platformSchedules()->update([
            'scheduled_at' => $dt,
            'status'       => 'scheduled',
            'is_missed'    => false,
        ]);

        $this->logActivity($post, $actor, 'scheduled', 'scheduled_at', (string) $post->getOriginal('scheduled_at'), $dt->toDateTimeString(), "Scheduled for {$dt->format('d M Y, h:i A')}.");

        return $post;
    }

    /**
     * Reschedule a post (e.g. from calendar drag-and-drop or edit).
     */
    public function reschedulePost(ContentPost $post, User $actor, string|Carbon $newScheduledAt): ContentPost
    {
        $newDt = $newScheduledAt instanceof Carbon ? $newScheduledAt : Carbon::parse($newScheduledAt);
        $oldDt = $post->scheduled_at;

        $post->update([
            'scheduled_at' => $newDt,
            'is_missed'    => false,
            // If it was idea/planned, scheduling it transitions to scheduled if approved or planned
            'status'       => in_array($post->status, ['approved', 'scheduled'], true) ? 'scheduled' : $post->status,
        ]);

        $post->platformSchedules()->update([
            'scheduled_at' => $newDt,
            'is_missed'    => false,
        ]);

        $this->logActivity($post, $actor, 'rescheduled', 'scheduled_at', $oldDt ? $oldDt->toDateTimeString() : null, $newDt->toDateTimeString(), "Rescheduled to {$newDt->format('d M Y, h:i A')}.");

        return $post;
    }

    /**
     * Mark a post as published manually.
     */
    public function markPublished(ContentPost $post, User $actor, ?string $publishedAt = null, ?string $publishedUrl = null, ?string $notes = null): ContentPost
    {
        $publishDate = ! empty($publishedAt) ? Carbon::parse($publishedAt) : now();

        $post->update([
            'status'           => 'published',
            'published_at'     => $publishDate,
            'published_url'    => $publishedUrl,
            'published_by'     => $actor->id,
            'publishing_notes' => $notes,
            'is_missed'        => false,
            'failure_reason'   => null,
        ]);

        $post->platformSchedules()->update([
            'status'         => 'published',
            'published_at'   => $publishDate,
            'published_url'  => $publishedUrl,
            'published_by'   => $actor->id,
            'notes'          => $notes,
            'is_missed'      => false,
            'failure_reason' => null,
        ]);

        $desc = "Marked as published at {$publishDate->format('d M Y, h:i A')} by {$actor->name}." . ($publishedUrl ? " URL: {$publishedUrl}" : '');
        $this->logActivity($post, $actor, 'published', 'status', 'scheduled', 'published', $desc);

        return $post;
    }

    /**
     * Mark a post as failed.
     */
    public function markFailed(ContentPost $post, User $actor, string $reason, ?string $notes = null): ContentPost
    {
        $post->update([
            'status'           => 'failed',
            'failure_reason'   => $reason,
            'publishing_notes' => $notes,
            'is_missed'        => false,
        ]);

        $post->platformSchedules()->update([
            'status'         => 'failed',
            'failure_reason' => $reason,
            'notes'          => $notes,
        ]);

        $this->logActivity($post, $actor, 'failed', 'status', $post->getOriginal('status'), 'failed', "Publishing failed. Reason: {$reason}");

        return $post;
    }

    /**
     * Cancel a post.
     */
    public function cancelPost(ContentPost $post, User $actor, ?string $reason = null): ContentPost
    {
        $oldStatus = $post->status;
        $post->update([
            'status'         => 'cancelled',
            'internal_notes' => ($post->internal_notes ? $post->internal_notes . "\n" : '') . "Cancellation Reason: {$reason}",
        ]);

        $post->platformSchedules()->update([
            'status' => 'cancelled',
        ]);

        $this->logActivity($post, $actor, 'cancelled', 'status', $oldStatus, 'cancelled', "Post cancelled by {$actor->name}. Reason: " . ($reason ?: 'N/A'));

        return $post;
    }

    /**
     * Record an audit activity log entry.
     */
    public function logActivity(ContentPost $post, ?User $actor, string $action, ?string $field = null, ?string $old = null, ?string $new = null, ?string $desc = null): void
    {
        ContentActivityLog::create([
            'content_post_id' => $post->id,
            'user_id'         => $actor?->id,
            'action'          => $action,
            'field_name'      => $field,
            'old_value'       => $old ? substr($old, 0, 500) : null,
            'new_value'       => $new ? substr($new, 0, 500) : null,
            'description'     => $desc,
            'created_at'      => now(),
        ]);
    }

    /**
     * Query calendar events for a date window.
     */
    public function getCalendarFeed(User $user, Carbon $start, Carbon $end, array $filters = []): array
    {
        $query = ContentPost::query()
            ->with(['brand', 'client', 'assignee', 'media', 'platformSchedules.socialAccount'])
            ->forUser($user)
            ->where(function ($q) use ($start, $end) {
                // Include posts scheduled in this window
                $q->whereBetween('scheduled_at', [$start, $end])
                  // Or published in this window
                  ->orWhereBetween('published_at', [$start, $end]);
            });

        // Apply filters
        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['platform'])) {
            $query->whereHas('platformSchedules', fn ($sq) => $sq->where('platform', $filters['platform']));
        }
        if (! empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $query->where(function ($sq) use ($s) {
                $sq->where('title', 'like', $s)
                   ->orWhere('caption', 'like', $s)
                   ->orWhere('hashtags', 'like', $s);
            });
        }

        $posts = $query->orderBy('scheduled_at')->get();

        return $posts->map(function (ContentPost $post) {
            $platforms = $post->platformSchedules->pluck('platform')->unique()->all();
            $colorConfig = $post->brand ? $post->brand->colorConfig() : ['dot' => 'bg-indigo-500', 'badge' => 'bg-indigo-50 text-indigo-700'];

            $date = $post->scheduled_at ?: $post->published_at;

            $bg = match($post->status) {
                'published' => '#ecfdf5',
                'scheduled' => '#f5f3ff',
                'approved' => '#eff6ff',
                'pending_review' => '#fffbeb',
                'changes_requested' => '#fff7ed',
                'in_creation' => '#f0fdf4',
                'failed', 'missed' => '#fff1f2',
                'cancelled' => '#f1f5f9',
                default => '#f8fafc',
            };
            $text = match($post->status) {
                'published' => '#065f46',
                'scheduled' => '#5b21b6',
                'approved' => '#1e40af',
                'pending_review' => '#92400e',
                'changes_requested' => '#c2410c',
                'in_creation' => '#166534',
                'failed', 'missed' => '#9f1239',
                'cancelled' => '#475569',
                default => '#334155',
            };
            $bColor = $post->brand?->primary_color ?? $post->brand?->color ?? '#3b82f6';

            return [
                'id'            => $post->id,
                'title'         => $post->title,
                'start'         => $date?->toIso8601String() ?? $date?->format('Y-m-d'),
                'brand_name'    => $post->brand?->name ?? 'General',
                'brand_color'   => $bColor,
                'brandColor'    => $bColor,
                'backgroundColor' => $bg,
                'textColor'     => $text,
                'client_name'   => $post->client?->company ?? ($post->client?->name ?? 'Direct'),
                'post_type'     => $post->post_type,
                'post_type_label' => $post->postTypeLabel(),
                'post_type_icon'  => $post->postTypeIcon(),
                'status'        => $post->status,
                'status_label'  => $post->statusLabel(),
                'status_classes' => $post->statusBadgeClasses(),
                'status_badge_class' => $post->statusBadgeClasses(),
                'scheduled_at'  => $date?->toIso8601String(),
                'scheduled_date'=> $date?->format('Y-m-d'),
                'scheduled_time'=> $date?->format('H:i'),
                'display_time'  => $date?->format('h:i A'),
                'assignee_name' => $post->assignee?->name ?? 'Unassigned',
                'is_missed'     => $post->is_missed,
                'platforms'     => $platforms,
                'media_count'   => $post->media->count(),
                'first_media'   => $post->media->first()?->url(),
                'url'           => route('content.posts.show', $post),
                'extendedProps' => [
                    'brand_name' => $post->brand?->name ?? 'General',
                    'platforms' => $platforms,
                    'assignee_name' => $post->assignee?->name ?? 'Unassigned',
                    'status' => $post->status,
                    'status_badge_class' => $post->statusBadgeClasses(),
                ],
            ];
        })->all();
    }

    /**
     * Compute comprehensive dashboard statistics.
     */
    public function getDashboardStats(User $user): array
    {
        $base = ContentPost::query()->forUser($user);

        $now = now();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return [
            'total_posts'       => (clone $base)->count(),
            'planned'           => (clone $base)->whereIn('status', ['idea', 'planned'])->count(),
            'total_planned'     => (clone $base)->whereIn('status', ['idea', 'planned'])->count(),
            'in_creation'       => (clone $base)->where('status', 'in_creation')->count(),
            'pending_review'    => (clone $base)->where('status', 'pending_review')->count(),
            'approved'          => (clone $base)->where('status', 'approved')->count(),
            'scheduled'         => (clone $base)->where('status', 'scheduled')->count(),
            'published'         => (clone $base)->where('status', 'published')->count(),
            'missed'            => (clone $base)->where('is_missed', true)->count(),
            'failed'            => (clone $base)->where('status', 'failed')->count(),

            // Today's schedule
            'today_posts'       => (clone $base)
                ->with(['brand', 'assignee', 'platformSchedules.socialAccount'])
                ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
                ->orderBy('scheduled_at')
                ->get(),

            // Upcoming posts queue (next 7 days)
            'upcoming_posts'    => (clone $base)
                ->with(['brand', 'assignee', 'platformSchedules.socialAccount'])
                ->where('scheduled_at', '>', $now)
                ->whereIn('status', ['approved', 'scheduled'])
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get(),

            // Pending review approvals
            'pending_approvals' => (clone $base)
                ->with(['brand', 'contentCreator', 'assignee'])
                ->where('status', 'pending_review')
                ->latest('updated_at')
                ->limit(5)
                ->get(),

            // Recent activity
            'recent_activity'   => ContentActivityLog::query()
                ->with(['post.brand', 'user'])
                ->whereHas('post', fn ($pq) => $pq->forUser($user))
                ->latest('id')
                ->limit(8)
                ->get(),

            // Platform breakdown
            'platform_counts'   => ContentPlatformSchedule::query()
                ->whereHas('post', fn ($pq) => $pq->forUser($user))
                ->select('platform', DB::raw('count(*) as count'))
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->all(),

            // Brand breakdown
            'brand_counts'      => Brand::query()
                ->where('is_active', true)
                ->withCount(['contentPosts' => fn ($pq) => $pq->forUser($user)])
                ->orderByDesc('content_posts_count')
                ->limit(5)
                ->get(),
        ];
    }
}

