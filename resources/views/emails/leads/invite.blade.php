<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Invitation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 32px 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); text-align: left;">
                            <span style="display: inline-block; padding: 4px 12px; background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(129, 140, 248, 0.4); border-radius: 9999px; font-size: 11px; font-weight: 700; color: #c7d2fe; letter-spacing: 0.05em; text-transform: uppercase;">Meeting Invitation</span>
                            <h1 style="margin: 16px 0 0; color: #ffffff; font-size: 22px; font-weight: 700; line-height: 1.3;">Upcoming Meeting with {{ $host->name }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #334155;">
                                Hello <strong>{{ $lead->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #334155;">
                                A meeting has been scheduled for you with <strong>{{ $host->name }}</strong> from Iconic Pages. Please find the meeting schedule and access link below.
                            </p>

                            <!-- Meeting Info Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 12px; vertical-align: top;" width="110">
                                            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Date & Time</span>
                                        </td>
                                        <td style="padding-bottom: 12px; vertical-align: top;">
                                            <span style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $dateFormatted }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom: 12px; vertical-align: top;">
                                            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Host</span>
                                        </td>
                                        <td style="padding-bottom: 12px; vertical-align: top;">
                                            <span style="font-size: 14px; color: #1e293b; font-weight: 600;">{{ $host->name }}</span>
                                            <span style="font-size: 13px; color: #64748b;"> ({{ $host->email }})</span>
                                        </td>
                                    </tr>
                                    @if (! empty($followup->agenda))
                                    <tr>
                                        <td style="vertical-align: top;">
                                            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Agenda</span>
                                        </td>
                                        <td style="vertical-align: top;">
                                            <span style="font-size: 14px; color: #334155; line-height: 1.5;">{{ $followup->agenda }}</span>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            @if (! empty($meetingLink))
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $meetingLink }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);">
                                            Join Meeting Now
                                        </a>
                                        <p style="margin: 12px 0 0; font-size: 12px; color: #64748b;">
                                            Meeting Link: <a href="{{ $meetingLink }}" style="color: #4f46e5; text-decoration: underline; word-break: break-all;">{{ $meetingLink }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #64748b;">
                                If you need to reschedule or have any questions ahead of time, simply reply directly to this email.
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
