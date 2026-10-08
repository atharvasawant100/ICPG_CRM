<?php

namespace App\Http\Middleware;

use App\Rbac\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out any deactivated user and blocks the request.
 *
 * Applied to every authenticated route so that is_active = false takes effect
 * immediately, rather than waiting for the session or token to expire.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ((bool) $user->is_active) {
            return $next($request);
        }

        AuditLogger::recordAuth('deactivated_access', $user->getKey());

        // Revoke API tokens too, so a Sanctum client cannot keep working.
        if (method_exists($user, 'tokens')) {
            try {
                $user->tokens()->delete();
            } catch (\Throwable) {
                // Non-fatal: the session logout below is what matters.
            }
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        return redirect()->route('login')
            ->withErrors(['email' => 'Your account has been deactivated. Please contact an administrator.']);
    }
}