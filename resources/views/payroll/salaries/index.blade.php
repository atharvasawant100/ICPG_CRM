@extends('layouts.app')

@section('title', 'Employee Salary Configurations')
@section('page-title', 'Employee Salary Configurations')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Employee Salary Structures</h2>
        </div>

        <div>
            @can('payroll.create')
                <a href="{{ route('payrolls.create') }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Generate Payroll
                </a>
            @endcan
        </div>
    </div>

    <!-- Overview Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Employees</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $totalUsers }}</div>
            <div class="text-xs text-gray-500 mt-1">Active team roster</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Approved Salaries</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $approvedCount }}</div>
            <div class="text-xs text-gray-500 mt-1">Active for payroll generation</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-amber-200 dark:border-amber-700/60 bg-amber-50/30 dark:bg-amber-900/10">
            <div class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-1">Pending Admin Approval</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $pendingApprovalCount }}</div>
            <div class="text-xs text-amber-600 dark:text-amber-400/80 mt-1">Awaiting owner review</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pending Setup</div>
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ max(0, $totalUsers - $configuredCount) }}</div>
            <div class="text-xs text-gray-500 mt-1">Require salary structure setup</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 mb-5 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('employee-salaries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            <div class="lg:col-span-4">
                <label for="search" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Name, email, EMP ID..."
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
            </div>

            <div class="lg:col-span-3">
                <label for="department" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Department</label>
                <select name="department" id="department"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                            {{ ucfirst($dept) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="approval_status" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Approval Status</label>
                <select name="approval_status" id="approval_status"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    <option value="">All statuses</option>
                    <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending_approval" {{ request('approval_status') === 'pending_approval' ? 'selected' : '' }}>Pending Admin Approval</option>
                    <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 flex items-end gap-2">
                <button type="submit"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-xs shadow transition-all duration-200 text-center whitespace-nowrap">
                    Filter Employees
                </button>
                <a href="{{ route('employee-salaries.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2 px-3 rounded-lg text-xs transition-all duration-200 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EMP ID</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Basic Pay</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">HRA</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Allowances</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deductions</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Net Monthly</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($users as $user)
                        @php
                            $salary = $user->salary;
                            $hasSalary = (bool) $salary;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                                @if ($user->emp_id)
                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        {{ $user->emp_id }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-sm text-gray-800 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->department ? ucfirst($user->department) : 'No dept' }}
                                    @if ($user->designation)
                                        &middot; {{ \App\Rbac\Designation::label($user->designation) }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300 font-medium">
                                {{ $hasSalary ? '₹' . number_format($salary->basic_salary, 2) : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $hasSalary ? '₹' . number_format($salary->hra, 2) : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $hasSalary ? '₹' . number_format((float) $salary->conveyance_allowance + (float) $salary->special_allowance + (float) $salary->other_allowances, 2) : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-red-600 dark:text-red-400">
                                {{ $hasSalary ? '-₹' . number_format($salary->total_deductions, 2) : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-bold text-gray-900 dark:text-white">
                                {{ $hasSalary ? '₹' . number_format($salary->net_salary, 2) : 'Not configured' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                @if ($hasSalary)
                                    @if ($salary->isApproved())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            ✓ Approved
                                        </span>
                                    @elseif ($salary->isPendingApproval())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" title="Submitted for Admin approval">
                                            ⏳ Pending Approval
                                        </span>
                                    @elseif ($salary->isRejected())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300" title="{{ $salary->rejection_reason }}">
                                            ✕ Rejected
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Not Set Up
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                @can('payroll.edit')
                                    <a href="{{ route('employee-salaries.edit', $user) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $hasSalary ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60' : 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60' }} transition-colors">
                                        {{ $hasSalary ? 'Edit Structure' : 'Set Up Salary' }}
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500">
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($users as $user)
                @php
                    $salary = $user->salary;
                    $hasSalary = (bool) $salary;
                @endphp
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-white flex items-center gap-1.5">
                                {{ $user->name }}
                                @if ($user->emp_id)
                                    <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gray-100 dark:bg-gray-700">{{ $user->emp_id }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $user->department ? ucfirst($user->department) : 'No dept' }}</div>
                        </div>

                        <div>
                            @if ($hasSalary)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Configured</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </div>
                    </div>

                    @if ($hasSalary)
                        <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 dark:bg-gray-700/30 p-2 rounded-lg">
                            <div>Gross: <span class="font-semibold">₹{{ number_format($salary->gross_salary, 2) }}</span></div>
                            <div>Net: <span class="font-bold text-green-600">₹{{ number_format($salary->net_salary, 2) }}</span></div>
                        </div>
                    @endif

                    @can('payroll.edit')
                        <div class="pt-1">
                            <a href="{{ route('employee-salaries.edit', $user) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $hasSalary ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60' : 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60' }} transition-colors">
                                {{ $hasSalary ? 'Edit Structure' : 'Set Up Salary' }}
                            </a>
                        </div>
                    @endcan
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-500">No employees found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
