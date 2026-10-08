@extends('layouts.app')

@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Invoice: {{ $invoice->invoice_number }}</h2>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li class="font-medium">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Customer Selection -->
            <div>
                <label for="customer_id" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customer_id" required
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Invoice Date -->
            <div>
                <label for="invoice_date" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Invoice Date <span class="text-red-500">*</span></label>
                <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                       class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
            </div>

            <!-- Due Date -->
            <div>
                <label for="due_date" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Due Date <span class="text-red-500">*</span></label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                       class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
            </div>

            <!-- Payment Terms -->
            <div>
                <label for="payment_terms" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Payment Terms</label>
                <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', $invoice->payment_terms) }}" placeholder="e.g., Net 30, Due on receipt"
                       class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Status</label>
                <select name="status" id="status"
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ old('status', $invoice->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <label for="notes" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Additional notes or comments..."
                      class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">{{ old('notes', $invoice->notes) }}</textarea>
        </div>

        <!-- Invoice Items -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Invoice Items</h3>
                <button type="button" id="addItem" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all dark:bg-blue-700 dark:hover:bg-blue-600">
                    Add Item
                </button>
            </div>

            <div id="itemsContainer">
                <!-- Existing items will be loaded here -->
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 dark:bg-gray-700/50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2"></div>
                <div class="text-right">
                    <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Subtotal</label>
                    <input type="text" id="subtotal" readonly value="{{ number_format($invoice->subtotal, 2) }}"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm font-semibold bg-white text-right dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                    <input type="hidden" name="subtotal" id="subtotal_hidden" value="{{ $invoice->subtotal }}">
                </div>
                <div class="text-right">
                    <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Tax Amount</label>
                    <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0" value="{{ old('tax_amount', $invoice->tax_amount) }}" 
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400"
                           onchange="calculateTotals()">
                </div>
                <div class="text-right">
                    <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Discount Amount</label>
                    <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0" value="{{ old('discount_amount', $invoice->discount_amount) }}"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400"
                           onchange="calculateTotals()">
                </div>
                <div class="md:col-span-2"></div>
                <div class="md:col-span-2 text-right">
                    <label class="block text-gray-700 text-lg font-bold mb-2 dark:text-gray-300">Total Amount</label>
                    <input type="text" id="total_amount" readonly value="{{ number_format($invoice->total_amount, 2) }}"
                           class="shadow-sm border-2 border-gray-400 rounded-lg w-full py-2 px-4 text-gray-900 text-lg font-bold bg-white text-right dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                    <input type="hidden" name="total_amount" id="total_amount_hidden" value="{{ $invoice->total_amount }}">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-4">
            <button type="submit" class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600">
                Update Invoice
            </button>
            <a href="{{ route('invoices.show', $invoice) }}" class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
let itemCount = {{ $invoice->items->count() }};

// Services Reference (for JavaScript)
@php
    $servicesArray = $services->map(function($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description ?? '',
            'rate' => $service->rate,
            'frequency' => $service->frequency_label
        ];
    })->values();
@endphp
const services = @json($servicesArray);

// Add new item row
document.getElementById('addItem').addEventListener('click', function() {
    addItemRow();
});
// Load existing items
@foreach($invoice->items as $index => $item)
    addItemRow({{ $item->service_id ?: 'null' }}, {{ $item->quantity }}, {{ $item->unit_price }});
@endforeach

// If no items, add one empty row
if (itemCount === 0) {
    addItemRow();
}

function addItemRow(serviceId = '', quantity = 1, price = 0) {
    serviceId = serviceId || '';
    itemCount++;
    const container = document.getElementById('itemsContainer');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'item-row bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200 dark:bg-gray-700/50 dark:border-gray-600';
    
    // Build service options
    let serviceOptions = '<option value="">Select Service (Optional)</option>';
    @foreach($services as $service)
        const serviceIdStr = String(serviceId || '');
        const currentServiceId = '{{ $service->id }}';
        serviceOptions += `<option value="${currentServiceId}" data-rate="{{ $service->rate }}" ${serviceIdStr === currentServiceId ? 'selected' : ''}>{{ $service->name }} ({{ $service->frequency_label }}) - ₹{{ number_format($service->rate, 2) }}</option>`;
    @endforeach
    
    itemDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Service</label>
                <select name="items[${itemCount}][service_id]" class="item-service shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400" onchange="selectService(this, ${itemCount})">
                    ${serviceOptions}
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="items[${itemCount}][quantity]" required step="0.01" min="0.01" value="${quantity}" 
                       class="item-quantity shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400"
                       onchange="calculateItemTotal(this); calculateTotals();">
            </div>
            <div class="md:col-span-3">
                <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Unit Price <span class="text-red-500">*</span></label>
                <input type="number" name="items[${itemCount}][unit_price]" required step="0.01" min="0" value="${price}" 
                       class="item-price shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400"
                       onchange="calculateItemTotal(this); calculateTotals();">
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Total</label>
                <input type="text" class="item-total shadow-sm border border-gray-300 rounded-lg w-full py-2 px-4 text-gray-700 text-sm font-semibold bg-white text-right dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100" readonly value="${(quantity * price).toFixed(2)}">
            </div>
            <div class="md:col-span-1">
                <button type="button" class="remove-item bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-semibold transition-all w-full mt-6" onclick="removeItem(this)">
                    Remove
                </button>
            </div>
        </div>
    `;
    container.appendChild(itemDiv);
    calculateTotals();
}

function selectService(selectElement, itemIndex) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('.item-row');
    
    if (!selectedOption || selectedOption.value === '') {
        return;
    }
    
    const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
    
    // Update unit price automatically
    const priceInput = row.querySelector('.item-price');
    if (priceInput && rate > 0) {
        priceInput.value = rate;
        // Trigger change event to ensure calculations run
        priceInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Recalculate total
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
    document.querySelectorAll('.item-total').forEach(function(input) {
        subtotal += parseFloat(input.value) || 0;
    });

    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const totalAmount = subtotal + taxAmount - discountAmount;

    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
    document.getElementById('total_amount_hidden').value = totalAmount.toFixed(2);
}
</script>
@endsection
