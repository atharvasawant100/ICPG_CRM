@extends('layouts.app')

@section('title', 'Recurring Invoice Template')
@section('page-title', 'Recurring Invoice Template')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $template->name }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Created on {{ $template->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if($template->is_active)
                @can('recurring_invoices.edit')
                    <form action="{{ route('recurring-invoices.generate', $template) }}" method="POST" class="inline"
                        onsubmit="return confirm('Generate an invoice immediately from this template and email it to the customer?');">
                        @csrf
                        <button type="submit"
                            class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Generate Invoice Now</span>
                        </button>
                    </form>
                @endcan
            @endif
            <a href="{{ route('recurring-invoices.edit', $template) }}"
                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                Edit
            </a>
            <form action="{{ route('recurring-invoices.toggle', $template) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="bg-gradient-to-r from-{{ $template->is_active ? 'orange' : 'green' }}-600 to-{{ $template->is_active ? 'orange' : 'green' }}-700 hover:from-{{ $template->is_active ? 'orange' : 'green' }}-700 hover:to-{{ $template->is_active ? 'orange' : 'green' }}-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <form action="{{ route('recurring-invoices.destroy', $template) }}" method="POST" class="inline"
                onsubmit="return confirm('Are you sure you want to delete this template?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Template Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700 mb-6 transition-colors duration-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Customer Information -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Customer</h3>
                <div class="space-y-2">
                    <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $template->customer->name }}</p>
                    @if($template->customer->email)
                        <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $template->customer->email }}</p>
                    @endif
                    @if($template->customer->phone)
                        <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $template->customer->phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Template Information -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Template Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Frequency:</span>
                        <span class="text-gray-900 dark:text-gray-100 font-semibold">
                            {{ ucfirst($template->frequency) }}
                            @if($template->frequency === 'custom')
                                ({{ $template->custom_interval_days }} days)
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Start Date:</span>
                        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $template->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">End Date:</span>
                        <span
                            class="text-gray-900 dark:text-gray-100 font-semibold">{{ $template->end_date ? $template->end_date->format('M d, Y') : 'No end date' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Next Generation:</span>
                        <span
                            class="text-gray-900 dark:text-gray-100 font-semibold">{{ $template->next_generation_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status:</span>
                        @if($template->is_active)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                                Active
                            </span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                Inactive
                            </span>
                        @endif
                    </div>
                    @if($template->payment_terms)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Payment Terms:</span>
                            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $template->payment_terms }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Template Items -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Invoice Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit
                                Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($template->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-pre-wrap text-sm text-gray-900 dark:text-gray-100">{{ $item->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                    {{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                    ₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
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
                    <span class="font-semibold">₹{{ number_format($template->subtotal, 2) }}</span>
                </div>
                @if($template->tax_amount > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Tax:</span>
                        <span class="font-semibold">₹{{ number_format($template->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($template->discount_amount > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Discount:</span>
                        <span class="font-semibold">-₹{{ number_format($template->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 pt-2 border-t-2 border-gray-300 dark:border-gray-600">
                    <span>Total:</span>
                    <span>₹{{ number_format($template->subtotal + $template->tax_amount - $template->discount_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($template->notes)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">Notes</h3>
                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $template->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Generated Invoices -->
    @if($template->invoices->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700 mb-6 transition-colors duration-200">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Generated Invoices ({{ $template->invoices->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($template->invoices->take(10) as $invoice)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-medium">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-invoice-status-badge :status="$invoice->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                    ₹{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($template->invoices->count() > 10)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Showing latest 10 invoices. Total: {{ $template->invoices->count() }}</p>
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700 mb-6 transition-colors duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No invoices have been generated from this template yet.</p>
        </div>
    @endif

@endsection