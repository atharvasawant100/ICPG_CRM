<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccount extends Model
{
    use HasFactory, SoftDeletes;

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'linkedin'  => 'LinkedIn',
        'twitter_x' => 'X (Twitter)',
        'youtube'   => 'YouTube',
        'pinterest' => 'Pinterest',
        'tiktok'    => 'TikTok',
        'threads'   => 'Threads',
        'other'     => 'Other Platform',
    ];

    protected $fillable = [
        'brand_id',
        'customer_id',
        'client_id',
        'platform',
        'account_name',
        'username',
        'platform_account_id',
        'profile_url',
        'avatar_url',
        'connection_status',
        'credentials',
        'notes',
        'is_active',
        'created_by',
    ];

    public function setClientIdAttribute($value): void
    {
        $this->attributes['customer_id'] = $value;
    }

    public function getClientIdAttribute()
    {
        return $this->customer_id;
    }

    public function getPlatformBadgeClassAttribute(): string
    {
        return $this->platformBadgeClasses();
    }

    protected $casts = [
        'is_active'   => 'boolean',
        'credentials' => 'encrypted', // Encrypted at rest for token security
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ContentPlatformSchedule::class);
    }

    public function platformLabel(): string
    {
        return self::PLATFORMS[$this->platform] ?? ucfirst(str_replace('_', ' ', (string) $this->platform));
    }

    public function platformIcon(): string
    {
        return match ($this->platform) {
            'instagram' => '📸',
            'facebook'  => '📘',
            'linkedin'  => '💼',
            'twitter_x' => '✖️',
            'youtube'   => '▶️',
            'pinterest' => '📌',
            'tiktok'    => '🎵',
            'threads'   => '🧵',
            default     => '🌐',
        };
    }

    public function platformBadgeClasses(): string
    {
        return match ($this->platform) {
            'instagram' => 'bg-pink-50 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300 border-pink-200 dark:border-pink-800',
            'facebook'  => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'linkedin'  => 'bg-sky-50 text-sky-800 dark:bg-sky-950/40 dark:text-sky-300 border-sky-300 dark:border-sky-800',
            'twitter_x' => 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600',
            'youtube'   => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border-red-200 dark:border-red-800',
            'pinterest' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            'tiktok'    => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border-neutral-300 dark:border-neutral-700',
            'threads'   => 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 border-zinc-300 dark:border-zinc-700',
            default     => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
        };
    }
}
