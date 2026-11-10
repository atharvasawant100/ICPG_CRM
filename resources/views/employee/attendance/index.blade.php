@extends('layouts.app')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                My Attendance
            </h1>
        </div>

        <!-- Month & Year Filter Form -->
        <form method="GET" action="{{ route('my-attendance.index') }}" class="flex items-center gap-2">
            <select name="month" onchange="this.form.submit()"
                class="text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-3 py-2 shadow-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" onchange="this.form.submit()"
                class="text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-3 py-2 shadow-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Today's Punch Live Status Card -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-teal-400 flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-teal-400">Today's Punch Status</span>
                    <span class="text-xs text-gray-400">&bull; {{ now()->format('l, d M Y') }}</span>
                </div>
                <div class="mt-1 flex items-baseline gap-3">
                    @if($todayAttendance && $todayAttendance->check_out)
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                            Clocked Out
                        </h2>
                        <span class="text-sm text-gray-300">
                            In: {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }} &bull; Out: {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }} ({{ floor($todayAttendance->total_minutes / 60) }}h {{ $todayAttendance->total_minutes % 60 }}m)
                        </span>
                    @elseif($todayAttendance && $todayAttendance->check_in)
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Currently Clocked In
                        </h2>
                        <span class="text-sm text-teal-300 font-medium">
                            Since {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                        </span>
                    @else
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            Not Clocked In Yet
                        </h2>
                    @endif
                </div>
            </div>
        </div>

        <!-- Punch Buttons -->
        <div class="flex items-center gap-3">
            @if($todayAttendance && $todayAttendance->check_in && ! $todayAttendance->check_out)
                <button type="button" onclick="openPunchOutModal()"
                    class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Punch Out</span>
                </button>
            @elseif(! $todayAttendance || ! $todayAttendance->check_in)
                @php
                    $attSettings = \App\Models\AttendanceSetting::get();
                @endphp
                @if(! $attSettings->isPunchInAllowed())
                    <div class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-semibold text-xs rounded-xl flex items-center gap-2 shadow-xs" title="Office shift ended at {{ $attSettings->formattedShiftEndTime() }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Office Closed ({{ $attSettings->formattedShiftEndTime() }})</span>
                    </div>
                @else
                    <form action="{{ route('attendance.punch-in') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Punch In</span>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Month KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Present Days -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Days Present</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $presentDays }} <span class="text-xs font-normal text-gray-400">days</span></p>
            <p class="text-xs text-gray-400 mt-1">This month</p>
        </div>

        <!-- Working Hours -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Hours</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalWorkingHoursFormatted }}</p>
            <p class="text-xs text-gray-400 mt-1">Logged working time</p>
        </div>

        <!-- Late Days -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Late Days</p>
            <p class="text-2xl font-bold {{ $lateDays > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }} mt-1">
                {{ $lateDays }} <span class="text-xs font-normal text-gray-400">days</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Punch in after 10:00 AM</p>
        </div>

        <!-- On Leave -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">Approved Leaves</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $leaveDays }} <span class="text-xs font-normal text-gray-400">days</span></p>
            <p class="text-xs text-gray-400 mt-1">Paid / Casual / Sick</p>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Attendance Log for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthlyRecords->count() }} records</span>
        </div>

        @if($monthlyRecords->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <p class="text-sm font-semibold">No attendance records logged for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-750 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Check In</th>
                            <th class="py-3 px-4">Check Out</th>
                            <th class="py-3 px-4">Hours Logged</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($monthlyRecords as $rec)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($rec->date)->format('D, d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    @if($rec->check_in)
                                        <span class="font-mono font-medium">{{ \Carbon\Carbon::parse($rec->check_in)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    @if($rec->check_out)
                                        <span class="font-mono font-medium">{{ \Carbon\Carbon::parse($rec->check_out)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    @if($rec->total_minutes > 0)
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ floor($rec->total_minutes / 60) }}h {{ $rec->total_minutes % 60 }}m</span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($rec->status === 'present')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Present
                                        </span>
                                    @elseif($rec->status === 'late')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Late
                                        </span>
                                    @elseif($rec->status === 'half_day')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                            Half Day
                                        </span>
                                    @elseif($rec->status === 'on_leave')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                            On Leave
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst(str_replace('_', ' ', $rec->status)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $rec->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
