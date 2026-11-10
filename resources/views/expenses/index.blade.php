@extends('layouts.app')

@section('title', $title ?? 'Expenses')
@section('page-title', $title ?? 'Expenses')

@section('content')
<div class="space-y-4">

    <!-- Top Bar with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $title ?? 'Expenses' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Track, manage, and review company expense claims and approval workflows.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Expenses</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            @can('expenses.create')
            <a href="{{ route('expenses.create') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add New Expense</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ request()->routeIs('expenses.pending') ? route('expenses.pending') : route('expenses.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Description, Category..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
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

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Apply Filters
                    </button>
                    @if (request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']))
                    <a href="{{ request()->routeIs('expenses.pending') ? route('expenses.pending') : route('expenses.index') }}"
                        class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Description</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Amount
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Created By
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $expense->category?->name ?? '—' }}</div>
                                @if($expense->ai_tool)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 mt-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        {{ $expense->ai_tool }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ Str::limit($expense->description, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800 dark:text-white">
                                ₹{{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($expense->status === 'approved')
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @elseif($expense->status === 'rejected')
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span>{{ $expense->user?->name ?? '—' }}</span>
                                    @if($expense->user?->emp_id)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $expense->user->emp_id }}
                                        </span>
                                    @endif
                                </div>
                                @if($expense->user?->designation || $expense->user?->department)
                                    <div class="text-[11px] text-gray-400">
                                        {{ $expense->user->designation ? ucwords($expense->user->designation) : '' }}
                                        {{ $expense->user->department ? '(' . ucfirst($expense->user->department) . ')' : '' }}
                                    </div>
                                @endif
                                @if($expense->receipt)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Receipt
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('expenses.show', $expense) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                                    @if($expense->isPending())
                                        @can('expenses.edit')
                                            <a href="{{ route('expenses.edit', $expense) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                                        @endcan
                                        @can('expenses.approve')
                                            <button onclick="approveExpense({{ $expense->id }})"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">Approve</button>
                                            <button onclick="rejectExpense({{ $expense->id }})"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Reject</button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($expenses as $expense)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">
                                {{ $expense->category?->name ?? '—' }}
                                @if($expense->ai_tool)
                                    <span class="text-xs font-normal text-indigo-600 dark:text-indigo-400">({{ $expense->ai_tool }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $expense->expense_date->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800 dark:text-white">₹{{ number_format($expense->amount, 2) }}</p>
                            @if($expense->status === 'approved')
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 mt-1">
                                    Approved
                                </span>
                            @elseif($expense->status === 'rejected')
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 mt-1">
                                    Rejected
                                </span>
                            @else
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 mt-1">
                                    Pending
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($expense->description)
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ Str::limit($expense->description, 100) }}</p>
                    @endif
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">Created by:
                        {{ $expense->user?->name ?? '—' }}
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('expenses.show', $expense) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        @if($expense->isPending())
                            @can('expenses.edit')
                                <a href="{{ route('expenses.edit', $expense) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                            @endcan
                            @can('expenses.approve')
                                <button onclick="approveExpense({{ $expense->id }})"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">Approve</button>
                                <button onclick="rejectExpense({{ $expense->id }})"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Reject</button>
                            @endcan
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No expenses found.</p>
            @endforelse
        </div>

        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Approval Modal (requires expenses.approve) -->
    @can('expenses.approve')
        <div id="approval-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4" id="modal-title">Approve Expense</h3>
                <form id="approval-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" id="modal-action" value="approve">

                    <div class="mb-4" id="modal-rejection-reason" style="display: none;">
                        <label for="modal_rejection_reason"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Rejection Reason
                            *</label>
                        <textarea name="rejection_reason" id="modal_rejection_reason" rows="3" required
                            class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm shadow-md hover:shadow-lg transition-all flex-1">
                            Confirm
                        </button>
                        <button type="button" onclick="closeApprovalModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm flex-1">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
                function approveExpense(expenseId) {
                    document.getElementById('modal-title').textContent = 'Approve Expense';
                    document.getElementById('modal-action').value = 'approve';
                    document.getElementById('modal-rejection-reason').style.display = 'none';
                    document.getElementById('modal_rejection_reason').required = false;
                    document.getElementById('approval-form').action = '/expenses/' + expenseId + '/approve';
                    document.getElementById('approval-modal').classList.remove('hidden');
                }

            function rejectExpense(expenseId) {
                document.getElementById('modal-title').textContent = 'Reject Expense';
                document.getElementById('modal-action').value = 'reject';
                document.getElementById('modal-rejection-reason').style.display = 'block';
                document.getElementById('modal_rejection_reason').required = true;
                document.getElementById('approval-form').action = '/expenses/' + expenseId + '/approve';
                document.getElementById('approval-modal').classList.remove('hidden');
            }

            function closeApprovalModal() {
                document.getElementById('approval-modal').classList.add('hidden');
                document.getElementById('modal_rejection_reason').value = '';
            }
        </script>
    @endcan
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
