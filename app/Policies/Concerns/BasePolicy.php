<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Rbac\DataScope;

/**
 * Shared shape for every model policy.
 *
 * Order is deliberate and identical everywhere: the permission is checked
 * FIRST, then the data scope. A user without the permission is refused even if
 * they own the record; a user with the permission is still limited to what
 * their scope allows.
 *
 * Admin bypasses both via Gate::before in AppServiceProvider, so these methods
 * never even run for the admin.
 */
abstract class BasePolicy
{
    /**
     * Gate on a permission, tolerating a logged-out user.
     */
    protected function permitted(?User $user, string $permission): bool
    {
        return $user !== null && $user->can($permission);
    }

    /**
     * Gate on the permission, then on the record's data scope.
     */
    protected function permittedFor(?User $user, string $permission, mixed $record, string $module): bool
    {
        if (! $this->permitted($user, $permission)) {
            return false;
        }

        return DataScope::allowsRecord($user, $record, $module);
    }

    /**
     * The permission required for a "view any" style check (e.g. an index or a
     * create form), where there is no record to scope yet.
     */
    protected function permittedForModule(?User $user, string $permission): bool
    {
        return $this->permitted($user, $permission);
    }
}