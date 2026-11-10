<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'client_name',
        'department',
        'status',
        'color',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Alias 'name' to 'title' for backwards/cross-compatibility.
     */
    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    /**
     * All tasks belonging to this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->latest('id');
    }

    /**
     * The user who created this project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate task completion percentage (0 - 100).
     */
    public function completionPercentage(): int
    {
        $total = $this->tasks->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasks->where('status', 'completed')->count();

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Total task count.
     */
    public function totalTasksCount(): int
    {
        return $this->tasks->count();
    }

    /**
     * Completed task count.
     */
    public function completedTasksCount(): int
    {
        return $this->tasks->where('status', 'completed')->count();
    }

    /**
     * Pending task count.
     */
    public function pendingTasksCount(): int
    {
        return $this->tasks->where('status', 'pending')->count();
    }

    /**
     * In-progress task count.
     */
    public function inProgressTasksCount(): int
    {
        return $this->tasks->where('status', 'in_progress')->count();
    }

    /**
     * Check if project is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->status !== 'completed' && $this->due_date->isPast();
    }

    /**
     * Color scheme for the project card accent.
     */
    public function colorConfig(): array
    {
        return match ($this->color) {
            'violet', 'purple' => [
                'border' => 'border-purple-500/20 dark:border-purple-500/30',
                'bg_gradient' => 'from-purple-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'bar' => 'bg-gradient-to-r from-purple-500 to-indigo-500',
                'accent' => 'text-purple-600 dark:text-purple-400',
            ],
            'emerald', 'green' => [
                'border' => 'border-emerald-500/20 dark:border-emerald-500/30',
                'bg_gradient' => 'from-emerald-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'bar' => 'bg-gradient-to-r from-emerald-500 to-teal-500',
                'accent' => 'text-emerald-600 dark:text-emerald-400',
            ],
            'amber', 'orange' => [
                'border' => 'border-amber-500/20 dark:border-amber-500/30',
                'bg_gradient' => 'from-amber-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'bar' => 'bg-gradient-to-r from-amber-500 to-yellow-500',
                'accent' => 'text-amber-600 dark:text-amber-400',
            ],
            'rose', 'red' => [
                'border' => 'border-rose-500/20 dark:border-rose-500/30',
                'bg_gradient' => 'from-rose-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'bar' => 'bg-gradient-to-r from-rose-500 to-pink-500',
                'accent' => 'text-rose-600 dark:text-rose-400',
            ],
            'sky', 'cyan' => [
                'border' => 'border-sky-500/20 dark:border-sky-500/30',
                'bg_gradient' => 'from-sky-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                'bar' => 'bg-gradient-to-r from-sky-500 to-blue-500',
                'accent' => 'text-sky-600 dark:text-sky-400',
            ],
            default => [
                'border' => 'border-indigo-500/20 dark:border-indigo-500/30',
                'bg_gradient' => 'from-indigo-500/10 via-transparent to-transparent',
                'tag_bg' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'bar' => 'bg-gradient-to-r from-indigo-500 to-violet-500',
                'accent' => 'text-indigo-600 dark:text-indigo-400',
            ],
        };
    }

    /**
     * Scope projects visible to a given user based on role and department.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            if ($user->department) {
                $userDept = strtolower(trim((string) $user->department));
                $q->whereRaw('LOWER(TRIM(department)) = ?', [$userDept]);
            } else {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function ($tq) use ($user) {
                      $tq->where('assigned_to', $user->id)
                         ->orWhere('assigned_by', $user->id);
                  });
            }
        });
    }

    /**
     * Check if a user can access / view this project.
     */
    public function canBeAccessedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->department && $user->department) {
            return strtolower(trim((string) $this->department)) === strtolower(trim((string) $user->department));
        }

        if ($this->created_by && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        return $this->tasks()->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('assigned_by', $user->id);
        })->exists();
    }

    /**
     * Check if a user can manage (edit or delete) this project.
     */
    public function canBeManagedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->created_by && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        if ($user->isManager() && $this->department && $user->department && strtolower((string) $this->department) === strtolower((string) $user->department)) {
            return true;
        }

        return false;
    }

    /**
     * Status badge styling.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
            'on_hold' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
            'archived' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600',
            default => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50',
        };
    }
}
