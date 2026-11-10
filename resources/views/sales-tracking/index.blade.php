@extends('layouts.app')

@section('title', 'Sales Tracking')
@section('page-title', 'Sales Tracking')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Range Filter Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-5 sm:p-6 transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Sales Tracking & Performance
                </h1>
                <p class="text-xs text-slate-400 font-medium mt-1">
                    {{ $rangeLabel }}
                    @if (! $isManager)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                            Personal Performance View
                        </span>
                    @endif
                </p>
            </div>

            <!-- Date Range & Filter Form -->
            <form method="GET" action="{{ route('sales-tracking.index') }}" class="flex flex-wrap items-center gap-2.5">
                @if ($isManager)
                    <div class="w-full sm:w-auto">
                        <select name="employee_id" onchange="this.form.submit()"
                            class="w-full sm:w-48 text-xs font-medium rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Team Overview (All)</option>
                            @foreach ($teamMembers as $member)
                                <option value="{{ $member->id }}" {{ (string)$selectedEmployeeId === (string)$member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-900 p-1 border border-slate-200 dark:border-slate-700/70 text-xs font-semibold">
                    <a href="{{ route('sales-tracking.index', ['range' => 'today', 'employee_id' => $selectedEmployeeId]) }}"
                        class="px-3 py-1.5 rounded-lg transition-all {{ $range === 'today' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        Today
                    </a>
                    <a href="{{ route('sales-tracking.index', ['range' => 'this_week', 'employee_id' => $selectedEmployeeId]) }}"
                        class="px-3 py-1.5 rounded-lg transition-all {{ $range === 'this_week' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        This Week
                    </a>
                    <a href="{{ route('sales-tracking.index', ['range' => 'this_month', 'employee_id' => $selectedEmployeeId]) }}"
                        class="px-3 py-1.5 rounded-lg transition-all {{ $range === 'this_month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        This Month
                    </a>
                    <a href="{{ route('sales-tracking.index', ['range' => 'last_month', 'employee_id' => $selectedEmployeeId]) }}"
                        class="px-3 py-1.5 rounded-lg transition-all {{ $range === 'last_month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        Last Month
                    </a>
                    <button type="button" onclick="document.getElementById('custom-date-box').classList.toggle('hidden')"
                        class="px-3 py-1.5 rounded-lg transition-all {{ $range === 'custom' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        Custom
                    </button>
                </div>
            </form>
        </div>

        <!-- Custom Date Drawer -->
        <div id="custom-date-box" class="{{ $range === 'custom' ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60">
            <form method="GET" action="{{ route('sales-tracking.index') }}" class="flex flex-wrap items-end gap-3 text-xs">
                <input type="hidden" name="range" value="custom">
                @if ($selectedEmployeeId)
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
                @endif
                <div>
                    <label class="block text-slate-500 font-medium mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                        class="rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs px-3 py-2">
                </div>
                <div>
                    <label class="block text-slate-500 font-medium mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                        class="rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs px-3 py-2">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all shadow-sm">
                    Apply Filter
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Core Metric Cards: Won Deals, Revenue, Conversion Rate, Meetings & Calls -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Customers Converted / Won Deals (This Month) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-emerald-200 dark:border-emerald-900/50 shadow-sm bg-gradient-to-br from-white to-emerald-50/20 dark:from-slate-800 dark:to-emerald-950/20 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Won Deals</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($wonLeads) }}</span>
                <span class="text-xs font-semibold text-emerald-700/80 dark:text-emerald-400">Converted</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                {{ $lostLeads }} marked as lost
            </div>
        </div>

        <!-- 2. Revenue -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-blue-200 dark:border-blue-900/50 shadow-sm bg-gradient-to-br from-white to-blue-50/20 dark:from-slate-800 dark:to-blue-950/20 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Revenue</span>
                <span class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm">
                    ₹
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-blue-600 dark:text-blue-400">₹{{ number_format($totalRevenue) }}</span>
                <span class="text-xs text-slate-400">Won</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Closed deals & invoices
            </div>
        </div>

        <!-- 3. Conversion Rate -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-indigo-200 dark:border-indigo-900/50 shadow-sm bg-gradient-to-br from-white to-indigo-50/20 dark:from-slate-800 dark:to-indigo-950/20 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Conversion Rate</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $conversionRate }}%</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Won leads / total leads
            </div>
        </div>

        <!-- 4. Meetings & Calls -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-purple-200 dark:border-purple-900/50 shadow-sm bg-gradient-to-br from-white to-purple-50/20 dark:from-slate-800 dark:to-purple-950/20 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Meetings & Calls</span>
                <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $followupsCompleted }}<span class="text-lg font-normal text-slate-400">/{{ $followupsScheduled }}</span></span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
                <span class="font-semibold text-purple-600 dark:text-purple-400">{{ $followupCompletionRate }}%</span> completed
                @if ($meetingsScheduled > 0)
                    &bull; {{ $meetingsScheduled }} meetings
                @endif
            </div>
        </div>
    </div>

    <!-- Manager Only: Employee Comparison Leaderboard -->
    @if ($isManager)
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/70 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/70 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Sales Team Comparison Leaderboard</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Employee-wise breakdown and performance comparison for {{ $rangeLabel }}.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg">
                {{ $employeeComparison->count() }} Reps
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 dark:bg-slate-900/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700/70">
                    <tr>
                        <th class="py-3 px-6">Sales Rep</th>
                        <th class="py-3 px-4 text-center">Leads Assigned</th>
                        <th class="py-3 px-4 text-center">Contacted</th>
                        <th class="py-3 px-4 text-center">Won Deals</th>
                        <th class="py-3 px-4 text-center">Lost</th>
                        <th class="py-3 px-4 text-center">Conversion Rate</th>
                        <th class="py-3 px-4 text-center">Follow-ups Done</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($employeeComparison as $index => $row)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors {{ (string)$selectedEmployeeId === (string)$row->user->id ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($row->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $row->user->name }}</span>
                                            @if ($index === 0 && $row->won > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                    Top Performer
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">{{ $row->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-800 dark:text-slate-200">
                                {{ $row->total_leads }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-600 dark:text-slate-300">
                                {{ $row->contacted }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    {{ $row->won }} Won
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs text-rose-500 font-medium">
                                {{ $row->lost }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="w-16 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, $row->conversion_rate) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $row->conversion_rate }}%</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-600 dark:text-slate-300">
                                {{ $row->followups_completed }} / {{ $row->followups_scheduled }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <a href="{{ route('sales-tracking.index', ['range' => $range, 'employee_id' => $row->user->id]) }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center gap-1">
                                    <span>Drill Down</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-sm text-slate-400">
                                No sales team members found reporting to you.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- 2 Column Pipeline & Schedule Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Pipeline Progression & Recent Won Leads -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Pipeline Status Progression Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200/90 dark:border-slate-700/70 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Pipeline Status Distribution</h3>
                    <span class="text-xs text-slate-400">{{ $totalLeads }} Total Pipeline</span>
                </div>

                <!-- Horizontal Distribution Bar -->
                <div class="h-4 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden flex shadow-inner">
                    @php
                        $total = max(1, $totalLeads);
                        $pNew = ($newLeads / $total) * 100;
                        $pContacted = ($contactedLeads / $total) * 100;
                        $pFollow = ($followUpLeads / $total) * 100;
                        $pWon = ($wonLeads / $total) * 100;
                        $pLost = ($lostLeads / $total) * 100;
                    @endphp
                    @if ($pNew > 0) <div style="width: {{ $pNew }}%" class="bg-slate-400 transition-all" title="New: {{ $newLeads }}"></div> @endif
                    @if ($pContacted > 0) <div style="width: {{ $pContacted }}%" class="bg-cyan-500 transition-all" title="Contacted: {{ $contactedLeads }}"></div> @endif
                    @if ($pFollow > 0) <div style="width: {{ $pFollow }}%" class="bg-amber-500 transition-all" title="Follow-Up: {{ $followUpLeads }}"></div> @endif
                    @if ($pWon > 0) <div style="width: {{ $pWon }}%" class="bg-emerald-500 transition-all" title="Won: {{ $wonLeads }}"></div> @endif
                    @if ($pLost > 0) <div style="width: {{ $pLost }}%" class="bg-rose-500 transition-all" title="Lost: {{ $lostLeads }}"></div> @endif
                </div>

                <!-- Legend & Counts -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> New</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $newLeads }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> Contacted</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $contactedLeads }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> In Follow-Up</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $followUpLeads }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Won Deals</div>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $wonLeads }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-rose-500"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Lost</div>
                        <div class="text-lg font-bold text-rose-600 mt-1">{{ $lostLeads }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Won Leads Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/70 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        Recent Won Deals
                    </h3>
                    <a href="{{ route('leads.index', ['status' => 'won']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View All Won Leads
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse ($recentWonLeads as $wLead)
                        <div class="p-3.5 rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/20 dark:bg-emerald-950/20 flex items-center justify-between">
                            <div>
                                <a href="{{ route('leads.show', $wLead) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 block truncate max-w-[180px]">
                                    {{ $wLead->name }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $wLead->company ?: 'Direct Client' }}</p>
                                <span class="text-[10px] text-slate-400 mt-1 block">Assigned to: {{ $wLead->assignedUser?->name ?? 'Unassigned' }}</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                Won
                            </span>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6 text-sm text-slate-400">
                            No won deals logged in this period.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Upcoming Scheduled Meetings & Reminders -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200/90 dark:border-slate-700/70 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Upcoming Meetings
                    </h3>
                    <a href="{{ route('leads.calendar') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Open Calendar
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($upcomingEvents as $evt)
                        <div class="p-3.5 rounded-xl border border-slate-100 dark:border-slate-700/70 bg-slate-50/60 dark:bg-slate-900/40 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $evt->followup_type === 'meeting' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                    {{ ucfirst($evt->followup_type) }}
                                </span>
                                <span class="text-[11px] text-slate-500 font-medium">
                                    {{ $evt->followup_date?->format('M d, h:i A') }}
                                </span>
                            </div>

                            <a href="{{ $evt->lead ? route('leads.show', $evt->lead) : '#' }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 block">
                                {{ $evt->lead?->name ?? 'Lead' }}
                            </a>

                            @if (! empty($evt->meeting_link))
                                <div class="pt-1">
                                    <a href="{{ $evt->meeting_link }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        Join Room Link
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-slate-400">
                            No upcoming meetings scheduled.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60">
                    <a href="{{ route('leads.calendar') }}"
                        class="w-full inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Schedule Follow-Up / Meeting
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
