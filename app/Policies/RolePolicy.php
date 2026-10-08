<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\BasePolicy;
use Spatie\Permission\Models\Role;

/**
 * Permissions for the Roles module.
 */
class RolePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->permittedForModule($user, 'roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->permittedForModule($user, 'roles.view');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->permittedForModule($user, 'roles.edit');
    }
}