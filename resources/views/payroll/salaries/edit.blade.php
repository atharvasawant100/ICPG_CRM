@extends('layouts.app')

@section('title', 'Configure Salary - ' . $user->name)
@section('page-title', 'Configure Salary: ' . $user->name)

@section('content')
<div class="w-full">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('employee-salaries.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Payroll</a>
                    <span>/</span>
                    <a href="{{ route('employee-salaries.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Salary Settings</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Configure</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Configure Salary Structure</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $user->name }}
                    @if ($user->emp_id)
                        &middot; <span class="font-mono font-semibold">{{ $user->emp_id }}</span>
                    @endif
                    &middot; {{ $user->department ? ucfirst($user->department) : 'No department' }}
                    @if ($user->designation)
                        &middot; {{ \App\Rbac\Designation::label($user->designation) }}
                    @endif
                </p>
            </div>

        </div>

        <!-- Approval Status Notice -->
        @if (!auth()->user()->isAdmin())
            @if ($salary->isPendingApproval())
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h3 class="text-sm font-bold text-amber-800 dark:text-amber-200">Awaiting Admin Approval</h3>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                This salary structure is currently pending review by the Company Owner (Admin). Updating it will submit the new structure for approval.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif ($salary->isRejected())
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Salary Structure Rejected</h3>
                            <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                                Reason: <span class="font-semibold">{{ $salary->rejection_reason ?: 'Rejected by Admin. Please revise and resubmit.' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h3 class="text-sm font-bold text-blue-800 dark:text-blue-200">Admin Approval Required</h3>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                                As an HR Executive, saving this salary structure will submit it to the Company Owner (Admin) for approval before it can be used to generate monthly payroll.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('employee-salaries.update', $user) }}" class="space-y-7">
            @csrf
            @method('PUT')

            <!-- 1. Monthly Earnings Components -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100 dark:border-gray-700/80">
                    1. Monthly Earnings Components
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Basic Salary -->
                    <div>
                        <label for="basic_salary" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Basic Salary (₹) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 text-sm font-semibold">₹</div>
                            <input type="number" step="0.01" min="0" name="basic_salary" id="basic_salary" required
                                value="{{ old('basic_salary', $salary->basic_salary) }}"
                                placeholder="e.g. 50000.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('basic_salary') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Core base salary</p>
                    </div>

                    <!-- HRA -->
                    <div>
                        <label for="hra" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            House Rent Allowance (HRA) (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="hra" id="hra"
                                value="{{ old('hra', $salary->hra) }}"
                                placeholder="e.g. 20000.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('hra') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Usually 40-50% of basic</p>
                    </div>

                    <!-- Conveyance Allowance -->
                    <div>
                        <label for="conveyance_allowance" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Conveyance Allowance (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="conveyance_allowance" id="conveyance_allowance"
                                value="{{ old('conveyance_allowance', $salary->conveyance_allowance) }}"
                                placeholder="e.g. 1600.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('conveyance_allowance') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Travel &amp; transport</p>
                    </div>

                    <!-- Special Allowance -->
                    <div>
                        <label for="special_allowance" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Special Allowance (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0a1.724 1.724 0 002.573 1.066c.898-.307 1.68.474 1.373 1.373a1.724 1.724 0 001.065 2.573c.921.3.921 1.603 0 1.902a1.724 1.724 0 00-1.066 2.573c.307.898-.474 1.68-1.373 1.373a1.724 1.724 0 00-2.573 1.066c-.3.921-1.603.921-1.902 0a1.724 1.724 0 00-2.573-1.066c-.898.307-1.68-.474-1.373-1.373a1.724 1.724 0 00-1.066-2.573c-.921-.3-.921-1.603 0-1.902a1.724 1.724 0 001.066-2.573c-.307-.898.474-1.68 1.373-1.373a1.724 1.724 0 002.573-1.066z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="special_allowance" id="special_allowance"
                                value="{{ old('special_allowance', $salary->special_allowance) }}"
                                placeholder="e.g. 5000.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('special_allowance') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Any other fixed monthly payout</p>
                    </div>

                    <!-- Other Allowances -->
                    <div>
                        <label for="other_allowances" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Other Allowances (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="other_allowances" id="other_allowances"
                                value="{{ old('other_allowances', $salary->other_allowances) }}"
                                placeholder="e.g. 0.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('other_allowances') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Catch-all for anything not listed above</p>
                    </div>

                </div>
            </div>

            <!-- 2. Monthly Deductions -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100 dark:border-gray-700/80">
                    2. Monthly Deductions
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Provident Fund -->
                    <div>
                        <label for="provident_fund" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Provident Fund (PF) (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="provident_fund" id="provident_fund"
                                value="{{ old('provident_fund', $salary->provident_fund) }}"
                                placeholder="e.g. 1800.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('provident_fund') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Employee PF contribution</p>
                    </div>

                    <!-- Professional Tax -->
                    <div>
                        <label for="professional_tax" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Professional Tax (PT) (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="professional_tax" id="professional_tax"
                                value="{{ old('professional_tax', $salary->professional_tax) }}"
                                placeholder="e.g. 200.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('professional_tax') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">State-wise monthly slab</p>
                    </div>

                    <!-- TDS -->
                    <div>
                        <label for="tds" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            TDS / Income Tax (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="tds" id="tds"
                                value="{{ old('tds', $salary->tds) }}"
                                placeholder="e.g. 0.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('tds') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Withheld against annual income</p>
                    </div>

                    <!-- Other Deductions -->
                    <div>
                        <label for="other_deductions" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Other Deductions (₹)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12.75v-1.5m-6 1.5v-1.5m-6 1.5v-1.5m12 9.75h-12A2.25 2.25 0 013 19.5V6.75A2.25 2.25 0 015.25 4.5h12A2.25 2.25 0 0119.5 6.75v12.75a2.25 2.25 0 01-2.25 2.25z"/></svg>
                            </div>
                            <input type="number" step="0.01" min="0" name="other_deductions" id="other_deductions"
                                value="{{ old('other_deductions', $salary->other_deductions) }}"
                                placeholder="e.g. 0.00"
                                class="salary-calc w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('other_deductions') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Advance recovery or loan EMI</p>
                    </div>

                </div>
            </div>

            <!-- Live Calculation Preview -->
            <div class="bg-gray-50/75 dark:bg-gray-750 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Gross Monthly</div>
                    <div id="calc-gross" class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">₹0.00</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Monthly Deductions</div>
                    <div id="calc-deductions" class="text-2xl font-extrabold text-red-600 mt-1">-₹0.00</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net In-Hand Salary</div>
                    <div id="calc-net" class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">₹0.00</div>
                </div>
            </div>

            <!-- 3. Disbursement & Banking Information -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100 dark:border-gray-700/80">
                    3. Disbursement &amp; Banking Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Disbursement Mode -->
                    <div>
                        <label for="payment_method" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Disbursement Mode <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <select name="payment_method" id="payment_method" required
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('payment_method') border-red-500 @enderror">
                                <option value="bank_transfer" {{ old('payment_method', $salary->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer (NEFT/RTGS/IMPS)</option>
                                <option value="upi" {{ old('payment_method', $salary->payment_method) === 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="cheque" {{ old('payment_method', $salary->payment_method) === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="cash" {{ old('payment_method', $salary->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">How the salary reaches the employee each month</p>
                    </div>

                    <!-- Bank Name -->
                    <div>
                        <label for="bank_name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Bank Name
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <input type="text" name="bank_name" id="bank_name" placeholder="e.g. HDFC Bank, SBI, ICICI"
                                value="{{ old('bank_name', $salary->bank_name) }}"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('bank_name') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Registered bank for NEFT/RTGS/IMPS payouts</p>
                    </div>

                    <!-- Account Number -->
                    <div>
                        <label for="account_number" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Account Number
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <input type="text" name="account_number" id="account_number" placeholder="e.g. 50100234567890"
                                value="{{ old('account_number', $salary->account_number) }}"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('account_number') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Salary is credited to this account</p>
                    </div>

                    <!-- IFSC Code -->
                    <div>
                        <label for="ifsc_code" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            IFSC Code
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14l3 3 5-5m-5 5V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h2"/></svg>
                            </div>
                            <input type="text" name="ifsc_code" id="ifsc_code" placeholder="e.g. HDFC0001234"
                                value="{{ old('ifsc_code', $salary->ifsc_code) }}"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('ifsc_code') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Required to route the bank transfer</p>
                    </div>

                    <!-- PAN Number -->
                    <div>
                        <label for="pan_number" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            PAN Number
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" name="pan_number" id="pan_number" placeholder="e.g. ABCDE1234F"
                                value="{{ old('pan_number', $salary->pan_number) }}"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('pan_number') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Used for TDS reporting</p>
                    </div>

                </div>
            </div>

            <!-- Notes / Policy Remarks -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Notes / Policy Remarks
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <textarea name="notes" id="notes" rows="3" placeholder="e.g. Any special compensation clauses, probation terms or notes..."
                        class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('notes') border-red-500 @enderror">{{ old('notes', $salary->notes) }}</textarea>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Internal context shown to payroll staff only</p>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('employee-salaries.index') }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        {{ auth()->user()->isAdmin() ? 'Save & Approve Salary' : 'Submit for Admin Approval' }}
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>

<script>
    (function() {
        function updateSalaryCalc() {
            var basic = parseFloat(document.getElementById('basic_salary').value) || 0;
            var hra = parseFloat(document.getElementById('hra').value) || 0;
            var conveyance = parseFloat(document.getElementById('conveyance_allowance').value) || 0;
            var special = parseFloat(document.getElementById('special_allowance').value) || 0;
            var otherAllow = parseFloat(document.getElementById('other_allowances').value) || 0;

            var pf = parseFloat(document.getElementById('provident_fund').value) || 0;
            var pt = parseFloat(document.getElementById('professional_tax').value) || 0;
            var tds = parseFloat(document.getElementById('tds').value) || 0;
            var otherDed = parseFloat(document.getElementById('other_deductions').value) || 0;

            var gross = basic + hra + conveyance + special + otherAllow;
            var deductions = pf + pt + tds + otherDed;
            var net = Math.max(0, gross - deductions);

            document.getElementById('calc-gross').textContent = '₹' + gross.toFixed(2);
            document.getElementById('calc-deductions').textContent = '-₹' + deductions.toFixed(2);
            document.getElementById('calc-net').textContent = '₹' + net.toFixed(2);
        }

        document.querySelectorAll('.salary-calc').forEach(function(el) {
            el.addEventListener('input', updateSalaryCalc);
        });

        updateSalaryCalc();
    })();
</script>
@endsection
