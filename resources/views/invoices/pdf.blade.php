<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 20px 25px 20px 25px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            padding: 20px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
        }

        .logo-img {
            max-width: 200px;
            max-height: 60px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            text-align: right;
        }

        .invoice-number {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }

        /* Billing Section */
        .billing-section {
            margin-bottom: 30px;
        }

        .billing-table {
            width: 100%;
        }

        .section-label {
            font-size: 10px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e5e7eb;
        }

        .client-name {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .client-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .details-box {
            background: #f8fafc;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .detail-row {
            margin-bottom: 8px;
        }

        .detail-label {
            color: #666;
            font-size: 11px;
        }

        .detail-value {
            font-weight: bold;
            color: #333;
            float: right;
            font-size: 11px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            margin-top: 5px;
        }

        .items-table th {
            background: #1e3a8a;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .items-table .text-center {
            text-align: center;
        }

        .items-table .text-right {
            text-align: right;
        }

        .item-name {
            font-weight: bold;
            color: #333;
            font-size: 12px;
        }

        .item-desc {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        /* Summary */
        .summary-section {
            margin-bottom: 30px;
            margin-top: 10px;
        }

        .summary-table {
            width: 300px;
            float: right;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .summary-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-label {
            color: #666;
            font-size: 11px;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }

        .total-row {
            background: #1e3a8a;
        }

        .total-row td {
            color: #fff;
            padding: 14px 15px;
            font-size: 14px;
            font-weight: bold;
        }

        .balance-row {
            background: #fee2e2;
        }

        .balance-row td {
            color: #dc2626;
            font-weight: bold;
            padding: 12px 15px;
        }

        .paid-amount {
            color: #16a34a;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Bottom Section */
        .bottom-section {
            margin-top: 35px;
            clear: both;
        }

        .bottom-table {
            width: 100%;
        }

        .notes-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            padding: 15px;
            border-radius: 4px;
        }

        .payment-box {
            background: #f0f9ff;
            border: 1px solid #7dd3fc;
            padding: 15px;
            border-radius: 4px;
        }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .notes-box .box-title {
            color: #92400e;
        }

        .payment-box .box-title {
            color: #0369a1;
        }

        .box-content {
            font-size: 11px;
            line-height: 1.6;
        }

        .notes-box .box-content {
            color: #78350f;
        }

        .payment-box .box-content {
            color: #0c4a6e;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            margin-top: 12px;
            border: 1px solid #fcd34d;
            padding: 4px;
            background: #fff;
        }

        /* Footer */
        .footer {
            margin-top: 45px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }

        .footer-company {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .footer-contact {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .footer-note {
            font-size: 9px;
            color: #999;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 50%; vertical-align: middle;">
                    @php
                        $logoUri = \App\Support\PdfImage::dataUri(public_path('storage/images/logo.png'), 320, 85);
                    @endphp
                    @if($logoUri)
                        <img src="{{ $logoUri }}" alt="Logo" class="logo-img">
                    @else
                        <div class="company-name">{{ config('app.name', 'ICONICPAGES') }}</div>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: middle;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">
                        Invoice No: <strong>#{{ $invoice->invoice_number }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Billing Section -->
    <div class="billing-section">
        <table class="billing-table">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 25px;">
                    <div class="section-label">Bill To</div>
                    <div class="client-name">{{ $invoice->customer->name }}</div>
                    @if($invoice->customer->company)
                        <div class="client-info">{{ $invoice->customer->company }}</div>
                    @endif
                    <div class="client-info" style="color: #1e3a8a;">{{ $invoice->customer->email }}</div>
                    <div class="client-info">{{ $invoice->customer->address }}</div>
                </td>
                <td style="width: 45%; vertical-align: top;">
                    <div class="section-label">Invoice Details</div>
                    <div class="details-box">
                        <div class="detail-row clearfix">
                            <span class="detail-label">Invoice Date:</span>
                            <span class="detail-value">{{ $invoice->invoice_date->format('d M, Y') }}</span>
                        </div>
                        <div class="detail-row clearfix">
                            <span class="detail-label">Due Date:</span>
                            <span class="detail-value">{{ $invoice->due_date->format('d M, Y') }}</span>
                        </div>
                        @if($invoice->payment_terms)
                            <div class="detail-row clearfix">
                                <span class="detail-label">Payment Terms:</span>
                                <span class="detail-value">{{ $invoice->payment_terms }}</span>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Description</th>
                <th class="text-right" style="width: 12%;">Qty</th>
                <th class="text-right" style="width: 18%;">Unit Price</th>
                <th class="text-right" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->description ?? ($item->service->name ?? 'N/A') }}</div>
                        <div class="item-desc">Service Period: {{ $invoice->invoice_date->format('F Y') }}</div>
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">₹{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section clearfix">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal</td>
                <td class="summary-value">₹{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->tax_amount > 0)
                <tr>
                    <td class="summary-label">Tax (GST 18%)</td>
                    <td class="summary-value">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->discount_amount > 0)
                <tr>
                    <td class="summary-label">Discount</td>
                    <td class="summary-value" style="color: #16a34a;">-₹{{ number_format($invoice->discount_amount, 2) }}
                    </td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total Amount</td>
                <td style="text-align: right;">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            @php
                $paidAmount = $invoice->getPaidAmount();
                $remaining = $invoice->getRemainingAmount();
            @endphp
            @if($paidAmount > 0)
                <tr>
                    <td class="summary-label">Amount Paid</td>
                    <td class="summary-value paid-amount">₹{{ number_format($paidAmount, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td>Balance Due</td>
                    <td style="text-align: right;">₹{{ number_format($remaining, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <table class="bottom-table">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                    <div class="notes-box">
                        <div class="box-title">Notes</div>
                        <div class="box-content">
                            {{ $invoice->notes ?? 'Thank you for your business. We appreciate your trust in our services.' }}
                        </div>

                        <div class="box-title" style="margin-top: 15px;">Scan to Pay</div>
                        @php
                            $qrUri = \App\Support\PdfImage::dataUri(public_path('storage/images/qr-code.jpg'), 180, 80);
                        @endphp
                        @if($qrUri)
                            <img src="{{ $qrUri }}" alt="QR Code" class="qr-code">
                        @endif
                    </div>
                </td>
                <td style="width: 52%; vertical-align: top; padding-left: 15px;">
                    <div class="payment-box">
                        <div class="box-title">Payment Information</div>
                        <div class="box-content">
                            <strong>Bank Name:</strong> Kotak Mahindra Bank<br>
                            <strong>Account Type:</strong> Current Account<br>
                            <strong>Account Name:</strong> ICPG TECHNOLOGIES PRIVATE LIMITED<br>
                            <strong>Account No:</strong> 0051882907<br>
                            <strong>IFSC Code:</strong> KKBK0002127<br>
                            <strong>UPI ID:</strong> 7666228107@Kotak
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">ICPG Technologies Pvt. Ltd.</div>
        <div class="footer-contact">
            www.iconicpages.com | support@iconicpages.com | +91 8087744220
        </div>
        <div class="footer-note">This is a computer-generated invoice and does not require a signature.</div>
    </div>
</body>

</html>