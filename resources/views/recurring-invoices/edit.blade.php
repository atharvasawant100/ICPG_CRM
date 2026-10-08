@extends('layouts.app')

@section('title', 'Edit Recurring Invoice Template')
@section('page-title', 'Edit Recurring Invoice Template')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Template: {{ $template->name }}</h2>
        </div>

        @if($errors->any())
            <div
                class="mb-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/50 dark:to-red-800/50 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li class="font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('recurring-invoices.update', $template) }}" id="templateForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Template Name -->
                <div>
                    <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Template Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Customer Selection -->
                <div>
                    <label for="customer_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Customer <span
                            class="text-red-500">*</span></label>
                    <select name="customer_id" id="customer_id" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $template->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Frequency -->
                <div>
                    <label for="frequency" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Frequency <span
                            class="text-red-500">*</span></label>
                    <select name="frequency" id="frequency" required onchange="toggleCustomInterval()"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="weekly" {{ old('frequency', $template->frequency) === 'weekly' ? 'selected' : '' }}>
                            Weekly</option>
                        <option value="monthly" {{ old('frequency', $template->frequency) === 'monthly' ? 'selected' : '' }}>
                            Monthly</option>
                        <option value="yearly" {{ old('frequency', $template->frequency) === 'yearly' ? 'selected' : '' }}>
                            Yearly</option>
                        <option value="custom" {{ old('frequency', $template->frequency) === 'custom' ? 'selected' : '' }}>
                            Custom</option>
                    </select>
                </div>

                <!-- Custom Interval Days -->
                <div id="custom_interval_container"
                    style="display: {{ old('frequency', $template->frequency) === 'custom' ? 'block' : 'none' }};">
                    <label for="custom_interval_days" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Interval (Days)
                        <span class="text-red-500">*</span></label>
                    <input type="number" name="custom_interval_days" id="custom_interval_days"
                        value="{{ old('custom_interval_days', $template->custom_interval_days) }}" min="1"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Start Date <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date"
                        value="{{ old('start_date', $template->start_date->format('Y-m-d')) }}" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">End Date (Optional)</label>
                    <input type="date" name="end_date" id="end_date"
                        value="{{ old('end_date', $template->end_date ? $template->end_date->format('Y-m-d') : '') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Payment Terms -->
                <div>
                    <label for="payment_terms" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Payment Terms</label>
                    <input type="text" name="payment_terms" id="payment_terms"
                        value="{{ old('payment_terms', $template->payment_terms) }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">{{ old('notes', $template->notes) }}</textarea>
            </div>

            <!-- Template Items -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Invoice Items</h3>
                    <button type="button" id="addItem"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                        Add Item
                    </button>
                </div>

                <div id="itemsContainer">
                    <!-- Existing items will be loaded here -->
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2"></div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Subtotal</label>
                        <input type="text" id="subtotal" readonly value="{{ number_format($template->subtotal, 2) }}"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm font-semibold bg-white dark:bg-gray-700 text-right">
                        <input type="hidden" name="subtotal" id="subtotal_hidden" value="{{ $template->subtotal }}">
                    </div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Tax Amount</label>
                        <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0"
                            value="{{ old('tax_amount', $template->tax_amount) }}"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right"
                            onchange="calculateTotals()">
                    </div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Discount Amount</label>
                        <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                            value="{{ old('discount_amount', $template->discount_amount) }}"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right"
                            onchange="calculateTotals()">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">
                    Update Template
                </button>
                <a href="{{ route('recurring-invoices.show', $template) }}"
                    class="bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-lg font-semibold transition-all duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        let itemCount = {{ $template->items->count() }};

        // Add new item row
        document.getElementById('addItem').addEventListener('click', function () {
            addItemRow();
        });

        // Load existing items
        @foreach($template->items as $index => $item)
            addItemRow({{ $item->quantity }}, {{ $item->unit_price }}, '{{ addslashes($item->description) }}');
        @endforeach

        // If no items, add one empty row
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
            itemDiv.className = 'item-row bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-600';
            itemDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description <span class="text-red-500">*</span></label>
                        <textarea name="items[${itemCount}][description]" required rows="2" placeholder="Item description..."
                                  class="item-description shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent">${description}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="items[${itemCount}][quantity]" required step="0.01" min="0.01" value="${quantity}" 
                               class="item-quantity shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right"
                               onchange="calculateItemTotal(this); calculateTotals();">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Unit Price <span class="text-red-500">*</span></label>
                        <input type="number" name="items[${itemCount}][unit_price]" required step="0.01" min="0" value="${price}" 
                               class="item-price shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right"
                               onchange="calculateItemTotal(this); calculateTotals();">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Total</label>
                        <input type="text" class="item-total shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm font-semibold bg-white dark:bg-gray-700 text-right" readonly value="${(quantity * price).toFixed(2)}">
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" class="remove-item bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-semibold transition-all w-full" onclick="removeItem(this)">
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


        // Refresh session every 5 minutes to prevent CSRF token expiration
        setInterval(function () {
            fetch(window.location.href, {
                method: 'HEAD',
                credentials: 'same-origin'
            }).catch(err => {
                console.error('Session refresh failed:', err);
            });
        }, 300000); // 5 minutes

        function calculateTotals() {
            let subtotal = 0;
            document.querySelectorAll('.item-total').forEach(function (input) {
                subtotal += parseFloat(input.value) || 0;
            });

            document.getElementById('subtotal').value = subtotal.toFixed(2);
            document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
        }
    </script>
@endsection