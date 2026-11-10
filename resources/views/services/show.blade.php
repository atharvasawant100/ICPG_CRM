@extends('layouts.app')

@section('title', 'Service Details')
@section('page-title', 'Service Details')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">{{ $service->name }}</h2>
            <p class="text-sm text-gray-600 mt-1 dark:text-gray-400">Created on {{ $service->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('services.edit', $service) }}"
                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                Edit
            </a>
            <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline"
                onsubmit="return confirm('Are you sure you want to delete this service?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Service Details -->
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Service Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Service Name</label>
                        <p class="text-gray-900 font-medium dark:text-gray-100">{{ $service->name }}</p>
                    </div>
                    @if($service->description)
                        <div>
                            <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Description</label>
                            <p class="text-gray-900 whitespace-pre-wrap dark:text-gray-100">{{ $service->description }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Rate</label>
                        <p class="text-gray-900 font-semibold text-lg dark:text-gray-100">₹{{ number_format($service->rate, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Frequency & Status -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Frequency & Status</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Frequency</label>
                        <p class="text-gray-900 font-medium dark:text-gray-100">{{ $service->frequency_label }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Status</label>
                        @if($service->is_active)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                Active
                            </span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-semibold mb-1 dark:text-gray-400">Created By</label>
                        <p class="text-gray-900 font-medium dark:text-gray-100">{{ $service->creator->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Usage in Invoices -->
    @if($service->invoiceItems->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">Used in Invoices ({{ $service->invoiceItems->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach($service->invoiceItems->take(20) as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $item->invoice) }}"
                                        class="text-blue-600 hover:text-blue-800 hover:underline font-medium dark:text-blue-400 dark:hover:text-blue-300">{{ $item->invoice->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item->invoice->customer->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item->invoice->invoice_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right dark:text-gray-100">
                                    {{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right dark:text-gray-100">
                                    ₹{{ number_format($item->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-invoice-status-badge :status="$item->invoice->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($service->invoiceItems->count() > 20)
                <p class="mt-4 text-sm text-gray-600">Showing latest 20 invoices. Total: {{ $service->invoiceItems->count() }}</p>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <p class="text-gray-500 text-center py-4 dark:text-gray-400">This service has not been used in any invoices yet.</p>
        </div>
    @endif
@endsection