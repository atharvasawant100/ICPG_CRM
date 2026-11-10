@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@php
$formatIndianAmount = function ($num) {
    return \App\Support\NumberHelper::formatIndianCompact($num);
};

$growthBadge = function (?float $pct, bool $invert = false) {
    if ($pct === null) {
        return '<span class="text-xs text-gray-400">—</span>';
    }
    $isUp = $invert ? $pct < 0 : $pct >= 0;
    $color = $isUp
        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    $arrow = $pct >= 0 ? '↑' : '↓';
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' . $color . '">'
        . $arrow . ' ' . abs($pct) . '%</span>';
};
@endphp

<style>
    @keyframes dashFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .dash-animate { animation: dashFadeUp 0.45s ease-out both; }
    .dash-animate-delay-1 { animation-delay: 0.05s; }
    .dash-animate-delay-2 { animation-delay: 0.1s; }

    /* Compact KPI cards when shown 2-up on small screens */
    @media (max-width: 639px) {
        .kpi-grid > * { padding: 0.875rem; }
        .kpi-grid > * p.text-3xl { font-size: 1.375rem; line-height: 1.75rem; }
        .kpi-grid > * .w-12.h-12 { width: 2.25rem; height: 2.25rem; }
        .kpi-grid > * .w-12.h-12 svg { width: 1.125rem; height: 1.125rem; }
    }
    .dash-animate-delay-3 { animation-delay: 0.15s; }
    .chart-tab.active {
        color: rgb(37 99 235);
        border-bottom-color: rgb(37 99 235);
    }
    .dark .chart-tab.active {
        color: rgb(96 165 250);
        border-bottom-color: rgb(96 165 250);
    }
    @media print {
        aside, header, .no-print, .sidebar, nav { display: none !important; }
        main, .print-area { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .print-only-table { display: block !important; }
        body { background: white !important; }
    }
</style>

@if(!auth()->user()->isAdmin())
@php
    $bannerCheckedIn = $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out;
    $bannerCheckedOut = $todayAttendance && $todayAttendance->check_out;
    $bannerNotClocked = !$todayAttendance || (!$todayAttendance->check_in && !$todayAttendance->check_out);
    $bannerSettings = \App\Models\AttendanceSetting::get();
    $bannerShiftClosed = ! $bannerSettings->isPunchInAllowed();
@endphp
<div class="mb-6 dash-animate">
    <div class="rounded-2xl p-5 border shadow-sm transition-all duration-300 {{ $bannerCheckedIn ? 'bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border-emerald-300 dark:border-emerald-700/60' : ($bannerCheckedOut ? 'bg-gradient-to-r from-blue-500/10 via-blue-500/5 to-transparent border-blue-200 dark:border-blue-800/60' : ($bannerShiftClosed ? 'bg-gradient-to-r from-gray-500/10 via-gray-500/5 to-transparent border-gray-300 dark:border-gray-700/60' : 'bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-amber-300 dark:border-amber-700/60')) }} dark:bg-gray-800/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm {{ $bannerCheckedIn ? 'bg-emerald-500 text-white' : ($bannerCheckedOut ? 'bg-blue-600 text-white' : ($bannerShiftClosed ? 'bg-gray-500 text-white' : 'bg-amber-500 text-white')) }}">
                    @if($bannerCheckedIn)
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($bannerCheckedOut)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                            @if($bannerCheckedIn)
                                Shift In Progress
                            @elseif($bannerCheckedOut)
                                Shift Completed for Today
                            @else
                                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name }}!
                            @endif
                        </h3>
                        @if($bannerCheckedIn)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-ping"></span>
                                Clocked In
                            </span>
                        @elseif($bannerCheckedOut)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                Shift Done
                            </span>
                        @elseif($bannerShiftClosed)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                Shift Closed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                Not Clocked In
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                        @if($bannerCheckedIn)
                            Clocked in at <strong class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>. Active time today: <strong class="text-gray-900 dark:text-white">{{ $employeeTodayWorkingHoursFormatted ?? '' }}</strong>.
                        @elseif($bannerCheckedOut)
                            Clocked in at {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }} &bull; Clocked out at {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }} (Total: {{ $todayAttendance->formatted_working_hours }}).
                        @elseif($bannerShiftClosed)
                            Office hours ended at <strong class="text-gray-900 dark:text-white">{{ $bannerSettings->formattedShiftEndTime() }}</strong>. Punch-in is closed for today.
                        @else
                            You have not punched in yet today. Click the button to start your shift.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0">
                @if($bannerNotClocked && $bannerShiftClosed)
                    <div class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Office Closed ({{ $bannerSettings->formattedShiftEndTime() }})</span>
                    </div>
                @elseif($bannerNotClocked)
                    <form action="{{ route('attendance.punch-in') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" id="dashboard-punch-in-btn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Punch In</span>
                        </button>
                    </form>
                @elseif($bannerCheckedIn)
                    <button type="button" onclick="openPunchOutModal()" id="dashboard-punch-out-btn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-xl shadow-md hover:shadow-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Punch Out &amp; Complete Tasks</span>
                    </button>
                @else
                    <a href="{{ route('my-attendance.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <span>View Attendance History</span>
                        <span>&rarr;</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($isManager)
<!-- ============================================ -->
<!-- SECTION: MANAGER & DEPARTMENT OVERVIEW -->
<!-- ============================================ -->
<div class="mb-8 dash-animate">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                Department &amp; Team Overview
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 capitalize">{{ auth()->user()->department ?? 'Team' }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            @can('team.view')
            <a href="{{ route('team.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>Team Members</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
            @if(auth()->user()->hasTasks())
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Task Pool</span>
            </a>
            @endif
            @can('expenses.view')
            <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>All Expenses</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Core Overview Containers -->
    <div class="kpi-grid grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()->hasTasks() ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4 sm:gap-5">
        @if(auth()->user()->hasTasks())
        <!-- 1. Task Container (Assigned to their team) -->
        <a href="{{ route('tasks.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Team Tasks</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 opacity-80 group-hover:opacity-100 transition-opacity">Pool ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400 truncate">{{ number_format($teamTasksActiveCount) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $teamTasksInProgressCount }} in progress · {{ $teamTasksPendingCount }} pending
                        </span>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:underline">View pool &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
        </a>
        @endif

        <!-- 2. Team Container (Number of Team member of his own dept) -->
        <a href="{{ route('team.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-blue-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Department Team</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 opacity-80 group-hover:opacity-100 transition-opacity">Staff ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 truncate">{{ number_format($deptTeamMembersCount) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $deptTeamPresentToday }} present · {{ $deptTeamOnLeaveToday }} on leave
                        </span>
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 group-hover:underline">My team &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </a>

        <!-- 3. Pending Paid Leaves of the Manager (Leave Balance) -->
        <a href="{{ route('my-leaves.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-teal-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-teal-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Paid Leaves Balance</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300 opacity-80 group-hover:opacity-100 transition-opacity">Balance ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-teal-600 dark:text-teal-400 truncate">{{ rtrim(rtrim(number_format($managerPaidLeavesRemaining, 1), '0'), '.') }} <span class="text-sm font-semibold">Days</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            @if($managerPendingLeaveRequestsCount > 0)
                                <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $managerPendingLeaveRequestsCount }} pending</span> · {{ rtrim(rtrim(number_format($managerPaidLeavesUsed, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format($managerPaidLeavesAllocated, 1), '0'), '.') }} used
                            @else
                                {{ rtrim(rtrim(number_format($managerPaidLeavesUsed, 1), '0'), '.') }} used of {{ rtrim(rtrim(number_format($managerPaidLeavesAllocated, 1), '0'), '.') }} days
                            @endif
                        </span>
                        <span class="text-xs font-semibold text-teal-600 dark:text-teal-400 group-hover:underline">My leaves &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </a>

        <!-- 4. Expense of the Current Month -->
        <div role="button" tabindex="0" onclick="openKpiModal('expenses')" onkeydown="if(event.key==='Enter') openKpiModal('expenses')"
            class="group relative overflow-hidden bg-gradient-to-br from-orange-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-orange-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 cursor-pointer transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Monthly Expenses</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 opacity-80 group-hover:opacity-100 transition-opacity">List ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-orange-600 dark:text-orange-400 truncate" title="₹{{ number_format((float) $currentMonthExpenses, 2) }}">₹{{ $formatIndianAmount($currentMonthExpenses) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['expenses'] ?? null, true) !!}</div>
                        <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->hasTasks())
