<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip {{ $payroll->period_label }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #1e3a8a;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 26px;
            font-weight: bold;
        }

        .header .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        .content {
            font-size: 15px;
            line-height: 1.8;
        }

        .salary-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .salary-grid td {
            padding: 9px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .salary-grid td.label {
            color: #6b7280;
        }

        .salary-grid td.value {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }

        .salary-grid tr.deduction td.value {
            color: #b91c1c;
        }

        .salary-grid tr.total td {
            border-bottom: none;
            border-top: 2px solid #1e3a8a;
            font-weight: bold;
            padding-top: 12px;
            color: #1e3a8a;
        }

        .net-box {
            background-color: #1e3a8a;
            color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }

        .net-box .label {
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .net-box .amount {
            font-size: 30px;
            font-weight: bold;
            margin-top: 6px;
        }

        .paid-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
        }

        .footer {
            margin-top: 34px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .company-name {
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Salary Slip</h1>
            <div class="subtitle">{{ $payroll->period_label }} &middot; {{ $payroll->payroll_code }}</div>
        </div>

        <div class="content">
            <p>Dear {{ $payroll->user?->name ?? 'there' }},</p>

            <p>
                Your salary for <strong>{{ $payroll->period_label }}</strong> has been processed and paid.
                Please find your detailed salary slip attached as a PDF.
            </p>

            <div class="paid-badge">
                &#10003; PAID on {{ $payroll->payment_date?->format('d M Y') ?? 'today' }}
            </div>

            <table class="salary-grid">
                <tr>
                    <td class="label">Basic Salary</td>
                    <td class="value">₹{{ number_format((float) $payroll->basic_salary, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">House Rent Allowance (HRA)</td>
                    <td class="value">₹{{ number_format((float) $payroll->hra, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Special &amp; Other Allowances</td>
                    <td class="value">₹{{ number_format((float) $payroll->allowances, 2) }}</td>
                </tr>
                @if ($payroll->bonus > 0)
                    <tr>
                        <td class="label">Performance Bonus</td>
                        <td class="value">₹{{ number_format((float) $payroll->bonus, 2) }}</td>
                    </tr>
                @endif
                @if ($payroll->overtime_pay > 0)
                    <tr>
                        <td class="label">Overtime Pay</td>
                        <td class="value">₹{{ number_format((float) $payroll->overtime_pay, 2) }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td class="label">Total Gross Earnings</td>
                    <td class="value">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                </tr>
                <tr class="deduction">
                    <td class="label">Total Deductions (PF, PT, TDS, Other)</td>
                    <td class="value">-₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                </tr>
            </table>

            <div class="net-box">
                <div class="label">Take Home / Net Payable</div>
                <div class="amount">₹{{ number_format((float) $payroll->net_salary, 2) }}</div>
            </div>

            @if ($payroll->payment_method)
                <p style="font-size:14px;">
                    <strong>Payment Mode:</strong>
                    {{ ucwords(str_replace('_', ' ', $payroll->payment_method)) }}
                    @if ($payroll->transaction_reference)
                        &middot; <strong>Ref:</strong> {{ $payroll->transaction_reference }}
                    @endif
                </p>
            @endif

            <p>
                If anything looks incorrect, please reply to this email or contact
                <a href="mailto:payroll@iconicpages.com">payroll@iconicpages.com</a> within 7 days.
            </p>

            <p>Warm regards,<br>
                <span class="company-name">Payroll Team, ICPG Technologies Pvt. Ltd.</span>
            </p>
        </div>

        <div class="footer">
            <p>This is an automatically generated salary slip and does not require a physical signature.</p>
            <p>&copy; {{ date('Y') }} ICPG Technologies Pvt. Ltd. All rights reserved.</p>
            <p>www.iconicpages.com | payroll@iconicpages.com</p>
        </div>
    </div>
</body>

</html>
