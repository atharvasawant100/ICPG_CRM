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
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('invoices.pdf', $invoice) }}" download="{{ $invoice->getPdfFilename() }}" id="btn-download-pdf"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-4 sm:px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Download PDF</span>
            </a>

            @php
                $canEdit = in_array($invoice->status, ['draft', 'sent']) && $invoice->payments->isEmpty();
                $canSendEmail = in_array($invoice->status, ['draft', 'sent', 'partial', 'paid']) && !empty($invoice->customer->email);
                $canMarkSent = in_array($invoice->status, ['draft', 'sent', 'partial', 'paid']);
                $canCancel = !in_array($invoice->status, ['paid', 'cancelled']);
                $canDelete = $invoice->status === 'draft';
                $hasSecondaryActions = $canEdit || $canSendEmail || $canMarkSent || $canCancel || $canDelete;
            @endphp

            @if($hasSecondaryActions)
                <div class="relative" id="invoice-actions-dropdown">
                    <button type="button" id="btn-invoice-actions"
                        class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 dark:border-gray-600 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        aria-expanded="false"
                        aria-haspopup="true">
                        <span>Actions</span>
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200" id="invoice-actions-caret" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="invoice-actions-menu"
                        class="hidden absolute right-0 mt-2 w-60 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1.5 z-50 transform origin-top-right transition-all duration-150">
                        @if($canEdit)
                            <a href="{{ route('invoices.edit', $invoice) }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700/60 dark:hover:text-white transition-colors">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edit Invoice</span>
                            </a>
                        @endif

                        @if($canSendEmail)
                            @php
                                $emailCount = (int) ($invoice->email_sent_count ?? 0);
                                $lastEmailLabel = $invoice->email_sent_at ? $invoice->email_sent_at->format('M d, Y h:i A') : null;
                                $confirmText = $lastEmailLabel
                                    ? "Email was sent {$emailCount} time(s). Last sent on {$lastEmailLabel}. Send again to {$invoice->customer->email}?"
                                    : "Are you sure you want to send this invoice to {$invoice->customer->email}?";
                            @endphp
                            <form action="{{ route('invoices.send', $invoice) }}" method="POST"
                                onsubmit="return confirmInvoiceEmail(this, @js($confirmText));">
                                @csrf
                                <input type="hidden" name="send_email" value="1">
                                <button type="submit" id="btn-send-email"
                                    class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700/60 dark:hover:text-white transition-colors text-left disabled:opacity-60 disabled:cursor-wait">
                                    <span class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Send As Email</span>
                                    </span>
                                    @if($emailCount > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300">
                                            {{ $emailCount }}
                                        </span>
                                    @endif
                                </button>
                            </form>
                        @endif

                        @if($canMarkSent)
                            <form action="{{ route('invoices.send', $invoice) }}" method="POST">
                                @csrf
                                <input type="hidden" name="send_email" value="0">
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700/60 dark:hover:text-white transition-colors text-left">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Mark as Sent <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">(no email)</span></span>
                                </button>
                            </form>
                        @endif

                        @if($canCancel || $canDelete)
                            <div class="border-t border-gray-100 dark:border-gray-700/80 my-1"></div>
                        @endif

                        @if($canCancel)
                            <form action="{{ route('invoices.cancel', $invoice) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to cancel this invoice?');">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-amber-600 hover:bg-amber-50 hover:text-amber-700 dark:text-amber-400 dark:hover:bg-amber-900/20 dark:hover:text-amber-300 transition-colors text-left">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span>Cancel Invoice</span>
                                </button>
                            </form>
                        @endif

                        @if($canDelete)
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300 transition-colors text-left">
                                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Delete Invoice</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function() {
            var actionsBtn = document.getElementById('btn-invoice-actions');
            var actionsMenu = document.getElementById('invoice-actions-menu');
            var actionsCaret = document.getElementById('invoice-actions-caret');

            if (actionsBtn && actionsMenu) {
                function openMenu() {
                    actionsMenu.classList.remove('hidden');
                    actionsBtn.setAttribute('aria-expanded', 'true');
                    if (actionsCaret) actionsCaret.classList.add('rotate-180');
                }

                function closeMenu() {
                    actionsMenu.classList.add('hidden');
                    actionsBtn.setAttribute('aria-expanded', 'false');
                    if (actionsCaret) actionsCaret.classList.remove('rotate-180');
                }

                actionsBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var isOpen = !actionsMenu.classList.contains('hidden');
                    if (isOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!actionsMenu.contains(e.target) && !actionsBtn.contains(e.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeMenu();
                    }
                });
            }
        })();

        function confirmInvoiceEmail(form, message) {
            if (!confirm(message)) {
                return false;
            }
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Queueing...';
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
                    @if($invoice->brand)
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400"><span class="text-gray-500 dark:text-gray-400 font-normal">Brand:</span> {{ $invoice->brand }}</p>
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
                    @if($invoice->brand)
                    <div class="flex justify-between items-center py-0.5">
                        <span class="text-gray-600 dark:text-gray-400">Brand:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ $invoice->brand }}</span>
                    </div>
                    @endif
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

@endsection