<!-- Daily Tasks Done Today Section -->
<div class="mb-8 dash-animate dash-animate-delay-1">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span>Daily Tasks Done (Today)</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                        {{ $dailyTeamTasksCompletedCount }} Completed
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Deliverables ticked complete today by your team ({{ \Carbon\Carbon::today()->format('D, M d, Y') }}). Resets tomorrow.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.index', ['tab' => 'daily']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800 rounded-lg transition shadow-sm">
                    <span>View Daily Tasks Sheet</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        @if($dailyTeamTasksCompleted->isEmpty())
            <div class="p-8 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No tasks completed by your team today yet</p>
                <p class="text-xs text-gray-400 mt-1">When members tick off tasks or confirm shift punch-out, completed tasks will appear here.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($dailyTeamTasksCompleted as $task)
                @php
                    $assignee = $task->assignee;
                    $initials = $assignee ? collect(explode(' ', $assignee->name))->map(fn($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') : 'UN';
                @endphp
                <div class="p-4 sm:px-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            ✓
                        </span>
                        <div class="min-w-0">
                            <a href="{{ route('tasks.show', $task) }}" class="font-bold text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate block">
                                {{ $task->title }}
                            </a>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                                @if($task->project_name)
                                    <span class="inline-block text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">📁 {{ $task->project_name }}</span>
                                    <span>&bull;</span>
                                @endif
                                <span class="capitalize font-medium">{{ $task->priority }} priority</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center sm:text-right gap-3 flex-shrink-0 justify-between sm:justify-end">
                        @if($assignee)
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-[10px] font-bold">
                                {{ $initials }}
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="block text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $assignee->name }}</span>
                                <span class="block text-[10px] text-gray-400">{{ ucwords($assignee->designation ?? 'Member') }}</span>
                            </div>
                        </div>
                        @endif
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full border border-emerald-200/50 dark:border-emerald-800/50">
                            {{ $task->completed_at ? $task->completed_at->format('h:i A') : 'Today' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Team Tasks Container (Panel View) -->
<div class="mb-8 dash-animate dash-animate-delay-1">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    Team Tasks Container
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">{{ $teamTasksActiveCount }} Active</span>
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg transition shadow-sm">
                    <span>Task Pool</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
            </div>
        </div>

        @if($teamTasksList->isEmpty())
            <div class="p-8 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No tasks currently assigned to your team</p>
                <p class="text-xs text-gray-400 mt-1">Assign tasks to your department members via the Task Pool.</p>
                <div class="mt-4">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                        <span>Go to Task Pool</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-800/70 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold border-b border-gray-100 dark:border-gray-800">
                            <th class="py-3 px-4">Task &amp; Project</th>
                            <th class="py-3 px-4">Assignee</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($teamTasksList as $task)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ $task->title }}</div>
                                @if($task->project_name)
                                    <span class="inline-block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">📁 {{ $task->project_name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($task->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-[10px] font-bold">
                                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $task->assignee->name }}</span>
                                            <span class="block text-[10px] text-gray-400 capitalize">{{ $task->assignee->designation ?? 'Member' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $task->priorityBadgeClasses() }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $task->statusBadgeClasses() }}">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($task->due_date)
                                    @php
                                        $isOverdue = $task->due_date->isPast() && !$task->isCompleted();
                                        $isToday = $task->due_date->isToday();
                                    @endphp
                                    <span class="text-xs {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : ($isToday ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-gray-600 dark:text-gray-300') }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($isOverdue)
                                            <span class="text-[10px] block text-red-500 font-bold">Overdue</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Open &rarr;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endif

@if($isEmployee)
<!-- ============================================ -->
<!-- SECTION: EMPLOYEE WORKPLACE DASHBOARD (Development & Marketing) -->
<!-- ============================================ -->
<div class="mb-8 dash-animate">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                Employee Workplace Dashboard
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 capitalize">{{ auth()->user()->department ?? 'Staff' }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('my-attendance.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>My Attendance</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @if(auth()->user()->hasTasks())
            <a href="{{ route('tasks.index', ['tab' => 'my']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>My Tasks</span>
            </a>
            @endif
            <a href="{{ route('my-leaves.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>Apply Leave</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Core Employee KPI Containers -->
    <div class="kpi-grid grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()->hasTasks() ? 'lg:grid-cols-3 xl:grid-cols-5' : 'lg:grid-cols-3' }} gap-4 sm:gap-5">
        <!-- 1. Working Hours -->
        <a href="{{ route('my-attendance.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Working Hours</p>
                        @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 animate-pulse">In</span>
                        @endif
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400 truncate">{{ $employeeMonthWorkingHoursFormatted }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Today: <strong class="text-gray-700 dark:text-gray-300">{{ $employeeTodayWorkingHoursFormatted }}</strong></span>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:underline">Sheet &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        <!-- 2. Leave Remaining -->
        <a href="{{ route('my-leaves.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-teal-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-teal-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Leave Remaining</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300 opacity-80 group-hover:opacity-100 transition-opacity">Quota ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-teal-600 dark:text-teal-400 truncate">{{ rtrim(rtrim(number_format($employeeLeaveRemaining, 1), '0'), '.') }} <span class="text-sm font-semibold">Days</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ rtrim(rtrim(number_format($employeeLeaveUsed, 1), '0'), '.') }} / {{ rtrim(rtrim(number_format($employeeLeaveAllocated, 1), '0'), '.') }} used</span>
                        <span class="text-xs font-semibold text-teal-600 dark:text-teal-400 group-hover:underline">Apply &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </a>

        <!-- 3. Attendance -->
        <a href="{{ route('my-attendance.index') }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-emerald-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-emerald-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Attendance</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 opacity-80 group-hover:opacity-100 transition-opacity">Month ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ $employeeMonthPresentDays }} <span class="text-sm font-semibold">Days</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            @if($employeeMonthLateDays > 0)
                                <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $employeeMonthLateDays }} late</span> ·
                            @endif
                            Present this month
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 group-hover:underline">Logs &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        @if(auth()->user()->hasTasks())
        <!-- 4. Tasks Completed -->
        <a href="{{ route('tasks.index', ['tab' => 'my', 'status' => 'completed']) }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-blue-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Tasks Completed</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 opacity-80 group-hover:opacity-100 transition-opacity">Done ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 truncate">{{ number_format($employeeTasksCompleted) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Out of {{ $employeeTasksTotal }} assigned</span>
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </a>

        <!-- 5. Overdue Tasks -->
        <a href="{{ route('tasks.index', ['tab' => 'my']) }}"
            class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-rose-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-rose-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Overdue Tasks</p>
                        @if($employeeTasksOverdue > 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 animate-pulse">Attention</span>
                        @endif
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold {{ $employeeTasksOverdue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }} truncate">{{ number_format($employeeTasksOverdue) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $employeeTasksActive }} active tasks</span>
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 group-hover:underline">Resolve &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </a>
        @endif
    </div>
</div>

@if(auth()->user()->hasTasks())
<!-- Employee My Tasks Container (Panel View) -->
<div class="mb-8 dash-animate dash-animate-delay-1">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    My Assigned Tasks
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">{{ $employeeTasksActive }} Active</span>
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.index', ['tab' => 'my']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg transition shadow-sm">
                    <span>Task Pool</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
            </div>
        </div>

        @if($employeeTasksList->isEmpty())
            <div class="p-8 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">You have no tasks assigned right now</p>
                <p class="text-xs text-gray-400 mt-1">Check the Task Pool or speak with your manager for upcoming assignments.</p>
                <div class="mt-4">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                        <span>Open Task Pool</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-800/70 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold border-b border-gray-100 dark:border-gray-800">
                            <th class="py-3 px-4">Task &amp; Project</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($employeeTasksList as $task)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ $task->title }}</div>
                                @if($task->project_name)
                                    <span class="inline-block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">📁 {{ $task->project_name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $task->priorityBadgeClasses() }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $task->statusBadgeClasses() }}">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($task->due_date)
                                    @php
                                        $isOverdue = $task->due_date->isPast() && !$task->isCompleted();
                                        $isToday = $task->due_date->isToday();
                                    @endphp
                                    <span class="text-xs {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : ($isToday ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-gray-600 dark:text-gray-300') }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($isOverdue)
                                            <span class="text-[10px] block text-red-500 font-bold">Overdue</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('tasks.index', ['tab' => 'my']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Open &rarr;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endif

<!-- ============================================ -->
<!-- SECTION 1: FINANCIAL & REVENUE OVERVIEW -->
<!-- ============================================ -->
@if((! $isManager && ! $isEmployee && (auth()->user()->can('invoices.view') || auth()->user()->can('expenses.view'))) || ($isManager && auth()->user()->can('invoices.view')))
<div class="mb-8 dash-animate">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                Financial & Revenue Overview
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">₹ INR</span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            @can('invoices.view')
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>All Invoices</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
            @can('expenses.view')
            <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>All Expenses</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
        </div>
    </div>
    <div class="kpi-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @can('invoices.view')
        <div role="button" tabindex="0" onclick="openKpiModal('collected')" onkeydown="if(event.key==='Enter') openKpiModal('collected')"
            class="group relative overflow-hidden bg-gradient-to-br from-green-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-green-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 cursor-pointer transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Amount Collected</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 opacity-80 group-hover:opacity-100 transition-opacity">List ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400 truncate" title="₹{{ number_format((float) $paidInvoices, 2) }}">₹{{ $formatIndianAmount($paidInvoices) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">All payments</span>
                        <span class="text-xs font-semibold text-green-600 dark:text-green-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        @endcan

        @can('invoices.view')
        <div role="button" tabindex="0" onclick="openKpiModal('overdue')" onkeydown="if(event.key==='Enter') openKpiModal('overdue')"
            class="group relative overflow-hidden bg-gradient-to-br from-red-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-red-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 cursor-pointer transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Overdue Outstanding</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 opacity-80 group-hover:opacity-100 transition-opacity">List ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600 dark:text-red-400 truncate" title="₹{{ number_format((float) $overdueInvoices, 2) }}">₹{{ $formatIndianAmount($overdueInvoices) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Past due date</span>
                        <span class="text-xs font-semibold text-red-600 dark:text-red-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        @endcan

        @can('invoices.view')
        <div role="button" tabindex="0" onclick="openKpiModal('revenue')" onkeydown="if(event.key==='Enter') openKpiModal('revenue')"
            class="group relative overflow-hidden bg-gradient-to-br from-teal-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-teal-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 cursor-pointer transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Monthly Revenue</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300 opacity-80 group-hover:opacity-100 transition-opacity">List ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-teal-600 dark:text-teal-400 truncate" title="₹{{ number_format((float) $currentMonthRevenue, 2) }}">₹{{ $formatIndianAmount($currentMonthRevenue) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['revenue'] ?? null) !!}</div>
                        <span class="text-xs font-semibold text-teal-600 dark:text-teal-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        @endcan

        @can('expenses.view')
        <div role="button" tabindex="0" onclick="openKpiModal('expenses')" onkeydown="if(event.key==='Enter') openKpiModal('expenses')"
            class="group relative overflow-hidden bg-gradient-to-br from-orange-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-orange-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 cursor-pointer transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Monthly Expenses</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 opacity-80 group-hover:opacity-100 transition-opacity">List ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-orange-600 dark:text-orange-400 truncate" title="₹{{ number_format((float) $currentMonthExpenses, 2) }}">₹{{ $formatIndianAmount($currentMonthExpenses) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['expenses'] ?? null, true) !!}</div>
                        <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">View &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
        </div>
        @endcan

        @can('invoices.view')
        <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-cyan-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Net Profit (Month)</p>
                    <p class="text-2xl sm:text-3xl font-bold truncate {{ $currentMonthProfit >= 0 ? 'text-cyan-600 dark:text-cyan-400' : 'text-red-600 dark:text-red-400' }}" title="₹{{ number_format((float) $currentMonthProfit, 2) }}">₹{{ $formatIndianAmount($currentMonthProfit) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['profit'] ?? null) !!}</div>
                        <span class="text-xs text-gray-400">Rev - Exp</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </div>
        @endcan

        @can('invoices.view')
        <a href="{{ route('invoices.index') }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Invoices</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 opacity-80 group-hover:opacity-100 transition-opacity">All ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($totalInvoices) }}</p>
                    <div class="mt-2 flex items-center justify-between gap-1">
                        <div>{!! $growthBadge($growth['invoices'] ?? null) !!}</div>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:underline">View all &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </a>
        @endcan
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- SECTION 2: LEADS & PIPELINE PERFORMANCE -->
<!-- ============================================ -->
@can('leads.view')
<div class="mb-8 dash-animate dash-animate-delay-1">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                Lead Generation & Pipeline Performance
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">CRM</span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>View All Leads</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @can('leads.create')
            <a href="{{ route('leads.create.manual') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Lead</span>
            </a>
            @endcan
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('leads.index') }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-blue-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Leads</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalLeads) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['leads'] ?? null) !!}</div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:underline">View all &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('leads.index') }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-sky-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-sky-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Active in Pipeline</p>
                    <p class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400">{{ number_format($newLeads + $contactedLeads + $interestedLeads + $qualifiedLeads) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ number_format($newLeads) }} new · {{ number_format($qualifiedLeads) }} qualified</span>
                        <span class="text-xs text-sky-600 dark:text-sky-400 group-hover:underline">Funnel &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('leads.index', ['status' => 'converted']) }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-emerald-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-emerald-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Converted Leads</p>
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($convertedLeads) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['converted'] ?? null) !!}</div>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 group-hover:underline">Won deals &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-purple-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Conversion Rate</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $conversionRate }}%</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>{!! $growthBadge($growth['conversion'] ?? null) !!}</div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total won %</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- ============================================ -->
<!-- SECTION: HR & WORKFORCE STATISTICAL OVERVIEW -->
<!-- ============================================ -->
@if(auth()->user()->isHr() || auth()->user()->isAdmin() || auth()->user()->can('users.view'))
<div class="mb-8 dash-animate dash-animate-delay-1">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                HR &amp; Workforce Overview
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">HR Portal</span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            @can('users.view')
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>All Users</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
            @can('attendance.view')
            <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <span>Daily Sheet</span>
                <span class="text-gray-400">&rarr;</span>
            </a>
            @endcan
            @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Employee</span>
            </a>
            @endcan
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total EMP -->
        <a href="{{ auth()->user()->can('users.view') ? route('admin.users.index') : '#' }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total EMP</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 opacity-80 group-hover:opacity-100 transition-opacity">Staff ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($hrStats['total_employees'] ?? 0) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Active staff</span>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:underline">Directory &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </a>

        <!-- 2. Present Today -->
        <a href="{{ auth()->user()->can('attendance.view') ? route('admin.attendance.index') : '#' }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-emerald-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-emerald-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Present Today</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 opacity-80 group-hover:opacity-100 transition-opacity">Live ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($hrStats['present_today'] ?? 0) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ ($hrStats['late_today'] ?? 0) > 0 ? $hrStats['late_today'] . ' marked late' : 'On-duty today' }}</span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 group-hover:underline">Daily Sheet &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        <!-- 3. On Leave Today -->
        <a href="{{ auth()->user()->can('leaves.view') ? route('admin.leaves.index') : '#' }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-amber-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-amber-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">On Leave Today</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 opacity-80 group-hover:opacity-100 transition-opacity">Leaves ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($hrStats['on_leave_today'] ?? 0) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Approved leave</span>
                        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 group-hover:underline">View leaves &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </a>

        <!-- 4. Pending Leaves -->
        <a href="{{ auth()->user()->can('leaves.view') ? route('admin.leaves.index', ['status' => 'pending']) : '#' }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-rose-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-rose-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Pending Leaves</p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 opacity-80 group-hover:opacity-100 transition-opacity">Review ↗</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($hrStats['pending_leaves'] ?? 0) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Awaiting approval</span>
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 group-hover:underline">Review &rarr;</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- SECTION 2: QUICK ACTIONS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate dash-animate-delay-1">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        @can('leads.create')
        <a href="{{ route('leads.create.manual') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">New Lead</span>
        </a>
        @endcan
        @can('invoices.create')
        <a href="{{ route('invoices.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">New Invoice</span>
        </a>
        @endcan
        @can('expenses.create')
        <a href="{{ route('expenses.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Add Expense</span>
        </a>
        @endcan
        @can('customers.create')
        <a href="{{ route('customers.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Add Customer</span>
        </a>
        @endcan
        @can('users.create')
        <a href="{{ route('admin.users.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Add Employee</span>
        </a>
        @endcan
        @can('attendance.create')
        <a href="{{ route('admin.attendance.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Mark Attendance</span>
        </a>
        @endcan
        @can('leaves.create')
        <a href="{{ route('admin.leaves.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Record Leave</span>
        </a>
        @endcan
        @can('payroll.create')
        <a href="{{ route('payrolls.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Generate Payroll</span>
        </a>
        @endcan
        @can('followups.view')
        <a href="{{ route('leads.calendar') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Follow-up Calendar</span>
        </a>
        @endcan
        @can('recurring_invoices.view')
        <a href="{{ route('recurring-invoices.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Recurring Invoice</span>
        </a>
        @endcan
        @can('team.view')
        <a href="{{ route('team.index') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">My Team</span>
        </a>
        @endcan
        @if(auth()->user()->hasTasks() && ($isManager || auth()->user()->can('tasks.view')))
        <a href="{{ route('tasks.index') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Task Pool</span>
        </a>
        @endif
        @can('my_leaves.view')
        <a href="{{ route('my-leaves.index') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">My Leaves</span>
        </a>
        @endcan
        @can('leaves.approve')
        <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Review Leaves</span>
        </a>
        @endcan
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 3: ANALYTICS TABS -->
<!-- ============================================ -->
@php
    /*
     | Analytics tabs. A tab the user cannot view is not rendered, and the first
     | permitted tab becomes the default so the panel is never blank.
     */
    $chartTabs = [
        'leads' => ['label' => 'Leads', 'permission' => 'leads.view'],
        'invoices' => ['label' => 'Invoices', 'permission' => 'invoices.view'],
        'expenses' => ['label' => 'Expenses', 'permission' => 'expenses.view'],
        'revenue' => ['label' => 'Revenue', 'permission' => 'invoices.view'],
        'workforce' => ['label' => 'HR & Workforce', 'permission' => 'users.view'],
    ];

    $allowedChartTabs = array_filter(
        $chartTabs,
        fn ($tab) => auth()->user()?->can($tab['permission'])
    );

    $activeChartTab = array_key_first($allowedChartTabs);
