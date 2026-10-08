<?php

namespace App\Policies;

use App\Models\LeadFollowup;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

/**
 * Follow-ups are scoped on the followups module, which maps to lead_followups.user_id.
 */
class LeadFollowupPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'followups.view');
    }

    public function view(User $user, LeadFollowup $followup): bool
    {
        return $this->permittedFor($user, 'followups.view', $followup, 'followups');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'followups.create');
    }

    public function update(User $user, LeadFollowup $followup): bool
    {
        return $this->permittedFor($user, 'followups.edit', $followup, 'followups');
    }

    public function delete(User $user, LeadFollowup $followup): bool
    {
        return $this->permittedFor($user, 'followups.delete', $followup, 'followups');
    }
}