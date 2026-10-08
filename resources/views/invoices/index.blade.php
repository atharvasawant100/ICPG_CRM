@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">All Invoices</h2>
        <a href="{{ route('invoices.create') }}"
            class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600">
            Create Invoice
        </a>
    </div>

    <!-- Filters Panel -->
    @if(isset($customers) && $customers->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap gap-3">
                <select name="status" id="status" onchange="this.form.submit()"
                    class="shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="customer_id" id="customer_id" onchange="this.form.submit()"
                    class="shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>

                @if(request()->hasAny(['status', 'customer_id']))
                    <a href="{{ route('invoices.index') }}"
                        class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Invoice #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Brand
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Due Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                    class="text-blue-600 hover:text-blue-800 hover:underline font-medium dark:text-blue-400 dark:hover:text-blue-300">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $invoice->customer?->company ?: ($invoice->customer?->name ?? '—') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $invoice->due_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-200">
                                ₹{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-invoice-status-badge :status="$invoice->status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                                    @if(in_array($invoice->status, ['draft', 'sent']) && $invoice->payments_count === 0)
                                        <a href="{{ route('invoices.edit', $invoice) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">Edit</a>
                                    @endif
                                    <a href="{{ route('invoices.pdf', $invoice) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">PDF</a>
                                    @if($invoice->status === 'draft')
                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }} permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($invoices as $invoice)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow dark:from-gray-800 dark:to-gray-700 dark:border-gray-600">
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('invoices.show', $invoice) }}"
                            class="text-blue-600 hover:text-blue-800 hover:underline font-semibold text-sm dark:text-blue-400 dark:hover:text-blue-300">{{ $invoice->invoice_number }}</a>
                        <x-invoice-status-badge :status="$invoice->status" />
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 mb-3 dark:text-gray-300">
                        <p><span class="font-semibold">Brand:</span> {{ $invoice->customer?->company ?: ($invoice->customer?->name ?? '—') }}</p>
                        <p><span class="font-semibold">Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><span class="font-semibold">Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}</p>
                        <p><span class="font-semibold">Total:</span> ₹{{ number_format($invoice->total_amount, 2) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <a href="{{ route('invoices.show', $invoice) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        @if(in_array($invoice->status, ['draft', 'sent']) && $invoice->payments_count === 0)
                            <a href="{{ route('invoices.edit', $invoice) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">Edit</a>
                        @endif
                        <a href="{{ route('invoices.pdf', $invoice) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">PDF</a>
                        @if($invoice->status === 'draft')
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                                onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }} permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No invoices found.</p>
            @endforelse
        </div>

        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
@endsection