<?php

namespace App\Rbac;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * The one and only place where a user's Spatie role is assigned.
 *
 * Everything that changes a role must go through here so that the read-only
 * users.role mirror can never drift from the Spatie source of truth.
 */
trait AssignsRoles
{
    /**
     * Assign a Spatie role and keep users.role in sync.
     *
     * This is a wrapper rather than a direct call to Spatie's assignRole()
     * precisely so the mirror is written in one place. Calling Spatie directly
     * anywhere else will drift the mirror, which
     * RoleMirrorIntegrityTest guards against.
     *
     * @param  string|\Spatie\Permission\Models\Role  $role
     */
    public function assignRbacRole($role, bool $syncLegacyColumn = true): static
    {
        $roleName = $role instanceof Role ? $role->name : (string) $role;

        // Spatie: source of truth.
        $this->assignRole($roleName);

        if ($syncLegacyColumn) {
            $this->forceFill(['role' => $roleName])->saveQuietly();
        }

        return $this;
    }

    /**
     * Remove every Spatie role and clear the legacy mirror.
     */
    public function clearRbacRoles(bool $syncLegacyColumn = true): static
    {
        $this->syncRoles();

        if ($syncLegacyColumn) {
            $this->forceFill(['role' => null])->saveQuietly();
        }

        return $this;
    }

    /**
     * The user's Spatie role name, or null when they have none.
     */
    public function rbacRoleName(): ?string
    {
        return $this->getRoleNames()->first();
    }
}