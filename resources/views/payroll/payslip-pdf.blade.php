<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip {{ $payroll->payroll_code }}</title>
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
            margin-bottom: 25px;
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

        /* Employee & Details Section */
        .billing-section {
            margin-bottom: 25px;
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
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .detail-row {
            margin-bottom: 6px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
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

        /* Items / Earnings & Deductions Tables */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            margin-top: 5px;
        }

        .items-table th {
            background: #1e3a8a;
            color: #fff;
            padding: 10px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10.5px;
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

        .subtotal-row td {
            background: #f8fafc;
            font-weight: bold;
            border-top: 1.5px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
        }

        /* Summary */
        .summary-section {
            margin-bottom: 25px;
            margin-top: 5px;
        }

        .summary-table {
            width: 320px;
            float: right;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .summary-table td {
            padding: 9px 15px;
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
            padding: 12px 15px;
            font-size: 13.5px;
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Bottom Section */
        .bottom-section {
            margin-top: 20px;
            clear: both;
        }

        .bottom-table {
            width: 100%;
        }

        .notes-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            padding: 12px 14px;
            border-radius: 4px;
        }

        .payment-box {
            background: #f0f9ff;
            border: 1px solid #7dd3fc;
            padding: 12px 14px;
            border-radius: 4px;
        }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .notes-box .box-title {
            color: #92400e;
        }

        .payment-box .box-title {
            color: #0369a1;
        }

        .box-content {
            font-size: 10.5px;
            line-height: 1.55;
        }

        .notes-box .box-content {
            color: #78350f;
        }

        .payment-box .box-content {
            color: #0c4a6e;
        }

        /* Signatures */
        .signatures {
            width: 100%;
            margin-top: 35px;
            clear: both;
        }

        .sig-space {
            height: 42px;
            border-bottom: 1px solid #d1d5db;
        }

        .sig-label {
            padding-top: 6px;
            font-size: 9.5px;
            color: #666;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }

        .footer-company {
            font-size: 11.5px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 3px;
        }

        .footer-note {
            font-size: 9px;
            color: #999;
            margin-top: 4px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    @php
        $employee = $payroll->user;
        $bank = $employee?->salary;
    @endphp

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
                    <div class="invoice-title">PAYSLIP</div>
                    <div class="invoice-number">
                        Payslip No: <strong>#{{ $payroll->payroll_code }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Employee & Payslip Details -->
    <div class="billing-section">
        <table class="billing-table">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 25px;">
                    <div class="section-label">Employee Details</div>
                    <div class="client-name">{{ $employee?->name ?? 'Unknown Employee' }}</div>
                    @if($employee?->emp_id)
                        <div class="client-info" style="font-weight: bold; color: #1e3a8a;">EMP ID: {{ $employee->emp_id }}</div>
                    @endif
                    @if($employee?->designation)
                        <div class="client-info" style="font-weight: bold; color: #333;">
                            Designation: {{ \App\Rbac\Designation::label($employee->designation) }}
                        </div>
                    @endif
                    @if($employee?->department)
                        <div class="client-info">Department: {{ ucfirst($employee->department) }}</div>
                    @endif
                    @if($employee?->email)
                        <div class="client-info" style="color: #1e3a8a;">{{ $employee->email }}</div>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top;">
                    <div class="section-label">Payslip Details</div>
                    <div class="details-box">
                        <div class="detail-row clearfix">
                            <span class="detail-label">Pay Period:</span>
                            <span class="detail-value">{{ $payroll->period_label }}</span>
                        </div>
                        <div class="detail-row clearfix">
                            <span class="detail-label">Issue Date:</span>
                            <span class="detail-value">{{ $payroll->created_at->format('d M, Y') }}</span>
                        </div>
                        <div class="detail-row clearfix">
                            <span class="detail-label">Payment Date:</span>
                            <span class="detail-value">{{ $payroll->payment_date ? $payroll->payment_date->format('d M, Y') : 'Pending disbursement' }}</span>
                        </div>
                        <div class="detail-row clearfix">
                            <span class="detail-label">Payment Status:</span>
                            <span class="detail-value" style="color: {{ $payroll->payment_status === 'paid' ? '#16a34a' : '#d97706' }};">
                                {{ strtoupper($payroll->payment_status) }}
                            </span>
                        </div>
                        <div class="detail-row clearfix">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">{{ ucwords(str_replace('_', ' ', $payroll->payment_method ?? 'Bank Transfer')) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Earnings & Deductions Tables -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 65%;">Earnings</th>
                            <th class="text-right" style="width: 35%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->basic_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td>House Rent Allowance (HRA)</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->hra, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Special &amp; Other Allowances</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->allowances, 2) }}</td>
                        </tr>
                        @if ($payroll->bonus > 0)
                            <tr>
                                <td>Performance Bonus</td>
                                <td class="text-right" style="color: #16a34a; font-weight: bold;">₹{{ number_format((float) $payroll->bonus, 2) }}</td>
                            </tr>
                        @endif
                        @if ($payroll->overtime_pay > 0)
                            <tr>
                                <td>Overtime Pay</td>
                                <td class="text-right" style="color: #16a34a; font-weight: bold;">₹{{ number_format((float) $payroll->overtime_pay, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td style="font-weight: bold;">Total Gross Earnings</td>
                            <td class="text-right" style="font-weight: bold;">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 65%;">Deductions</th>
                            <th class="text-right" style="width: 35%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Provident Fund (PF)</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->provident_fund, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Professional Tax (PT)</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->professional_tax, 2) }}</td>
                        </tr>
                        <tr>
                            <td>TDS / Income Tax</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->tds, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Other Deductions</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->other_deductions, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td style="font-weight: bold; color: #dc2626;">Total Deductions</td>
                            <td class="text-right" style="font-weight: bold; color: #dc2626;">-₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <!-- Summary Section -->
    <div class="summary-section clearfix">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Total Gross Earnings</td>
                <td class="summary-value">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
            </tr>
            @if($payroll->total_deductions > 0)
                <tr>
                    <td class="summary-label">Total Deductions</td>
                    <td class="summary-value" style="color: #dc2626;">-₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Net Payable Salary</td>
                <td style="text-align: right;">₹{{ number_format((float) $payroll->net_salary, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <table class="bottom-table">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 12px;">
                    <div class="notes-box">
                        <div class="box-title">Notes &amp; Remarks</div>
                        <div class="box-content">
                            @if($payroll->notes)
                                <p style="margin-bottom: 5px;">{{ $payroll->notes }}</p>
                            @else
                                <p>Monthly payroll disbursed as per company compensation and benefits policy.</p>
                            @endif
                            <p style="font-size: 9.5px; color: #92400e; margin-top: 6px; font-style: italic;">
                                Confidential &mdash; intended solely for {{ $employee?->name ?? 'the employee' }}.
                            </p>
                        </div>
                    </div>
                </td>
                <td style="width: 52%; vertical-align: top; padding-left: 12px;">
                    <div class="payment-box">
                        <div class="box-title">Payment &amp; Bank Details</div>
                        <div class="box-content">
                            <div class="detail-row clearfix">
                                <span class="detail-label" style="color: #0369a1;">Bank Name:</span>
                                <span class="detail-value" style="color: #0c4a6e;">{{ $bank?->bank_name ?: 'On File' }}</span>
                            </div>
                            <div class="detail-row clearfix">
                                <span class="detail-label" style="color: #0369a1;">Account No:</span>
                                <span class="detail-value" style="color: #0c4a6e; font-family: 'DejaVu Sans Mono', monospace;">{{ $bank?->account_number ?: 'On File' }}</span>
                            </div>
                            <div class="detail-row clearfix">
                                <span class="detail-label" style="color: #0369a1;">IFSC Code:</span>
                                <span class="detail-value" style="color: #0c4a6e; font-family: 'DejaVu Sans Mono', monospace;">{{ $bank?->ifsc_code ?: 'On File' }}</span>
                            </div>
                            @if($payroll->transaction_reference)
                                <div class="detail-row clearfix">
                                    <span class="detail-label" style="color: #0369a1;">Txn Reference:</span>
                                    <span class="detail-value" style="color: #0c4a6e;">{{ $payroll->transaction_reference }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td style="width: 44%; padding-right: 25px;">
                <div class="sig-space"></div>
                <div class="sig-label">Employee Signature</div>
            </td>
            <td style="width: 12%;"></td>
            <td style="width: 44%; padding-left: 25px;">
                <div class="sig-space"></div>
                <div class="sig-label">Authorized Signatory (ICPG)</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">ICPG Technologies Pvt. Ltd.</div>
        <div class="footer-note">
            This is a computer-generated salary slip and does not require a physical signature.<br>
            &copy; {{ date('Y') }} ICPG Technologies Pvt. Ltd. &middot; All Rights Reserved.
        </div>
    </div>
</body>

</html>
