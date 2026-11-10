@extends('layouts.app')

@section('title', 'Generate Monthly Payroll')
@section('page-title', 'Generate Monthly Payroll')

@section('content')
<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80 gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('payrolls.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Payroll</a>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Generate Monthly Payroll</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Generate Monthly Payroll
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Calculate and prepare salary disbursements for eligible staff for {{ date('F', mktime(0, 0, 0, $selectedMonth, 1)) }} {{ $selectedYear }}.
                </p>
            </div>
            <div>
                <a href="{{ route('payrolls.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Payroll List</span>
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were some problems with your submission</h3>
                        <ul class="mt-2 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Period Selection Bar -->
        <div class="bg-gray-50/70 dark:bg-gray-700/40 rounded-xl p-4 sm:p-5 mb-6 border border-gray-200/80 dark:border-gray-600">
            <form method="GET" action="{{ route('payrolls.create') }}" id="period-form" class="flex flex-wrap items-end gap-5">
                <div>
                    <label for="period_month" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1.5">
                        Payroll Month <span class="text-red-500">*</span>
                    </label>
                    <select name="month" id="period_month" onchange="document.getElementById('period-form').submit()"
                        class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-xl py-2 px-3.5 text-gray-800 dark:text-gray-200 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="period_year" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1.5">
                        Payroll Year <span class="text-red-500">*</span>
                    </label>
                    <select name="year" id="period_year" onchange="document.getElementById('period-form').submit()"
                        class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-xl py-2 px-3.5 text-gray-800 dark:text-gray-200 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 pb-2">
                    Generating payroll period: <span class="font-bold text-gray-800 dark:text-white">{{ date('F', mktime(0, 0, 0, $selectedMonth, 1)) }} {{ $selectedYear }}</span>
                </div>
            </form>
        </div>

        <!-- Generation Form -->
        <form method="POST" action="{{ route('payrolls.store') }}">
            @csrf
            <input type="hidden" name="payroll_year" value="{{ $selectedYear }}">
            <input type="hidden" name="payroll_month" value="{{ $selectedMonth }}">

            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 mb-8">
                <div class="p-4 sm:p-5 bg-gray-50/70 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-semibold text-gray-800 dark:text-white">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span>Select All Eligible Employees</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-2">
                            <label for="payment_status" class="font-semibold text-gray-700 dark:text-gray-300">Initial Status:</label>
                            <select name="payment_status" id="payment_status"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-3 text-xs dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="processed">Processed</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>

                        <div id="payment-date-wrap" class="hidden flex items-center gap-2">
                            <label for="payment_date" class="font-semibold text-gray-700 dark:text-gray-300">Date:</label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg py-1 px-2.5 text-xs dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">Select</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Basic Pay</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">HRA + Allowances</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deductions</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bonus (₹)</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Overtime (₹)</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Est. Net Pay</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($employees as $emp)
                                @php
                                    $alreadyGenerated = isset($generatedUserIds[$emp->id]);
                                    $salary = $emp->salary;
                                    $isApproved = $salary && $salary->isApproved();
                                    $isPendingApproval = $salary && $salary->isPendingApproval();
                                    $isRejected = $salary && $salary->isRejected();
                                    $canSelect = !$alreadyGenerated && $isApproved;

                                    $basic = $salary ? (float) $salary->basic_salary : 0;
                                    $allowances = $salary ? ((float) $salary->hra + (float) $salary->conveyance_allowance + (float) $salary->special_allowance + (float) $salary->other_allowances) : 0;
                                    $deductions = $salary ? (float) $salary->total_deductions : 0;
                                    $net = $salary ? (float) $salary->net_salary : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors {{ ($alreadyGenerated || !$isApproved) ? 'opacity-70 bg-gray-50/50 dark:bg-gray-800/50' : '' }}">
                                    <td class="px-5 py-3.5">
                                        <input type="checkbox" name="user_ids[]" value="{{ $emp->id }}"
                                            {{ $canSelect ? 'checked' : 'disabled' }}
                                            class="emp-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                            {{ $emp->name }}
                                            @if ($emp->emp_id)
                                                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ $emp->emp_id }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $emp->department ? ucfirst($emp->department) : 'No dept' }}
                                            @if ($emp->designation)
                                                &middot; {{ \App\Rbac\Designation::label($emp->designation) }}
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-gray-700 dark:text-gray-300">
                                        ₹{{ number_format($basic, 2) }}
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-gray-700 dark:text-gray-300">
                                        ₹{{ number_format($allowances, 2) }}
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-red-600 dark:text-red-400">
                                        -₹{{ number_format($deductions, 2) }}
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <input type="number" step="0.01" min="0" name="bonus[{{ $emp->id }}]" value="0"
                                            {{ $canSelect ? '' : 'disabled' }}
                                            class="w-24 shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1 px-2.5 text-xs dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <input type="number" step="0.01" min="0" name="overtime_pay[{{ $emp->id }}]" value="0"
                                            {{ $canSelect ? '' : 'disabled' }}
                                            class="w-24 shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1 px-2.5 text-xs dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-bold text-gray-900 dark:text-white">
                                        ₹{{ number_format($net, 2) }}
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                        @if ($alreadyGenerated)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                ✓ Already Generated
                                            </span>
                                        @elseif ($isPendingApproval)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" title="Salary structure is awaiting Admin approval">
                                                ⏳ Pending Approval
                                            </span>
                                        @elseif ($isRejected)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300" title="Salary structure was rejected by Admin">
                                                ✕ Rejected
                                            </span>
                                        @elseif ($isApproved)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                ✓ Ready
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                Unconfigured (₹0)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No active employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('payrolls.index') }}"
                    class="order-2 sm:order-1 text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="order-1 sm:order-2 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Generate Payroll for Selected</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        var selectAll = document.getElementById('select-all');
        var checkboxes = document.querySelectorAll('.emp-checkbox:not(:disabled)');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
            });
        }

        var statusSelect = document.getElementById('payment_status');
        var dateWrap = document.getElementById('payment-date-wrap');

        if (statusSelect && dateWrap) {
            statusSelect.addEventListener('change', function() {
                if (statusSelect.value === 'paid') {
                    dateWrap.classList.remove('hidden');
                } else {
                    dateWrap.classList.add('hidden');
                }
            });
        }
    })();
</script>
@endsection
