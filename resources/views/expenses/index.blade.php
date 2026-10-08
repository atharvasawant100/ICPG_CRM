@extends('layouts.app')

@section('title', $title ?? 'Expenses')
@section('page-title', $title ?? 'Expenses')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">
            {{ $title ?? 'Expenses' }}
        </h2>
        <div class="flex flex-row gap-3">
            <a href="{{ route('expenses.create') }}"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 text-center">
                Add New Expense
            </a>
            <button type="button" onclick="toggleFilters()"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                <span>Filters</span>
                <svg id="filter-arrow"
                    class="w-4 h-4 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filters Panel (Hidden by default, show if filters are active) -->
    <div id="filters-panel"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700"
        style="display: {{ request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to']) ? 'block' : 'none' }};">
        <form method="GET"
            action="{{ request()->routeIs('expenses.pending') ? route('expenses.pending') : route('expenses.index') }}"
            class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Description, Category..."
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                    <select name="status" id="status"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category_id"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category</label>
                    <select name="category_id" id="category_id"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
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
                    <label for="date_from" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Date
                        From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Date
                        To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>


            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Apply Filters
                </button>
                <a href="{{ request()->routeIs('expenses.pending') ? route('expenses.pending') : route('expenses.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Clear
                </a>
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
                                {{ $expense->category?->name ?? '—' }}
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
                                {{ $expense->user?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('expenses.show', $expense) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 mr-3 font-semibold">View</a>
                                @if($expense->isPending())
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 mr-3 font-semibold">Edit</a>
                                    @if(auth()->user()->isAdmin())
                                        <button onclick="approveExpense({{ $expense->id }})"
                                            class="text-green-600 dark:text-green-400 hover:text-green-800 mr-3 font-semibold">Approve</button>
                                        <button onclick="rejectExpense({{ $expense->id }})"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 font-semibold">Reject</button>
                                    @endif
                                @endif
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
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $expense->category?->name ?? '—' }}</p>
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
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('expenses.show', $expense) }}"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 text-sm font-semibold">View</a>
                        @if($expense->isPending())
                            <a href="{{ route('expenses.edit', $expense) }}"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 text-sm font-semibold">Edit</a>
                            @if(auth()->user()->isAdmin())
                                <button onclick="approveExpense({{ $expense->id }})"
                                    class="text-green-600 dark:text-green-400 hover:text-green-800 text-sm font-semibold">Approve</button>
                                <button onclick="rejectExpense({{ $expense->id }})"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 text-sm font-semibold">Reject</button>
                            @endif
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

    <!-- Approval Modal (Admin only) -->
    @if(auth()->user()->isAdmin())
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
                            class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm flex-1">
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

            function toggleFilters() {
                const panel = document.getElementById('filters-panel');
                const arrow = document.getElementById('filter-arrow');

                if (panel.style.display === 'none' || panel.style.display === '') {
                    panel.style.display = 'block';
                    arrow.classList.add('rotate-180');
                } else {
                    panel.style.display = 'none';
                    arrow.classList.remove('rotate-180');
                }
            }
        </script>
    @endif
@endsection