@extends('layouts.app')

@section('title', 'Employee Costing & Payroll Analytics')
@section('page-title', 'Employee Costing')

@section('content')
    <!-- Header Section -->
    <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Owner Confidential
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Updated today</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mt-1">
                Employee Costing & Workforce Analytics
            </h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.attendance.monthly') }}"
                class="inline-flex items-center bg-white border border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Attendance Statistics
            </a>

            <button type="button" onclick="window.print()"
                class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Export Report
            </button>
        </div>
    </div>

    <!-- Pending Salary Approvals Banner (Executive Action Required) -->
    @if ($pendingApprovals->isNotEmpty())
        <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20 border-2 border-amber-300 dark:border-amber-700/60 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-amber-200 dark:border-amber-800/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            Pending Salary Structure Approvals
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-500 text-white shadow-sm">
                                {{ $pendingApprovals->count() }} {{ Str::plural('Action Required', $pendingApprovals->count()) }}
                            </span>
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            The following salaries were set up by HR and require Company Owner approval before payroll can be generated.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($pendingApprovals as $pending)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-amber-200 dark:border-amber-800/60 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">
                                        {{ $pending->user->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $pending->user->emp_id ?: 'ID #'.$pending->user->id }} &bull; {{ $pending->user->designation ?: 'Staff' }} &bull; <span class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($pending->user->department ?: 'General') }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                    Pending Approval
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 py-2.5 my-2 border-y border-gray-100 dark:border-gray-700 text-xs">
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase">Basic</div>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">₹{{ number_format($pending->basic_salary, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase">Gross CTC</div>
                                    <div class="font-extrabold text-indigo-600 dark:text-indigo-400">₹{{ number_format($pending->gross_salary, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase">Net Pay</div>
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format($pending->net_salary, 2) }}</div>
                                </div>
                            </div>

                            <div class="text-[11px] text-gray-400 mb-3">
                                Submitted by: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $pending->submittedBy?->name ?? 'HR Department' }}</span>
                                @if ($pending->updated_at)
                                    &bull; {{ $pending->updated_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>

                        @php
                            $pendingPayload = [
                                'id' => $pending->id,
                                'employee_name' => $pending->user->name,
                                'emp_id' => $pending->user->emp_id ?: 'ID #'.$pending->user->id,
                                'designation' => $pending->user->designation ?: 'Staff',
                                'department' => ucfirst($pending->user->department ?: 'General'),
                                'approval_status' => $pending->approval_status,
                                'rejection_reason' => $pending->rejection_reason,
                                'submitted_by' => $pending->submittedBy?->name ?? 'HR Department',
                                'submitted_at' => $pending->updated_at ? $pending->updated_at->format('d M Y, h:i A') : 'N/A',
                                'approved_by' => $pending->approvedBy?->name ?? null,
                                'approved_at' => $pending->approved_at ? $pending->approved_at->format('d M Y, h:i A') : null,
                                'basic_salary' => (float) $pending->basic_salary,
                                'hra' => (float) $pending->hra,
                                'conveyance_allowance' => (float) $pending->conveyance_allowance,
                                'special_allowance' => (float) $pending->special_allowance,
                                'other_allowances' => (float) $pending->other_allowances,
                                'gross_salary' => (float) $pending->gross_salary,
                                'provident_fund' => (float) $pending->provident_fund,
                                'professional_tax' => (float) $pending->professional_tax,
                                'tds' => (float) $pending->tds,
                                'other_deductions' => (float) $pending->other_deductions,
                                'total_deductions' => (float) $pending->total_deductions,
                                'net_salary' => (float) $pending->net_salary,
                                'payment_method' => $pending->payment_method ?: 'bank_transfer',
                                'bank_name' => $pending->bank_name ?: 'Not Provided',
                                'account_number' => $pending->account_number ?: 'Not Provided',
                                'ifsc_code' => $pending->ifsc_code ?: 'Not Provided',
                                'pan_number' => $pending->pan_number ?: 'Not Provided',
                                'notes' => $pending->notes ?: 'None',
                                'approve_url' => route('admin.salaries.approve', $pending),
                                'reject_url' => route('admin.salaries.reject', $pending),
                            ];
                        @endphp

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-700 flex-wrap">
                            <!-- View Breakdown Button -->
                            <button type="button"
                                onclick='openSalaryDetailsModal(@json($pendingPayload))'
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                View
                            </button>

                            <!-- Approve Form -->
                            <form action="{{ route('admin.salaries.approve', $pending) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Approve the salary structure for {{ $pending->user->name }}?')"
                                    class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                    Approve
                                </button>
                            </form>

                            <!-- Reject Form with Reason Prompt -->
                            <form action="{{ route('admin.salaries.reject', $pending) }}" method="POST" class="flex-1"
                                onsubmit="let r = prompt('Reason for rejecting this salary structure (optional):'); if (r === null) return false; this.querySelector('input[name=rejection_reason]').value = r; return true;">
                                @csrf
                                <input type="hidden" name="rejection_reason" value="">
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Monthly Gross Outflow -->
        <div class="bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-indigo-100 dark:border-gray-700 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Monthly Payroll Cost</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
                    ₹
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white" title="₹{{ number_format($totalMonthlyGross, 2) }}">
                {{ \App\Support\NumberHelper::rupee($totalMonthlyGross) }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                <span>Annual Run Rate:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200" title="₹{{ number_format($totalAnnualRunRate, 0) }}">{{ \App\Support\NumberHelper::rupee($totalAnnualRunRate) }}</span>
            </div>
        </div>

        <!-- Net Take-Home Disbursal -->
        <div class="bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-emerald-100 dark:border-gray-700 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Net Take-Home Pay</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white" title="₹{{ number_format($totalMonthlyNet, 2) }}">
                {{ \App\Support\NumberHelper::rupee($totalMonthlyNet) }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                <span>Total Deductions:</span>
                <span class="font-semibold text-red-600 dark:text-red-400" title="₹{{ number_format($totalMonthlyDeductions, 2) }}">{{ \App\Support\NumberHelper::rupee($totalMonthlyDeductions) }}</span>
            </div>
        </div>

        <!-- Average Cost Per Employee -->
        <div class="bg-gradient-to-br from-blue-500/10 via-sky-500/5 to-transparent bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-blue-100 dark:border-gray-700 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Avg Cost / Employee</span>
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white" title="₹{{ number_format($avgMonthlyCost, 2) }}">
                {{ \App\Support\NumberHelper::rupee($avgMonthlyCost) }}
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">/mo</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                <span>Annual Average CTC:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200" title="₹{{ number_format($avgAnnualCost, 0) }}">{{ \App\Support\NumberHelper::rupee($avgAnnualCost) }}</span>
            </div>
        </div>

        <!-- Headcount & Coverage -->
        <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-amber-100 dark:border-gray-700 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Active Staff Count</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                {{ $totalActiveStaff }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Total</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                <span>Configured Salaries:</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $configuredCount }} / {{ $totalActiveStaff }}</span>
            </div>
        </div>
    </div>

    <!-- Department Cost Breakdown & Salary Component Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Department-wise Cost Breakdown -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Department Cost Breakdown
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Workforce expenditure and budget distribution by department
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($departmentCosts as $deptKey => $data)
                    <div class="p-3.5 rounded-lg border border-gray-100 dark:border-gray-700/80 hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-sm text-gray-800 dark:text-white">
                                    {{ $data['name'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $data['headcount'] }} {{ Str::plural('Member', $data['headcount']) }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="font-extrabold text-sm text-gray-900 dark:text-white" title="₹{{ number_format($data['monthly_gross'], 2) }}">
                                    {{ \App\Support\NumberHelper::rupee($data['monthly_gross']) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                    ({{ $data['share_pct'] }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden mb-2">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                                style="width: {{ min(100, max(2, $data['share_pct'])) }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                            <span>Avg CTC: <strong class="text-gray-700 dark:text-gray-300">{{ \App\Support\NumberHelper::rupee($data['avg_salary']) }}/mo</strong></span>
                            <span>Annualized: <strong class="text-gray-700 dark:text-gray-300">{{ \App\Support\NumberHelper::rupee($data['annual_gross']) }}</strong></span>
                            <span>Take-Home: <strong class="text-emerald-600 dark:text-emerald-400">{{ \App\Support\NumberHelper::rupee($data['monthly_net']) }}</strong></span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500 text-sm">
                        No department salary data configured yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Compensation Structure & Tax Liabilities -->
        <div class="space-y-6">
            <!-- Salary Components Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    Monthly Earnings Components
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Aggregated company compensation makeup
                </p>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Basic Salary</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalBasic, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">House Rent Allowance (HRA)</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalHra, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Conveyance Allowance</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalConveyance, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Special Allowance</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalSpecial, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-600 dark:text-gray-400">Other Allowances</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalOtherAllowances, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Statutory & Tax Liabilities -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                    Statutory & Tax Withholdings
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Monthly statutory compliance deductions
                </p>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Provident Fund (PF)</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalPf, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Tax Deducted at Source (TDS)</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalTds, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Professional Tax (PT)</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalPt, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-600 dark:text-gray-400">Other Deductions</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">₹{{ number_format($totalOtherDeductions, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Slabs Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-1">
            Workforce Salary Slab Distribution
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Compensation tier spread across team members
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach ($brackets as $b)
                @php
                    $pct = $configuredCount > 0 ? round(($b['count'] / $configuredCount) * 100, 1) : 0;
                @endphp
                <div class="p-3.5 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $b['label'] }}</div>
                    <div class="text-xl font-extrabold text-gray-900 dark:text-white mt-1">
                        {{ $b['count'] }} <span class="text-xs font-normal text-gray-500">({{ $pct }}%)</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        Total: {{ \App\Support\NumberHelper::rupee($b['cost']) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Employee Costing Roster Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
        <!-- Table Header & Filters -->
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Employee Compensation Directory
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Individual cost to company, allowances, withholdings, and net compensation
                </p>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.reports.costing') }}" class="flex flex-wrap items-center gap-2.5">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search name, ID, role..."
                    class="text-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">

                <select name="department" onchange="this.form.submit()"
                    class="text-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDept === $dept ? 'selected' : '' }}>
                            {{ ucfirst($dept) }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="text-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="configured" {{ $selectedStatus === 'configured' ? 'selected' : '' }}>Configured Salary</option>
                    <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending_approval" {{ $selectedStatus === 'pending_approval' ? 'selected' : '' }}>Pending Admin Review</option>
                    <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending Setup (No Salary)</option>
                </select>

                <button type="submit"
                    class="px-3 py-2 rounded-lg text-xs font-semibold bg-gray-800 text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase font-semibold text-[11px] text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-3">Department</th>
                        <th class="py-3 px-3 text-right">Basic</th>
                        <th class="py-3 px-3 text-right">Allowances</th>
                        <th class="py-3 px-3 text-right">Gross / CTC</th>
                        <th class="py-3 px-3 text-right">Deductions</th>
                        <th class="py-3 px-3 text-right">Net Take-Home</th>
                        <th class="py-3 px-3 text-right">Annual Run-rate</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($employees as $employee)
                        @php
                            $sal = $employee->salary;
                            $gross = $sal ? (float) $sal->gross_salary : 0;
                            $basic = $sal ? (float) $sal->basic_salary : 0;
                            $allowances = $sal ? ($gross - $basic) : 0;
                            $deductions = $sal ? (float) $sal->total_deductions : 0;
                            $net = $sal ? (float) $sal->net_salary : 0;
                            $annual = $gross * 12;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.users.show', $employee) }}" class="flex items-center gap-2.5 group">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs uppercase flex-shrink-0 group-hover:scale-105 transition-transform">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ $employee->name }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            {{ $employee->emp_id ?: 'ID: #'.$employee->id }} &bull; {{ $employee->designation ?: 'Staff' }}
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($employee->department ?: 'General') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($basic, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($allowances, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-extrabold text-gray-900 dark:text-white">
                                ₹{{ number_format($gross, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right text-red-600 dark:text-red-400 font-medium">
                                ₹{{ number_format($deductions, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                ₹{{ number_format($net, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-semibold text-gray-800 dark:text-gray-200">
                                ₹{{ number_format($annual, 0) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($sal)
                                    @if ($sal->isApproved())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            ✓ Approved
                                        </span>
                                    @elseif ($sal->isPendingApproval())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 animate-pulse">
                                            ⏳ Pending Review
                                        </span>
                                    @elseif ($sal->isRejected())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300" title="{{ $sal->rejection_reason }}">
                                            ✕ Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                            {{ ucfirst($sal->approval_status) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Pending Setup
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if ($sal)
                                    @php
                                        $rowPayload = [
                                            'id' => $sal->id,
                                            'employee_name' => $employee->name,
                                            'emp_id' => $employee->emp_id ?: 'ID #'.$employee->id,
                                            'designation' => $employee->designation ?: 'Staff',
                                            'department' => ucfirst($employee->department ?: 'General'),
                                            'approval_status' => $sal->approval_status,
                                            'rejection_reason' => $sal->rejection_reason,
                                            'submitted_by' => $sal->submittedBy?->name ?? 'HR Department',
                                            'submitted_at' => $sal->updated_at ? $sal->updated_at->format('d M Y, h:i A') : 'N/A',
                                            'approved_by' => $sal->approvedBy?->name ?? null,
                                            'approved_at' => $sal->approved_at ? $sal->approved_at->format('d M Y, h:i A') : null,
                                            'basic_salary' => (float) $sal->basic_salary,
                                            'hra' => (float) $sal->hra,
                                            'conveyance_allowance' => (float) $sal->conveyance_allowance,
                                            'special_allowance' => (float) $sal->special_allowance,
                                            'other_allowances' => (float) $sal->other_allowances,
                                            'gross_salary' => (float) $sal->gross_salary,
                                            'provident_fund' => (float) $sal->provident_fund,
                                            'professional_tax' => (float) $sal->professional_tax,
                                            'tds' => (float) $sal->tds,
                                            'other_deductions' => (float) $sal->other_deductions,
                                            'total_deductions' => (float) $sal->total_deductions,
                                            'net_salary' => (float) $sal->net_salary,
                                            'payment_method' => $sal->payment_method ?: 'bank_transfer',
                                            'bank_name' => $sal->bank_name ?: 'Not Provided',
                                            'account_number' => $sal->account_number ?: 'Not Provided',
                                            'ifsc_code' => $sal->ifsc_code ?: 'Not Provided',
                                            'pan_number' => $sal->pan_number ?: 'Not Provided',
                                            'notes' => $sal->notes ?: 'None',
                                            'approve_url' => route('admin.salaries.approve', $sal),
                                            'reject_url' => route('admin.salaries.reject', $sal),
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- View Details Button -->
                                        <button type="button"
                                            onclick='openSalaryDetailsModal(@json($rowPayload))'
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors"
                                            title="View Complete Breakdown">
                                            View
                                        </button>

                                        @if ($sal->isPendingApproval())
                                            <form action="{{ route('admin.salaries.approve', $sal) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Approve Salary"
                                                    onclick="return confirm('Approve salary for {{ $employee->name }}?')"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.salaries.reject', $sal) }}" method="POST" class="inline"
                                                onsubmit="let r = prompt('Reason for rejecting salary:'); if (r === null) return false; this.querySelector('input[name=rejection_reason]').value = r; return true;">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="">
                                                <button type="submit" title="Reject Salary"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors">
                                                    Reject
                                                </button>
                                            </form>
                                        @elseif ($sal->isRejected())
                                            <form action="{{ route('admin.salaries.approve', $sal) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Re-approve Salary Structure"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                                    Re-Approve
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-gray-500">
                                No employees found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Salary Structure Details Modal (Admin Review) -->
    <div id="salaryDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeSalaryDetailsModal()"></div>

        <!-- Modal Box -->
        <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-left">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-3xl border border-gray-100 dark:border-gray-700 my-8">
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-indigo-50/40 dark:from-gray-800 dark:to-indigo-950/20 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div id="modal-avatar" class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-extrabold text-base uppercase shadow-sm flex-shrink-0">
                            A
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 id="modal-employee-name" class="text-lg font-bold text-gray-900 dark:text-white">
                                    Employee Name
                                </h3>
                                <span id="modal-status-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800">
                                    Approved
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <span id="modal-emp-id">ICPG_00</span> &bull;
                                <span id="modal-designation">Designation</span> &bull;
                                <strong id="modal-department" class="text-gray-700 dark:text-gray-300">Department</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSalaryDetailsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                    <!-- Top Metrics Strip -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gross Monthly CTC</div>
                            <div id="modal-gross" class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-0.5">₹0.00</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Annual: <span id="modal-annual">₹0</span></div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Deductions</div>
                            <div id="modal-deductions" class="text-xl font-extrabold text-red-600 dark:text-red-400 mt-0.5">-₹0.00</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">PF, PT, TDS & Others</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net In-Hand Pay</div>
                            <div id="modal-net" class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">₹0.00</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Disbursed Take-home</div>
                        </div>
                    </div>

                    <!-- Side-by-Side: Earnings vs Deductions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Earnings Breakdown -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-white dark:bg-gray-800 shadow-2xs">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Monthly Earnings (Allowances)
                            </h4>
                            <div class="space-y-2 mt-3 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Basic Salary</span>
                                    <strong id="modal-basic" class="text-gray-800 dark:text-gray-200 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">House Rent Allowance (HRA)</span>
                                    <strong id="modal-hra" class="text-gray-800 dark:text-gray-200 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Conveyance Allowance</span>
                                    <strong id="modal-conveyance" class="text-gray-800 dark:text-gray-200 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Special Allowance</span>
                                    <strong id="modal-special" class="text-gray-800 dark:text-gray-200 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Other Allowances</span>
                                    <strong id="modal-other-allowances" class="text-gray-800 dark:text-gray-200 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center pt-2 font-bold text-gray-900 dark:text-white">
                                    <span>Total Monthly Gross</span>
                                    <span id="modal-gross-total" class="text-indigo-600 dark:text-indigo-400 font-extrabold">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Deductions Breakdown -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-white dark:bg-gray-800 shadow-2xs">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Monthly Deductions
                            </h4>
                            <div class="space-y-2 mt-3 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Provident Fund (PF)</span>
                                    <strong id="modal-pf" class="text-red-600 dark:text-red-400 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Professional Tax (PT)</span>
                                    <strong id="modal-pt" class="text-red-600 dark:text-red-400 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Tax / TDS (Income Tax)</span>
                                    <strong id="modal-tds" class="text-red-600 dark:text-red-400 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700/50">
                                    <span class="text-gray-600 dark:text-gray-400">Other Deductions / Advance</span>
                                    <strong id="modal-other-deductions" class="text-red-600 dark:text-red-400 font-semibold">₹0.00</strong>
                                </div>
                                <div class="flex justify-between items-center pt-2 font-bold text-gray-900 dark:text-white">
                                    <span>Total Deductions</span>
                                    <span id="modal-deductions-total" class="text-red-600 dark:text-red-400 font-extrabold">-₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Banking & Disbursement Details -->
                    <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-750/40">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Disbursement & Banking Details
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mt-3">
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">Payment Method</span>
                                <strong id="modal-payment-method" class="text-gray-800 dark:text-gray-200">Bank Transfer</strong>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">Bank Name</span>
                                <strong id="modal-bank-name" class="text-gray-800 dark:text-gray-200">-</strong>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">Account Number</span>
                                <strong id="modal-account-number" class="text-gray-800 dark:text-gray-200 font-mono">-</strong>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">IFSC / PAN</span>
                                <strong id="modal-ifsc-pan" class="text-gray-800 dark:text-gray-200 font-mono">-</strong>
                            </div>
                        </div>
                        <div id="modal-notes-container" class="mt-2.5 pt-2 border-t border-gray-200/60 dark:border-gray-700 text-xs text-gray-500">
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Notes:</span> <span id="modal-notes">None</span>
                        </div>
                    </div>

                    <!-- 4. Approval & Audit Trail -->
                    <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-750/40 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">Submitted by HR</span>
                                <span id="modal-submitted-info" class="font-medium text-gray-800 dark:text-gray-200">katrina &bull; 17 Oct 2026</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase block">Approval Status</span>
                                <span id="modal-approval-info" class="font-medium text-gray-800 dark:text-gray-200">Pending Review</span>
                            </div>
                        </div>
                        <div id="modal-rejection-box" class="mt-2.5 p-2.5 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 hidden">
                            <span class="font-bold text-red-800 dark:text-red-200">Rejection Reason:</span>
                            <span id="modal-rejection-text" class="text-red-700 dark:text-red-300 ml-1"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <button type="button" onclick="closeSalaryDetailsModal()"
                        class="px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">
                        Close
                    </button>

                    <!-- Direct Approve/Reject buttons inside modal -->
                    <div id="modal-actions-container" class="flex items-center gap-2 hidden">
                        <form id="modal-approve-form" method="POST" action="">
                            @csrf
                            <button type="submit" onclick="return confirm('Approve this salary structure?')"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve Salary
                            </button>
                        </form>

                        <form id="modal-reject-form" method="POST" action=""
                            onsubmit="let r = prompt('Reason for rejecting this salary structure:'); if (r === null) return false; this.querySelector('input[name=rejection_reason]').value = r; return true;">
                            @csrf
                            <input type="hidden" name="rejection_reason" value="">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject Salary
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatINR(val) {
            var num = parseFloat(val) || 0;
            return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function openSalaryDetailsModal(data) {
            if (!data) return;

            // Header
            document.getElementById('modal-avatar').textContent = (data.employee_name || 'E').substring(0, 1).toUpperCase();
            document.getElementById('modal-employee-name').textContent = data.employee_name || 'Employee';
            document.getElementById('modal-emp-id').textContent = data.emp_id || '';
            document.getElementById('modal-designation').textContent = data.designation || '';
            document.getElementById('modal-department').textContent = data.department || '';

            // Status Badge
            var badge = document.getElementById('modal-status-badge');
            if (data.approval_status === 'approved') {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
                badge.textContent = '✓ Approved';
            } else if (data.approval_status === 'pending_approval') {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 animate-pulse';
                badge.textContent = '⏳ Pending Admin Approval';
            } else if (data.approval_status === 'rejected') {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
                badge.textContent = '✕ Rejected';
            } else {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-800';
                badge.textContent = data.approval_status || 'Configured';
            }

            // Top Metrics
            document.getElementById('modal-gross').textContent = formatINR(data.gross_salary);
            document.getElementById('modal-gross-total').textContent = formatINR(data.gross_salary);
            document.getElementById('modal-annual').textContent = '₹' + Math.round((parseFloat(data.gross_salary) || 0) * 12).toLocaleString('en-IN');
            document.getElementById('modal-deductions').textContent = '-' + formatINR(data.total_deductions);
            document.getElementById('modal-deductions-total').textContent = '-' + formatINR(data.total_deductions);
            document.getElementById('modal-net').textContent = formatINR(data.net_salary);

            // Earnings components
            document.getElementById('modal-basic').textContent = formatINR(data.basic_salary);
            document.getElementById('modal-hra').textContent = formatINR(data.hra);
            document.getElementById('modal-conveyance').textContent = formatINR(data.conveyance_allowance);
            document.getElementById('modal-special').textContent = formatINR(data.special_allowance);
            document.getElementById('modal-other-allowances').textContent = formatINR(data.other_allowances);

            // Deductions components (PF, PT, TDS, etc.)
            document.getElementById('modal-pf').textContent = formatINR(data.provident_fund);
            document.getElementById('modal-pt').textContent = formatINR(data.professional_tax);
            document.getElementById('modal-tds').textContent = formatINR(data.tds);
            document.getElementById('modal-other-deductions').textContent = formatINR(data.other_deductions);

            // Banking & Disbursement Details
            var methodLabel = (data.payment_method || 'bank_transfer').replace(/_/g, ' ').toUpperCase();
            document.getElementById('modal-payment-method').textContent = methodLabel;
            document.getElementById('modal-bank-name').textContent = data.bank_name || 'Not Provided';
            document.getElementById('modal-account-number').textContent = data.account_number || 'Not Provided';
            var ifscPan = (data.ifsc_code ? 'IFSC: ' + data.ifsc_code : '') + ((data.ifsc_code && data.pan_number) ? ' | ' : '') + (data.pan_number ? 'PAN: ' + data.pan_number : '');
            document.getElementById('modal-ifsc-pan').textContent = ifscPan || 'Not Provided';
            document.getElementById('modal-notes').textContent = data.notes || 'None';

            // Audit Trail
            document.getElementById('modal-submitted-info').textContent = (data.submitted_by || 'HR Department') + (data.submitted_at ? ' • ' + data.submitted_at : '');
            if (data.approval_status === 'approved') {
                document.getElementById('modal-approval-info').textContent = 'Approved' + (data.approved_by ? ' by ' + data.approved_by : '') + (data.approved_at ? ' • ' + data.approved_at : '');
            } else if (data.approval_status === 'pending_approval') {
                document.getElementById('modal-approval-info').textContent = 'Awaiting Company Owner (Admin) Review';
            } else if (data.approval_status === 'rejected') {
                document.getElementById('modal-approval-info').textContent = 'Rejected by Admin';
            } else {
                document.getElementById('modal-approval-info').textContent = 'Active Structure';
            }

            // Rejection reason box
            var rejBox = document.getElementById('modal-rejection-box');
            if (data.approval_status === 'rejected' && data.rejection_reason) {
                document.getElementById('modal-rejection-text').textContent = data.rejection_reason;
                rejBox.classList.remove('hidden');
            } else {
                rejBox.classList.add('hidden');
            }

            // Direct actions container in modal footer
            var actionsContainer = document.getElementById('modal-actions-container');
            if (data.approval_status === 'pending_approval' && data.approve_url && data.reject_url) {
                document.getElementById('modal-approve-form').action = data.approve_url;
                document.getElementById('modal-reject-form').action = data.reject_url;
                actionsContainer.classList.remove('hidden');
            } else {
                actionsContainer.classList.add('hidden');
            }

            // Display modal
            document.getElementById('salaryDetailsModal').classList.remove('hidden');
        }

        function closeSalaryDetailsModal() {
            document.getElementById('salaryDetailsModal').classList.add('hidden');
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSalaryDetailsModal();
            }
        });
    </script>
@endsection
