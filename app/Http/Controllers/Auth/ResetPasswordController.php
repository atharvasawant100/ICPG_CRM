<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rbac\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $email = $request->query('email', old('email'));

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    /**
     * Verify OTP and reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'otp.required' => 'Please enter the 6-digit OTP sent to your email.',
            'otp.size' => 'The OTP must be exactly 6 digits.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters long.',
        ]);

        $email = strtolower(trim((string) $request->email));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record) {
            return back()->withInput()->withErrors([
                'otp' => 'No active password reset request found for this email. Please request a new OTP.',
            ]);
        }

        // Validate 15-minute expiration
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->withInput()->withErrors([
                'otp' => 'This OTP has expired. Please request a new one.',
            ]);
        }

        // Validate OTP code match
        if (! Hash::check($request->otp, $record->token)) {
            return back()->withInput()->withErrors([
                'otp' => 'The entered OTP is incorrect. Please check your email and try again.',
            ]);
        }

        // Update password
        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Invalidate the reset token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        AuditLogger::recordAuth('password_reset_completed', $user->getKey(), ['email' => $user->email]);

        return redirect()->route('login')->with(
            'status',
            'Your password has been successfully reset! Please log in with your new password.'
        );
    }
}
