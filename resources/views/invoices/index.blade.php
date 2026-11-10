@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
<div class="space-y-4">

    <!-- Top Bar with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">All Invoices</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage invoices, payments, and track client billing statuses.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Invoices</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'customer_id', 'date_from', 'date_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            @can('invoices.create')
            <a href="{{ route('invoices.create') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Invoice</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status', 'customer_id', 'date_from', 'date_to']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Invoice #, Customer, Email..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" id="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Customer Filter -->
                <div>
                    <label for="customer_id" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Customer</label>
                    <select name="customer_id" id="customer_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Customers</option>
                        @if(isset($customers))
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Apply Filters
                    </button>
                    @if (request()->hasAny(['search', 'status', 'customer_id', 'date_from', 'date_to']))
                    <a href="{{ route('invoices.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                @if($invoice->brand)
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $invoice->brand }}</div>
                                    @if($invoice->customer)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->customer->name }}</div>
                                    @endif
                                @else
                                    <div>{{ $invoice->customer?->company ?: ($invoice->customer?->name ?? '—') }}</div>
                                @endif
                            </td>
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
@can('invoices.edit')
                                        <a href="{{ route('invoices.edit', $invoice) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
@endcan
                                    @endif
@can('invoices.export')
                                    <a href="{{ route('invoices.pdf', $invoice) }}"
                                        download="{{ $invoice->getPdfFilename() }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">PDF</a>
@endcan
                                    @if($invoice->status === 'draft')
@can('invoices.delete')
                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }} permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">
                                                Delete
                                            </button>
                                        </form>
@endcan
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
                        <p><span class="font-semibold">Brand:</span> {{ $invoice->brand ?: ($invoice->customer?->company ?: ($invoice->customer?->name ?? '—')) }}</p>
                        @if($invoice->brand && $invoice->customer)
                            <p><span class="font-semibold">Customer:</span> {{ $invoice->customer->name }}</p>
                        @endif
                        <p><span class="font-semibold">Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><span class="font-semibold">Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}</p>
                        <p><span class="font-semibold">Total:</span> ₹{{ number_format($invoice->total_amount, 2) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <a href="{{ route('invoices.show', $invoice) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        @if(in_array($invoice->status, ['draft', 'sent']) && $invoice->payments_count === 0)
                            <a href="{{ route('invoices.edit', $invoice) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                        @endif
                        <a href="{{ route('invoices.pdf', $invoice) }}"
                            download="{{ $invoice->getPdfFilename() }}"
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
</div>

<script>
function toggleFilters() {
    const panel = document.getElementById('filters-panel');
    const arrow = document.getElementById('filter-arrow');
    if (!panel) return;
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        if (arrow) arrow.classList.add('rotate-180');
    } else {
        panel.style.display = 'none';
        if (arrow) arrow.classList.remove('rotate-180');
    }
}
</script>
@endsection