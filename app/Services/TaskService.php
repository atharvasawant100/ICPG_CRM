<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskActivityLog;
use App\Models\TaskAssignment;
use App\Models\TaskAttachment;
use App\Models\TaskCollaborator;
use App\Models\TaskComment;
use App\Models\TaskHelpRequest;
use App\Models\TaskReviewAction;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCollaboratorNotification;
use App\Notifications\TaskCommentNotification;
use App\Notifications\TaskEscalatedNotification;
use App\Notifications\TaskHelpRequestedNotification;
use App\Notifications\TaskHelpRespondedNotification;
use App\Notifications\TaskReviewDecisionNotification;
use App\Notifications\TaskReviewSubmittedNotification;
use App\Notifications\TaskReworkNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskService
{
    /**
     * Create a new task.
     * Logged-in user becomes creator and default assignee (self-task),
     * unless an explicit assignee is authorized and provided.
     */
    public function createTask(User $creator, array $data): Task
    {
        return DB::transaction(function () use ($creator, $data) {
            $assignedToId = ! empty($data['assigned_to']) ? (int) $data['assigned_to'] : $creator->id;
            $targetUser = $assignedToId === $creator->id ? $creator : User::find($assignedToId);

            // Business rule: Regular employees cannot assign tasks to managers or administrators
            if (! $creator->isAdmin() && ! $creator->isManager() && $targetUser && $assignedToId !== $creator->id) {
                if ($targetUser->isManager() || $targetUser->isAdmin() || ((int) $creator->manager_id === (int) $targetUser->id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'assigned_to' => 'Employees cannot assign tasks to managers or administrators.',
                    ]);
                }
            }

            // Business rule: When Admin assigns task to EMP, it must be of Urgent priority
            $priority = $data['priority'] ?? 'medium';
            if ($creator->isAdmin() && $targetUser && ! $targetUser->isAdmin() && $assignedToId !== $creator->id) {
                $priority = 'urgent';
            }

            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'expected_outcomes' => $data['expected_outcomes'] ?? null,
                'priority' => $priority,
                'status' => $data['status'] ?? 'open',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'progress' => isset($data['progress']) ? (int) $data['progress'] : 0,
                'review_required' => ! empty($data['review_required']),
                'project_id' => ! empty($data['project_id']) ? (int) $data['project_id'] : null,
                'project_name' => $data['project_name'] ?? null,
                'department' => ! empty($data['department']) ? strtolower((string) $data['department']) : ($targetUser?->department ?? $creator->department),
                'assigned_by' => $creator->id,
                'assigned_to' => $assignedToId,
            ]);

            // Record initial assignment history
            TaskAssignment::create([
                'task_id' => $task->id,
                'assigned_by' => $creator->id,
                'assigned_to' => $assignedToId,
                'notes' => $data['assignment_notes'] ?? ($assignedToId === $creator->id ? 'Self-assigned task' : 'Direct task assignment'),
                'assigned_at' => now(),
            ]);

            // Record activity log
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $creator->id,
                'action' => 'created',
                'field_name' => null,
                'old_value' => null,
                'new_value' => $task->status,
                'comment' => "Task created by {$creator->name}",
            ]);

            // Notify assignee if delegated to someone else
            if ($assignedToId !== $creator->id && $targetUser) {
                try {
                    $targetUser->notify(new TaskAssignedNotification($task, $creator->name));
                } catch (\Throwable) {
                }
            }

            return $task;
        });
    }

    /**
     * Delegate task to an eligible colleague.
     * Enforces the single accountable owner principle.
     */
    public function delegateTask(Task $task, User $delegator, int $newAssigneeId, ?string $notes = null): Task
    {
        return DB::transaction(function () use ($task, $delegator, $newAssigneeId, $notes) {
            $newAssignee = User::findOrFail($newAssigneeId);

            if ((int) $task->assigned_to === (int) $newAssigneeId) {
                if ($delegator->isAdmin() && ! $newAssignee->isAdmin() && $task->priority !== 'urgent') {
                    $task->priority = 'urgent';
                    $task->save();
                }
                return $task;
            }

            // Business rule: Regular employees cannot delegate tasks to managers or administrators
            if (! $delegator->isAdmin() && ! $delegator->isManager()) {
                if ($newAssignee->isManager() || $newAssignee->isAdmin() || ((int) $delegator->manager_id === (int) $newAssignee->id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'assigned_to' => 'Employees cannot delegate tasks to managers or administrators.',
                    ]);
                }
            }

            $oldAssignee = $task->assignee;
            $oldAssigneeName = $oldAssignee?->name ?? "User #{$task->assigned_to}";
            $oldPriority = $task->priority;

            // Update accountable owner
            $task->assigned_to = $newAssigneeId;
            if ($newAssignee->department) {
                $task->department = $newAssignee->department;
            }

            // Business rule: When Admin assigns/delegates task to EMP, it should be of Urgent priority
            if ($delegator->isAdmin() && ! $newAssignee->isAdmin()) {
                $task->priority = 'urgent';
            }

            $task->save();

            // Record immutable delegation in assignment history
            TaskAssignment::create([
                'task_id' => $task->id,
                'assigned_by' => $delegator->id,
                'assigned_to' => $newAssigneeId,
                'notes' => $notes ?: "Delegated by {$delegator->name}" . ($delegator->isAdmin() && ! $newAssignee->isAdmin() ? ' (Elevated to Urgent priority by Admin)' : ''),
                'assigned_at' => now(),
            ]);

            // Record activity trail
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $delegator->id,
                'action' => 'delegated',
                'field_name' => 'assigned_to',
                'old_value' => $oldAssigneeName,
                'new_value' => $newAssignee->name,
                'comment' => $notes ?: "Task delegated to {$newAssignee->name}" . ($delegator->isAdmin() && ! $newAssignee->isAdmin() ? ' (Priority set to Urgent by Admin)' : ''),
            ]);

            if ($oldPriority !== $task->priority) {
                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => $delegator->id,
                    'action' => 'updated',
                    'field_name' => 'priority',
                    'old_value' => $oldPriority,
                    'new_value' => $task->priority,
                    'comment' => 'Priority elevated to Urgent (delegated by Admin)',
                ]);
            }

            // Notify new assignee
            try {
                $newAssignee->notify(new TaskAssignedNotification($task, $delegator->name));
            } catch (\Throwable) {
            }

            return $task->fresh(['assignee', 'creator', 'assignments', 'activityLogs']);
        });
    }

    /**
     * Update task status and progress with activity logging.
     */
    public function updateStatus(Task $task, User $actor, string $newStatus, ?int $progress = null, ?string $comment = null): Task
    {
        return DB::transaction(function () use ($task, $actor, $newStatus, $progress, $comment) {
            $oldStatus = $task->status;
            $oldProgress = (int) $task->progress;

            $task->status = $newStatus;

            if ($progress !== null) {
                $task->progress = max(0, min(100, $progress));
            } elseif ($newStatus === 'completed') {
                $task->progress = 100;
            }

            $task->save();

            if ($oldStatus !== $newStatus) {
                if ($newStatus === 'completed') {
                    try {
                        app(\App\Services\NotificationDispatcher::class)->taskCompleted($task, $actor);
                    } catch (\Throwable) {
                    }
                }

                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => $actor->id,
                    'action' => 'status_changed',
                    'field_name' => 'status',
                    'old_value' => $oldStatus,
                    'new_value' => $newStatus,
                    'comment' => $comment ?: "Status changed from {$oldStatus} to {$newStatus}",
                ]);
            }

            if ($progress !== null && $oldProgress !== $task->progress) {
                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => $actor->id,
                    'action' => 'progress_updated',
                    'field_name' => 'progress',
                    'old_value' => "{$oldProgress}%",
                    'new_value' => "{$task->progress}%",
                    'comment' => "Progress updated to {$task->progress}%",
                ]);
            }

            return $task->fresh();
        });
    }

    /**
     * Update task details (title, description, outcomes, priority, dates).
     */
    public function updateTask(Task $task, User $actor, array $data): Task
    {
        return DB::transaction(function () use ($task, $actor, $data) {
            // Handle reassignment if assigned_to is supplied and changed
            if (! empty($data['assigned_to']) && (int) $data['assigned_to'] !== (int) $task->assigned_to) {
                $newAssignee = User::find($data['assigned_to']);
                if ($newAssignee) {
                    if (! $actor->isAdmin() && ! $actor->isManager()) {
                        if ($newAssignee->isManager() || $newAssignee->isAdmin() || ((int) $actor->manager_id === (int) $newAssignee->id)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'assigned_to' => 'Employees cannot assign tasks to managers or administrators.',
                            ]);
                        }
                    }

                    $task->assigned_to = $newAssignee->id;
                    if ($newAssignee->department) {
                        $task->department = $newAssignee->department;
                    }

                    TaskAssignment::create([
                        'task_id' => $task->id,
                        'assigned_by' => $actor->id,
                        'assigned_to' => $newAssignee->id,
                        'notes' => "Reassigned by {$actor->name}",
                        'assigned_at' => now(),
                    ]);

                    // When Admin assigns/reassigns to EMP, priority is urgent
                    if ($actor->isAdmin() && ! $newAssignee->isAdmin()) {
                        $data['priority'] = 'urgent';
                    }
                }
            }

            $task->fill([
                'title' => $data['title'] ?? $task->title,
                'description' => array_key_exists('description', $data) ? $data['description'] : $task->description,
                'expected_outcomes' => array_key_exists('expected_outcomes', $data) ? $data['expected_outcomes'] : $task->expected_outcomes,
                'priority' => $data['priority'] ?? $task->priority,
                'department' => ! empty($data['department']) ? strtolower((string) $data['department']) : $task->department,
                'start_date' => array_key_exists('start_date', $data) ? $data['start_date'] : $task->start_date,
                'due_date' => array_key_exists('due_date', $data) ? $data['due_date'] : $task->due_date,
                'review_required' => array_key_exists('review_required', $data) ? (bool) $data['review_required'] : $task->review_required,
                'project_id' => array_key_exists('project_id', $data) ? $data['project_id'] : $task->project_id,
            ]);

            if (isset($data['progress'])) {
                $task->progress = max(0, min(100, (int) $data['progress']));
            }

            if (isset($data['status'])) {
                $task->status = $data['status'];
            }

            $isDirty = $task->isDirty();
            $dirtyFields = array_keys($task->getDirty());

            $task->save();

            if ($isDirty) {
                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => $actor->id,
                    'action' => 'updated',
                    'field_name' => implode(', ', $dirtyFields),
                    'old_value' => null,
                    'new_value' => null,
                    'comment' => "Task updated by {$actor->name}",
                ]);
            }

            return $task->fresh();
        });
    }

    /**
     * Request help from an eligible colleague.
     * Crucial rule: Requesting help NEVER changes the task's accountable owner.
     */
    public function requestHelp(Task $task, User $requester, int $helperId, string $message): TaskHelpRequest
    {
        return DB::transaction(function () use ($task, $requester, $helperId, $message) {
            $helper = User::findOrFail($helperId);

            $helpRequest = TaskHelpRequest::create([
                'task_id' => $task->id,
                'requester_id' => $requester->id,
                'helper_id' => $helperId,
                'message' => $message,
                'status' => TaskHelpRequest::STATUS_PENDING,
            ]);

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $requester->id,
                'action' => 'help_requested',
                'field_name' => 'helper_id',
                'old_value' => null,
                'new_value' => $helper->name,
                'comment' => "Requested help from {$helper->name}: \"{$message}\"",
            ]);

            try {
                $helper->notify(new TaskHelpRequestedNotification($task, $requester, $message));
            } catch (\Throwable) {
            }

            return $helpRequest;
        });
    }

    /**
     * Helper responds (accept / reject) to a help request.
     */
    public function respondHelp(TaskHelpRequest $helpRequest, User $helper, string $status, ?string $notes = null): TaskHelpRequest
    {
        return DB::transaction(function () use ($helpRequest, $helper, $status, $notes) {
            $helpRequest->update([
                'status' => $status,
                'response_notes' => $notes,
                'responded_at' => now(),
            ]);

            $task = $helpRequest->task;

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $helper->id,
                'action' => 'help_responded',
                'field_name' => 'status',
                'old_value' => 'pending',
                'new_value' => $status,
                'comment' => "{$helper->name} {$status} the help request." . ($notes ? " Note: \"{$notes}\"" : ''),
            ]);

            // If accepted, ensure helper is also added as collaborator for transparent visibility
            if ($status === TaskHelpRequest::STATUS_ACCEPTED) {
                if (! $task->hasCollaborator($helper->id)) {
                    $this->addCollaborator($task, $helper, $helper->id, 'helper');
                }
            }

            try {
                $helpRequest->requester->notify(new TaskHelpRespondedNotification($task, $helper, $status, $notes));
            } catch (\Throwable) {
            }

            return $helpRequest->fresh();
        });
    }

    /**
     * Add a collaborator to the task.
     */
    public function addCollaborator(Task $task, User $actor, int $userId, string $role = 'collaborator'): TaskCollaborator
    {
        return DB::transaction(function () use ($task, $actor, $userId, $role) {
            $user = User::findOrFail($userId);

            $collaborator = TaskCollaborator::firstOrCreate(
                ['task_id' => $task->id, 'user_id' => $userId],
                ['added_by' => $actor->id, 'role' => $role]
            );

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'action' => 'collaborator_added',
                'field_name' => 'user_id',
                'old_value' => null,
                'new_value' => $user->name,
                'comment' => "{$user->name} was added as {$role} by {$actor->name}",
            ]);

            try {
                $user->notify(new TaskCollaboratorNotification($task, $actor, 'added'));
            } catch (\Throwable) {
            }

            return $collaborator;
        });
    }

    /**
     * Remove a collaborator from the task.
     */
    public function removeCollaborator(Task $task, User $actor, int $userId): bool
    {
        return DB::transaction(function () use ($task, $actor, $userId) {
            $user = User::findOrFail($userId);
            $deleted = TaskCollaborator::where('task_id', $task->id)->where('user_id', $userId)->delete();

            if ($deleted) {
                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => $actor->id,
                    'action' => 'collaborator_removed',
                    'field_name' => 'user_id',
                    'old_value' => $user->name,
                    'new_value' => null,
                    'comment' => "{$user->name} was removed from collaborators by {$actor->name}",
                ]);

                try {
                    $user->notify(new TaskCollaboratorNotification($task, $actor, 'removed'));
                } catch (\Throwable) {
                }
            }

            return (bool) $deleted;
        });
    }

    /**
     * Post a comment or reply on a task.
     */
    public function addComment(Task $task, User $author, string $commentText, ?int $parentId = null): TaskComment
    {
        return DB::transaction(function () use ($task, $author, $commentText, $parentId) {
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $author->id,
                'parent_id' => $parentId,
                'comment' => $commentText,
            ]);

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $author->id,
                'action' => 'comment_added',
                'field_name' => 'comment',
                'old_value' => null,
                'new_value' => null,
                'comment' => "{$author->name} commented on the task",
            ]);

            // Notify assignee and creator if distinct from commenter
            $recipients = collect([$task->assignee, $task->creator])
                ->filter()
                ->unique('id')
                ->reject(fn ($u) => (int) $u->id === (int) $author->id);

            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new TaskCommentNotification($task, $author, $commentText));
                } catch (\Throwable) {
                }
            }

            return $comment->load('user');
        });
    }

    /**
     * Upload an attachment to a task.
     */
    public function uploadAttachment(Task $task, User $uploader, UploadedFile $file): TaskAttachment
    {
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Store file securely in storage/app/task-attachments/{task_id}
        $storedPath = $file->store("task-attachments/{$task->id}", 'local');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $uploader->id,
            'file_name' => $fileName,
            'file_path' => $storedPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
        ]);

        TaskActivityLog::create([
            'task_id' => $task->id,
            'user_id' => $uploader->id,
            'action' => 'attachment_uploaded',
            'field_name' => 'file_name',
            'old_value' => null,
            'new_value' => $fileName,
            'comment' => "Attached file \"{$fileName}\"",
        ]);

        return $attachment;
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(TaskAttachment $attachment, User $actor): bool
    {
        if (Storage::disk('local')->exists($attachment->file_path)) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        $fileName = $attachment->file_name;
        $taskId = $attachment->task_id;

        $attachment->delete();

        TaskActivityLog::create([
            'task_id' => $taskId,
            'user_id' => $actor->id,
            'action' => 'attachment_deleted',
            'field_name' => 'file_name',
            'old_value' => $fileName,
            'new_value' => null,
            'comment' => "Removed attachment \"{$fileName}\"",
        ]);

        return true;
    }

    /**
     * Submit task for review.
     */
    public function submitForReview(Task $task, User $submitter, ?string $notes = null): TaskReviewAction
    {
        return DB::transaction(function () use ($task, $submitter, $notes) {
            $task->update([
                'status' => 'in_review',
            ]);

            $action = TaskReviewAction::create([
                'task_id' => $task->id,
                'reviewer_id' => null,
                'action' => TaskReviewAction::ACTION_SUBMITTED,
                'notes' => $notes ?: 'Submitted for review',
            ]);

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $submitter->id,
                'action' => 'submitted_for_review',
                'field_name' => 'status',
                'old_value' => $task->getOriginal('status'),
                'new_value' => 'in_review',
                'comment' => "{$submitter->name} submitted task for review" . ($notes ? ": \"{$notes}\"" : ''),
            ]);

            // Reviewer can be creator or department manager
            $reviewer = ($task->creator && $task->creator->id !== $submitter->id)
                ? $task->creator
                : $submitter->departmentManager();

            if ($reviewer) {
                try {
                    $reviewer->notify(new TaskReviewSubmittedNotification($task, $submitter, $notes));
                } catch (\Throwable) {
                }
            }

            return $action;
        });
    }

    /**
     * Reviewer decides (approve / request changes).
     */
    public function decideReview(Task $task, User $reviewer, string $decision, ?string $notes = null): TaskReviewAction
    {
        return DB::transaction(function () use ($task, $reviewer, $decision, $notes) {
            if ($decision === 'approved') {
                $task->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'completed_at' => now(),
                ]);
            } else { // changes_requested
                $task->update([
                    'status' => 'in_progress',
                ]);
            }

            $action = TaskReviewAction::create([
                'task_id' => $task->id,
                'reviewer_id' => $reviewer->id,
                'action' => $decision === 'approved' ? TaskReviewAction::ACTION_APPROVED : TaskReviewAction::ACTION_CHANGES_REQUESTED,
                'notes' => $notes,
            ]);

            $label = $decision === 'approved' ? 'Approved' : 'Changes requested';
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $reviewer->id,
                'action' => 'review_' . $decision,
                'field_name' => 'status',
                'old_value' => 'in_review',
                'new_value' => $task->status,
                'comment' => "{$reviewer->name} {$label}." . ($notes ? " Notes: \"{$notes}\"" : ''),
            ]);

            // Notify task owner
            if ($task->assignee && $task->assignee->id !== $reviewer->id) {
                try {
                    $task->assignee->notify(new TaskReviewDecisionNotification($task, $reviewer, $decision, $notes));
                } catch (\Throwable) {
                }
            }

            return $action;
        });
    }

    /**
     * Fetch list of eligible employees for delegation, help requests, or collaboration.
     */
    public function eligibleDelegates(User $user, ?Task $task = null): Collection
    {
        $query = User::active()->orderBy('name', 'asc');

        if ($task && $task->assigned_to) {
            $query->where('id', '!=', $task->assigned_to);
        }

        if (! $user->isAdmin()) {
            $dept = $user->department;
            $query->where(function ($q) use ($user, $dept) {
                if ($dept) {
                    $q->where('department', $dept);
                }
                $q->orWhere('manager_id', $user->id)
                  ->orWhere('id', $user->id);
            });
        }

        $candidates = $query->get();

        // Business rule: Regular employees cannot assign or delegate tasks to managers or administrators
        if (! $user->isAdmin() && ! $user->isManager()) {
            $candidates = $candidates->filter(function (User $candidate) use ($user) {
                if ($candidate->id === $user->id) {
                    return true;
                }

                if ($user->manager_id && (int) $candidate->id === (int) $user->manager_id) {
                    return false;
                }

                if ($candidate->isManager() || $candidate->isAdmin()) {
                    return false;
                }

                return true;
            })->values();
        }

        return $candidates;
    }

    /**
     * Escalate an active task to Urgent priority by an Administrator.
     * Updates priority, logs escalation audit trail, creates a special escalation comment notice,
     * and dispatches a dedicated escalation notification to the assignee.
     */
    public function escalateTask(Task $task, User $admin, string $reason): Task
    {
        if ($task->isCompleted() || $task->status === 'cancelled') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reason' => 'Completed or cancelled tasks cannot be escalated.',
            ]);
        }

        return DB::transaction(function () use ($task, $admin, $reason) {
            $oldPriority = $task->priority;

            $task->update([
                'priority' => 'urgent',
                'is_escalated' => true,
                'escalation_reason' => $reason,
                'escalated_by' => $admin->id,
                'escalated_at' => now(),
            ]);

            // Audit log
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $admin->id,
                'action' => 'escalated',
                'field_name' => 'priority',
                'old_value' => $oldPriority,
                'new_value' => 'urgent',
                'comment' => "Admin Escalation by {$admin->name}: {$reason}",
            ]);

            // Post special escalation comment notice in the task discussion thread
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $admin->id,
                'comment' => "🚨 **ADMIN ESCALATION NOTICE**\n\n{$reason}",
            ]);

            // Dispatch notification to the assignee
            if ($task->assignee && (int) $task->assignee->id !== (int) $admin->id) {
                try {
                    $task->assignee->notify(new TaskEscalatedNotification($task, $admin, $reason));
                } catch (\Throwable) {
                }
            }

            return $task;
        });
    }

    /**
     * Request rework on a task (even after task is completed).
     * Accessible by Task Creator, Department Manager, and Admin.
     * Transitions task status to 'rework', clears completed_at, updates progress,
     * logs activity, posts rework comment, and notifies assignee.
     */
    public function requestRework(Task $task, User $requester, string $notes, ?string $dueDate = null): Task
    {
        return DB::transaction(function () use ($task, $requester, $notes, $dueDate) {
            $oldStatus = $task->status;

            $updateData = [
                'status' => 'rework',
                'completed_at' => null,
                'progress' => min((int) ($task->progress ?? 0), 50),
                'rework_notes' => $notes,
                'rework_count' => (int) ($task->rework_count ?? 0) + 1,
                'rework_by' => $requester->id,
                'rework_at' => now(),
            ];

            if ($dueDate) {
                $updateData['due_date'] = $dueDate;
            }

            $task->update($updateData);

            // Audit log
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $requester->id,
                'action' => 'rework_requested',
                'field_name' => 'status',
                'old_value' => $oldStatus,
                'new_value' => 'rework',
                'comment' => "Re-work requested by {$requester->name}: {$notes}",
            ]);

            // Post official rework notice in comments
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $requester->id,
                'comment' => "🔄 **TASK SENT BACK FOR RE-WORK**\n\n{$notes}" . ($dueDate ? "\n\n*Revised Due Date: {$dueDate}*" : ''),
            ]);

            // Notify assignee if not the requester
            if ($task->assignee && (int) $task->assignee->id !== (int) $requester->id) {
                try {
                    $task->assignee->notify(new TaskReworkNotification($task, $requester, $notes));
                } catch (\Throwable) {
                }
            }

            return $task;
        });
    }
}
