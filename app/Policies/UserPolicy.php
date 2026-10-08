<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\BasePolicy;
use Spatie\Permission\Models\Role;

/**
 * Permissions for the Users module.
 *
 * Registered as a policy on the User model, so viewAny/view/create/update/delete
 * map onto the standard CRUD abilities. Roles are checked by permission, never
 * by role name, except for the last-admin rule which is about safety rather
 * than access and therefore legitimately looks at the admin role.
 */
class UserPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->permittedForModule($user, 'users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $this->permittedForModule($user, 'users.view');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $this->permittedForModule($user, 'users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $this->permittedForModule($user, 'users.delete');
    }

    /**
     * Editing a role's permissions.
     *
     * There is no Role model policy because Role carries no ownership; this
     * ability is exposed as a Gate so the controller can call authorize().
     */
    public function updateRole(User $user, Role $role): bool
    {
        return $this->permittedForModule($user, 'roles.edit');
    }
}