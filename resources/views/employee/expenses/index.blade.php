@extends('layouts.app')

@section('title', 'My Expenses')
@section('page-title', 'My Expenses')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                My Expense Claims
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track and review your submitted company reimbursement claims and statuses.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Expenses</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <button type="button" onclick="openExpenseModal()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all flex items-center gap-1.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Request Reimbursement</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Claimed -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Claimed</span>
                <span class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-gray-900 dark:text-white">
                    ₹{{ number_format($totalClaimed, 2) }}
                </div>
                <div class="text-xs text-gray-400 mt-0.5">Across all submitted claims</div>
            </div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-amber-200/60 dark:border-amber-900/40 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Pending Review</span>
                <span class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                    ₹{{ number_format($totalPending, 2) }}
                </div>
                <div class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-0.5 font-medium flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ $countPending }} {{ Str::plural('request', $countPending) }} awaiting admin
                </div>
            </div>
        </div>

        <!-- Approved / Reimbursed -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-emerald-200/60 dark:border-emerald-900/40 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Approved / Reimbursed</span>
                <span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                    ₹{{ number_format($totalApproved, 2) }}
                </div>
                <div class="text-xs text-emerald-700/80 dark:text-emerald-400/80 mt-0.5 font-medium flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $countApproved }} approved {{ Str::plural('claim', $countApproved) }}
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-rose-200/60 dark:border-rose-900/40 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Rejected</span>
                <span class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
                    ₹{{ number_format($totalRejected, 2) }}
                </div>
                <div class="text-xs text-rose-700/80 dark:text-rose-400/80 mt-0.5 font-medium flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    {{ $countRejected }} rejected {{ Str::plural('claim', $countRejected) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('my-expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div>
                <label for="search" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Search description..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                <select name="status" id="status"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category_id" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Category</label>
                <select name="category_id" id="category_id"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                    Apply Filters
                </button>
                @if (request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']))
                <a href="{{ route('my-expenses.index') }}"
                    class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Expense Requests History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Reimbursement Requests History
            </h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $expenses->total() }} claims found</span>
        </div>

        @if($expenses->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No expense reimbursement requests found.</p>
                <p class="text-xs text-gray-400 mt-1">Need to claim a work-related expense? Click &ldquo;Request Reimbursement&rdquo; above to submit a claim.</p>
            </div>
        @else
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-750 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Receipt</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Admin Remarks</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($expenses as $expense)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/50 transition-colors">
                                <!-- Date -->
                                <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $expense->expense_date->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        Submitted {{ $expense->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-650">
                                            {{ $expense->category?->name ?? 'General' }}
                                        </span>
                                        @if($expense->ai_tool)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800" title="AI Tool Purchased">
                                                <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                {{ $expense->ai_tool }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Description -->
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300 max-w-xs">
                                    <p class="truncate font-medium">{{ $expense->description }}</p>
                                </td>

                                <!-- Amount -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-bold text-gray-900 dark:text-white text-sm">
                                    ₹{{ number_format($expense->amount, 2) }}
                                </td>

                                <!-- Receipt -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($expense->receipt)
                                        <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 hover:bg-indigo-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Bill
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-[11px]">No receipt</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($expense->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Approved
                                        </span>
                                    @elseif($expense->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    @endif
                                </td>

                                <!-- Admin Remarks -->
                                <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 max-w-xs">
                                    @if($expense->status === 'rejected' && $expense->rejection_reason)
                                        <div class="text-rose-600 dark:text-rose-400 font-medium text-xs truncate" title="{{ $expense->rejection_reason }}">
                                            Reason: {{ $expense->rejection_reason }}
                                        </div>
                                    @elseif($expense->approver)
                                        <div class="text-xs text-gray-700 dark:text-gray-300">
                                            By {{ $expense->approver->name }}
                                        </div>
                                        @if($expense->approved_at)
                                            <div class="text-[10px] text-gray-400">
                                                {{ $expense->approved_at->format('d M Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- View Details Modal Trigger -->
                                        <button type="button"
                                            onclick='showExpenseDetails(@json($expense), @json($expense->category?->name ?? "General"), @json($expense->approver?->name ?? null), @json($expense->receipt ? asset("storage/" . $expense->receipt) : null))'
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors"
                                            title="View Details">
                                            View
                                        </button>

                                        <!-- Cancel Request (Only if Pending) -->
                                        @if($expense->isPending())
                                            <form action="{{ route('my-expenses.destroy', $expense) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to cancel this reimbursement claim?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors"
                                                    title="Cancel Claim">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($expenses as $expense)
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-start justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $expense->category?->name ?? 'General' }}
                                    </span>
                                    @if($expense->ai_tool)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                            {{ $expense->ai_tool }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $expense->expense_date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base font-bold text-gray-900 dark:text-white">
                                    ₹{{ number_format($expense->amount, 2) }}
                                </div>
                                <div class="mt-1">
                                    @if($expense->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                            Approved
                                        </span>
                                    @elseif($expense->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                            Pending Review
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            {{ $expense->description }}
                        </p>

                        @if($expense->status === 'rejected' && $expense->rejection_reason)
                            <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-[11px] text-rose-700 dark:text-rose-300">
                                <strong>Rejection Reason:</strong> {{ $expense->rejection_reason }}
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                            <div>
                                @if($expense->receipt)
                                    <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank"
                                        class="text-indigo-600 dark:text-indigo-400 font-medium inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Receipt
                                    </a>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button"
                                    onclick='showExpenseDetails(@json($expense), @json($expense->category?->name ?? "General"), @json($expense->approver?->name ?? null), @json($expense->receipt ? asset("storage/" . $expense->receipt) : null))'
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                    Details
                                </button>
                                @if($expense->isPending())
                                    <form action="{{ route('my-expenses.destroy', $expense) }}" method="POST"
                                        onsubmit="return confirm('Cancel this claim?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Request Expense Reimbursement -->
<div id="requestExpenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 transform transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Request Expense Reimbursement
            </h3>
            <button type="button" onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('my-expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
            @csrf

            <!-- Expense Category -->
            <div>
                <label for="modal_category_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Expense Category <span class="text-rose-500">*</span>
                </label>
                <select name="category_id" id="modal_category_id" required onchange="toggleModalAiToolField(true)"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Select an Expense Category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- AI Tool Name (Shown when category is AI tools) -->
            <div id="modal_ai_tool_container" class="hidden">
                <label for="modal_ai_tool" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    AI Tool Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="ai_tool" id="modal_ai_tool"
                        placeholder="e.g., Claude, ChatGPT, GitHub Copilot"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Specify which AI tool was purchased (e.g., Claude, ChatGPT)</p>
            </div>

            <!-- Amount & Date Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Amount (₹) -->
                <div>
                    <label for="modal_amount" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Amount (₹) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-bold text-sm">₹</span>
                        <input type="number" step="0.01" min="0.01" name="amount" id="modal_amount" required
                            placeholder="0.00"
                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 pl-8 pr-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-semibold">
                    </div>
                </div>

                <!-- Expense Date -->
                <div>
                    <label for="modal_expense_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Expense Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="expense_date" id="modal_expense_date" required
                        value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="modal_description" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Business Purpose / Description <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" id="modal_description" rows="3" required
                    placeholder="Provide details about the business purpose of this expense (e.g., client lunch, travel to meeting, software license)..."
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <!-- Receipt Upload -->
            <div>
                <label for="modal_receipt" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Attach Bill / Invoice / Receipt
                </label>
                <input type="file" name="receipt" id="modal_receipt" accept=".jpeg,.png,.jpg,.pdf,.webp"
                    class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/50 dark:file:text-indigo-300 hover:file:bg-indigo-100 border border-gray-300 dark:border-gray-600 rounded-lg p-1 bg-white dark:bg-gray-700 cursor-pointer">
                <p class="text-[11px] text-gray-400 mt-1">Accepted: JPG, PNG, PDF, WEBP up to 10MB.</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="closeExpenseModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md hover:shadow-lg transition-all transform active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Submit Claim</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Expense Details -->
<div id="viewExpenseDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 transform transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Claim Details
            </h3>
            <button type="button" onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-4 mt-4 text-xs">
            <!-- Amount & Status Banner -->
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Claim Amount</span>
                    <span id="detail-amount" class="text-xl font-extrabold text-gray-900 dark:text-white"></span>
                </div>
                <div id="detail-status-badge"></div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Category</span>
                    <span id="detail-category" class="font-semibold text-gray-900 dark:text-white"></span>
                </div>
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Expense Date</span>
                    <span id="detail-date" class="font-semibold text-gray-900 dark:text-white"></span>
                </div>
            </div>

            <!-- AI Tool Details if present -->
            <div id="detail-ai-tool-box" class="hidden p-3 rounded-lg bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60">
                <span class="text-indigo-600 dark:text-indigo-400 block text-[10px] uppercase font-bold mb-0.5">AI Tool Purchased</span>
                <p id="detail-ai-tool" class="text-indigo-950 dark:text-indigo-200 font-semibold"></p>
            </div>

            <!-- Description -->
            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
                <span class="text-gray-400 block text-[10px] uppercase font-bold mb-1">Business Purpose / Description</span>
                <p id="detail-description" class="text-gray-800 dark:text-gray-200 leading-relaxed"></p>
            </div>

            <!-- Rejection Reason if Rejected -->
            <div id="detail-rejection-box" class="hidden p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900">
                <span class="text-rose-700 dark:text-rose-400 block text-[11px] font-bold uppercase mb-1">Reason for Rejection</span>
                <p id="detail-rejection-reason" class="text-rose-800 dark:text-rose-300 font-medium"></p>
            </div>

            <!-- Reviewer Info -->
            <div id="detail-reviewer-box" class="hidden p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Reviewed By</span>
                    <span id="detail-approver" class="font-semibold text-gray-900 dark:text-white"></span>
                </div>
                <span id="detail-approved-at" class="text-gray-500 text-[11px]"></span>
            </div>

            <!-- Receipt Link -->
            <div id="detail-receipt-container" class="hidden">
                <span class="text-gray-400 block text-[10px] uppercase font-bold mb-1">Attached Receipt / Bill</span>
                <a id="detail-receipt-link" href="#" target="_blank"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300 text-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Open Uploaded Document</span>
                </a>
            </div>
        </div>

        <div class="mt-6 flex justify-end pt-3 border-t border-gray-100 dark:border-gray-700">
            <button type="button" onclick="closeDetailsModal()"
                class="px-5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xl text-xs font-semibold text-gray-800 dark:text-white transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function openExpenseModal() {
        const modal = document.getElementById('requestExpenseModal');
        if (modal) modal.classList.remove('hidden');
        toggleModalAiToolField(false);
    }

    function closeExpenseModal() {
        const modal = document.getElementById('requestExpenseModal');
        if (modal) modal.classList.add('hidden');
    }

    function toggleModalAiToolField(isUserChange = false) {
        const select = document.getElementById('modal_category_id');
        const container = document.getElementById('modal_ai_tool_container');
        const input = document.getElementById('modal_ai_tool');
        if (!select || !container) return;

        const selectedOption = select.options[select.selectedIndex];
        const categoryName = selectedOption ? (selectedOption.getAttribute('data-name') || selectedOption.text || '').toLowerCase() : '';

        if (categoryName.includes('ai tool')) {
            container.classList.remove('hidden');
            if (input) input.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            if (input) {
                input.removeAttribute('required');
                if (isUserChange) {
                    input.value = '';
                }
            }
        }
    }

    function showExpenseDetails(expense, categoryName, approverName, receiptUrl) {
        document.getElementById('detail-amount').textContent = '₹' + parseFloat(expense.amount).toFixed(2);
        document.getElementById('detail-category').textContent = categoryName || 'General';

        // AI Tool
        const aiToolBox = document.getElementById('detail-ai-tool-box');
        if (expense.ai_tool) {
            aiToolBox.classList.remove('hidden');
            document.getElementById('detail-ai-tool').textContent = expense.ai_tool;
        } else {
            aiToolBox.classList.add('hidden');
        }
        
        const dateObj = new Date(expense.expense_date);
        document.getElementById('detail-date').textContent = isNaN(dateObj) ? expense.expense_date : dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        document.getElementById('detail-description').textContent = expense.description || '—';

        // Badge
        const badgeContainer = document.getElementById('detail-status-badge');
        if (expense.status === 'approved') {
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Approved</span>';
        } else if (expense.status === 'rejected') {
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Rejected</span>';
        } else {
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Pending Review</span>';
        }

        // Rejection reason
        const rejBox = document.getElementById('detail-rejection-box');
        if (expense.status === 'rejected' && expense.rejection_reason) {
            rejBox.classList.remove('hidden');
            document.getElementById('detail-rejection-reason').textContent = expense.rejection_reason;
        } else {
            rejBox.classList.add('hidden');
        }

        // Reviewer
        const revBox = document.getElementById('detail-reviewer-box');
        if (approverName) {
            revBox.classList.remove('hidden');
            document.getElementById('detail-approver').textContent = approverName;
            document.getElementById('detail-approved-at').textContent = expense.approved_at ? new Date(expense.approved_at).toLocaleDateString() : '';
        } else {
            revBox.classList.add('hidden');
        }

        // Receipt
        const receiptContainer = document.getElementById('detail-receipt-container');
        const receiptLink = document.getElementById('detail-receipt-link');
        if (receiptUrl) {
            receiptContainer.classList.remove('hidden');
            receiptLink.href = receiptUrl;
        } else {
            receiptContainer.classList.add('hidden');
        }

        document.getElementById('viewExpenseDetailsModal').classList.remove('hidden');
    }

    function closeDetailsModal() {
        document.getElementById('viewExpenseDetailsModal').classList.add('hidden');
    }

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

    // Close modals on clicking outside or ESC
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExpenseModal();
            closeDetailsModal();
        }
    });
</script>
@endsection
