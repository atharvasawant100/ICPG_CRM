<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Approved</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 10px;
            background-color: #f3f4f6;
        }
        .container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 16px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .badge {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 14px;
            border-radius: 9999px;
            margin-top: 10px;
        }
        .content {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        .message {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .details-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px 4px;
            font-size: 14px;
            vertical-align: top;
        }
        .details-table .label {
            color: #6b7280;
            font-weight: 500;
            width: 38%;
        }
        .details-table .value {
            color: #111827;
            font-weight: 600;
        }
        .remarks-box {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
            margin-top: 16px;
            font-size: 14px;
            color: #065f46;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0 10px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 28px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
        .footer p {
            margin: 4px 0;
        }
        .footer .contact {
            color: #4f46e5;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <img src="https://iconicpages.com/wp-content/uploads/2025/12/IMG_3440.png" alt="Iconic Pages">
            <h1>Leave Application Approved</h1>
            <div class="badge">Approved &bull; Official HR Notice</div>
        </div>

        <!-- Body Content -->
        <div class="content">
            <div class="greeting">Dear {{ $user->name }},</div>
            <p class="message">
                We are pleased to inform you that your leave application has been <strong>approved</strong> by the HR Department. Your attendance roster and leave balance have been updated accordingly.
            </p>

            <!-- Leave Details Card -->
            <div class="details-card">
                <table class="details-table">
                    <tr>
                        <td class="label">Employee ID:</td>
                        <td class="value">{{ $user->emp_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Leave Type:</td>
                        <td class="value" style="color: #4f46e5;">{{ $leave->leaveType?->name ?? 'General Leave' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Duration:</td>
                        <td class="value">
                            {{ $leave->total_days }} {{ $leave->total_days == 1 ? 'Day' : 'Days' }}
                            @if($leave->is_half_day)
                                <span style="font-weight: normal; color: #6b7280;">({{ ucfirst(str_replace('_', ' ', $leave->half_day_type ?? 'half day')) }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Date Range:</td>
                        <td class="value">
                            @if($leave->start_date && $leave->end_date && $leave->start_date->ne($leave->end_date))
                                {{ $leave->start_date->format('M d, Y') }} &ndash; {{ $leave->end_date->format('M d, Y') }}
                            @else
                                {{ $leave->start_date?->format('M d, Y') }}
                            @endif
                        </td>
                    </tr>
                    @if($leave->reason)
                    <tr>
                        <td class="label">Reason:</td>
                        <td class="value" style="font-weight: 400; color: #374151;">{{ $leave->reason }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Approved By:</td>
                        <td class="value">{{ $approver->name ?? 'HR Executive' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Approved Date:</td>
                        <td class="value">{{ $leave->action_at ? $leave->action_at->format('M d, Y · h:i A') : now()->format('M d, Y · h:i A') }}</td>
                    </tr>
                </table>

                @if($leave->admin_remarks)
                <div class="remarks-box">
                    <strong>HR Remarks:</strong> {{ $leave->admin_remarks }}
                </div>
                @endif
            </div>

            <div class="btn-container">
                <a href="{{ route('my-leaves.index') }}" class="btn">View My Leaves &rarr;</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Iconic Pages HR Department</strong></p>
            <p>Email: <a href="mailto:hr@iconicpages.com" class="contact">hr@iconicpages.com</a></p>
            <p style="font-size: 11px; color: #9ca3af; margin-top: 12px;">
                This is an automated administrative notification sent from Iconic Pages Office HR Portal.
            </p>
        </div>
    </div>
</body>
</html>
