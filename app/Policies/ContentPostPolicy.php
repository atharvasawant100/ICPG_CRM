<?php

namespace App\Policies;

use App\Models\ContentPost;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class ContentPostPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permitted($user, 'content.view');
    }

    public function view(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.view')) {
            return true;
        }

        return (int) $post->assigned_to === (int) $user->id
            || (int) $post->content_creator_id === (int) $user->id
            || (int) $post->reviewer_id === (int) $user->id
            || (int) $post->created_by === (int) $user->id
            || $post->tasks()->where('assigned_to', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $this->permitted($user, 'content.create');
    }

    public function update(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.edit')) {
            return true;
        }

        // Assigned employee or creator can edit drafts, active creation, or when changes are requested
        if ((int) $post->assigned_to === (int) $user->id || (int) $post->content_creator_id === (int) $user->id) {
            return in_array($post->status, ['idea', 'planned', 'in_creation', 'changes_requested'], true);
        }

        return false;
    }

    public function delete(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.delete')) {
            return true;
        }

        // Creator can delete only if still in idea stage
        if ((int) $post->created_by === (int) $user->id && $post->status === 'idea') {
            return true;
        }

        return false;
    }

    public function submitReview(User $user, ContentPost $post): bool
    {
        if (! in_array($post->status, ['idea', 'planned', 'in_creation', 'changes_requested'], true)) {
            return false;
        }

        if ($user->isAdmin() || $this->permitted($user, 'content.edit')) {
            return true;
        }

        return (int) $post->assigned_to === (int) $user->id || (int) $post->content_creator_id === (int) $user->id;
    }

    public function review(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.review')) {
            return true;
        }

        if ((int) $post->reviewer_id === (int) $user->id) {
            return true;
        }

        return $user->isManager();
    }

    public function schedule(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.publish') || $this->permitted($user, 'content.edit')) {
            return true;
        }

        return $user->isManager();
    }

    public function publish(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.publish') || $this->permitted($user, 'content.edit')) {
            return true;
        }

        return (int) $post->assigned_to === (int) $user->id || $user->isManager();
    }

    public function assign(User $user, ContentPost $post): bool
    {
        if ($user->isAdmin() || $this->permitted($user, 'content.assign')) {
            return true;
        }

        return (int) $post->created_by === (int) $user->id || $user->isManager();
    }
}

