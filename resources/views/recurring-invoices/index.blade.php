@extends('layouts.app')

@section('title', 'Recurring Invoice Templates')
@section('page-title', 'Recurring Invoice Templates')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Recurring Invoice Templates</h2>
        @can('recurring_invoices.create')
        <a href="{{ route('recurring-invoices.create') }}"
            class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
            Create Template
        </a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Template
                            Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Frequency
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Next
                            Generation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total
                            Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('recurring-invoices.show', $template) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-medium">{{ $template->name }}</a>
                                    @if(isset($template->invoices_count) && $template->invoices_count > 0)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300"
                                            title="{{ $template->invoices_count }} invoices generated">
                                            {{ $template->invoices_count }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $template->customer->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ ucfirst($template->frequency) }}
                                @if($template->frequency === 'custom')
                                    ({{ $template->custom_interval_days }} days)
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $nextDate = $template->next_generation_date;
                                    $today = \Carbon\Carbon::today();
                                    $isDue = $nextDate->lte($today);
                                    $isUpcoming = $nextDate->diffInDays($today) <= 3 && !$isDue;
                                @endphp

                                @if($isDue && $template->is_active)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300">
                                        🔴 Due: {{ $nextDate->format('M d, Y') }}
                                    </span>
                                @elseif($isUpcoming && $template->is_active)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300">
                                        ⚠️ Soon: {{ $nextDate->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-400">{{ $nextDate->format('M d, Y') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                ₹{{ number_format($template->subtotal + $template->tax_amount - $template->discount_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('recurring-invoices.show', $template) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                        View
                                    </a>
                                    @can('recurring_invoices.edit')
                                        <a href="{{ route('recurring-invoices.edit', $template) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                            Edit
                                        </a>
                                    @endcan
                                    @if($template->is_active)
                                        @can('recurring_invoices.edit')
                                            <form action="{{ route('recurring-invoices.generate', $template) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Generate an invoice immediately for &quot;{{ addslashes($template->name) }}&quot; and send the invoice email to the customer?');">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $isDue ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm' : 'bg-purple-100 text-purple-700 hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:bg-purple-900/60' }} transition-colors"
                                                    title="Generate invoice and send email now">
                                                    ⚡ Generate
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                    @can('recurring_invoices.toggle')
                                        <form action="{{ route('recurring-invoices.toggle', $template) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to {{ $template->is_active ? 'deactivate' : 'activate' }} this template?');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $template->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60' : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60' }} transition-colors">
                                                {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No recurring invoice templates found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($templates as $template)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('recurring-invoices.show', $template) }}"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-semibold text-sm">{{ $template->name }}</a>
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
                    <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400 mb-3">
                        <p><span class="font-semibold">Customer:</span> {{ $template->customer->name }}</p>
                        <p><span class="font-semibold">Frequency:</span>
                            {{ ucfirst($template->frequency) }}@if($template->frequency === 'custom')
                            ({{ $template->custom_interval_days }} days)@endif</p>
                        <p><span class="font-semibold">Next Generation:</span>
                            {{ $template->next_generation_date->format('M d, Y') }}</p>
                        <p><span class="font-semibold">Total:</span>
                            ₹{{ number_format($template->subtotal + $template->tax_amount - $template->discount_amount, 2) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <a href="{{ route('recurring-invoices.show', $template) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        @can('recurring_invoices.edit')
                            <a href="{{ route('recurring-invoices.edit', $template) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                        @endcan
                        @can('recurring_invoices.toggle')
                            <form action="{{ route('recurring-invoices.toggle', $template) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to {{ $template->is_active ? 'deactivate' : 'activate' }} this template?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $template->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60' : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60' }} transition-colors">
                                    {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No recurring invoice templates found.</p>
            @endforelse
        </div>

        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
@endsection