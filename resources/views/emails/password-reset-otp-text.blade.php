Hello {{ $user->name }},

We received a request to reset your password for your account ({{ $user->email }}).

Your 6-digit Verification OTP:
{{ $otp }}

This code is valid for {{ $expiresInMinutes }} minutes.

If you did not request a password reset, please ignore this email or notify your administrator.

Sent by no-reply@iconicpages.com - {{ $appName }}
