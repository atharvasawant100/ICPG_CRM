@extends('layouts.app')

@section('title', 'Edit Attendance - ' . $attendance->user->name)
@section('page-title', 'Edit Attendance: ' . $attendance->user->name)

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.attendance.index', ['date' => $attendance->date->toDateString()]) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Attendance</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit Record</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Attendance Record</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $attendance->user->name }} &middot;
                    @if ($attendance->user->emp_id)
                        <span class="font-mono font-semibold">{{ $attendance->user->emp_id }}</span> &middot;
                    @endif
                    {{ $attendance->date->format('l, d F Y') }}
                </p>
            </div>

            <a href="{{ route('admin.attendance.index', ['date' => $attendance->date->toDateString()]) }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Attendance
            </a>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.attendance.update', $attendance) }}" class="space-y-7">
            @csrf
            @method('PUT')

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Attendance Status -->
                <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Attendance Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <select name="status" id="status" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('status') border-red-500 @enderror">
                            <option value="present" {{ old('status', $attendance->status) === 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ old('status', $attendance->status) === 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ old('status', $attendance->status) === 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="on_leave" {{ old('status', $attendance->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="absent" {{ old('status', $attendance->status) === 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Official daily status designation</p>
                </div>

                <!-- Check In Time -->
                <div>
                    <label for="check_in" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Check In Time
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input type="time" name="check_in" id="check_in"
                            value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Punch-in timestamp (24-hour)</p>
                </div>

                <!-- Check Out Time -->
                <div>
                    <label for="check_out" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Check Out Time
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <input type="time" name="check_out" id="check_out"
                            value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Punch-out timestamp (24-hour)</p>
                </div>

                @if ($attendance->total_minutes > 0)
                    <div class="md:col-span-2 p-4 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center justify-between text-xs text-blue-800 dark:text-blue-300">
                        <span class="font-medium">Current Calculated Working Time:</span>
                        <span class="font-bold text-sm font-mono text-blue-900 dark:text-blue-100">{{ $attendance->working_hours_formatted }}</span>
                    </div>
                @endif

                <!-- Remarks / Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Remarks / Correction Reason
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="notes" id="notes" rows="3" placeholder="e.g. Approved on-site client duty, forgot punch..."
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('notes', $attendance->notes) }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Reason for manual correction or override</p>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.attendance.index', ['date' => $attendance->date->toDateString()]) }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Save Changes
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection
