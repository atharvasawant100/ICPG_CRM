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
}