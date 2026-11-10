<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use App\Rbac\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Generate an OTP and send it via email.
     */
    public function sendResetOtpEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim((string) $request->email));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => 'We could not find an account matching that email address.',
            ]);
        }

        if (! $user->is_active) {
            return back()->withInput()->withErrors([
                'email' => 'This account has been deactivated. Please contact an administrator.',
            ]);
        }

        // Throttle: enforce 60-second cooldown between requests
        $existing = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if ($existing && $existing->created_at) {
            $created = Carbon::parse($existing->created_at);
            if ($created->addSeconds(60)->isFuture()) {
                $seconds = $created->diffInSeconds(now());
                return redirect()->route('password.reset', ['email' => $user->email])
                    ->with('status', "An OTP was already sent recently. Please check your inbox or wait {$seconds}s before requesting a new code.");
            }
        }

        // Generate a 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);

        // Store hashed OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send OTP email from no-reply@iconicpages.com
        try {
            Mail::to($user->email)->send(new ResetPasswordOtpMail($user, $otp));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset OTP: ' . $e->getMessage());

            return back()->withInput()->withErrors([
                'email' => 'Unable to send OTP email at this time. Please try again or contact support.',
            ]);
        }

        AuditLogger::recordAuth('password_reset_otp_sent', $user->getKey(), ['email' => $user->email]);

        return redirect()->route('password.reset', ['email' => $user->email])
            ->with('status', 'A 6-digit verification OTP has been sent to ' . $user->email . ' from no-reply@iconicpages.com. Please enter it below.');
    }

    /**
     * Resend the OTP to the user's email.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = strtolower(trim((string) $request->email));
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Unable to resend OTP for this account.',
            ]);
        }

        // 60s cooldown check
        $existing = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if ($existing && $existing->created_at) {
            $created = Carbon::parse($existing->created_at);
            if ($created->addSeconds(60)->isFuture()) {
                $seconds = $created->diffInSeconds(now());
                return back()->withErrors([
                    'otp' => "Please wait {$seconds}s before requesting a new OTP.",
                ]);
            }
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(new ResetPasswordOtpMail($user, $otp));
        } catch (\Throwable $e) {
            Log::error('Failed to resend password reset OTP: ' . $e->getMessage());

            return back()->withErrors([
                'otp' => 'Failed to send fresh OTP. Please try again later.',
            ]);
        }

        AuditLogger::recordAuth('password_reset_otp_resent', $user->getKey(), ['email' => $user->email]);

        return back()->with('status', 'A new 6-digit OTP has been sent to ' . $user->email . ' from no-reply@iconicpages.com.');
    }
}
