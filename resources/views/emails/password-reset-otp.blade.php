<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 540px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e5e7eb;">
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 32px 24px 20px 24px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                            <div style="background-color: #ffffff; padding: 10px 18px; border-radius: 8px; display: inline-block;">
                                <img src="https://iconicpages.com/wp-content/themes/iconicpages-theme/assets/images/logo.png" alt="ICPG Logo" height="42" style="display: block; max-height: 42px; width: auto; border: 0;">
                            </div>
                            <h1 style="color: #ffffff; margin: 16px 0 0 0; font-size: 20px; font-weight: 700; letter-spacing: 0.3px;">
                                Password Reset Verification
                            </h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 28px 24px 28px;">
                            <p style="font-size: 15px; line-height: 24px; color: #374151; margin: 0 0 16px 0;">
                                Hello <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 22px; color: #4b5563; margin: 0 0 20px 0;">
                                We received a request to reset the password for your account associated with <strong>{{ $user->email }}</strong>. Use the One-Time Password (OTP) below to complete your reset:
                            </p>

                            <!-- OTP Box -->
                            <div style="background: linear-gradient(180deg, #f5f3ff 0%, #ede9fe 100%); border: 2px dashed #6366f1; border-radius: 12px; padding: 22px; text-align: center; margin: 24px 0;">
                                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: #4338ca; margin-bottom: 8px;">
                                    Your Verification Code
                                </div>
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #1e1b4b; padding: 4px 0;">
                                    {{ $otp }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 8px;">
                                    Valid for <strong>{{ $expiresInMinutes }} minutes</strong>
                                </div>
                            </div>

                            <p style="font-size: 13px; line-height: 20px; color: #6b7280; margin: 20px 0 0 0;">
                                Please return to the reset password page and enter this 6-digit code along with your new password.
                            </p>

                            <!-- Security Notice -->
                            <div style="margin-top: 24px; padding: 14px 16px; background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 6px;">
                                <p style="font-size: 12px; line-height: 18px; color: #92400e; margin: 0;">
                                    <strong>Security Notice:</strong> If you did not request a password reset, please ignore this email or contact your administrator immediately. Your password remains unchanged until this OTP is verified.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 28px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0 0 4px 0;">
                                Sent by <strong>no-reply@iconicpages.com</strong> &bull; {{ $appName }}
                            </p>
                            <p style="font-size: 11px; color: #9ca3af; margin: 0;">
                                This is an automated security notification. Replies to this email address are not monitored.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
