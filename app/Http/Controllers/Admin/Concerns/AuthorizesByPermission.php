<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Auth\Access\AuthorizationException;

/**
 * Permission checks for the admin screens.
 *
 * The admin modules do not map cleanly onto the model policy abilities:
 * users.view, roles.view, audit_logs.view and settings.view all resolve to
 * "viewAny" on models that have no meaningful owner relationship. Rather than
 * inventing four near-identical policies, the permission is named explicitly.
 *
 * Gate::before still runs first, so the admin role passes without any of this
 * executing, and a user without the permission is refused server-side whether
 * or not the view hides the button.
 */
trait AuthorizesByPermission
{
    /**
     * @throws AuthorizationException
     */
    protected function authorizePermission(string $permission): void
    {
        $user = request()->user();

        if ($user === null || ! $user->can($permission)) {
            throw new AuthorizationException(
                "This action requires the [{$permission}] permission."
            );
        }
    }
}