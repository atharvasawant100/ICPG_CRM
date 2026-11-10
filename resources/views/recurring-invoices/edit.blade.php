@extends('layouts.app')

@section('title', 'Edit Recurring Invoice Template')
@section('page-title', 'Edit Recurring Invoice Template')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('recurring-invoices.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Recurring Invoices</a>
                    <span>/</span>
                    <a href="{{ route('recurring-invoices.show', $template) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $template->name }}</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Template: {{ $template->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Modify recurring intervals, client details, payment terms, and invoice items.
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('recurring-invoices.show', $template) }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Template
                </a>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('recurring-invoices.update', $template) }}" id="templateForm" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Template Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Template Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('name') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Descriptive label for this schedule</p>
                </div>

                <!-- Customer Selection -->
                <div>
                    <label for="customer_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <select name="customer_id" id="customer_id" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('customer_id') border-red-500 @enderror">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $template->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Client receiving invoices</p>
                </div>

                <!-- Frequency -->
                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Frequency <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <select name="frequency" id="frequency" required onchange="toggleCustomInterval()"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('frequency') border-red-500 @enderror">
                            <option value="weekly" {{ old('frequency', $template->frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('frequency', $template->frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('frequency', $template->frequency) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency', $template->frequency) === 'custom' ? 'selected' : '' }}>Custom Interval</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Recurrence rhythm</p>
                </div>

                <!-- Custom Interval Days -->
                <div id="custom_interval_container" style="display: {{ old('frequency', $template->frequency) === 'custom' ? 'block' : 'none' }};">
                    <label for="custom_interval_days" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Interval (Days) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input type="number" name="custom_interval_days" id="custom_interval_days"
                            value="{{ old('custom_interval_days', $template->custom_interval_days) }}" min="1"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Custom days interval</p>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="start_date" id="start_date"
                            value="{{ old('start_date', $template->start_date ? $template->start_date->format('Y-m-d') : date('Y-m-d')) }}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Template effective start date</p>
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        End Date <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ old('end_date', $template->end_date ? $template->end_date->format('Y-m-d') : '') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Leave blank for indefinite recurrence</p>
                </div>

                <!-- Payment Terms -->
                <div class="md:col-span-2">
                    <label for="payment_terms" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Payment Terms
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', $template->payment_terms) }}"
                            placeholder="e.g. Net 30, Due on receipt"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Notes</label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="notes" id="notes" rows="3" placeholder="Additional notes or payment instructions..."
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('notes', $template->notes) }}</textarea>
                    </div>
                </div>

            </div>

            <!-- Template Items -->
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Invoice Items</h3>
                    <button type="button" id="addItem"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item
                    </button>
                </div>

                <div id="itemsContainer" class="space-y-4">
                    <!-- Loaded dynamically via script -->
                </div>
            </div>

            <!-- Totals Section -->
            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl p-6 border border-gray-200/80 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Subtotal</label>
                        <input type="text" id="subtotal" readonly value="{{ number_format($template->subtotal, 2) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold text-right">
                        <input type="hidden" name="subtotal" id="subtotal_hidden" value="{{ $template->subtotal }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Tax Amount (₹)</label>
                        <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0"
                            value="{{ old('tax_amount', $template->tax_amount) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-semibold text-right focus:ring-2 focus:ring-blue-500"
                            onchange="calculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Discount Amount (₹)</label>
                        <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                            value="{{ old('discount_amount', $template->discount_amount) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-semibold text-right focus:ring-2 focus:ring-blue-500"
                            onchange="calculateTotals()">
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('recurring-invoices.show', $template) }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Update Template
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    let itemCount = {{ $template->items->count() }};

    document.getElementById('addItem').addEventListener('click', function () {
        addItemRow();
    });

    @foreach($template->items as $index => $item)
        addItemRow({{ $item->quantity }}, {{ $item->unit_price }}, '{{ addslashes($item->description) }}');
    @endforeach

    if (itemCount === 0) {
        addItemRow();
    }

    function toggleCustomInterval() {
        const frequency = document.getElementById('frequency').value;
        const container = document.getElementById('custom_interval_container');
        if (frequency === 'custom') {
            container.style.display = 'block';
            document.getElementById('custom_interval_days').required = true;
        } else {
            container.style.display = 'none';
            document.getElementById('custom_interval_days').required = false;
        }
    }

    function addItemRow(quantity = 1, price = 0, description = '') {
        itemCount++;
        const container = document.getElementById('itemsContainer');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'item-row bg-gray-50 dark:bg-gray-900/40 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700';
        itemDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description <span class="text-red-500">*</span></label>
                    <textarea name="items[${itemCount}][description]" required rows="2" placeholder="Item details..."
                              class="item-description w-full p-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">${description}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="items[${itemCount}][quantity]" required step="0.01" min="0.01" value="${quantity}" 
                           class="item-quantity w-full py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right focus:ring-2 focus:ring-blue-500"
                           onchange="calculateItemTotal(this); calculateTotals();">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Unit Price (₹) <span class="text-red-500">*</span></label>
                    <input type="number" name="items[${itemCount}][unit_price]" required step="0.01" min="0" value="${price}" 
                           class="item-price w-full py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right focus:ring-2 focus:ring-blue-500"
                           onchange="calculateItemTotal(this); calculateTotals();">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Total</label>
                    <input type="text" class="item-total w-full py-2.5 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold text-right" readonly value="${(quantity * price).toFixed(2)}">
                </div>
                <div class="md:col-span-1">
                    <button type="button" class="remove-item w-full py-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 font-semibold text-xs transition-colors" onclick="removeItem(this)">
                        Remove
                    </button>
                </div>
            </div>
        `;
        container.appendChild(itemDiv);
        calculateTotals();
    }

    function removeItem(button) {
        button.closest('.item-row').remove();
        calculateTotals();
    }

    function calculateItemTotal(input) {
        const row = input.closest('.item-row');
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const total = quantity * price;
        row.querySelector('.item-total').value = total.toFixed(2);
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-total').forEach(function (input) {
            subtotal += parseFloat(input.value) || 0;
        });

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
    }

    // Refresh session every 5 minutes to prevent CSRF token expiration
    setInterval(function () {
        fetch(window.location.href, {
            method: 'HEAD',
            credentials: 'same-origin'
        }).catch(err => {
            console.error('Session refresh failed:', err);
        });
    }, 300000);
</script>
@endsection