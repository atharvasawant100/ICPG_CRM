<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rbac\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /*
         | The old implementation pinned `'role' => 'admin'` into the credentials,
         | which locked out every non-admin user. Access is now governed by
         | permissions, so only email + password are matched here. The legacy
         | `admin` middleware is no longer applied to business routes.
         */
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLogger::recordAuth('login_failed', null, ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        // Deactivated accounts cannot sign in, even with the right password.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLogger::recordAuth('login_blocked_inactive', $user->getKey(), ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact an administrator.'],
            ]);
        }

        AuditLogger::recordAuth('login', $user->getKey());

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}