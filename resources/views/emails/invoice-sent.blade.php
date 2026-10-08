<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            font-size: 28px;
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
            font-size: 24px;
            font-weight: bold;
            color: #16a34a;
            margin: 15px 0;
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
            <h1>Invoice</h1>
        </div>

        <div class="content">
            <p>Dear {{ $invoice->customer->name }},</p>

            <p>Please find attached your invoice <span class="invoice-number">#{{ $invoice->invoice_number }}</span>.
            </p>

            @php
                $paidAmount = (float) $invoice->getPaidAmount();
                $remainingAmount = (float) $invoice->getRemainingAmount();
            @endphp

            <div class="amount">Total Amount: ₹{{ number_format((float) $invoice->total_amount, 2) }}</div>
            <p><strong>Amount Paid:</strong> ₹{{ number_format($paidAmount, 2) }}</p>
            <div class="amount" style="color: {{ $remainingAmount > 0 ? '#dc2626' : '#16a34a' }}; font-size: 20px;">
                {{ $remainingAmount > 0 ? 'Balance Due' : 'Balance Due (Paid)' }}:
                ₹{{ number_format($remainingAmount, 2) }}
            </div>

            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M, Y') }}</p>

            @if($invoice->notes)
                <p><strong>Note:</strong> {{ $invoice->notes }}</p>
            @endif

            <p>If you have any questions regarding this invoice, please feel free to contact us at <a href="mailto:support@iconicpages.com">support@iconicpages.com</a></p>

            <p>Thank you for your business!</p>

            <p>Best regards,<br>
                <span class="company-name">ICPG Technologies Pvt. Ltd.</span>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} ICPG Technologies Pvt. Ltd. All rights reserved.</p>
            <p>www.iconicpages.com | support@iconicpages.com | +91 8087744220</p>
        </div>
    </div>
</body>

</html>