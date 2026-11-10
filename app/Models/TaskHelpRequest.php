<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHelpRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'task_id',
        'requester_id',
        'helper_id',
        'message',
        'status',
        'response_notes',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function helper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'helper_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50',
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}

