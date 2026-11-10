@extends('layouts.app')

@section('title', 'Edit Payroll - ' . $payroll->payroll_code)
@section('page-title', 'Edit Payroll: ' . $payroll->payroll_code)

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('payrolls.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Payroll</a>
                    <span>/</span>
                    <a href="{{ route('payrolls.show', $payroll) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $payroll->payroll_code }}</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Payroll Record</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $payroll->user->name }} &middot; {{ $payroll->period_label }} &middot; <span class="font-mono">{{ $payroll->payroll_code }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('payrolls.show', $payroll) }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Payroll
                </a>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
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

        <form method="POST" action="{{ route('payrolls.update', $payroll) }}" id="edit-payroll-form" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Earnings Section -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                    Earnings Breakdown
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label for="basic_salary" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Basic Salary (₹) <span class="text-red-500">*</span></label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="basic_salary" id="basic_salary" required
                                value="{{ old('basic_salary', $payroll->basic_salary) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold">
                        </div>
                    </div>

                    <div>
                        <label for="hra" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">HRA (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="hra" id="hra"
                                value="{{ old('hra', $payroll->hra) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="allowances" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Other Allowances (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="allowances" id="allowances"
                                value="{{ old('allowances', $payroll->allowances) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="bonus" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Bonus / Incentives (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="bonus" id="bonus"
                                value="{{ old('bonus', $payroll->bonus) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="overtime_pay" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Overtime Pay (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="overtime_pay" id="overtime_pay"
                                value="{{ old('overtime_pay', $payroll->overtime_pay) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions Section -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                    Deductions Breakdown
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <label for="provident_fund" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">PF (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="provident_fund" id="provident_fund"
                                value="{{ old('provident_fund', $payroll->provident_fund) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-red-600 dark:text-red-400 font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="professional_tax" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">PT (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="professional_tax" id="professional_tax"
                                value="{{ old('professional_tax', $payroll->professional_tax) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-red-600 dark:text-red-400 font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="tds" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">TDS / Income Tax (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="tds" id="tds"
                                value="{{ old('tds', $payroll->tds) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-red-600 dark:text-red-400 font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="other_deductions" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Other Deductions (₹)</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <span class="text-sm font-bold">₹</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="other_deductions" id="other_deductions"
                                value="{{ old('other_deductions', $payroll->other_deductions) }}"
                                class="calc-input w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-red-600 dark:text-red-400 font-medium">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculated Preview Banner -->
            <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-2xs">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Gross Salary</div>
                    <div id="preview-gross" class="text-xl font-extrabold text-gray-900 dark:text-white">₹0.00</div>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-2xs">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Total Deductions</div>
                    <div id="preview-deductions" class="text-xl font-extrabold text-rose-600 dark:text-rose-400">₹0.00</div>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-200 dark:border-blue-800 shadow-2xs">
                    <div class="text-xs text-blue-700 dark:text-blue-300 uppercase font-bold tracking-wider mb-1">Net Payable Amount</div>
                    <div id="preview-net" class="text-xl font-black text-blue-600 dark:text-blue-400">₹0.00</div>
                </div>
            </div>

            <!-- Disbursement Details -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                    Disbursement & Payment Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <label for="payment_status" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="payment_status" id="payment_status" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                            <option value="pending" {{ old('payment_status', $payroll->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processed" {{ old('payment_status', $payroll->payment_status) === 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="paid" {{ old('payment_status', $payroll->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date"
                            value="{{ old('payment_date', $payroll->payment_date?->format('Y-m-d')) }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Payment Method</label>
                        <input type="text" name="payment_method" id="payment_method" placeholder="e.g. Bank Transfer, UPI, Cheque"
                            value="{{ old('payment_method', $payroll->payment_method) }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label for="transaction_reference" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Transaction Ref No</label>
                        <input type="text" name="transaction_reference" id="transaction_reference"
                            value="{{ old('transaction_reference', $payroll->transaction_reference) }}"
                            placeholder="UTR / Cheque Ref Number"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Notes / Remarks</label>
                <div class="relative rounded-xl shadow-sm">
                    <textarea name="notes" id="notes" rows="2"
                        placeholder="Internal notes or payroll adjustment explanation..."
                        class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('notes', $payroll->notes) }}</textarea>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('payrolls.show', $payroll) }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Save Changes
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    (function() {
        function updatePreview() {
            var basic = parseFloat(document.getElementById('basic_salary').value) || 0;
            var hra = parseFloat(document.getElementById('hra').value) || 0;
            var allowances = parseFloat(document.getElementById('allowances').value) || 0;
            var bonus = parseFloat(document.getElementById('bonus').value) || 0;
            var overtime = parseFloat(document.getElementById('overtime_pay').value) || 0;

            var pf = parseFloat(document.getElementById('provident_fund').value) || 0;
            var pt = parseFloat(document.getElementById('professional_tax').value) || 0;
            var tds = parseFloat(document.getElementById('tds').value) || 0;
            var otherDed = parseFloat(document.getElementById('other_deductions').value) || 0;

            var gross = basic + hra + allowances + bonus + overtime;
            var ded = pf + pt + tds + otherDed;
            var net = Math.max(0, gross - ded);

            document.getElementById('preview-gross').textContent = '₹' + gross.toFixed(2);
            document.getElementById('preview-deductions').textContent = '-₹' + ded.toFixed(2);
            document.getElementById('preview-net').textContent = '₹' + net.toFixed(2);
        }

        document.querySelectorAll('.calc-input').forEach(function(inp) {
            inp.addEventListener('input', updatePreview);
        });

        updatePreview();
    })();
</script>
@endsection
