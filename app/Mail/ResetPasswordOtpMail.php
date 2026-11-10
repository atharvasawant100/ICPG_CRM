<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * OTP email sent to user for password reset from no-reply@iconicpages.com.
 */
class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
    ) {
    }

    public function build(): self
    {
        $fromAddress = config('mail.from.address') ?: 'no-reply@iconicpages.com';
        $fromName = config('mail.from.name', 'ICPG Office');

        return $this->subject('Your Password Reset OTP - ICPG Office')
            ->from($fromAddress, $fromName)
            ->to($this->user->email, $this->user->name)
            ->view('emails.password-reset-otp', [
                'user' => $this->user,
                'otp' => $this->otp,
                'appName' => config('app.name', 'ICPG Technologies Pvt Ltd'),
                'expiresInMinutes' => 15,
            ])
            ->text('emails.password-reset-otp-text', [
                'user' => $this->user,
                'otp' => $this->otp,
                'appName' => config('app.name', 'ICPG Technologies Pvt Ltd'),
                'expiresInMinutes' => 15,
            ]);
    }
}
