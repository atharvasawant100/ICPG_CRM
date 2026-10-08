@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice Details')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Invoice: {{ $invoice->invoice_number }}
            </h2>
            <p class="text-sm text-gray-600 mt-1 dark:text-gray-400">Created on {{ $invoice->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if(in_array($invoice->status, ['draft', 'sent']) && $invoice->payments->isEmpty())
                <a href="{{ route('invoices.edit', $invoice) }}"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    Edit
                </a>
            @endif
            @if($invoice->status === 'draft')
                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                        Delete
                    </button>
                </form>
            @endif
            @if(in_array($invoice->status, ['draft', 'sent', 'partial', 'paid']))
                <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="send_email" value="0" id="send_email_hidden">
                    <button type="submit" onclick="document.getElementById('send_email_hidden').value='0'"
                        class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                        Mark as Sent
                    </button>
                </form>
                @if($invoice->customer->email)
                    @php
                        $emailCount = (int) ($invoice->email_sent_count ?? 0);
                        $emailCountLabel = $emailCount > 0 ? " ({$emailCount})" : '';
                        $lastEmailLabel = $invoice->email_sent_at ? $invoice->email_sent_at->format('M d, Y h:i A') : null;
                        $confirmText = $lastEmailLabel
                            ? "Email was sent {$emailCount} time(s). Last sent on {$lastEmailLabel}. Send again to {$invoice->customer->email}?"
                            : "Are you sure you want to send this invoice to {$invoice->customer->email}?";
                    @endphp
                    <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="inline"
                        onsubmit="return confirmInvoiceEmail(this, @js($confirmText));">
                        @csrf
                        <input type="hidden" name="send_email" value="1">
                        <button type="submit" id="btn-send-email"
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 disabled:opacity-60 disabled:cursor-wait">
                            Send As Email{{ $emailCountLabel }}
                        </button>
                    </form>
                @endif
            @endif
            @if(!in_array($invoice->status, ['paid', 'cancelled']))
                <form action="{{ route('invoices.cancel', $invoice) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to cancel this invoice?');">
                    @csrf
                    <button type="submit"
                        class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                        Cancel
                    </button>
                </form>
            @endif
            <a href="{{ route('invoices.pdf', $invoice) }}" id="btn-download-pdf"
                class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                Download PDF
            </a>
        </div>
    </div>

    <script>
        function confirmInvoiceEmail(form, message) {
            if (!confirm(message)) {
                return false;
            }
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Sending...';
            }
            return true;
        }

        document.getElementById('btn-download-pdf')?.addEventListener('click', function () {
            this.classList.add('opacity-60');
            var original = this.textContent;
            this.textContent = 'Preparing PDF...';
            var link = this;
            setTimeout(function () {
                link.textContent = original;
                link.classList.remove('opacity-60');
            }, 15000);
        });
    </script>

    <!-- Invoice Details -->
    <div
        class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Customer Information -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-gray-200">Bill To</h3>
                <div class="space-y-2">
                    <p class="text-gray-900 font-semibold dark:text-white">{{ $invoice->customer->name }}</p>
                    @if($invoice->customer->email)
                        <p class="text-gray-600 text-sm dark:text-gray-400">{{ $invoice->customer->email }}</p>
                    @endif
                    @if($invoice->customer->phone)
                        <p class="text-gray-600 text-sm dark:text-gray-400">{{ $invoice->customer->phone }}</p>
                    @endif
                    @if($invoice->customer->company)
                        <p class="text-gray-600 text-sm dark:text-gray-400">{{ $invoice->customer->company }}</p>
                    @endif
                    @if($invoice->customer->address)
                        <p class="text-gray-600 text-sm whitespace-pre-line dark:text-gray-400">
                            {{ $invoice->customer->address }}</p>
                    @endif
                </div>
            </div>

            <!-- Invoice Information -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-gray-200">Invoice Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Invoice Number:</span>
                        <span class="text-gray-900 font-semibold dark:text-gray-200">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Invoice Date:</span>
                        <span
                            class="text-gray-900 font-semibold dark:text-gray-200">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Due Date:</span>
                        <span
                            class="text-gray-900 font-semibold dark:text-gray-200">{{ $invoice->due_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status:</span>
                        <x-invoice-status-badge :status="$invoice->status" />
                    </div>
                    @if($invoice->payment_terms)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Payment Terms:</span>
                            <span class="text-gray-900 font-semibold dark:text-gray-200">{{ $invoice->payment_terms }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Description</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Quantity</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Unit
                                Price</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    <div>
                                        @if($item->service)
                                            <span class="font-semibold">{{ $item->service->name }}</span>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400">({{ $item->service->frequency_label }})</span>
                                            <br>
                                            <span
                                                class="text-gray-600 whitespace-pre-wrap dark:text-gray-300">{{ $item->description }}</span>
                                        @else
                                            <span class="whitespace-pre-wrap">{{ $item->description }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right dark:text-gray-200">
                                    {{ number_format($item->quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right dark:text-gray-200">
                                    ₹{{ number_format($item->unit_price, 2) }}</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right dark:text-gray-200">
                                    ₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-full md:w-1/3 space-y-2">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span class="font-semibold">₹{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Tax:</span>
                        <span class="font-semibold">₹{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Discount:</span>
                        <span class="font-semibold">-₹{{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div
                    class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t-2 border-gray-300 dark:text-white dark:border-gray-600">
                    <span>Total:</span>
                    <span>₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @php
                    $paidAmount = $invoice->getPaidAmount();
                    $remainingAmount = $invoice->getRemainingAmount();
                @endphp
                @if($paidAmount > 0)
                    <div class="flex justify-between text-gray-600 pt-2">
                        <span>Paid:</span>
                        <span class="font-semibold text-green-600">₹{{ number_format($paidAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-900 font-bold pt-2">
                        <span>Remaining:</span>
                        <span class="text-red-600">₹{{ number_format($remainingAmount, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($invoice->notes)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 mb-2 dark:text-gray-200">Notes</h3>
                <p class="text-gray-600 whitespace-pre-wrap dark:text-gray-400">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Payment History -->
    @if($invoice->payments->count() > 0)
        <div
            class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 mb-6 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Payment History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Amount
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Method
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Reference
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Added By
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach($invoice->payments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                    {{ $payment->payment_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-200">
                                    ₹{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                    {{ $payment->payment_method }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->reference_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->creator->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('invoices.payments.destroy', $payment) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @if($payment->notes)
                                <tr>
                                    <td colspan="6" class="px-6 py-2 text-xs text-gray-500 italic">{{ $payment->notes }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Add Payment -->
    @if(!in_array($invoice->status, ['paid', 'cancelled']) && $invoice->getRemainingAmount() > 0)
        <div
            class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Add Payment</h3>
            <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label for="amount" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Amount
                            <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                            max="{{ $invoice->getRemainingAmount() }}" required
                            class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    </div>
                    <div>
                        <label for="payment_date"
                            class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Payment Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}"
                            max="{{ date('Y-m-d') }}" required
                            class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    </div>
                    <div>
                        <label for="payment_method"
                            class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Payment Method <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="payment_method" id="payment_method" placeholder="e.g., Cash, Bank Transfer"
                            required
                            class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    </div>
                    <div>
                        <label for="reference_number"
                            class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Reference
                            Number</label>
                        <input type="text" name="reference_number" id="reference_number" placeholder="Transaction/Cheque #"
                            class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Notes</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Additional payment notes..."
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400"></textarea>
                </div>
                <button type="submit"
                    class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">
                    Record Payment
                </button>
            </form>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('invoices.index') }}"
            class="text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors dark:text-gray-400 dark:hover:text-gray-200">←
            Back to Invoices</a>
    </div>
@endsection