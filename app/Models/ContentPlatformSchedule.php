<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPlatformSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_post_id',
        'social_account_id',
        'platform',
        'custom_caption',
        'custom_hashtags',
        'scheduled_at',
        'status',
        'published_at',
        'published_url',
        'published_by',
        'notes',
        'failure_reason',
        'is_missed',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'is_missed'    => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function effectiveCaption(): string
    {
        return ! empty($this->custom_caption) ? $this->custom_caption : (string) ($this->post?->caption ?? '');
    }

    public function effectiveHashtags(): string
    {
        return ! empty($this->custom_hashtags) ? $this->custom_hashtags : (string) ($this->post?->hashtags ?? '');
    }

    public function getPlatformBadgeClassAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => 'bg-pink-50 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300 border border-pink-200 dark:border-pink-800',
            'facebook'  => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
            'linkedin'  => 'bg-sky-50 text-sky-800 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-300 dark:border-sky-800',
            'twitter_x' => 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-600',
            'youtube'   => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800',
            'pinterest' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
            'tiktok'    => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-700',
            'threads'   => 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-700',
            default     => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800',
        };
    }

    public function getScheduledDateAttribute()
    {
        return $this->scheduled_at ? $this->scheduled_at : null;
    }

    public function setScheduledDateAttribute($value): void
    {
        $this->attributes['scheduled_at'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function platformLabel(): string
    {
        return SocialAccount::PLATFORMS[$this->platform] ?? ucfirst(str_replace('_', ' ', (string) $this->platform));
    }
}
