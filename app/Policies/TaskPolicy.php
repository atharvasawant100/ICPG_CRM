<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskHelpRequest;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class TaskPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Assignee or creator can always view
        if ((int) $task->assigned_to === (int) $user->id || (int) $task->assigned_by === (int) $user->id) {
            return true;
        }

        // Active collaborators or helpers
        if ($task->hasCollaborator($user->id)) {
            return true;
        }

        if ($task->helpRequests()->where('helper_id', $user->id)->whereIn('status', ['pending', 'accepted'])->exists()) {
            return true;
        }

        // Project-level access: Anyone who can access the project can view its tasks
        if ($task->project && $task->project->canBeAccessedBy($user)) {
            return true;
        }

        // Department-level view: Peers and managers in the same department can view tasks
        if ($task->department && $user->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
            return true;
        }

        // Department manager or direct reporting manager of the assignee
        if ($user->isManager()) {
            if ($task->assignee && (int) $task->assignee->manager_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Any authenticated employee can create personal tasks
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Creator or assignee can update
        if ((int) $task->assigned_by === (int) $user->id || (int) $task->assigned_to === (int) $user->id) {
            return true;
        }

        // Department manager
        if ($user->isManager()) {
            if ($task->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
                return true;
            }
            if ($task->assignee && (int) $task->assignee->manager_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }

    public function delegate(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Creator, current assignee, or their manager can delegate
        if ((int) $task->assigned_by === (int) $user->id || (int) $task->assigned_to === (int) $user->id) {
            return true;
        }

        if ($user->isManager()) {
            if ($task->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
                return true;
            }
            if ($task->assignee && (int) $task->assignee->manager_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }

    public function manageCollaborators(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function requestHelp(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function respondHelp(User $user, Task $task, TaskHelpRequest $helpRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $helpRequest->helper_id === (int) $user->id;
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function uploadAttachment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function deleteAttachment(User $user, Task $task, TaskAttachment $attachment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $attachment->user_id === (int) $user->id) {
            return true;
        }

        if ((int) $task->assigned_by === (int) $user->id || (int) $task->assigned_to === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function submitReview(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // The current accountable owner or creator can submit
        return (int) $task->assigned_to === (int) $user->id || (int) $task->assigned_by === (int) $user->id;
    }

    public function decideReview(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // The creator can decide review if not the assignee
        if ((int) $task->assigned_by === (int) $user->id && (int) $task->assigned_to !== (int) $user->id) {
            return true;
        }

        // Manager of the task/department
        if ($user->isManager()) {
            if ($task->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
                return true;
            }
            if ($task->assignee && (int) $task->assignee->manager_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $task->assigned_by === (int) $user->id) {
            return true;
        }

        if ($user->isManager() && $task->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can escalate the task to Urgent.
     * Only Administrators can escalate active tasks.
     */
    public function escalate(User $user, Task $task): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($task->isCompleted() || $task->status === 'cancelled') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can request rework on a task.
     * Task Creator, Department Manager, and Administrators can request rework,
     * even after the task is completed.
     */
    public function rework(User $user, Task $task): bool
    {
        if ($task->status === 'cancelled') {
            return false;
        }

        // 1. Admin can always request rework
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Task Creator can request rework
        if ((int) $task->assigned_by === (int) $user->id) {
            return true;
        }

        // 3. Department Manager (same department or reporting manager of assignee/creator)
        if ($user->isManager()) {
            if ($task->department && strtolower((string) $user->department) === strtolower((string) $task->department)) {
                return true;
            }
            if ($task->assignee && (int) $task->assignee->manager_id === (int) $user->id) {
                return true;
            }
            if ($task->creator && (int) $task->creator->manager_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }
}
