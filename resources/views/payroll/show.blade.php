@extends('layouts.app')

@section('title', 'Payslip - ' . $payroll->payroll_code)
@section('page-title', 'Payslip: ' . $payroll->payroll_code)

@section('content')
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            nav, aside, header, .no-print {
                display: none !important;
            }
            .printable-payslip {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>

    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
                Salary Slip &middot; {{ $payroll->period_label }}
            </h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('payrolls.pdf', $payroll) }}" download="{{ $payroll->getPdfFilename() }}" id="btn-download-pdf"
                class="inline-flex items-center bg-gradient-to-r from-blue-700 to-indigo-800 hover:from-blue-800 hover:to-indigo-900 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF
            </a>

            <button type="button" onclick="window.print()"
                class="inline-flex items-center bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Save PDF
            </button>

            @can('payroll.edit')
                <a href="{{ route('payrolls.edit', $payroll) }}"
                    class="inline-flex items-center bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    Edit Record
                </a>
            @endcan
        </div>
    </div>

    @php
        $employee = $payroll->user;
        $salary = $employee?->salary;
    @endphp

    <!-- Printable Payslip Document (Mirrors Invoice Template Structure) -->
    <div class="printable-payslip bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 max-w-4xl mx-auto p-6 sm:p-8 md:p-10 text-gray-800 dark:text-gray-200">
        <!-- Header -->
        <div class="border-b-[3px] border-[#1e3a8a] pb-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center">
                    @if(file_exists(public_path('storage/images/logo.png')))
                        <img src="{{ asset('storage/images/logo.png') }}" alt="Iconicpages" class="max-h-14 w-auto">
                    @else
                        <div>
                            <h1 class="text-2xl font-black text-[#1e3a8a] dark:text-blue-400 tracking-wide">
                                {{ config('app.name', 'ICONICPAGES') }}
                            </h1>
                            <p class="text-xs text-gray-500 mt-0.5">Make Your Brand Iconic</p>
                        </div>
                    @endif
                </div>

                <div class="sm:text-right">
                    <h2 class="text-3xl font-extrabold text-[#1e3a8a] dark:text-blue-400 tracking-tight uppercase">
                        PAYSLIP
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Payslip No: <strong class="text-gray-900 dark:text-gray-200 font-mono">#{{ $payroll->payroll_code }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Employee & Payslip Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Employee Details (Bill To Style) -->
            <div>
                <div class="text-[11px] font-bold text-[#1e3a8a] dark:text-blue-400 uppercase tracking-wider pb-1.5 mb-3 border-b-2 border-gray-200 dark:border-gray-700">
                    Employee Details
                </div>
                <div class="space-y-1 text-xs">
                    <p class="text-base font-bold text-gray-900 dark:text-white">{{ $employee?->name ?? 'Unknown' }}</p>
                    @if($employee?->emp_id)
                        <p class="font-bold text-[#1e3a8a] dark:text-blue-400 font-mono">EMP ID: {{ $employee->emp_id }}</p>
                    @endif
                    @if($employee?->designation)
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ \App\Rbac\Designation::label($employee->designation) ?? 'Team Member' }}</p>
                    @endif
                    @if($employee?->department)
                        <p class="text-gray-600 dark:text-gray-400">Department: {{ ucfirst($employee->department) }}</p>
                    @endif
                    @if($employee?->email)
                        <p class="text-[#1e3a8a] dark:text-blue-400">{{ $employee->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Payslip Details Box -->
            <div>
                <div class="text-[11px] font-bold text-[#1e3a8a] dark:text-blue-400 uppercase tracking-wider pb-1.5 mb-3 border-b-2 border-gray-200 dark:border-gray-700">
                    Payslip Details
                </div>
                <div class="bg-slate-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Pay Period:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $payroll->period_label }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Issue Date:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $payroll->created_at->format('d M, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Payment Date:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $payroll->payment_date ? $payroll->payment_date->format('d M, Y') : 'Pending disbursement' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Payment Status:</span>
                        <span class="font-bold uppercase {{ $payroll->payment_status === 'paid' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $payroll->payment_status }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $payroll->payment_method ?? 'Bank Transfer')) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Earnings Table -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                    <thead>
                        <tr class="bg-[#1e3a8a] text-white">
                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-[10px]">Earnings</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wider text-[10px]">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Basic Salary</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-750">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">House Rent Allowance (HRA)</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->hra, 2) }}</td>
                        </tr>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Special & Other Allowances</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->allowances, 2) }}</td>
                        </tr>
                        @if ($payroll->bonus > 0)
                            <tr class="bg-gray-50/50 dark:bg-gray-750">
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Performance Bonus</td>
                                <td class="px-4 py-2 text-right font-bold text-green-600 dark:text-green-400">₹{{ number_format($payroll->bonus, 2) }}</td>
                            </tr>
                        @endif
                        @if ($payroll->overtime_pay > 0)
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Overtime Pay</td>
                                <td class="px-4 py-2 text-right font-bold text-green-600 dark:text-green-400">₹{{ number_format($payroll->overtime_pay, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-gray-700/50 font-bold border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">Total Gross Earnings</td>
                            <td class="px-4 py-2 text-right text-gray-900 dark:text-white">₹{{ number_format($payroll->gross_salary, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Deductions Table -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                    <thead>
                        <tr class="bg-[#1e3a8a] text-white">
                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-[10px]">Deductions</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wider text-[10px]">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Provident Fund (PF)</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->provident_fund, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-750">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Professional Tax (PT)</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->professional_tax, 2) }}</td>
                        </tr>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">TDS / Income Tax</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->tds, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-750">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Other Deductions</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">₹{{ number_format($payroll->other_deductions, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-gray-700/50 font-bold border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2 text-red-600 dark:text-red-400">Total Deductions</td>
                            <td class="px-4 py-2 text-right text-red-600 dark:text-red-400">-₹{{ number_format($payroll->total_deductions, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Summary Table (Matches Invoice Summary Block) -->
        <div class="flex justify-end mb-6">
            <div class="w-full sm:w-80 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden text-xs">
                <div class="flex justify-between items-center px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Total Gross Earnings</span>
                    <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($payroll->gross_salary, 2) }}</span>
                </div>
                @if($payroll->total_deductions > 0)
                    <div class="flex justify-between items-center px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Total Deductions</span>
                        <span class="font-bold text-red-600 dark:text-red-400">-₹{{ number_format($payroll->total_deductions, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center px-4 py-3 bg-[#1e3a8a] text-white font-bold text-sm">
                    <span>Net Payable Salary</span>
                    <span>₹{{ number_format($payroll->net_salary, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Notes & Payment Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Notes Box -->
            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-800/40 rounded-lg p-4 text-xs">
                <div class="text-[10px] font-bold text-amber-900 dark:text-amber-400 uppercase tracking-wider mb-2">
                    Notes &amp; Remarks
                </div>
                <div class="text-amber-900 dark:text-amber-200 space-y-1">
                    @if($payroll->notes)
                        <p>{{ $payroll->notes }}</p>
                    @else
                        <p>Monthly payroll disbursed as per company compensation and benefits policy.</p>
                    @endif
                    <p class="text-[10px] text-amber-700 dark:text-amber-400 italic pt-1">
                        Confidential &mdash; intended solely for {{ $employee?->name ?? 'the employee' }}.
                    </p>
                </div>
            </div>

            <!-- Payment & Bank Details Box -->
            <div class="bg-sky-50 dark:bg-sky-950/20 border border-sky-300 dark:border-sky-800/40 rounded-lg p-4 text-xs">
                <div class="text-[10px] font-bold text-sky-900 dark:text-sky-400 uppercase tracking-wider mb-2">
                    Payment &amp; Bank Details
                </div>
                <div class="space-y-1 text-sky-950 dark:text-sky-200">
                    <div class="flex justify-between">
                        <span class="text-sky-800 dark:text-sky-400">Bank Name:</span>
                        <span class="font-semibold">{{ $salary?->bank_name ?: 'On File' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sky-800 dark:text-sky-400">Account No:</span>
                        <span class="font-mono font-semibold">{{ $salary?->account_number ?: 'On File' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sky-800 dark:text-sky-400">IFSC Code:</span>
                        <span class="font-mono font-semibold">{{ $salary?->ifsc_code ?: 'On File' }}</span>
                    </div>
                    @if ($payroll->transaction_reference)
                        <div class="flex justify-between">
                            <span class="text-sky-800 dark:text-sky-400">Txn Ref:</span>
                            <span class="font-mono font-semibold">{{ $payroll->transaction_reference }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-10 text-xs text-center text-gray-600 dark:text-gray-400">
            <div>
                <div class="h-12 border-b border-gray-300 dark:border-gray-600"></div>
                <div class="pt-2 font-medium">Employee Signature</div>
            </div>

            <div>
                <div class="h-12 border-b border-gray-300 dark:border-gray-600"></div>
                <div class="pt-2 font-medium">Authorized Signatory (ICPG)</div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-700 text-center">
            <p class="text-xs font-bold text-[#1e3a8a] dark:text-blue-400">ICPG Technologies Pvt. Ltd.</p>
            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                This is a computer-generated salary slip and does not require a physical signature.<br>
                &copy; {{ date('Y') }} ICPG Technologies Pvt. Ltd. &middot; All Rights Reserved.
            </p>
        </div>
    </div>
@endsection
