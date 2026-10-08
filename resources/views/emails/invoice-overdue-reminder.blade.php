<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Invoice {{ $invoice->invoice_number }}</title>
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
            border-bottom: 3px solid #b91c1c;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #b91c1c;
            margin: 0;
            font-size: 26px;
        }

        .content {
            font-size: 16px;
            line-height: 1.8;
        }

        .invoice-number {
            font-weight: bold;
            color: #1e3a8a;
            font-size: 18px;
        }

        .amount {
            font-size: 22px;
            font-weight: bold;
            color: #dc2626;
            margin: 14px 0;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .company-name {
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Payment Reminder</h1>
        </div>

        <div class="content">
            <p>Dear {{ $invoice->customer->name }},</p>

            <p>
                This is a friendly reminder that your invoice
                <span class="invoice-number">#{{ $invoice->invoice_number }}</span>
                is <strong>overdue</strong>.
            </p>

            @php
                $paidAmount = (float) $invoice->getPaidAmount();
                $remainingAmount = (float) $invoice->getRemainingAmount();
            @endphp

            <p><strong>Total Amount:</strong> ₹{{ number_format((float) $invoice->total_amount, 2) }}</p>
            <p><strong>Amount Paid:</strong> ₹{{ number_format($paidAmount, 2) }}</p>
            <div class="amount">Balance Due: ₹{{ number_format($remainingAmount, 2) }}</div>

            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M, Y') }}</p>

            <p class="muted">If you have already made the payment, please ignore this email.</p>

            <p>Thank you,<br>
                <span class="company-name">{{ config('mail.from.name', 'Iconicpages') }}</span>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('mail.from.name', 'Iconicpages') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>

