<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReviewAction extends Model
{
    use HasFactory;

    public const ACTION_SUBMITTED = 'submitted';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_CHANGES_REQUESTED = 'changes_requested';

    protected $fillable = [
        'task_id',
        'reviewer_id',
        'action',
        'notes',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function actionBadgeClasses(): string
    {
        return match ($this->action) {
            self::ACTION_APPROVED => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
            self::ACTION_CHANGES_REQUESTED => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
            self::ACTION_SUBMITTED => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_APPROVED => 'Approved',
            self::ACTION_CHANGES_REQUESTED => 'Changes Requested',
            self::ACTION_SUBMITTED => 'Submitted for Review',
            default => ucfirst(str_replace('_', ' ', (string) $this->action)),
        };
    }
}

