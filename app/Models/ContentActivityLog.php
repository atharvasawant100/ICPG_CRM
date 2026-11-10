<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'content_post_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionIcon(): string
    {
        return match ($this->action) {
            'created'           => '✨',
            'updated'           => '✏️',
            'assigned'          => '👤',
            'media_uploaded'    => '📎',
            'media_removed'     => '🗑️',
            'review_submitted'  => '📤',
            'approved'          => '✅',
            'changes_requested' => '🔄',
            'scheduled'         => '📅',
            'rescheduled'       => '⏱️',
            'published'         => '🚀',
            'failed'            => '❌',
            'missed'            => '⚠️',
            'cancelled'         => '🚫',
            default             => '📝',
        };
    }
}

