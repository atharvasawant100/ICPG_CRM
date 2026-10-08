@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@section('content')
    <div class="space-y-4 sm:space-y-5 md:space-y-6">
        <!-- Expense Information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
            <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Expense Details</h2>
                <div class="flex flex-wrap gap-2">
                    @if($expense->isPending())
                        <a href="{{ route('expenses.edit', $expense) }}"
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">Edit</a>
                    @endif
                    <a href="{{ route('expenses.index') }}"
                        class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">Back</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->category->name }}</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Amount</label>
                    <p class="text-gray-800 dark:text-gray-100 text-lg font-bold">₹{{ number_format($expense->amount, 2) }}</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Expense Date</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->expense_date->format('M d, Y') }}</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                    @if($expense->status === 'approved')
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                            Approved
                        </span>
                    @elseif($expense->status === 'rejected')
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300">
                            Rejected
                        </span>
                    @else
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300">
                            Pending
                        </span>
                    @endif
                </div>

                @if($expense->description)
                    <div class="md:col-span-2 p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description</label>
                        <p class="text-gray-800 dark:text-gray-100 whitespace-pre-wrap font-medium">{{ $expense->description }}</p>
                    </div>
                @endif



                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Created By</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->user->name }}</p>
                </div>

                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Created On</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->created_at->format('M d, Y h:i A') }}</p>
                </div>

                @if($expense->isApproved() || $expense->isRejected())
                    <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Reviewed By</label>
                        <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->approver->name }}</p>
                    </div>

                    <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Reviewed On</label>
                        <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->approved_at->format('M d, Y h:i A') }}</p>
                    </div>
                @endif

                @if($expense->isRejected() && $expense->rejection_reason)
                    <div class="md:col-span-2 p-4 bg-gradient-to-br from-red-50 to-white dark:from-red-900/30 dark:to-gray-800 rounded-xl border border-red-200 dark:border-red-800/50">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Rejection Reason</label>
                        <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $expense->rejection_reason }}</p>
                    </div>
                @endif

                @if($expense->receipt)
                    <div class="md:col-span-2 p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Receipt</label>
                        <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" class="inline-block">
                            <img src="{{ asset('storage/' . $expense->receipt) }}" alt="Receipt"
                                class="max-w-md rounded-xl shadow-lg">
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Approval Section (requires expenses.approve, for pending expenses) -->
        @if($expense->isPending())
            @can('expenses.approve')
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Approve/Reject Expense</h3>
                <form action="{{ route('expenses.approve', $expense) }}" method="POST" id="approval-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" id="action" value="approve">

                    <div class="mb-4" id="rejection-reason-field" style="display: none;">
                        <label for="rejection_reason" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Rejection Reason
                            *</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3"
                            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="submitApproval('approve')"
                            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                            Approve
                        </button>
                        <button type="button" onclick="showRejectionField()"
                            class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
                @endcan
        @endif
    </div>

    <script>
        function showRejectionField() {
            document.getElementById('action').value = 'reject';
            document.getElementById('rejection-reason-field').style.display = 'block';
            document.getElementById('rejection_reason').required = true;
        }

        function submitApproval(action) {
            if (action === 'approve') {
                document.getElementById('action').value = 'approve';
                document.getElementById('rejection-reason-field').style.display = 'none';
                document.getElementById('rejection_reason').required = false;
            }
            document.getElementById('approval-form').submit();
        }
    </script>
@endsection
