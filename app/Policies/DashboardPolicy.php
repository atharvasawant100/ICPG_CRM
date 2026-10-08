<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\BasePolicy;

/**
 * The dashboard has no model, so this policy exists purely to express the
 * `dashboard.view` permission through the same authorize() path as every other
 * module.
 */
class DashboardPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->permittedForModule($user, 'dashboard.view');
    }

    /**
     * Fallback callback.
     *
     * The controller authorizes with the policy class itself
     * ($this->authorize('viewAny', DashboardPolicy::class)) because there is no
     * model to infer from. Gate resolves an unregistered class argument by
     * calling the method on it, so without this method the check could not be
     * resolved and every non-admin was denied the dashboard even when holding
     * dashboard.view.
     */
    public function handle(User $user, string $ability): bool
    {
        return match ($ability) {
            'viewAny', 'view' => $this->viewAny($user),
            default => false,
        };
    }
}