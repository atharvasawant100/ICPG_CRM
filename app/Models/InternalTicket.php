<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalTicket extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'manager_blocker' => 'Missing Requirements / Blocked by Manager',
        'unfair_blame' => 'Unfair Blame / Internal Politics',
        'missing_resources' => 'Missing Access, Credentials or Assets',
        'workplace_concern' => 'Workplace & Interpersonal Concern',
        'other' => 'Other Confidential Issue',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'in_review' => 'Under Admin Review',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    protected $fillable = [
        'ticket_number',
        'user_id',
        'task_id',
        'category',
        'priority',
        'subject',
        'description',
        'status',
        'is_confidential',
        'admin_notes',
        'admin_response',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'priority' => 'high',
        'status' => 'open',
        'is_confidential' => true,
    ];

    protected static function booted(): void
    {
        static::creating(function (InternalTicket $ticket) {
            if (empty($ticket->ticket_number)) {
                $maxId = (int) (static::max('id') ?? 0) + 1;
                $ticket->ticket_number = 'TKT-' . str_pad((string) $maxId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InternalTicketMessage::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(InternalTicketAttachment::class)->orderBy('created_at', 'asc');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_review'], true);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst($this->priority);
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'open' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 border border-amber-300 dark:border-amber-700',
            'in_review' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700',
            'resolved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700',
            'closed' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-600',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-600 text-white shadow-xs font-black animate-pulse',
            'high' => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300 border border-red-200 dark:border-red-800 font-bold',
            'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-semibold',
            'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-medium',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function categoryBadgeClasses(): string
    {
        return match ($this->category) {
            'manager_blocker' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
            'unfair_blame' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
            'missing_resources' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
            'workplace_concern' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
            default => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
        };
    }
}

