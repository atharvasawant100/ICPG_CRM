@extends('layouts.app')

@section('title', $customer->name . ' - Customer Details')
@section('page-title', 'Customer Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
            <a href="{{ route('customers.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Customers
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span class="text-gray-900 dark:text-gray-200 font-semibold">{{ $customer->name }}</span>
        </div>

    </div>

    <!-- Hero Profile Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <!-- Left: Avatar, Name & Metadata -->
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gray-900 dark:bg-gray-700 text-white font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ $customer->name }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $customer->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ ucfirst($customer->status) }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                            #CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-gray-500 dark:text-gray-400 mt-1">
                        @if($customer->company)
                            <span class="flex items-center gap-1 font-medium text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $customer->company }}
                            </span>
                        @endif

                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Client since {{ $customer->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                @can('invoices.create')
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Invoice
                </a>
                @endcan

                @can('customers.edit')
                <a href="{{ route('customers.edit', $customer) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endcan

                @can('customers.delete')
                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to delete customer {{ $customer->name }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <!-- Financial Key Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Invoiced -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Billed</p>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">₹{{ number_format($totalInvoiced, 2) }}</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $invoicesCount }} lifetime {{ Str::plural('invoice', $invoicesCount) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-750 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
        </div>

        <!-- Paid / Collected -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collected</p>
                <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">₹{{ number_format($totalPaid, 2) }}</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Paid in full</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Pending / Outstanding -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending / Due</p>
                <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">₹{{ number_format($totalOutstanding, 2) }}</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Awaiting settlement</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Brands Count -->
        @php
            $brandList = $customer->brands_list;
            $brandCount = count($brandList);
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Associated Brands</p>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $brandCount }}</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Configured under customer</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-750 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left / Primary Column (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Contact & Business Information (Clean Definition Table) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                    <h2 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Customer &amp; Business Information
                    </h2>
                    @can('customers.edit')
                    <a href="{{ route('customers.edit', $customer) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Edit Info &rarr;
                    </a>
                    @endcan
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700/80">
                    <!-- Row 1: Email & Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700/80">
                        <div class="px-6 py-4">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Email Address</span>
                            <a href="mailto:{{ $customer->email }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $customer->email }}
                            </a>
                        </div>
                        <div class="px-6 py-4">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Phone Number</span>
                            @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $customer->phone }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400 italic">Not provided</span>
                            @endif
                        </div>
                    </div>

                    <!-- Row 2: Company & Associated Brands -->
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700/80">
                        <div class="px-6 py-4">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Company / Organization</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $customer->company ?: '—' }}
                            </span>
                        </div>
                        <div class="px-6 py-4">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1.5">Associated Brands</span>
                            @if($brandCount > 0)
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($brandList as $b)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            {{ $b }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">No brands specified</span>
                            @endif
                        </div>
                    </div>

                    <!-- Row 3: Billing Address -->
                    <div class="px-6 py-4">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Billing &amp; Office Address</span>
                        @if($customer->address)
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-relaxed flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $customer->address }}
                            </p>
                        @else
                            <span class="text-sm text-gray-400 italic">No address on file</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Invoices & Billing History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Invoices &amp; Billing History
                        </h2>
                        <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full border border-gray-200 dark:border-gray-600">
                            {{ $invoicesCount }}
                        </span>
                    </div>

                    @can('invoices.create')
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Invoice
                    </a>
                    @endcan
                </div>

                @if($invoices->count() > 0)
                    <!-- Desktop Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/75 dark:bg-gray-750">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Brand</th>
                                    <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800 text-sm">
                                @foreach($invoices as $inv)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition-colors">
                                        <td class="px-6 py-3.5 whitespace-nowrap font-medium">
                                            <a href="{{ route('invoices.show', $inv) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                            {{ $inv->brand ?: ($customer->company ?: '—') }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            {{ $inv->invoice_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                            ₹{{ number_format($inv->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap">
                                            <x-invoice-status-badge :status="$inv->status" />
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('invoices.show', $inv) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                                    View
                                                </a>
                                                @can('invoices.export')
                                                <a href="{{ route('invoices.pdf', $inv) }}"
                                                    download="{{ $inv->getPdfFilename() }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                                    PDF
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($invoicesCount > 6)
                        <div class="px-6 py-3 bg-gray-50/50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 text-right">
                            <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}"
                                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                View all {{ $invoicesCount }} customer invoices &rarr;
                            </a>
                        </div>
                    @endif
                @else
                    <div class="py-10 px-6 text-center text-gray-500 dark:text-gray-400 text-xs">
                        No invoices generated yet for this customer.
                    </div>
                @endif
            </div>

        </div>

        <!-- Right / Sidebar Column (1 col) -->
        <div class="space-y-6">

            <!-- Card 1: Designated Client Representative -->
            @php
                $designatedName = $customer->designated_person ?: ($customer->assignedPerson?->name ?: ($customer->creator?->name ?? 'Unassigned'));
                $designatedUser = $customer->assignedPerson ?: $customer->creator;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Designated Representative
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        Primary Contact
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-lg bg-gray-900 dark:bg-gray-700 text-white font-bold text-base flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($designatedName, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                {{ $designatedName }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $designatedUser?->designation ?: ($designatedUser?->role ? ucfirst($designatedUser->role) : 'Account Executive') }}
                            </p>
                        </div>
                    </div>

                    @if($designatedUser?->email)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700">
                            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 block">Direct Email</span>
                            <a href="mailto:{{ $designatedUser->email }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline break-all block mt-0.5">
                                {{ $designatedUser->email }}
                            </a>
                        </div>
                    @endif

                    <div class="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span>Role:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $designatedUser?->role ? ucfirst($designatedUser->role) : 'Staff' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Department:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $designatedUser?->department ? ucfirst($designatedUser->department) : 'Client Management' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Account Record Metadata -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80">
                    <h3 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Account Details</h3>
                </div>

                <div class="p-6 space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Client Reference:</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-white">#CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Account Status:</span>
                        <span class="font-semibold {{ $customer->status === 'active' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created By:</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $customer->creator?->name ?? 'System' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created On:</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $customer->created_at->format('M d, Y · h:i A') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Last Activity:</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $customer->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection