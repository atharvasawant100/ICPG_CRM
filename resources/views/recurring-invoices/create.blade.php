@extends('layouts.app')

@section('title', 'Create Recurring Invoice Template')
@section('page-title', 'Create Recurring Invoice Template')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Create Recurring Invoice Template</h2>
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

        <form method="POST" action="{{ route('recurring-invoices.store') }}" id="templateForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Template Name -->
                <div>
                    <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Template Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="e.g., Monthly Subscription"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Customer Selection -->
                <div>
                    <label for="customer_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Customer <span
                            class="text-red-500">*</span></label>
                    <select name="customer_id" id="customer_id" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly
                        </option>
                        <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="custom" {{ old('frequency') === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <!-- Custom Interval Days (only shown when frequency is custom) -->
                <div id="custom_interval_container"
                    style="display: {{ old('frequency') === 'custom' ? 'block' : 'none' }};">
                    <label for="custom_interval_days" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Interval (Days)
                        <span class="text-red-500">*</span></label>
                    <input type="number" name="custom_interval_days" id="custom_interval_days"
                        value="{{ old('custom_interval_days', 30) }}" min="1" placeholder="e.g., 30"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Start Date <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                        required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- End Date (Optional) -->
                <div>
                    <label for="end_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">End Date (Optional)</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty for infinite recurring invoices</p>
                </div>

                <!-- Payment Terms -->
                <div>
                    <label for="payment_terms" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Payment Terms</label>
                    <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', 'Net 30') }}"
                        placeholder="e.g., Net 30, Due on receipt"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Additional notes or comments..."
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">{{ old('notes') }}</textarea>
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
                    <!-- Items will be added here dynamically -->
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2"></div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Subtotal</label>
                        <input type="text" id="subtotal" readonly value="0.00"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm font-semibold bg-white text-right">
                        <input type="hidden" name="subtotal" id="subtotal_hidden" value="0">
                    </div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Tax Amount</label>
                        <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0"
                            value="{{ old('tax_amount', 0) }}"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                            onchange="calculateTotals()">
                    </div>
                    <div class="text-right">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Discount Amount</label>
                        <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                            value="{{ old('discount_amount', 0) }}"
                            class="shadow-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                            onchange="calculateTotals()">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">
                    Create Template
                </button>
                <a href="{{ route('recurring-invoices.index') }}"
                    class="bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-lg font-semibold transition-all duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        let itemCount = 0;

        // Add new item row
        document.getElementById('addItem').addEventListener('click', function () {
            addItemRow();
        });

        // Add initial item row
        addItemRow();

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
                                      class="item-description shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">${description}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="items[${itemCount}][quantity]" required step="0.01" min="0.01" value="${quantity}" 
                                   class="item-quantity shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                                   onchange="calculateItemTotal(this); calculateTotals();">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Unit Price <span class="text-red-500">*</span></label>
                            <input type="number" name="items[${itemCount}][unit_price]" required step="0.01" min="0" value="${price}" 
                                   class="item-price shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2 px-4 text-gray-700 dark:text-gray-200 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
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
        }, 300000); // 5 minutes

        // Validate form before submit
        document.getElementById('templateForm').addEventListener('submit', function (e) {
            const items = document.querySelectorAll('.item-row');
            if (items.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the template.');
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';

            // Re-enable after 30 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 30000);
        });

    </script>
@endsection