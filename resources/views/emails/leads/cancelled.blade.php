<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Cancelled</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 32px 24px; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); text-align: left;">
                            <span style="display: inline-block; padding: 4px 12px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); border-radius: 9999px; font-size: 11px; font-weight: 700; color: #ffffff; letter-spacing: 0.05em; text-transform: uppercase;">Meeting Update</span>
                            <h1 style="margin: 16px 0 0; color: #ffffff; font-size: 22px; font-weight: 700; line-height: 1.3;">Meeting Cancelled</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #334155;">
                                Hello <strong>{{ $lead->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #334155;">
                                This is to inform you that the meeting previously scheduled with <strong>{{ $host->name }}</strong> on <strong>{{ $dateFormatted }}</strong> has been cancelled.
                            </p>

                            @if (! empty($reason))
                            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
                                <span style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9f1239; margin-bottom: 4px;">Reason for cancellation</span>
                                <span style="font-size: 14px; color: #881337;">{{ $reason }}</span>
                            </div>
                            @endif

                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                                We apologize for any inconvenience caused. If you would like to pick a new date or discuss further, please reply to this email or reach out to {{ $host->name }} at {{ $host->email }}.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                &copy; {{ date('Y') }} Iconic Pages. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
