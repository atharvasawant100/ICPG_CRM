<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentPost extends Model
{
    use HasFactory, SoftDeletes;

    public const POST_TYPES = [
        'single_image' => 'Single Image',
        'video'        => 'Video Post',
        'carousel'     => 'Carousel (Multi-slide)',
        'reel_short'   => 'Reel / Short Video',
        'story'        => 'Story',
        'text_post'    => 'Text / Status Post',
        'other'        => 'Other Format',
    ];

    public const STATUSES = [
        'idea'              => 'Idea Draft',
        'planned'           => 'Planned',
        'in_creation'       => 'In Creation',
        'pending_review'    => 'Pending Review',
        'changes_requested' => 'Changes Requested',
        'approved'          => 'Approved',
        'scheduled'         => 'Scheduled',
        'published'         => 'Published',
        'failed'            => 'Failed',
        'cancelled'         => 'Cancelled',
    ];

    protected $fillable = [
        'client_id',
        'brand_id',
        'title',
        'post_type',
        'caption',
        'hashtags',
        'scheduled_at',
        'timezone',
        'content_creator_id',
        'assigned_to',
        'reviewer_id',
        'internal_notes',
        'approval_required',
        'status',
        'published_at',
        'published_url',
        'published_by',
        'publishing_notes',
        'failure_reason',
        'is_missed',
        'scheduled_date',
        'scheduled_time',
        'live_url',
        'notes',
        'require_approval',
        'created_by',
    ];

    public function getScheduledDateAttribute()
    {
        return $this->scheduled_at ? $this->scheduled_at->format('Y-m-d') : null;
    }

    public function setScheduledDateAttribute($value): void
    {
        if (empty($value)) return;
        $time = $this->scheduled_time ?? '12:00:00';
        $this->attributes['scheduled_at'] = Carbon::parse($value . ' ' . $time);
    }

    public function getScheduledTimeAttribute()
    {
        return $this->scheduled_at ? $this->scheduled_at->format('H:i:s') : null;
    }

    public function setScheduledTimeAttribute($value): void
    {
        if (empty($value)) return;
        $date = $this->scheduled_date ?? now()->format('Y-m-d');
        $this->attributes['scheduled_at'] = Carbon::parse($date . ' ' . $value);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->statusBadgeClasses();
    }

    public function getLiveUrlAttribute()
    {
        return $this->published_url;
    }

    public function setLiveUrlAttribute($value): void
    {
        $this->attributes['published_url'] = $value;
    }

    public function getNotesAttribute()
    {
        return $this->internal_notes;
    }

    public function setNotesAttribute($value): void
    {
        $this->attributes['internal_notes'] = $value;
    }

    public function getRequireApprovalAttribute(): bool
    {
        return (bool) $this->approval_required;
    }

    public function setRequireApprovalAttribute($value): void
    {
        $this->attributes['approval_required'] = (bool) $value;
    }

    public function isOverdue(): bool
    {
        if (! $this->scheduled_at || ! in_array($this->status, ['scheduled', 'approved'], true)) {
            return false;
        }

        return $this->scheduled_at->isPast();
    }

    protected $casts = [
        'scheduled_at'      => 'datetime',
        'published_at'      => 'datetime',
        'approval_required' => 'boolean',
        'is_missed'         => 'boolean',
    ];

    protected $attributes = [
        'post_type'         => 'single_image',
        'status'            => 'idea',
        'approval_required' => true,
        'is_missed'         => false,
        'timezone'          => 'Asia/Kolkata',
    ];

    /**
     * Relationships
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function customer(): BelongsTo
    {
        return $this->client();
    }

    public function contentCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ContentMedia::class)->orderBy('sort_order');
    }

    public function platformSchedules(): HasMany
    {
        return $this->hasMany(ContentPlatformSchedule::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ContentApproval::class)->latest('id');
    }

    public function latestApproval(): BelongsTo
    {
        return $this->belongsTo(ContentApproval::class, 'id', 'content_post_id')->latestOfMany();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ContentActivityLog::class)->latest('id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'content_post_id')->latest('id');
    }

    /**
     * Scopes
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->hasRole('marketing_manager')) {
            return $query;
        }

        // Team members can see posts assigned to them, created by them, or where they are reviewer
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('content_creator_id', $user->id)
              ->orWhere('reviewer_id', $user->id)
              ->orWhere('created_by', $user->id)
              ->orWhereHas('tasks', function ($tq) use ($user) {
                  $tq->where('assigned_to', $user->id);
              });
        });
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeMissed(Builder $query): Builder
    {
        return $query->where('is_missed', true);
    }

    /**
     * Helper checks
     */
    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'scheduled', 'published'], true);
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isChangesRequested(): bool
    {
        return $this->status === 'changes_requested';
    }

    /**
     * Labels and styling
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function postTypeLabel(): string
    {
        return self::POST_TYPES[$this->post_type] ?? ucfirst(str_replace('_', ' ', (string) $this->post_type));
    }

    public function postTypeIcon(): string
    {
        return match ($this->post_type) {
            'single_image' => '🖼️',
            'video'        => '🎥',
            'carousel'     => '📑',
            'reel_short'   => '⚡',
            'story'        => '⏱️',
            'text_post'    => '✍️',
            default        => '📝',
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'idea'              => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
            'planned'           => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800',
            'in_creation'       => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800',
            'pending_review'    => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
            'changes_requested' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
            'approved'          => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800',
            'scheduled'         => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-semibold',
            'published'         => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold',
            'failed'            => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800 font-bold',
            'cancelled'         => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
            default             => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
        };
    }
}
