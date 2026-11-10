@extends('layouts.app')

@section('title', 'Attendance Timing & Shift Settings')
@section('page-title', 'Attendance Settings')

@section('content')
<div class="w-full space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.attendance.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Attendance</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Settings & Policies</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Attendance & Shift Settings
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure official company shift hours, grace periods, automatic late mark triggers, half-day thresholds, and automated shift checkout policies.
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('admin.attendance.index') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Attendance
                </a>
            </div>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please review the form errors:</h3>
                    <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Settings Form -->
    <form action="{{ route('admin.attendance.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Section 1: Official Shift Timings -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">1</span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Official Office Shift Hours</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Company working hours displayed to employees</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="shift_start_time" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Shift Start Time <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="time" name="shift_start_time" id="shift_start_time" required
                                value="{{ old('shift_start_time', substr($settings->shift_start_time, 0, 5)) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">e.g. 09:30 AM</p>
                    </div>

                    <div>
                        <label for="shift_end_time" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Shift End Time <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="time" name="shift_end_time" id="shift_end_time" required
                                value="{{ old('shift_end_time', substr($settings->shift_end_time, 0, 5)) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">e.g. 06:30 PM (18:30)</p>
                    </div>
                </div>

                <div class="pt-1">
                    <label class="relative flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="block_punch_in_after_shift_end" value="1"
                            {{ old('block_punch_in_after_shift_end', $settings->block_punch_in_after_shift_end ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                Block Punch-In After Shift End Time
                            </span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                When enabled, employees cannot clock in after official office hours (e.g. after {{ \Carbon\Carbon::createFromFormat('H:i', substr($settings->shift_end_time, 0, 5))->format('h:i A') }}). Shift Closed will be displayed.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="rounded-xl bg-blue-50/70 dark:bg-blue-950/30 p-3.5 border border-blue-100 dark:border-blue-900/50 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-blue-800 dark:text-blue-200 leading-relaxed">
                        Employees logging in before the Late Mark threshold are marked <strong>Present</strong> with a full day credit.
                    </p>
                </div>
            </div>

            <!-- Section 2: Automated Late Mark & Half-Day Trigger -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">2</span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Late Mark & Half Day Login Triggers</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Automatic status assignment upon employee punch-in</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="late_mark_after" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Late Mark After <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="time" name="late_mark_after" id="late_mark_after" required
                                value="{{ old('late_mark_after', substr($settings->late_mark_after, 0, 5)) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-amber-500 focus:border-amber-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                            Clock-in after this time = <span class="font-bold">Late</span>
                        </p>
                    </div>

                    <div>
                        <label for="half_day_login_after" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Half Day Login After <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="time" name="half_day_login_after" id="half_day_login_after" required
                                value="{{ old('half_day_login_after', substr($settings->half_day_login_after, 0, 5)) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-amber-500 focus:border-amber-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1 font-medium">
                            Clock-in after this time = <span class="font-bold">Half Day</span>
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-amber-50/70 dark:bg-amber-950/30 p-3.5 border border-amber-100 dark:border-amber-900/50 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                        <strong>Example:</strong> If set to <strong>10:15</strong> and <strong>13:00</strong>: An employee logging in at 10:20 AM will automatically be marked <strong>Late</strong>. An employee logging in after 1:00 PM will automatically be marked <strong>Half Day</strong>.
                    </p>
                </div>
            </div>

            <!-- Section 3: Work Duration & Short Hours Rules -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">3</span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Minimum Work Hours Requirements</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total clocked minutes needed for full vs half day credits</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="full_day_min_hours" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Full Day Minimum Hours <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.5" min="1" max="24" name="full_day_min_hours" id="full_day_min_hours" required
                                value="{{ old('full_day_min_hours', $settings->full_day_min_hours) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Standard: 8.0 hours</p>
                    </div>

                    <div>
                        <label for="half_day_min_hours" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Half Day Minimum Hours <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.5" min="1" max="12" name="half_day_min_hours" id="half_day_min_hours" required
                                value="{{ old('half_day_min_hours', $settings->half_day_min_hours) }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 py-2.5 px-3">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Standard: 4.0 hours</p>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="relative flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_half_day_on_short_hours" value="1"
                            {{ old('auto_half_day_on_short_hours', $settings->auto_half_day_on_short_hours) ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                Auto-Adjust to Half Day on Short Hours
                            </span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                When an employee punches out with total working hours below the half-day minimum threshold (e.g. less than 4 hours), automatically set status to <strong>Half Day</strong>.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Section 4: Auto Punch-Out for Forgotten Checkouts -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold">4</span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Forgotten Punch-Out (Auto Clock-Out)</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nightly automated cleanup for employees who forgot to clock out</p>
                    </div>
                </div>

                <div>
                    <label class="relative flex items-start gap-3 cursor-pointer mb-4">
                        <input type="checkbox" name="enable_auto_punch_out" value="1"
                            {{ old('enable_auto_punch_out', $settings->enable_auto_punch_out) ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 rounded text-purple-600 focus:ring-purple-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                Enable Automatic Shift Punch-Out
                            </span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                Automatically clock out employees who checked in but forgot to punch out at the end of their shift.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="auto_punch_out_time" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Default Punch-Out Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="auto_punch_out_time" id="auto_punch_out_time" required
                            value="{{ old('auto_punch_out_time', substr($settings->auto_punch_out_time, 0, 5)) }}"
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500 py-2.5 px-3">
                        <p class="text-[11px] text-gray-400 mt-1">Assigned checkout time (e.g. 07:00 PM / 19:00)</p>
                    </div>

                    <div>
                        <label for="auto_punch_out_mode" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Punch-Out Calculation Mode <span class="text-red-500">*</span>
                        </label>
                        <select name="auto_punch_out_mode" id="auto_punch_out_mode" required
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500 py-2.5 px-3">
                            <option value="fixed_time" {{ old('auto_punch_out_mode', $settings->auto_punch_out_mode) === 'fixed_time' ? 'selected' : '' }}>
                                Fixed Time (Use Default Punch-Out Time)
                            </option>
                            <option value="shift_duration" {{ old('auto_punch_out_mode', $settings->auto_punch_out_mode) === 'shift_duration' ? 'selected' : '' }}>
                                Shift Duration (Check-in + Full Day Hours)
                            </option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">How working minutes are credited</p>
                    </div>
                </div>

                <div class="rounded-xl bg-purple-50/70 dark:bg-purple-950/30 p-3.5 border border-purple-100 dark:border-purple-900/50 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-purple-800 dark:text-purple-200 leading-relaxed">
                        Scheduled automatically every night at <strong>23:50 IST</strong>. All auto-closed shifts are stamped with a transparent system audit note: <em>"Auto clocked out by system (Forgot punch-out)"</em>.
                    </p>
                </div>
            </div>

        </div>

        <!-- Form Submit Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                @if($settings->editor)
                    Last updated by <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $settings->editor->name }}</span>
                    on {{ $settings->updated_at->format('d M Y, h:i A') }}
                @else
                    Default system settings active
                @endif
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.attendance.index') }}"
                    class="px-5 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Timing Settings
                </button>
            </div>
        </div>
    </form>

    <!-- Manual Quick-Action Card for HR: On-Demand Auto Punch-Out -->
    <div class="bg-gradient-to-r from-purple-500/10 via-indigo-500/10 to-blue-500/10 dark:from-purple-900/20 dark:via-indigo-900/20 dark:to-blue-900/20 rounded-2xl border border-purple-200 dark:border-purple-800/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                        On-Demand Action
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Run Auto Punch-Out Now</h3>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Did employees leave today or in past days without clocking out? You can trigger an instant auto-checkout right now.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <form action="{{ route('admin.attendance.auto-punch-out') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <button type="submit"
                        onclick="return confirm('Auto-clock out all open shifts for today ({{ now()->format('d M Y') }})?')"
                        class="px-4 py-2 text-xs font-bold rounded-xl text-purple-700 dark:text-purple-200 bg-white dark:bg-gray-800 hover:bg-purple-50 dark:hover:bg-purple-900/40 border border-purple-200 dark:border-purple-700 shadow-sm transition">
                        ⚡ Auto-Clock Out Today's Open Shifts
                    </button>
                </form>

                <form action="{{ route('admin.attendance.auto-punch-out') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="scope" value="all_past">
                    <button type="submit"
                        onclick="return confirm('Scan and auto-close all past open shifts with missing punch-outs?')"
                        class="px-4 py-2 text-xs font-bold rounded-xl text-indigo-700 dark:text-indigo-200 bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 border border-indigo-200 dark:border-indigo-700 shadow-sm transition">
                        🧹 Fix All Past Missing Punch-Outs
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
