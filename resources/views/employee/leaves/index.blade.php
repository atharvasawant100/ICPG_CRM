@extends('layouts.app')

@section('title', 'My Leaves')
@section('page-title', 'My Leaves')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                My Leaves
            </h1>
        </div>

        <button type="button" onclick="openApplyModal()"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Apply for Leave</span>
        </button>
    </div>

    <!-- Leave Balance Quota Cards -->
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
            Annual Quotas & Available Balances ({{ now()->year }})
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($balances as $bal)
                @php
                    $percent = $bal->total_allocated > 0 ? min(100, round(($bal->remaining_days / $bal->total_allocated) * 100)) : 0;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $bal->leaveType->name ?? 'Leave Type' }}</span>
                            <span class="text-[11px] font-semibold text-gray-400">{{ $bal->leaveType->code ?? '' }}</span>
                        </div>
                        <div class="flex items-baseline justify-between mt-3">
                            <div>
                                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $bal->remaining_days }}</span>
                                <span class="text-xs text-gray-400 font-medium">available</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $bal->used_days }} / {{ $bal->total_allocated }} used
                            </span>
                        </div>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 mt-3 overflow-hidden">
                        <div class="bg-teal-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-4 bg-white dark:bg-gray-800 rounded-xl text-center text-sm text-gray-400">
                    No leave quotas configured for your profile yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- My Leave Applications History -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                My Leave History
            </h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $myLeaves->total() }} requests</span>
        </div>

        @if($myLeaves->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">You haven't applied for any leaves yet.</p>
                <p class="text-xs text-gray-400 mt-1">Need time off? Click &ldquo;Apply for Leave&rdquo; above to submit a request.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-750 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold">
                            <th class="py-3 px-4">Leave Type</th>
                            <th class="py-3 px-4">Duration & Dates</th>
                            <th class="py-3 px-4">Reason</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Reviewer / Remarks</th>
                            <th class="py-3 px-4">Applied Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($myLeaves as $leave)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $leave->leaveType->name ?? 'Leave' }}
                                    @if($leave->is_half_day)
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                            Half Day ({{ $leave->half_day_type === 'first_half' ? '1st Half' : '2nd Half' }})
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}</span>
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}
                                        @if($leave->start_date !== $leave->end_date)
                                            &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                    {{ $leave->reason }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($leave->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Approved
                                        </span>
                                    @elseif($leave->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Rejected
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400">
                                    @if($leave->approver)
                                        <p class="font-medium text-gray-700 dark:text-gray-300">{{ $leave->approver->name }}</p>
                                    @endif
                                    @if($leave->admin_remarks)
                                        <p class="text-[11px] italic text-gray-500 mt-0.5">&ldquo;{{ $leave->admin_remarks }}&rdquo;</p>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-gray-400 whitespace-nowrap">
                                    {{ $leave->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $myLeaves->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Apply for Leave -->
<div id="applyLeaveModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 transform transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Apply for Time Off
            </h3>
            <button type="button" onclick="closeApplyModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('my-leaves.store') }}" method="POST" class="space-y-4 mt-4">
            @csrf

            <!-- Leave Type -->
            <div>
                <label for="leave_type_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Leave Type <span class="text-rose-500">*</span>
                </label>
                <select name="leave_type_id" id="leave_type_id" required
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Select a Leave Type...</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Start Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" required min="{{ now()->toDateString() }}"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        End Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" required min="{{ now()->toDateString() }}"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
            </div>

            <!-- Half Day Toggle -->
            <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg border border-gray-100 dark:border-gray-700">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_half_day" id="is_half_day" value="1" onchange="toggleHalfDay(this.checked)"
                        class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">This is a Half-Day Leave</span>
                </label>

                <div id="halfDayOptions" class="mt-2.5 hidden">
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Half Day Period:</label>
                    <div class="flex items-center gap-4 text-xs text-gray-700 dark:text-gray-300">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="half_day_type" value="first_half" checked class="text-teal-600 focus:ring-teal-500">
                            First Half (Morning)
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="half_day_type" value="second_half" class="text-teal-600 focus:ring-teal-500">
                            Second Half (Afternoon)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Reason for Leave <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" required placeholder="Explain the reason for taking leave..."
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 p-3 focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="closeApplyModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApplyModal() {
        document.getElementById('applyLeaveModal').classList.remove('hidden');
    }
    function closeApplyModal() {
        document.getElementById('applyLeaveModal').classList.add('hidden');
    }
    function toggleHalfDay(checked) {
        const container = document.getElementById('halfDayOptions');
        if (checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endsection