@endphp

@if(! $isEmployee && ! empty($allowedChartTabs))
<div class="mb-8 dash-animate dash-animate-delay-2">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Analytics</h2>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 px-2 sm:px-4 overflow-x-auto">
            <div class="flex min-w-max -mb-px">
                @foreach ($allowedChartTabs as $key => $tab)
                    <button type="button" onclick="showChartTab('{{ $key }}')" id="tab-{{ $key }}"
                        class="chart-tab {{ $key === $activeChartTab ? 'active' : '' }} px-4 sm:px-6 py-3.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border-b-2 border-transparent whitespace-nowrap">
                        {{ $tab['label'] }}</button>
                @endforeach
            </div>
        </div>

        @if ($activeChartTab === null)
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                No analytics available for your permissions.
            </div>
        @endif

        <!-- Leads Tab -->
        @can('leads.view')
        <div id="panel-leads" class="chart-panel p-4 sm:p-6 {{ $activeChartTab === 'leads' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Lead Conversion Funnel</h3>
                    <div class="space-y-3">
                        @php
                            $funnel = [
                                ['label' => 'New', 'count' => $newLeads, 'color' => 'bg-blue-500'],
                                ['label' => 'Contacted', 'count' => $contactedLeads, 'color' => 'bg-indigo-500'],
                                ['label' => 'Qualified', 'count' => $qualifiedLeads, 'color' => 'bg-purple-500'],
                                ['label' => 'Converted', 'count' => $convertedLeads, 'color' => 'bg-emerald-500'],
                                ['label' => 'Lost', 'count' => $lostLeads, 'color' => 'bg-red-400'],
                            ];
                            $funnelMax = max(array_column($funnel, 'count') ?: [1]) ?: 1;
                        @endphp
                        @foreach($funnel as $step)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $step['label'] }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($step['count']) }}</span>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $step['color'] }} rounded-full transition-all duration-700" style="width: {{ min(100, ($step['count'] / $funnelMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Lead Sources</h3>
                    <div class="relative h-64">
                        <canvas id="chartLeadSources"></canvas>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Lead Trend (12 months)</h3>
                <div class="relative h-72">
                    <canvas id="chartLeadTrend"></canvas>
                </div>
            </div>
        </div>

        @endcan

        <!-- Invoices Tab -->
        @can('invoices.view')
        <div id="panel-invoices" class="chart-panel p-4 sm:p-6 {{ $activeChartTab === 'invoices' ? '' : 'hidden' }}">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Paid</p>
                    <p class="text-xl font-bold text-green-600">₹{{ $formatIndianAmount($paidInvoices) }}</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-100 dark:border-amber-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Unpaid</p>
                    <p class="text-xl font-bold text-amber-600">₹{{ $formatIndianAmount($unpaidInvoices) }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Pending Collections</p>
                    <p class="text-xl font-bold text-blue-600">₹{{ $formatIndianAmount($pendingInvoices) }}</p>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Avg Invoice Value</p>
                    <p class="text-xl font-bold text-indigo-600">₹{{ $formatIndianAmount($averageInvoiceValue) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Invoice Status</h3>
                    <div class="relative h-64"><canvas id="chartInvoiceStatus"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Paid vs Unpaid</h3>
                    <div class="relative h-64"><canvas id="chartPaidUnpaid"></canvas></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Invoice Totals</h3>
                    <div class="relative h-64"><canvas id="chartInvoiceTrend"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Top Paying Customers</h3>
                    <div class="space-y-3">
                        @forelse($topPayingCustomers as $i => $cust)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $cust->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $cust->company ?: $cust->email }}</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-green-600 whitespace-nowrap">₹{{ $formatIndianAmount($cust->total_paid) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-8">No payment data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Tab -->
        @endcan

        <!-- Expenses Tab -->
        @can('expenses.view')
        <div id="panel-expenses" class="chart-panel p-4 sm:p-6 {{ $activeChartTab === 'expenses' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Expenses by Category</h3>
                    <div class="relative h-64"><canvas id="chartExpenseCategory"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Expense Status</h3>
                    <div class="relative h-64"><canvas id="chartExpenseStatus"></canvas></div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Expense Trend</h3>
                <div class="relative h-72"><canvas id="chartExpenseTrend"></canvas></div>
            </div>
        </div>

        <!-- Revenue Tab -->
        @endcan

        <!-- Revenue Tab -->
        @can('invoices.view')
        <div id="panel-revenue" class="chart-panel p-4 sm:p-6 {{ $activeChartTab === 'revenue' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <div class="bg-teal-50 dark:bg-teal-900/20 rounded-xl p-4 border border-teal-100 dark:border-teal-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">This Month Revenue</p>
                    <p class="text-2xl font-bold text-teal-600">₹{{ $formatIndianAmount($currentMonthRevenue) }}</p>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 border border-orange-100 dark:border-orange-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">This Month Expenses</p>
                    <p class="text-2xl font-bold text-orange-600">₹{{ $formatIndianAmount($currentMonthExpenses) }}</p>
                </div>
                <div class="bg-cyan-50 dark:bg-cyan-900/20 rounded-xl p-4 border border-cyan-100 dark:border-cyan-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Net Profit</p>
                    <p class="text-2xl font-bold {{ $currentMonthProfit >= 0 ? 'text-cyan-600' : 'text-red-600' }}">₹{{ $formatIndianAmount($currentMonthProfit) }}</p>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Profit & Loss (6 months)</h3>
                <div class="relative h-80"><canvas id="chartPnL"></canvas></div>
            </div>
        </div>
        @endcan

        <!-- Workforce & Attendance Tab (HR) -->
        @can('users.view')
        <div id="panel-workforce" class="chart-panel p-4 sm:p-6 {{ $activeChartTab === 'workforce' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Department Distribution -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Staff by Department</h3>
                        @can('departments.view')
                        <a href="{{ route('admin.departments.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">All Departments &rarr;</a>
                        @endcan
                    </div>
                    @php
                        $deptMax = max(array_values($employeesByDepartment ?: [1])) ?: 1;
                        $deptColors = ['bg-indigo-500', 'bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-amber-500', 'bg-teal-500'];
                        $colorIndex = 0;
                    @endphp
                    <div class="space-y-3">
                        @forelse($employeesByDepartment as $deptName => $deptCount)
                            @php
                                $barColor = $deptColors[$colorIndex % count($deptColors)];
                                $colorIndex++;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($deptName) }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $deptCount }} employee{{ $deptCount > 1 ? 's' : '' }}</span>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width: {{ min(100, ($deptCount / $deptMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-4 text-center">No department records found.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Today's Attendance & Duty Summary -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Today's Attendance Status</h3>
                        @can('attendance.view')
                        <a href="{{ route('admin.attendance.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Daily Sheet &rarr;</a>
                        @endcan
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                            <span class="text-xs text-gray-500">Present</span>
                            <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ $hrStats['present_today'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                            <span class="text-xs text-gray-500">Late Arrival</span>
                            <p class="text-xl font-bold text-amber-600 mt-0.5">{{ $hrStats['late_today'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                            <span class="text-xs text-gray-500">On Leave</span>
                            <p class="text-xl font-bold text-purple-600 mt-0.5">{{ $hrStats['on_leave_today'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                            <span class="text-xs text-gray-500">Pending Requests</span>
                            <p class="text-xl font-bold text-rose-600 mt-0.5">{{ $hrStats['pending_leaves'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-indigo-900 dark:text-indigo-200">Total Active Workforce</p>
                            <p class="text-xs text-indigo-700 dark:text-indigo-300">{{ $hrStats['total_employees'] ?? 0 }} staff registered across all departments</p>
                        </div>
                        @can('attendance.view')
                        <a href="{{ route('admin.attendance.monthly') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Monthly &rarr;</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- SECTION 4: USERS & ROLES (admin only)        -->
<!-- ============================================ -->
@can('roles.view')
    <div class="mb-8 dash-animate">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Users &amp; Roles</h2>
            </div>
            @can('users.view')
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    Manage Users
                </a>
            @endcan
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            @foreach (['Role', 'Users', 'Can access', 'Data scope'] as $heading)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($roleSummary as $summary)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-800 dark:text-white">
                                        {{ ucfirst($summary['name']) }}</span>
                                    @if ($summary['is_admin'])
                                        <span
                                            class="ml-2 px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 text-[10px] font-bold uppercase">
                                            Full access</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $summary['users_count'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $summary['access'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $summary['scope'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $summary['model']) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                            Edit permissions
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">View only</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($roleSummary as $summary)
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-semibold text-gray-800 dark:text-white">
                                {{ ucfirst($summary['name']) }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $summary['users_count'] }} user(s)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Can access:</span>
                            {{ $summary['access'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Data scope:</span>
                            {{ $summary['scope'] }}</p>
                        @can('roles.edit')
                            <a href="{{ route('admin.roles.edit', $summary['model']) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                Edit permissions</a>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endcan

@if(! $isManager && ! $isEmployee && (auth()->user()->isAdmin() || auth()->user()->can('leads.view') || auth()->user()->can('invoices.view')))
<!-- ============================================ -->
<!-- SECTION 5: WIDGETS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate dash-animate-delay-3">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Overview Widgets</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Upcoming Meetings -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-purple-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-purple-500 to-violet-600 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Upcoming Meetings
                </h3>
                <a href="{{ route('leads.calendar') }}" class="text-xs text-purple-100 hover:text-white no-print">Calendar →</a>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($upcomingMeetings as $meeting)
                    <a href="{{ route('leads.show', $meeting->lead_id) }}" class="block p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $meeting->lead->name ?? 'Lead' }}</p>
                        <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">{{ $meeting->followup_date->format('M d, Y · h:i A') }}</p>
                        @if($meeting->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $meeting->description }}</p>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No upcoming meetings.</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-blue-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Upcoming Follow-ups</h3>
                <a href="{{ route('leads.calendar') }}" class="text-xs text-blue-100 hover:text-white no-print">View all →</a>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($upcomingFollowups as $fu)
                    <a href="{{ route('leads.show', $fu->lead_id) }}" class="block p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $fu->lead->name ?? 'Lead' }}</p>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">{{ $fu->followup_type }}</span>
                        </div>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">{{ $fu->followup_date->format('M d, Y · h:i A') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No upcoming follow-ups.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-gray-700 to-gray-800">
                <h3 class="text-sm font-bold text-white">Recent Activity</h3>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="flex gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-500 flex-shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800 dark:text-gray-100">
                                <span class="font-semibold">{{ $activity->user->name ?? 'User' }}</span>
                                <span class="text-gray-500">· {{ $activity->description }}</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if($activity->lead)
                                    <a href="{{ route('leads.show', $activity->lead) }}" class="text-blue-600 hover:underline">{{ $activity->lead->name }}</a> ·
                                @endif
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No recent activity.</p>
                @endforelse
            </div>
        </div>

        <!-- Business Health -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-cyan-500 to-teal-600">
                <h3 class="text-sm font-bold text-white">Business Health</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Customers</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($activeCustomers) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Inactive Customers</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($inactiveCustomers) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Open Pipeline</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($newLeads + $contactedLeads + $interestedLeads + $qualifiedLeads) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Pending Expenses</span>
                    <span class="font-bold text-amber-600">₹{{ $formatIndianAmount($pendingExpenses) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Overdue Amount</span>
                    <span class="font-bold text-red-600">₹{{ $formatIndianAmount($overdueInvoices) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- KPI DRILL-DOWN / BREAKDOWN MODAL -->
<!-- ============================================ -->
<div id="kpi-detail-modal" class="hidden fixed inset-0 z-[100] bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 overflow-y-auto" style="z-index: 100 !important;">
    <div class="relative w-full max-w-5xl max-h-[90vh] min-h-[450px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col border border-gray-100 dark:border-gray-700 overflow-hidden" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/70 dark:bg-gray-900/60">
            <div class="flex items-center gap-3">
                <div id="kpi-modal-icon" class="w-10 h-10 rounded-xl flex items-center justify-center bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 id="kpi-modal-title" class="text-lg font-bold text-gray-800 dark:text-white">KPI Details</h3>
                    <p id="kpi-modal-subtitle" class="text-xs text-gray-500 dark:text-gray-400">Detailed list of tracked items</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Navigation Tabs inside Modal -->
                <div class="hidden sm:flex items-center bg-gray-200/70 dark:bg-gray-700/60 p-1 rounded-xl text-xs font-semibold">
                    <button type="button" onclick="switchKpiModalTab('collected')" id="kpi-tab-btn-collected" class="kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all text-gray-600 dark:text-gray-300">Amount Collected</button>
                    <button type="button" onclick="switchKpiModalTab('overdue')" id="kpi-tab-btn-overdue" class="kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all text-gray-600 dark:text-gray-300">Overdue Outstanding</button>
                    <button type="button" onclick="switchKpiModalTab('revenue')" id="kpi-tab-btn-revenue" class="kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all text-gray-600 dark:text-gray-300">Monthly Revenue</button>
                    <button type="button" onclick="switchKpiModalTab('expenses')" id="kpi-tab-btn-expenses" class="kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all text-gray-600 dark:text-gray-300">Monthly Expenses</button>
                </div>

                <button type="button" onclick="closeKpiModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Close (Esc)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Tabs (visible on small screens) -->
        <div class="sm:hidden px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex overflow-x-auto gap-1 bg-gray-50 dark:bg-gray-900/40">
            <button type="button" onclick="switchKpiModalTab('collected')" id="kpi-mob-btn-collected" class="kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-semibold">Collected</button>
            <button type="button" onclick="switchKpiModalTab('overdue')" id="kpi-mob-btn-overdue" class="kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-semibold">Overdue</button>
            <button type="button" onclick="switchKpiModalTab('revenue')" id="kpi-mob-btn-revenue" class="kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-semibold">Monthly Rev</button>
            <button type="button" onclick="switchKpiModalTab('expenses')" id="kpi-mob-btn-expenses" class="kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-semibold">Expenses</button>
        </div>

        <!-- Modal Body / Panels -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 max-h-[calc(90vh-140px)]">
            
            <!-- PANEL: AMOUNT COLLECTED -->
            <div id="kpi-panel-collected" class="kpi-modal-panel space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/40">
                    <div>
                        <p class="text-xs font-medium text-green-800 dark:text-green-300">Total Lifetime Payments Received</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">₹{{ number_format($paidInvoices, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($collectedPaymentsList) }} payment record(s) found</p>
                    </div>
                    @can('invoices.view')
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
                        View All Invoices &rarr;
                    </a>
                    @endcan
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Payment Date</th>
                                <th class="px-4 py-3">Invoice #</th>
                                <th class="px-4 py-3">Customer / Company</th>
                                <th class="px-4 py-3">Method &amp; Ref</th>
                                <th class="px-4 py-3 text-right">Amount Received</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @forelse($collectedPaymentsList as $p)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('invoices.show', $p->invoice_id) }}" class="hover:underline">
                                            {{ $p->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                        {{ $p->customer_company ?: ($p->customer_name ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $p->payment_method }}</span>
                                        @if($p->reference_number)
                                            <span class="block text-[11px] text-gray-400">Ref: {{ $p->reference_number }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-green-600 dark:text-green-400">
                                        ₹{{ number_format($p->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('invoices.show', $p->invoice_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No payments recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL: OVERDUE OUTSTANDING -->
            <div id="kpi-panel-overdue" class="kpi-modal-panel space-y-4 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40">
                    <div>
                        <p class="text-xs font-medium text-red-800 dark:text-red-300">Total Unpaid Overdue Balance</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">₹{{ number_format($overdueInvoices, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($overdueInvoicesList) }} invoice(s) currently overdue</p>
                    </div>
                    @can('invoices.view')
                    <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                        Open in Invoices List (Filtered) &rarr;
                    </a>
                    @endcan
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Invoice #</th>
                                <th class="px-4 py-3">Customer / Company</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3 text-right">Invoice Total</th>
                                <th class="px-4 py-3 text-right">Paid So Far</th>
                                <th class="px-4 py-3 text-right">Overdue Balance</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @forelse($overdueInvoicesList as $inv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('invoices.show', $inv['id']) }}" class="hover:underline">
                                            {{ $inv['invoice_number'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                        {{ $inv['customer_company'] ?: ($inv['customer_name'] ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $inv['due_date'] }}</span>
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                            {{ $inv['days_overdue'] }}d overdue
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                                        ₹{{ number_format($inv['total_amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-gray-500 dark:text-gray-400">
                                        ₹{{ number_format($inv['paid_amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-red-600 dark:text-red-400">
                                        ₹{{ number_format($inv['due_amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('invoices.show', $inv['id']) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                            Record Payment
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Great job! No overdue invoices right now.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL: MONTHLY REVENUE -->
            <div id="kpi-panel-revenue" class="kpi-modal-panel space-y-4 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800/40">
                    <div>
                        <p class="text-xs font-medium text-teal-800 dark:text-teal-300">Total Collections in {{ now()->format('F Y') }}</p>
                        <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">₹{{ number_format($currentMonthRevenue, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($monthlyRevenueList) }} payment record(s) received this month</p>
                    </div>
                    @can('invoices.view')
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-teal-600 text-white hover:bg-teal-700 transition">
                        View All Invoices &rarr;
                    </a>
                    @endcan
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Payment Date</th>
                                <th class="px-4 py-3">Invoice #</th>
                                <th class="px-4 py-3">Customer / Company</th>
                                <th class="px-4 py-3">Payment Method</th>
                                <th class="px-4 py-3 text-right">Amount Received</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @forelse($monthlyRevenueList as $rev)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($rev->payment_date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('invoices.show', $rev->invoice_id) }}" class="hover:underline">
                                            {{ $rev->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                        {{ $rev->customer_company ?: ($rev->customer_name ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400 text-xs">
                                        {{ $rev->payment_method }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-teal-600 dark:text-teal-400">
                                        ₹{{ number_format($rev->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('invoices.show', $rev->invoice_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No payments received yet in {{ now()->format('F Y') }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL: MONTHLY EXPENSES -->
            <div id="kpi-panel-expenses" class="kpi-modal-panel space-y-4 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800/40">
                    <div>
                        <p class="text-xs font-medium text-orange-800 dark:text-orange-300">Approved Expenses in {{ now()->format('F Y') }}</p>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">₹{{ number_format($currentMonthExpenses, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($monthlyExpensesList) }} approved expense(s) this month</p>
                    </div>
                    @can('expenses.view')
                    <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-orange-600 text-white hover:bg-orange-700 transition">
                        View All Expenses &rarr;
                    </a>
                    @endcan
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Expense Date</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @forelse($monthlyExpensesList as $exp)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                        {{ $exp['expense_date'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                        {{ $exp['title'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                        {{ $exp['category'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-orange-600 dark:text-orange-400">
                                        ₹{{ number_format($exp['amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('expenses.show', $exp['id']) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No expenses recorded in {{ now()->format('F Y') }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/70 dark:bg-gray-900/60 text-xs text-gray-500 dark:text-gray-400">
            <span>Press <kbd class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono">Esc</kbd> or click outside to close</span>
            <button type="button" onclick="closeKpiModal()" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Close
            </button>
        </div>

    </div>
</div>

<script>
(function () {
    const chartInstances = {};

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function themeColors() {
        const dark = isDark();
        return {
            text: dark ? '#e5e7eb' : '#374151',
            grid: dark ? 'rgba(75,85,99,0.4)' : 'rgba(229,231,235,0.8)',
            palette: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6', '#6366f1', '#ec4899', '#84cc16', '#06b6d4'],
        };
    }

    function destroyCharts() {
        Object.keys(chartInstances).forEach(k => {
            chartInstances[k].destroy();
            delete chartInstances[k];
        });
    }

    // Data from server
    const leadSources = {
        labels: @json($leadsBySource->keys()->values()),
        data: @json($leadsBySource->values()->map(fn($v) => (int)$v)->values()),
    };
    const leadTrends = {
        labels: @json($monthlyLeadTrends->pluck('month')),
        data: @json($monthlyLeadTrends->pluck('count')->map(fn($v) => (int)$v)),
    };
    const invoiceStatus = {
        labels: @json(array_keys($invoicesByStatus)),
        data: @json(array_values($invoicesByStatus)),
    };
    const invoiceTrends = {
        labels: @json($monthlyInvoiceTrends->pluck('month')),
        data: @json($monthlyInvoiceTrends->pluck('total')->map(fn($v) => (float)$v)),
    };
    const expenseStatus = {
        labels: @json(array_keys($expensesByStatus)),
        data: @json(array_values($expensesByStatus)),
    };
    const expenseCategory = {
        labels: @json($expensesByCategory->keys()->values()),
        data: @json($expensesByCategory->values()->map(fn($v) => (float)$v)->values()),
    };
    const expenseTrends = {
        labels: @json($monthlyExpenseTrends->pluck('month')),
        data: @json($monthlyExpenseTrends->pluck('total')->map(fn($v) => (float)$v)),
    };
    const pnl = {
        labels: @json($monthlyPnL->pluck('month')),
        revenue: @json($monthlyPnL->pluck('revenue')),
        expenses: @json($monthlyPnL->pluck('expenses')),
        profit: @json($monthlyPnL->pluck('profit')),
    };
    const paidUnpaid = {
        labels: ['Paid', 'Unpaid'],
        data: [{{ (float) $paidInvoices }}, {{ (float) $unpaidInvoices }}],
    };

    function makeDoughnut(id, labels, data) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    data: data.length ? data : [1],
                    backgroundColor: labels.length ? c.palette : ['#d1d5db'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: c.text, boxWidth: 12, padding: 12 } }
                }
            }
        });
    }

    function makeLine(id, labels, datasets) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: { beginAtZero: true, ticks: { color: c.text }, grid: { color: c.grid } }
                },
                plugins: { legend: { labels: { color: c.text } } }
            }
        });
    }

    function makeBar(id, labels, datasets) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: { beginAtZero: true, ticks: { color: c.text }, grid: { color: c.grid } }
                },
                plugins: { legend: { labels: { color: c.text } } }
            }
        });
    }

    window.initDashboardCharts = function () {
        destroyCharts();
        const c = themeColors();

        makeDoughnut('chartLeadSources', leadSources.labels, leadSources.data);
        makeLine('chartLeadTrend', leadTrends.labels, [{
            label: 'Leads',
            data: leadTrends.data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.15)',
            fill: true,
            tension: 0.35,
        }]);

        makeDoughnut('chartInvoiceStatus', invoiceStatus.labels, invoiceStatus.data);
        makeDoughnut('chartPaidUnpaid', paidUnpaid.labels, paidUnpaid.data);
        makeBar('chartInvoiceTrend', invoiceTrends.labels, [{
            label: 'Invoice Totals',
            data: invoiceTrends.data,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]);

        makeDoughnut('chartExpenseCategory', expenseCategory.labels, expenseCategory.data);
        makeDoughnut('chartExpenseStatus', expenseStatus.labels, expenseStatus.data);
        makeBar('chartExpenseTrend', expenseTrends.labels, [{
            label: 'Approved Expenses',
            data: expenseTrends.data,
            backgroundColor: '#f59e0b',
            borderRadius: 6,
        }]);

        makeBar('chartPnL', pnl.labels, [
            { label: 'Revenue', data: pnl.revenue, backgroundColor: '#14b8a6', borderRadius: 4 },
            { label: 'Expenses', data: pnl.expenses, backgroundColor: '#f97316', borderRadius: 4 },
            { label: 'Profit', data: pnl.profit, backgroundColor: '#06b6d4', borderRadius: 4 },
        ]);
    };

    window.showChartTab = function (tab) {
        document.querySelectorAll('.chart-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
        const panel = document.getElementById('panel-' + tab);
        const btn = document.getElementById('tab-' + tab);
        if (panel) panel.classList.remove('hidden');
        if (btn) btn.classList.add('active');
        // Charts in hidden panels need resize
        setTimeout(() => {
            Object.values(chartInstances).forEach(ch => ch.resize());
        }, 50);
    };

    document.addEventListener('DOMContentLoaded', function () {
        initDashboardCharts();
    });

    // Re-render charts when theme toggles
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(m => {
            if (m.attributeName === 'class') {
                initDashboardCharts();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    // KPI Breakdown Modal Functions
    function ensureKpiModalInBody() {
        const modal = document.getElementById('kpi-detail-modal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureKpiModalInBody);
    } else {
        ensureKpiModalInBody();
    }

    window.openKpiModal = function (tab) {
        ensureKpiModalInBody();
        const modal = document.getElementById('kpi-detail-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        switchKpiModalTab(tab);
    };

    window.closeKpiModal = function () {
        const modal = document.getElementById('kpi-detail-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.switchKpiModalTab = function (tab) {
        const tabs = ['collected', 'overdue', 'revenue', 'expenses'];
        const titles = {
            'collected': { title: 'Amount Collected Breakdown', sub: 'All lifetime payments received against invoices', iconBg: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400' },
            'overdue': { title: 'Overdue Outstanding Invoices', sub: 'Unpaid balance for invoices past their due date', iconBg: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400' },
            'revenue': { title: 'Monthly Revenue Breakdown', sub: 'Payments received during {{ now()->format("F Y") }}', iconBg: 'bg-teal-100 text-teal-600 dark:bg-teal-900/40 dark:text-teal-400' },
            'expenses': { title: 'Monthly Expenses Breakdown', sub: 'Approved expenses during {{ now()->format("F Y") }}', iconBg: 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-400' }
        };

        tabs.forEach(t => {
            const panel = document.getElementById('kpi-panel-' + t);
            const btnDesktop = document.getElementById('kpi-tab-btn-' + t);
            const btnMobile = document.getElementById('kpi-mob-btn-' + t);

            if (t === tab) {
                if (panel) panel.classList.remove('hidden');
                if (btnDesktop) {
                    btnDesktop.className = 'kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm font-bold';
                }
                if (btnMobile) {
                    btnMobile.className = 'kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm';
                }
            } else {
                if (panel) panel.classList.add('hidden');
                if (btnDesktop) {
                    btnDesktop.className = 'kpi-modal-tab-btn px-3 py-1.5 rounded-lg transition-all text-gray-600 dark:text-gray-300 hover:text-gray-900 font-medium';
                }
                if (btnMobile) {
                    btnMobile.className = 'kpi-modal-tab-btn whitespace-nowrap px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400';
                }
            }
        });

        const info = titles[tab] || titles['collected'];
        const titleEl = document.getElementById('kpi-modal-title');
        const subEl = document.getElementById('kpi-modal-subtitle');
        const iconEl = document.getElementById('kpi-modal-icon');
        if (titleEl) titleEl.textContent = info.title;
        if (subEl) subEl.textContent = info.sub;
        if (iconEl) iconEl.className = 'w-10 h-10 rounded-xl flex items-center justify-center ' + info.iconBg;
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeKpiModal();
    });

    document.getElementById('kpi-detail-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeKpiModal();
    });
})();
</script>

@endsection
