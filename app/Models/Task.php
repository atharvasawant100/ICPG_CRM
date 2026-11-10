<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
        'urgent' => 'Urgent',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'rework' => 'Rework',
        'blocked' => 'Blocked',
        'on_hold' => 'On Hold',
        'in_review' => 'In Review',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'project_id',
        'content_post_id',
        'content_task_type',
        'title',
        'project_name',
        'description',
        'expected_outcomes',
        'assigned_to',
        'assigned_by',
        'department',
        'priority',
        'status',
        'start_date',
        'due_date',
        'progress',
        'review_required',
        'is_escalated',
        'escalation_reason',
        'escalated_by',
        'escalated_at',
        'rework_notes',
        'rework_count',
        'rework_by',
        'rework_at',
        'started_at',
        'total_seconds_spent',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'integer',
        'review_required' => 'boolean',
        'is_escalated' => 'boolean',
        'escalated_at' => 'datetime',
        'rework_count' => 'integer',
        'rework_at' => 'datetime',
        'started_at' => 'datetime',
        'total_seconds_spent' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'priority' => 'medium',
        'status' => 'open',
        'progress' => 0,
        'review_required' => false,
        'is_escalated' => false,
        'rework_count' => 0,
        'total_seconds_spent' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if ($task->status === 'in_progress' && ! $task->started_at) {
                $task->started_at = Carbon::now();
            }
        });

        static::updating(function (Task $task) {
            if (! $task->isDirty('status')) {
                return;
            }

            $oldStatus = $task->getOriginal('status');
            $newStatus = $task->status;
            $now = Carbon::now();

            // Transitioning INTO in_progress starts the timer
            if ($oldStatus !== 'in_progress' && $newStatus === 'in_progress') {
                $task->started_at = $now;
            }

            // Transitioning OUT OF in_progress stops the timer and accumulates seconds
            if ($oldStatus === 'in_progress' && $newStatus !== 'in_progress') {
                if ($task->started_at) {
                    $elapsed = max(0, (int) $task->started_at->diffInSeconds($now, false));
                    $task->total_seconds_spent = (int) $task->total_seconds_spent + $elapsed;
                }
                $task->started_at = null;
            }

            if ($newStatus === 'completed') {
                if (! $task->completed_at) {
                    $task->completed_at = $now;
                }
                $task->progress = 100;
            } elseif ($newStatus !== 'completed') {
                $task->completed_at = null;
            }
        });
    }

    /**
     * Formatted identifier, e.g. TSK-00142.
     */
    public function getFormattedIdAttribute(): string
    {
        return 'TSK-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * The project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The social media content post this task is linked to.
     */
    public function contentPost(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    /**
     * The single accountable employee currently owning this task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The employee who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * The administrator who escalated this task.
     */
    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    /**
     * Whether this task has been escalated by an administrator.
     */
    public function isEscalated(): bool
    {
        return (bool) $this->is_escalated;
    }

    /**
     * The manager or admin or creator who requested rework.
     */
    public function reworkBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rework_by');
    }

    /**
     * Whether this task is currently sent back for rework.
     */
    public function isRework(): bool
    {
        return $this->status === 'rework';
    }

    /**
     * Full delegation & assignment history.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class)->orderBy('assigned_at', 'desc');
    }

    /**
     * Activity and audit trail.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(TaskActivityLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Daily work logs recorded when punching out.
     */
    public function workLogs(): HasMany
    {
        return $this->hasMany(TaskWorkLog::class)->latest('worked_date');
    }

    /**
     * Help requests associated with this task.
     */
    public function helpRequests(): HasMany
    {
        return $this->hasMany(TaskHelpRequest::class)->orderBy('created_at', 'desc');
    }

    /**
     * Active accepted help requests.
     */
    public function acceptedHelpRequests(): HasMany
    {
        return $this->hasMany(TaskHelpRequest::class)->where('status', 'accepted')->orderBy('updated_at', 'desc');
    }

    /**
     * Task collaborators.
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(TaskCollaborator::class);
    }

    /**
     * Collaborator users pivot.
     */
    public function collaboratorUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_collaborators', 'task_id', 'user_id')
            ->withPivot('added_by', 'role')
            ->withTimestamps();
    }

    /**
     * Top-level task comments with replies and user data.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id')->with(['replies.user', 'user'])->orderBy('created_at', 'asc');
    }

    /**
     * Task file attachments.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->with('user')->orderBy('created_at', 'desc');
    }

    /**
     * Task review submissions and decisions.
     */
    public function reviewActions(): HasMany
    {
        return $this->hasMany(TaskReviewAction::class)->with('reviewer')->orderBy('created_at', 'desc');
    }

    /**
     * Check if a user is an active collaborator.
     */
    public function hasCollaborator(int $userId): bool
    {
        return $this->collaborators()->where('user_id', $userId)->exists();
    }

    /**
     * Check if task is currently under review.
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'in_review';
    }

    /**
     * Scope for active tasks (not completed or cancelled).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for overdue tasks (past due date, not completed or cancelled).
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today());
    }

    /**
     * Scope for tasks assigned to a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for tasks created by a specific user.
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('assigned_by', $userId);
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if task is overdue (due date is strictly before today and task is not completed/cancelled).
     */
    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && ! in_array($this->status, ['completed', 'cancelled'], true)
            && $this->due_date->copy()->startOfDay()->lt(Carbon::today());
    }

    /**
     * Number of whole days past the due date.
     */
    public function overdueDays(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return max(1, (int) $this->due_date->copy()->startOfDay()->diffInDays(Carbon::today()));
    }

    /**
     * Human-readable overdue badge label, e.g. "Overdue by 3 days".
     */
    public function overdueBadgeLabel(): ?string
    {
        $days = $this->overdueDays();
        if ($days <= 0) {
            return null;
        }

        return 'Overdue by ' . $days . ' ' . Str::plural('day', $days);
    }

    /**
     * Check if the task timer is currently running.
     */
    public function isTimerRunning(): bool
    {
        return $this->status === 'in_progress' && $this->started_at !== null;
    }

    /**
     * Total seconds spent including any currently active in-progress session.
     */
    public function currentSecondsSpent(): int
    {
        $total = (int) ($this->total_seconds_spent ?? 0);

        if ($this->isTimerRunning()) {
            $elapsed = max(0, (int) $this->started_at->diffInSeconds(Carbon::now(), false));
            $total += $elapsed;
        }

        return $total;
    }

    /**
     * Format the time spent into a readable string (e.g. "2h 15m", "14m 30s", "45s").
     */
    public function formattedTimeSpent(): string
    {
        $seconds = $this->currentSecondsSpent();

        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        if ($minutes > 0) {
            return $secs > 0 ? "{$minutes}m {$secs}s" : "{$minutes}m";
        }

        return "{$secs}s";
    }

    /**
     * Mark the task as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    /**
     * Toggle task between completed and open/in_progress.
     */
    public function toggleCompleted(): bool
    {
        if ($this->isCompleted()) {
            $lastLog = $this->activityLogs()
                ->where('action', 'status_changed')
                ->where('new_value', 'completed')
                ->latest('id')
                ->first();

            $revertStatus = ($lastLog && $lastLog->old_value) ? $lastLog->old_value : 'open';

            $this->update([
                'status' => $revertStatus,
                'progress' => 0,
                'completed_at' => null,
            ]);
            return false;
        }

        $oldStatus = $this->status;

        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        TaskActivityLog::create([
            'task_id' => $this->id,
            'user_id' => auth()->id() ?? $this->assigned_to,
            'action' => 'status_changed',
            'field_name' => 'status',
            'old_value' => $oldStatus,
            'new_value' => 'completed',
            'comment' => 'Quick toggled to completed',
        ]);

        return true;
    }

    /**
     * Human-readable department name.
     */
    public function departmentLabel(): string
    {
        $dept = $this->department ?: ($this->assignee?->department ?: ($this->project?->department ?: 'General'));
        return ucfirst(strtolower($dept));
    }

    /**
     * Department badge color classes (Tailwind CSS) for crisp visual classification.
     */
    public function departmentBadgeClasses(): string
    {
        $dept = strtolower((string) ($this->department ?: ($this->assignee?->department ?: ($this->project?->department ?: 'general'))));

        return match ($dept) {
            'development', 'dev', 'engineering' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            'marketing', 'growth' => 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-950/40 dark:text-pink-300 dark:border-pink-800',
            'sales', 'business' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'hr', 'human resources' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'design', 'ui/ux', 'creative' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
            'finance', 'accounts' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800',
            'support', 'operations', 'ops' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800',
            default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        };
    }

    /**
     * Department icon representation.
     */
    public function departmentIcon(): string
    {
        $dept = strtolower((string) ($this->department ?: ($this->assignee?->department ?: ($this->project?->department ?: 'general'))));

        return match ($dept) {
            'development', 'dev', 'engineering' => '💻',
            'marketing', 'growth' => '📢',
            'sales', 'business' => '📈',
            'hr', 'human resources' => '👥',
            'design', 'ui/ux', 'creative' => '🎨',
            'finance', 'accounts' => '💳',
            'support', 'operations', 'ops' => '🛠️',
            default => '🏢',
        };
    }

    /**
     * Priority label.
     */
    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst((string) $this->priority);
    }

    /**
     * Status label.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? str_replace('_', ' ', ucfirst((string) $this->status));
    }

    /**
     * Priority badge CSS color classes.
     */
    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'critical', 'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-800/50',
            'high' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
            'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    /**
     * Status badge CSS color classes.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
            'rework' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200 border-2 border-amber-400 dark:border-amber-600 font-bold',
            'in_progress' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50',
            'in_review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50',
            'blocked' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50',
            'on_hold' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border border-orange-200 dark:border-orange-800/50',
            'open', 'pending' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800/50',
            'cancelled' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-700',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
