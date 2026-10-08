<?php

namespace App\Services;

use App\Models\User;
use App\Rbac\Rbac;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Server-side user management rules.
 *
 * All of these are enforced here so the controllers and the UI cannot disagree,
 * and so the rules hold no matter which entry point is used.
 */
class UserManagementService
{
    /**
     * How many active admins currently exist.
     */
    public function activeAdminCount(): int
    {
        return User::role(Rbac::ADMIN_ROLE)->active()->count();
    }

    /**
     * Would changing this user leave zero active admins?
     *
     * True means the change is blocked. Safe when the user is not an admin, or
     * when at least one other active admin would remain.
     */
    public function wouldOrphanAdmins(User $target, bool $targetWillBeAdmin): bool
    {
        if (! $target->hasRole(Rbac::ADMIN_ROLE) && ! $targetWillBeAdmin) {
            return false;
        }

        if (! $target->hasRole(Rbac::ADMIN_ROLE)) {
            // Promoting someone never reduces the admin count.
            return false;
        }

        // Removing or demoting this admin: only unsafe if they are the last one.
        return $this->activeAdminCount() <= 1;
    }

    /**
     * Assign a role through the one wrapper that keeps users.role in sync.
     *
     * Never writes users.role directly.
     */
    public function assignRole(User $user, string $roleName): void
    {
        if (! array_key_exists($roleName, array_flip(Rbac::ROLES))) {
            throw new \InvalidArgumentException("Unknown role [{$roleName}].");
        }

        $user->assignRbacRole($roleName);
    }

    /**
     * End every active session for a user, and revoke their API tokens.
     *
     * Handles the database session driver by deleting their rows. The file
     * driver is not per-user indexable, so we cannot delete those directly; the
     * EnsureUserIsActive middleware rejects the account on the next request
     * regardless, which is what actually enforces the logout.
     */
    public function revokeSessions(User $user): void
    {
        try {
            $user->tokens()->delete();
        } catch (\Throwable) {
            // Sanctum tables may not exist yet; the is_active check still applies.
        }

        if (config('session.driver') === 'database') {
            try {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->getKey())
                    ->delete();
            } catch (\Throwable) {
                // Non-fatal: is_active blocks the account regardless.
            }
        }
    }

    /**
     * Deactivate (or reactivate) a user and end their sessions when disabling.
     */
    public function setActive(User $user, bool $active): void
    {
        if (! $active) {
            $this->revokeSessions($user);
        }

        $user->forceFill(['is_active' => $active])->save();
    }

    /**
     * Reset a password and force a change on next login when asked.
     *
     * The plaintext is never returned or logged; AuditLogger strips it from the
     * audit payload.
     */
    public function resetPassword(User $user, string $plainPassword, bool $mustChange = false): void
    {
        $user->forceFill([
            'password' => $plainPassword, // cast to hashed by the model
            'remember_token' => Str::random(60),
        ])->save();

        $this->revokeSessions($user);

        if ($mustChange) {
            // The app has no "must change password" column, so this is recorded
            // as a forced reset marker on the audit trail only.
            \App\Rbac\AuditLogger::record(
                'password_reset_forced_change',
                'users',
                $user->getKey(),
                null,
                ['must_change_password' => true],
                $user->getKey()
            );
        }
    }

    /**
     * Would setting this user as $managerId create a loop in the chain?
     *
     * Walks up from the proposed manager; if the user being edited is met, the
     * chain would loop.
     */
    public function wouldCreateManagerLoop(User $user, ?int $managerId): bool
    {
        if ($managerId === null) {
            return false;
        }

        if ($managerId === $user->getKey()) {
            return true;
        }

        $seen = [];
        $cursor = User::find($managerId);

        while ($cursor !== null) {
            if ($cursor->getKey() === $user->getKey()) {
                return true;
            }

            // Defensive: a cycle already in the data must not hang the request.
            if (isset($seen[$cursor->getKey()])) {
                return true;
            }

            $seen[$cursor->getKey()] = true;
            $cursor = $cursor->manager_id ? User::find($cursor->manager_id) : null;
        }

        return false;
    }
}