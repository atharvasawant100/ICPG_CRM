@extends('layouts.app')

@section('title', 'Create Invoice')
@section('page-title', 'Create Invoice')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('invoices.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Invoices</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">New Invoice</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Create New Invoice</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Generate an official bill with customizable line items, tax, discounts, and optional recurring billing.
                </p>
            </div>

            <a href="{{ route('invoices.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Invoices
            </a>
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

        <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm" class="space-y-8">
            @csrf

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Recipient of this invoice</p>
                </div>

                <!-- Brand Selection -->
                <div>
                    <label for="brand" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Customer Brand / Trade Name
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <input type="text" name="brand" id="brand" list="customer_brands_list" value="{{ old('brand') }}"
                            placeholder="Select or enter brand name..."
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                        <datalist id="customer_brands_list">
                            <!-- Populated dynamically when customer is selected -->
                        </datalist>
                    </div>
                    <div id="brand_quick_select" class="flex flex-wrap items-center gap-1.5 mt-2 hidden">
                        <span class="text-xs text-gray-500 dark:text-gray-400 self-center mr-1">Brands:</span>
                    </div>
                </div>

                <!-- Invoice Date -->
                <div>
                    <label for="invoice_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Invoice Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="invoice_date" id="invoice_date"
                            value="{{ old('invoice_date', date('Y-m-d')) }}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Date invoice was issued</p>
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input type="date" name="due_date" id="due_date"
                            value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Payment deadline</p>
                </div>

                <!-- Payment Terms -->
                <div>
                    <label for="payment_terms" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Payment Terms
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', 'Net 30') }}"
                            placeholder="e.g. Net 30, Due on receipt"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Initial Status
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <select name="status" id="status"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ old('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Notes</label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="notes" id="notes" rows="3" placeholder="Additional notes or payment instructions..."
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Recurring Invoice Option Box -->
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                    <label class="inline-flex items-center gap-3 cursor-pointer mb-3">
                        <input type="checkbox" name="create_recurring" id="create_recurring" value="1" {{ old('create_recurring') ? 'checked' : '' }} onchange="toggleRecurringFields()"
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Create as Recurring Invoice</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Automatically repeat this invoice according to a recurring schedule</p>
                        </div>
                    </label>

                    <div id="recurring_fields" style="display: {{ old('create_recurring') ? 'block' : 'none' }};" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="recurring_frequency" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Frequency <span class="text-red-500">*</span></label>
                                <select name="recurring_frequency" id="recurring_frequency" onchange="toggleCustomInterval()"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="weekly" {{ old('recurring_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('recurring_frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('recurring_frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    <option value="custom" {{ old('recurring_frequency') === 'custom' ? 'selected' : '' }}>Custom Interval</option>
                                </select>
                            </div>

                            <div id="custom_interval_container" style="display: {{ old('recurring_frequency') === 'custom' ? 'block' : 'none' }};">
                                <label for="recurring_custom_interval_days" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Interval (Days) <span class="text-red-500">*</span></label>
                                <input type="number" name="recurring_custom_interval_days" id="recurring_custom_interval_days"
                                    value="{{ old('recurring_custom_interval_days', 30) }}" min="1" placeholder="e.g. 30"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>

                            <div>
                                <label for="recurring_start_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" name="recurring_start_date" id="recurring_start_date"
                                    value="{{ old('recurring_start_date', date('Y-m-d')) }}"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>

                            <div>
                                <label for="recurring_end_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">End Date (Optional)</label>
                                <input type="date" name="recurring_end_date" id="recurring_end_date"
                                    value="{{ old('recurring_end_date') }}"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Invoice Items Section -->
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
                    <!-- Dynamic items added here -->
                </div>
            </div>

            <!-- Totals Section -->
            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl p-6 border border-gray-200/80 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Subtotal</label>
                        <input type="text" id="subtotal" readonly value="0.00"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold text-right">
                        <input type="hidden" name="subtotal" id="subtotal_hidden" value="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Tax Amount (₹)</label>
                        <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0"
                            value="{{ old('tax_amount', 0) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-semibold text-right focus:ring-2 focus:ring-blue-500"
                            oninput="calculateTotals()" onchange="calculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Discount Amount (₹)</label>
                        <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                            value="{{ old('discount_amount', 0) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-semibold text-right focus:ring-2 focus:ring-blue-500"
                            oninput="calculateTotals()" onchange="calculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Total Amount (₹)</label>
                        <input type="text" id="total_amount" readonly value="0.00"
                            class="w-full py-2.5 px-4 rounded-xl border-2 border-blue-500 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 text-lg font-black text-right">
                        <input type="hidden" name="total_amount" id="total_amount_hidden" value="0">
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('invoices.index') }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Create Invoice
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    let itemCount = 0;

    document.getElementById('addItem').addEventListener('click', function () {
        addItemRow();
    });

    addItemRow();

    function addItemRow(serviceId = '', quantity = 1, price = 0) {
        itemCount++;
        const container = document.getElementById('itemsContainer');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'item-row bg-gray-50 dark:bg-gray-900/40 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700';
        itemDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Service Package</label>
                    <select name="items[${itemCount}][service_id]" class="item-service w-full py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm" onchange="selectService(this, ${itemCount})">
                        <option value="">Select Service (Optional)</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" data-rate="{{ $service->rate }}">{{ $service->name }} ({{ $service->frequency_label }}) - ₹{{ number_format($service->rate, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="items[${itemCount}][quantity]" required step="0.01" min="0.01" value="${quantity}" 
                           class="item-quantity w-full py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right focus:ring-2 focus:ring-blue-500"
                           onchange="calculateItemTotal(this); calculateTotals();">
                </div>
                <div class="md:col-span-3">
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
    }

    function selectService(selectElement, itemIndex) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const row = selectElement.closest('.item-row');

        if (!selectedOption || selectedOption.value === '') {
            return;
        }

        const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
        const priceInput = row.querySelector('.item-price');
        if (priceInput && rate > 0) {
            priceInput.value = rate;
            priceInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (priceInput) {
            calculateItemTotal(priceInput);
            calculateTotals();
        }
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

        const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const totalAmount = Math.max(0, subtotal + taxAmount - discountAmount);

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
        document.getElementById('total_amount').value = totalAmount.toFixed(2);
        document.getElementById('total_amount_hidden').value = totalAmount.toFixed(2);
    }

    document.getElementById('tax_amount')?.addEventListener('input', calculateTotals);
    document.getElementById('tax_amount')?.addEventListener('change', calculateTotals);
    document.getElementById('discount_amount')?.addEventListener('input', calculateTotals);
    document.getElementById('discount_amount')?.addEventListener('change', calculateTotals);

    function toggleRecurringFields() {
        const checkbox = document.getElementById('create_recurring');
        const fields = document.getElementById('recurring_fields');
        if (checkbox.checked) {
            fields.style.display = 'block';
        } else {
            fields.style.display = 'none';
        }
    }

    function toggleCustomInterval() {
        const frequency = document.getElementById('recurring_frequency').value;
        const customContainer = document.getElementById('custom_interval_container');
        if (frequency === 'custom') {
            customContainer.style.display = 'block';
        } else {
            customContainer.style.display = 'none';
        }
    }

    document.getElementById('invoiceForm').addEventListener('submit', function (e) {
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the invoice.');
            return false;
        }

        const createRecurring = document.getElementById('create_recurring').checked;
        if (createRecurring) {
            const frequency = document.getElementById('recurring_frequency').value;
            const startDate = document.getElementById('recurring_start_date').value;

            if (!frequency) {
                e.preventDefault();
                alert('Please select a frequency for recurring invoice.');
                return false;
            }

            if (!startDate) {
                e.preventDefault();
                alert('Please select a start date for recurring invoice.');
                return false;
            }

            if (frequency === 'custom') {
                const customDays = document.getElementById('recurring_custom_interval_days').value;
                if (!customDays || customDays < 1) {
                    e.preventDefault();
                    alert('Please enter a valid interval in days for custom frequency.');
                    return false;
                }
            }
        }
    });

    @php
        $customersData = $customers->mapWithKeys(function($c) {
            return [$c->id => [
                'id' => $c->id,
                'name' => $c->name,
                'company' => $c->company,
                'brands' => $c->brands_list,
            ]];
        });
    @endphp
    const customersData = @json($customersData);

    function updateCustomerBrands(customerId, preselectBrand = '') {
        const datalist = document.getElementById('customer_brands_list');
        const brandInput = document.getElementById('brand');
        const quickSelect = document.getElementById('brand_quick_select');
        if (!datalist || !brandInput) return;

        datalist.innerHTML = '';
        if (quickSelect) {
            quickSelect.innerHTML = '<span class="text-xs text-gray-500 dark:text-gray-400 self-center mr-1">Brands:</span>';
            quickSelect.classList.add('hidden');
        }

        if (!customerId || !customersData[customerId]) {
            return;
        }

        const customer = customersData[customerId];
        const brands = customer.brands || [];

        if (brands.length > 0) {
            brands.forEach(function(b) {
                const opt = document.createElement('option');
                opt.value = b;
                datalist.appendChild(opt);

                if (quickSelect) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition cursor-pointer';
                    btn.textContent = b;
                    btn.onclick = function() {
                        brandInput.value = b;
                    };
                    quickSelect.appendChild(btn);
                }
            });

            if (quickSelect && brands.length > 0) {
                quickSelect.classList.remove('hidden');
            }

            if (preselectBrand) {
                brandInput.value = preselectBrand;
            } else if (!brandInput.value && brands.length === 1) {
                brandInput.value = brands[0];
            }
        } else if (customer.company) {
            const opt = document.createElement('option');
            opt.value = customer.company;
            datalist.appendChild(opt);
            if (preselectBrand) {
                brandInput.value = preselectBrand;
            } else if (!brandInput.value) {
                brandInput.value = customer.company;
            }
        }
    }

    document.getElementById('customer_id')?.addEventListener('change', function() {
        updateCustomerBrands(this.value);
    });

    const initialCustId = document.getElementById('customer_id')?.value;
    if (initialCustId) {
        updateCustomerBrands(initialCustId, @json(old('brand', '')));
    }
</script>
@endsection