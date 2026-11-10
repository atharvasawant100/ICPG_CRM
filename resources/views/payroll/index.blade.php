@extends('layouts.app')

@section('title', 'Payroll')
@section('page-title', 'Payroll & Payslips')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Payroll & Payslips</h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @can('payroll.create')
                <a href="{{ route('payrolls.create') }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Generate Payroll
                </a>
            @endcan

            @can('payroll.view')
                <a href="{{ route('employee-salaries.index') }}"
                    class="inline-flex items-center bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    Salary Settings
                </a>
            @endcan
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Net Disbursed</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">
                ₹{{ number_format($summary['total_net'], 2) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Filtered cycle total</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Gross Earnings</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                ₹{{ number_format($summary['total_gross'], 2) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Basic + Allowances + Bonuses</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Deductions</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                ₹{{ number_format($summary['total_deductions'], 2) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">PF, PT, Taxes, Deductions</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Payment Status</div>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                    {{ $summary['paid_count'] }} Paid
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    {{ $summary['pending_count'] }} Pending
                </span>
            </div>
            <div class="text-xs text-gray-500 mt-2">{{ $payrolls->total() }} total records</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 mb-5 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('payrolls.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label for="search" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Employee name, email, EMP ID, code..."
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>

            <div>
                <label for="month" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Month</label>
                <select name="month" id="month"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <option value="">All months</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="year" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Year</label>
                <select name="year" id="year"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Status</label>
                <select name="status" id="status"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <option value="">All statuses</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processed" {{ $selectedStatus === 'processed' ? 'selected' : '' }}>Processed</option>
                    <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <div>
                <label for="department" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Department</label>
                <select name="department" id="department"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>
                            {{ ucfirst($dept) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-6 flex justify-end gap-2 pt-1">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-xs shadow transition-all duration-200">
                    Filter Records
                </button>
                <a href="{{ route('payrolls.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2 px-4 rounded-lg text-xs transition-all duration-200">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Payroll Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gross</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deductions</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Net Pay</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment Date</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($payrolls as $payroll)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                                {{ $payroll->payroll_code }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-sm text-gray-800 dark:text-white flex items-center gap-1.5">
                                    {{ $payroll->user->name }}
                                    @if ($payroll->user->emp_id)
                                        <span class="px-1.5 py-0.5 rounded text-[11px] font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ $payroll->user->emp_id }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $payroll->user->department ? ucfirst($payroll->user->department) : 'No dept' }}
                                    @if ($payroll->user->designation)
                                        &middot; {{ \App\Rbac\Designation::label($payroll->user->designation) }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300 font-medium">
                                {{ $payroll->period_label }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 font-medium">
                                ₹{{ number_format($payroll->gross_salary, 2) }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-red-600 dark:text-red-400 font-medium">
                                -₹{{ number_format($payroll->total_deductions, 2) }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($payroll->net_salary, 2) }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($payroll->payment_status === 'paid')
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        Paid
                                    </span>
                                @elseif ($payroll->payment_status === 'processed')
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        Processed
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('payrolls.show', $payroll) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                        Payslip
                                    </a>

                                    @can('payroll.edit')
                                        @if ($payroll->payment_status !== 'paid')
                                            <form action="{{ route('payrolls.pay', $payroll) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Mark payroll {{ $payroll->payroll_code }} as Paid?\n\nThe employee will be emailed their payslip.');">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">
                                                    Mark Paid
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('payrolls.edit', $payroll) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                            Edit
                                        </a>
                                    @endcan

                                    @can('payroll.delete')
                                        <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete payroll {{ $payroll->payroll_code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No payroll records found for this criteria.
                                @can('payroll.create')
                                    <div class="mt-2">
                                        <a href="{{ route('payrolls.create') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                                            Generate Payroll for this period &rarr;
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($payrolls as $payroll)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-white">
                                {{ $payroll->user->name }}
                            </div>
                            <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                {{ $payroll->payroll_code }} &middot; {{ $payroll->period_label }}
                            </div>
                        </div>

                        <div>
                            @if ($payroll->payment_status === 'paid')
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-xs bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-lg">
                        <div>
                            <span class="text-gray-500 block">Gross</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">₹{{ number_format($payroll->gross_salary, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Deductions</span>
                            <span class="font-medium text-red-600">₹{{ number_format($payroll->total_deductions, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Net Salary</span>
                            <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($payroll->net_salary, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 pt-1 flex-wrap">
                        <a href="{{ route('payrolls.show', $payroll) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 transition-colors">
                            Payslip
                        </a>
                        @can('payroll.edit')
                            @if ($payroll->payment_status !== 'paid')
                                <form action="{{ route('payrolls.pay', $payroll) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                        Mark Paid
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('payrolls.edit', $payroll) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 transition-colors">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                    No payroll records found.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $payrolls->links() }}
    </div>
@endsection
