@extends('layouts.app')

@section('title', 'Team Member Profile - ' . $member->name)
@section('page-title', 'Team Member Details')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-end">
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAssignTaskModal()"
                class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Assign Task</span>
            </button>
            <a href="{{ route('admin.attendance.index', ['search' => $member->name]) }}"
                class="px-3.5 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-colors">
                View Attendance Sheet
            </a>
            <a href="{{ route('admin.leaves.index', ['search' => $member->name]) }}"
                class="px-3.5 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-colors">
                View Leave Requests
            </a>
        </div>
    </div>

    <!-- Member Profile Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @php
                    $initials = collect(explode(' ', $member->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
                @endphp
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-gray-700 to-gray-900 text-white font-extrabold text-xl flex items-center justify-center shadow-md">
                    {{ $initials ?: 'EM' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $member->name }}</h1>
                        @if($member->emp_id)
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono text-xs font-semibold">
                                {{ $member->emp_id }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ ucwords($member->designation ?? 'Team Member') }} &bull; {{ ucfirst($member->department ?? 'General') }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $member->email }}
                    </p>
                </div>
            </div>

            <!-- Month Summary Highlights -->
            <div class="flex items-center gap-6 bg-gray-50 dark:bg-gray-700/50 px-5 py-3 rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Present</p>
                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $presentCount }}d</p>
                </div>
                <div class="w-px h-8 bg-gray-200 dark:bg-gray-600"></div>
                <div class="text-center">
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Late</p>
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $lateCount }}d</p>
                </div>
                <div class="w-px h-8 bg-gray-200 dark:bg-gray-600"></div>
                <div class="text-center">
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hours Worked</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ $totalHours }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balances Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Leave Balances
        </h2>
        @if($member->leaveBalances->isEmpty())
            <p class="text-sm text-gray-400 italic">No leave balances allocated yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($member->leaveBalances as $balance)
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $balance->leaveType->name ?? 'Leave Type' }}</p>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $balance->remaining_days }}</span>
                            <span class="text-xs text-gray-400">/ {{ $balance->total_allocated }} allocated</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                            @php
                                $percent = $balance->total_allocated > 0 ? min(100, round(($balance->remaining_days / $balance->total_allocated) * 100)) : 0;
                            @endphp
                            <div class="bg-teal-600 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Attendance Log & Leave Applications (2 columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Attendance Records -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Recent Attendance (This Month)</span>
                <span class="text-xs font-normal text-gray-400">{{ $monthlyAttendances->count() }} records</span>
            </h2>

            @if($monthlyAttendances->isEmpty())
                <p class="text-sm text-gray-400 italic py-4 text-center">No attendance logged this month.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                    @foreach($monthlyAttendances as $record)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($record->date)->format('D, d M Y') }}</span>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    @if($record->check_in)
                                        In: {{ \Carbon\Carbon::parse($record->check_in)->format('h:i A') }}
                                        @if($record->check_out)
                                            &bull; Out: {{ \Carbon\Carbon::parse($record->check_out)->format('h:i A') }}
                                        @endif
                                    @else
                                        No punch times recorded
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                                    @if($record->status === 'present') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                    @elseif($record->status === 'late') bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300
                                    @elseif($record->status === 'half_day') bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300
                                    @else bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                                @if($record->total_minutes > 0)
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ floor($record->total_minutes / 60) }}h {{ $record->total_minutes % 60 }}m
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Leave Requests -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Recent Leave Requests</span>
                <span class="text-xs font-normal text-gray-400">{{ $recentLeaves->count() }} records</span>
            </h2>

            @if($recentLeaves->isEmpty())
                <p class="text-sm text-gray-400 italic py-4 text-center">No leave applications found.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                    @foreach($recentLeaves as $leave)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $leave->leaveType->name ?? 'Leave' }} ({{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }})
                                </span>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                </div>
                                @if($leave->reason)
                                    <p class="text-[11px] text-gray-500 italic mt-0.5 truncate max-w-xs">&ldquo;{{ $leave->reason }}&rdquo;</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                                    @if($leave->status === 'approved') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                    @elseif($leave->status === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300
                                    @else bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 @endif">
                                    {{ ucfirst($leave->status) }}
                                </span>
                                @if($leave->approver)
                                    <p class="text-[10px] text-gray-400 mt-0.5">by {{ $leave->approver->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- Assigned Tasks (Task Pool) Card -->
    @if($currentUser->hasTasks() && $member->hasTasks())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Tasks Assigned to {{ $member->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300">
                            {{ $tasks->count() }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Current deliverables & verified shift punch-out work logs
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openAssignTaskModal()"
                    class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Assign Task</span>
                </button>
                <a href="{{ route('tasks.index', ['assigned_to' => $member->id]) }}"
                    class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    View in Pool &rarr;
                </a>
            </div>
        </div>

        @if($tasks->isEmpty())
            <div class="p-8 text-center bg-gray-50 dark:bg-gray-750 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No tasks currently assigned to {{ $member->name }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Assign tasks here so {{ $member->name }} can work on them and check them off at shift punch-out.</p>
                <button type="button" onclick="openAssignTaskModal()"
                    class="mt-3 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                    Assign First Task
                </button>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700/60 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[10px] font-bold uppercase tracking-wider text-gray-400 bg-gray-50/50 dark:bg-gray-750">
                            <th class="py-2.5 px-3">Task Title</th>
                            <th class="py-2.5 px-3">Priority</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Due Date</th>
                            <th class="py-2.5 px-3">Shift Work Logs</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($tasks as $t)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 font-semibold text-gray-900 dark:text-white">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($t->project_name)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-violet-50 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 border border-violet-100 dark:border-violet-800">
                                            <svg class="w-3 h-3 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                            {{ $t->project_name }}
                                        </span>
                                    @endif
                                    <a href="{{ route('tasks.show', $t) }}" class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $t->title }}
                                    </a>
                                </div>
                                @if($t->description)
                                    <p class="text-[11px] font-normal text-gray-500 dark:text-gray-400 truncate max-w-sm mt-0.5">{{ $t->description }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $t->priorityBadgeClasses() }}">
                                    {{ $t->priority }}
                                </span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <form action="{{ route('tasks.status', $t) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="text-[11px] font-semibold rounded py-0.5 px-2 border-0 focus:ring-1 focus:ring-indigo-500 cursor-pointer {{ $t->statusBadgeClasses() }}">
                                        <option value="pending" {{ $t->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $t->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $t->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                {{ $t->due_date ? $t->due_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($t->workLogs->count() > 0)
                                    <a href="{{ route('tasks.show', $t) }}" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                        <span>{{ $t->workLogs->count() }} shift log{{ $t->workLogs->count() > 1 ? 's' : '' }}</span>
                                    </a>
                                @else
                                    <span class="text-gray-400">Not logged yet</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap text-right">
                                <a href="{{ route('tasks.show', $t) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                    View Details
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

<!-- Assign Task to Member Modal -->
<div id="assign-task-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeAssignTaskModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="assigned_to" value="{{ $member->id }}">
                <input type="hidden" name="department" value="{{ $member->department }}">

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            Assign Task to {{ $member->name }}
                        </h3>
                        <button type="button" onclick="closeAssignTaskModal()" class="text-gray-400 hover:text-gray-500 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Task Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Implement user login API"
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Project Name</label>
                        <input type="text" name="project_name" placeholder="e.g. Website Redesign, Mobile App v2, ICPG RBAC..."
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description / Notes</label>
                        <textarea name="description" rows="3" placeholder="Deliverables, scope, or instructions..."
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                            <select name="priority" required
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                                <option value="low">Low</option>
                                <option value="medium" {{ auth()->user()->isAdmin() ? '' : 'selected' }}>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent" {{ auth()->user()->isAdmin() ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @if (auth()->user()->isAdmin())
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">
                                ⚡ Admin assigning to staff automatically marks task as <strong>Urgent</strong>.
                            </p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                            <input type="date" name="due_date"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeAssignTaskModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        Assign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAssignTaskModal() {
        document.getElementById('assign-task-modal').classList.remove('hidden');
    }
    function closeAssignTaskModal() {
        document.getElementById('assign-task-modal').classList.add('hidden');
    }
</script>
@endif
@endsection
