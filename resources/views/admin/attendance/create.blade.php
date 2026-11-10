@extends('layouts.app')

@section('title', 'Mark Employee Attendance')
@section('page-title', 'Mark Employee Attendance')

@section('content')
<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80 gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('admin.attendance.index', ['date' => $date]) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Attendance</a>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Mark Attendance</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Mark Attendance
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Record check-in, check-out and statuses for <span class="font-semibold text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</span>.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.attendance.index', ['date' => $date]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Attendance</span>
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were some problems with your submission</h3>
                        <ul class="mt-2 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <!-- Quick Toolbar -->
            <div class="bg-gray-50/70 dark:bg-gray-700/40 rounded-xl p-4 sm:p-5 mb-6 border border-gray-200/80 dark:border-gray-600 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mr-1">Quick Actions:</span>
                    <button type="button" onclick="setAllStatus('present', '09:30', '18:30')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                        ✓ Mark All Present (9:30 - 18:30)
                    </button>
                    <button type="button" onclick="clearAll()"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-gray-200/80 text-gray-700 hover:bg-gray-300/80 dark:bg-gray-600 dark:text-gray-200 transition-colors">
                        Clear Inputs
                    </button>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Total Employees: <span class="font-bold text-gray-900 dark:text-white">{{ $employees->count() }}</span>
                </div>
            </div>

            <!-- Employees Attendance Table -->
            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 mb-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status <span class="text-red-500">*</span></th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks / Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($employees as $emp)
                                @php
                                    $att = $attendances->get($emp->id);
                                    $currentStatus = old("records.{$emp->id}.status", $att?->status);
                                    $checkIn = old("records.{$emp->id}.check_in", $att && $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '');
                                    $checkOut = old("records.{$emp->id}.check_out", $att && $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '');
                                    $notes = old("records.{$emp->id}.notes", $att?->notes ?? '');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                            {{ $emp->name }}
                                            @if ($emp->emp_id)
                                                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ $emp->emp_id }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $emp->department ? ucfirst($emp->department) : 'No dept' }}
                                        </div>
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <select name="records[{{ $emp->id }}][status]" id="status_{{ $emp->id }}"
                                            class="status-select shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-3 text-xs dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Select --</option>
                                            <option value="present" {{ $currentStatus === 'present' ? 'selected' : '' }}>Present</option>
                                            <option value="late" {{ $currentStatus === 'late' ? 'selected' : '' }}>Late</option>
                                            <option value="half_day" {{ $currentStatus === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                            <option value="on_leave" {{ $currentStatus === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                            <option value="absent" {{ $currentStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                                        </select>
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <input type="time" name="records[{{ $emp->id }}][check_in]" id="in_{{ $emp->id }}"
                                            value="{{ $checkIn }}"
                                            class="checkin-input shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-2.5 text-xs font-mono dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <input type="time" name="records[{{ $emp->id }}][check_out]" id="out_{{ $emp->id }}"
                                            value="{{ $checkOut }}"
                                            class="checkout-input shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-2.5 text-xs font-mono dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <input type="text" name="records[{{ $emp->id }}][notes]" id="note_{{ $emp->id }}"
                                            value="{{ $notes }}" placeholder="Optional remarks..."
                                            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-3 text-xs dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-60 focus:ring-2 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No active employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.attendance.index', ['date' => $date]) }}"
                    class="order-2 sm:order-1 text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="order-1 sm:order-2 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Save Attendance Records</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setAllStatus(status, checkIn, checkOut) {
        document.querySelectorAll('.status-select').forEach(function(sel) {
            sel.value = status;
        });
        if (checkIn) {
            document.querySelectorAll('.checkin-input').forEach(function(inp) {
                if (!inp.value) inp.value = checkIn;
            });
        }
        if (checkOut) {
            document.querySelectorAll('.checkout-input').forEach(function(inp) {
                if (!inp.value) inp.value = checkOut;
            });
        }
    }

    function clearAll() {
        document.querySelectorAll('.status-select').forEach(function(sel) {
            sel.value = '';
        });
        document.querySelectorAll('.checkin-input').forEach(function(inp) {
            inp.value = '';
        });
        document.querySelectorAll('.checkout-input').forEach(function(inp) {
            inp.value = '';
        });
        document.querySelectorAll('input[id^="note_"]').forEach(function(inp) {
            inp.value = '';
        });
    }
</script>
@endsection
