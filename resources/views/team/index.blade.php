@extends('layouts.app')

@section('title', 'My Team')
@section('page-title', 'My Team')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                My Team
            </h1>
        </div>

        <div class="flex items-center gap-2">
            @if($currentUser->hasTasks())
            <a href="{{ route('tasks.index', ['tab' => 'daily']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Daily Tasks Done ({{ $todayTeamTasksCompletedCount }})</span>
            </a>
            <a href="{{ route('tasks.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Task Pool</span>
            </a>
            @endif

            @if($pendingLeavesCount > 0 && Gate::allows('leaves.approve'))
                <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $pendingLeavesCount }} Pending Leave {{ Str::plural('Approval', $pendingLeavesCount) }}</span>
                </a>
            @endif
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Members -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Team Size</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCount }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Active supervised staff</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- Present Today -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Present Today</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $presentTodayCount }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    Clocked in today
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- On Leave Today -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">On Leave Today</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $onLeaveTodayCount }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Approved out of office</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Not Clocked In / Absent -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Not Clocked In</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $notClockedInCount }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pending punch-in</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Leave Notice (if any) -->
    @if($pendingLeaves->isNotEmpty())
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/30 border border-amber-200 dark:border-amber-800 rounded-xl p-4 sm:p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-500 text-white rounded-lg flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                            Action Needed: {{ $pendingLeaves->count() }} Leave Request(s) Awaiting Your Review
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            Team members have applied for leaves that require manager approval.
                        </p>
                        <div class="mt-2.5 flex flex-wrap gap-2">
                            @foreach($pendingLeaves->take(3) as $pl)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 text-xs text-gray-700 dark:text-gray-300">
                                    <span class="font-semibold">{{ $pl->user->name }}:</span>
                                    <span>{{ $pl->leaveType->name ?? 'Leave' }} ({{ $pl->total_days }} {{ Str::plural('day', $pl->total_days) }})</span>
                                </span>
                            @endforeach
                            @if($pendingLeaves->count() > 3)
                                <span class="text-xs text-gray-500 self-center">+{{ $pendingLeaves->count() - 3 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}"
                    class="px-3.5 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors">
                    Review Leaves &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <form method="GET" action="{{ route('team.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Search Member</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name, email, or EMP ID..."
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 pl-9 pr-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Designation Filter -->
            <div>
                <label for="designation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Designation</label>
                <select name="designation" id="designation"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">All Designations</option>
                    @foreach($designations as $desig)
                        <option value="{{ $desig }}" {{ request('designation') === $desig ? 'selected' : '' }}>
                            {{ ucwords($desig) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Today's Status</label>
                <select name="status" id="status"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present / Clocked In</option>
                    <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="not_clocked_in" {{ request('status') === 'not_clocked_in' ? 'selected' : '' }}>Not Clocked In</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2 px-4 rounded-lg text-sm shadow transition-all duration-200">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'designation', 'status']))
                    <a href="{{ route('team.index') }}"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg text-sm font-semibold transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Team Members Grid -->
    @if($members->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">No team members found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No employees match your active filter criteria or are assigned under your supervision.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($members as $member)
                @php
                    $initials = collect(explode(' ', $member->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
                    $hasPunchIn = $member->today_attendance && $member->today_attendance->check_in;
                    $punchInTime = $hasPunchIn ? \Carbon\Carbon::parse($member->today_attendance->check_in)->format('h:i A') : null;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden">
                    
                    <!-- Card Top Info -->
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <a href="{{ route('admin.users.show', $member) }}" class="group" title="View {{ $member->name }}'s Profile">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-gray-700 to-gray-900 text-white font-bold text-base flex items-center justify-center shadow flex-shrink-0 group-hover:scale-105 group-hover:ring-2 group-hover:ring-teal-500 transition-all">
                                        {{ $initials ?: 'EM' }}
                                    </div>
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.show', $member) }}" class="group" title="View {{ $member->name }}'s Profile">
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                            {{ $member->name }}
                                        </h3>
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($member->emp_id)
                                            <a href="{{ route('admin.users.show', $member) }}" title="View {{ $member->name }}'s Profile">
                                                <span class="inline-block px-1.5 py-0.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[11px] font-mono font-bold rounded border border-indigo-200/60 dark:border-indigo-800/60 transition">
                                                    {{ $member->emp_id }}
                                                </span>
                                            </a>
                                        @endif
                                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ ucwords($member->designation ?? 'Team Member') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Status Badge -->
                            @if($member->computed_status === 'present')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Present
                                </span>
                            @elseif($member->computed_status === 'late')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Late
                                </span>
                            @elseif($member->computed_status === 'on_leave')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    On Leave
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Not In
                                </span>
                            @endif
                        </div>

                        <!-- Today's Attendance Detail Snippet -->
                        <div class="mt-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Today's Punch:</span>
                                @if($hasPunchIn)
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                                        In: {{ $punchInTime }}
                                        @if($member->today_attendance->check_out)
                                            &bull; Out: {{ \Carbon\Carbon::parse($member->today_attendance->check_out)->format('h:i A') }}
                                        @endif
                                    </span>
                                @elseif($member->today_leave)
                                    <span class="font-semibold text-purple-600 dark:text-purple-400">
                                        {{ $member->today_leave->leaveType->name ?? 'Approved Leave' }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 italic">No punch record yet</span>
                                @endif
                            </div>
                            @if($member->hasTasks() && $member->today_tasks_completed_count > 0)
                            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-gray-200/50 dark:border-gray-600/50">
                                <span class="text-gray-500 dark:text-gray-400">Daily Deliverables:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <span>✓ {{ $member->today_tasks_completed_count }} {{ Str::plural('task', $member->today_tasks_completed_count) }} done today</span>
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Month-to-date Stats -->
                        <div class="grid grid-cols-3 gap-2 mt-4 text-center divide-x divide-gray-100 dark:divide-gray-700">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Present</p>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $member->month_present_days }}d</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
                                <p class="text-sm font-bold {{ $member->month_late_days > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-800 dark:text-gray-200' }} mt-0.5">
                                    {{ $member->month_late_days }}d
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Hours</p>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $member->month_total_hours }}</p>
                            </div>
                        </div>

                        <!-- Email contact -->
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="truncate flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $member->email }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="px-5 py-3 bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <a href="{{ route('admin.users.show', $member) }}"
                            class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 flex items-center gap-1">
                            <span>View Profile & History</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.users.show', $member) }}"
                                title="View Member Profile & Assigned Tasks"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profile</span>
                            </a>
                            <a href="{{ route('admin.attendance.index', ['search' => $member->name]) }}"
                                title="View in Attendance Sheet"
                                class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </a>
                            <a href="{{ route('admin.leaves.index', ['search' => $member->name]) }}"
                                title="View Leave Requests"
                                class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
