@extends('layouts.app')

@section('title', $project->title . ' - Tasks & Work Checklist')

@section('content')
@php
    $colorCfg = $project->colorConfig();
    $completion = $completion ?? $project->completionPercentage();
    $totalTasks = $totalTasks ?? $project->totalTasksCount();
    $completedTasks = $completedTasks ?? $project->completedTasksCount();
    $pendingTasks = $pendingTasks ?? $project->pendingTasksCount();
    $inProgressTasks = $inProgressTasks ?? $project->inProgressTasksCount();
    $assignees = $tasks->getCollection()->pluck('assignee')->filter()->unique('id');
@endphp

<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Top Bar: Navigation & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div>
            <a href="{{ route('tasks.index') }}"
                class="inline-flex items-center gap-2 h-10 px-3.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 shadow-xs hover:shadow-sm transition">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Task Pool</span>
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
            @can('create', App\Models\Task::class)
                @if($project->canBeAccessedBy(auth()->user()))
                <button type="button" onclick="openCreateTaskModal()"
                    class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Task to Project</span>
                </button>
                @endif

                @if($project->canBeManagedBy(auth()->user()))
                <button type="button" onclick='openEditProjectModal(@json($project))'
                    class="inline-flex items-center justify-center gap-1.5 h-10 px-3.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Edit Project</span>
                </button>

                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline-flex m-0 p-0"
                    onsubmit="return confirm('Delete this project? Associated tasks will remain in the pool.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/40 border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-800 shadow-sm transition"
                        title="Delete Project">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
                @endif
            @endcan
        </div>
    </div>

    <!-- Project Overview Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/90 dark:border-gray-700/80 shadow-sm overflow-hidden" id="project-card-{{ $project->id }}">
        <!-- Top Colored Stripe -->
        <div class="h-2.5 w-full {{ $colorCfg['bar'] }}"></div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex items-start gap-4 min-w-0">
                    <div class="w-14 h-14 rounded-2xl flex-shrink-0 flex items-center justify-center {{ $colorCfg['tag_bg'] }} border shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>

                    <div class="min-w-0 space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase tracking-wider {{ $colorCfg['tag_bg'] }}">
                                Project
                            </span>
                            @if ($project->client_name)
                            <span class="text-xs px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-medium">
                                POC: <strong class="font-bold">{{ $project->client_name }}</strong>
                            </span>
                            @endif
                            @if ($project->department)
                            <span class="text-xs px-2.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ ucfirst($project->department) }}
                            </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $project->statusBadgeClasses() }}">
                                {{ str_replace('_', ' ', $project->status) }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight break-words">
                            {{ $project->title }}
                        </h1>

                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Created by {{ $project->creator?->name ?? 'Management' }} on {{ $project->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                @if ($project->due_date)
                <div class="flex-shrink-0 flex items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700">
                    <svg class="w-5 h-5 {{ $project->isOverdue() ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Target Deadline</div>
                        <div class="text-sm font-bold {{ $project->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}">
                            {{ $project->due_date->format('M d, Y') }}
                            @if ($project->isOverdue())
                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 uppercase font-bold">Overdue</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if ($project->description)
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block mb-1">Project Scope & Deliverables</span>
                <p class="whitespace-pre-line leading-relaxed">{{ $project->description }}</p>
            </div>
            @endif

            <!-- Metrics Summary Row -->
            <div class="pt-2">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700/80 font-medium text-gray-700 dark:text-gray-300">
                            Total Tasks: <strong class="font-bold" id="stat-total-tasks">{{ $totalTasks }}</strong>
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 font-medium text-emerald-700 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/40">
                            Done: <strong class="font-bold" id="stat-completed-tasks">{{ $completedTasks }}</strong>
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 font-medium text-blue-700 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/40">
                            In Progress: <strong class="font-bold" id="stat-inprogress-tasks">{{ $inProgressTasks }}</strong>
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 font-medium text-amber-700 dark:text-amber-300 border border-amber-200/50 dark:border-amber-800/40">
                            Pending: <strong class="font-bold" id="stat-pending-tasks">{{ $pendingTasks }}</strong>
                        </span>
                    </div>

                    @if ($isManager && $assignees->isNotEmpty())
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-400 font-medium">Assigned Team:</span>
                        <div class="flex -space-x-2 overflow-hidden">
                            @foreach ($assignees as $emp)
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-xs ring-2 ring-white dark:ring-gray-800"
                                title="{{ $emp->name }} ({{ $emp->designation ?: 'Staff' }})">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Checklist Section -->
    <div class="space-y-4">
        <!-- Header and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Work Items & Task Checklist
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">
                        {{ $tasks instanceof \Illuminate\Pagination\Contracts\Paginator ? $tasks->total() : $tasks->count() }} items
                    </span>
                </div>

                <span class="text-xs text-gray-400">
                    Click any tick mark to mark completed
                </span>
            </div>

            <!-- Filter Controls -->
            <form action="{{ route('projects.show', $project) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                @if (request()->filled('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <div class="sm:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Task ID (e.g. 10 or TSK-00010), title, or details..."
                        class="w-full text-xs sm:text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                </div>

                <div>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full text-xs sm:text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="rework" {{ request('status') === 'rework' ? 'selected' : '' }}>Rework</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select name="priority" onchange="this.form.submit()"
                        class="w-full text-xs sm:text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>

                    @if(request()->anyFilled(['search', 'status', 'priority']))
                    <a href="{{ route('projects.show', $project) }}"
                        class="p-2 text-xs font-semibold text-gray-500 hover:text-red-600 rounded-xl bg-gray-100 dark:bg-gray-700 transition"
                        title="Clear filters">
                        ✕
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Task Items Checklist -->
        <div class="space-y-3">
            @forelse ($tasks as $task)
            @php
                $isDone = $task->isCompleted();
                $canToggle = auth()->user()->isAdmin() || auth()->user()->can('tasks.edit') || $task->assigned_to === auth()->id() || $task->assigned_by === auth()->id();
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800 border {{ $isDone ? 'border-emerald-200/80 dark:border-emerald-900/50 bg-emerald-50/10' : 'border-gray-200/90 dark:border-gray-700' }} shadow-xs hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm transition-all flex items-start justify-between gap-4 group" id="task-row-{{ $task->id }}">
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <!-- Interactive Tick Mark Button -->
                    <button type="button"
                        @if($canToggle)
                        onclick="toggleTaskStatus({{ $task->id }}, {{ $project->id }})"
                        @else
                        disabled
                        @endif
                        id="task-tick-btn-{{ $task->id }}"
                        aria-label="Toggle task status"
                        class="mt-1 flex-shrink-0 w-7 h-7 rounded-xl border-2 flex items-center justify-center transition-all duration-200 {{ $isDone ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm shadow-emerald-500/30' : 'border-gray-300 dark:border-gray-600 hover:border-emerald-500 text-transparent hover:text-emerald-500 bg-white dark:bg-gray-750' }} {{ $canToggle ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                        title="{{ $canToggle ? ($isDone ? 'Mark as incomplete' : 'Tick to mark completed') : 'Assigned to another member' }}">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>

                    <!-- Content -->
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('tasks.show', $task) }}"
                               id="task-title-{{ $task->id }}"
                               class="text-sm sm:text-base font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-all break-words {{ $isDone ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $task->title }}
                            </a>
                            @if (auth()->user()->isAdmin())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $task->departmentBadgeClasses() }}">
                                <span>{{ $task->departmentIcon() }}</span>
                                <span>{{ $task->departmentLabel() }}</span>
                            </span>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize {{ $task->priorityBadgeClasses() }}">
                                {{ $task->priority }}
                            </span>
                            @if ($task->isEscalated())
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-2xs animate-pulse" title="Escalated by Admin: {{ $task->escalation_reason }}">
                                🚨 Escalated
                            </span>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize {{ $task->statusBadgeClasses() }}" id="task-status-badge-{{ $task->id }}">
                                {{ str_replace('_', ' ', $task->status) }}
                            </span>
                        </div>

                        @if ($task->description)
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed pt-0.5">
                            {{ $task->description }}
                        </p>
                        @endif

                        <div class="flex items-center gap-3 flex-wrap pt-2 text-xs">
                            <!-- Assignee -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $task->assigned_to === auth()->id() ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-semibold ring-1 ring-indigo-200 dark:ring-indigo-800' : 'bg-gray-100 dark:bg-gray-700/80 font-medium text-gray-700 dark:text-gray-300' }}">
                                <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[9px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($task->assignee?->name ?? 'U', 0, 1)) }}
                                </span>
                                <span>{{ $task->assigned_to === auth()->id() ? 'Assigned to Myself' : ($task->assignee?->name ?? 'Unassigned') }}</span>
                            </span>

                            @if ($task->due_date)
                            <span class="inline-flex items-center gap-1 font-semibold {{ $task->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_date->format('M d, Y') }}
                                @if ($task->isOverdue())
                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 uppercase font-bold">Overdue</span>
                                @endif
                            </span>
                            @endif

                            <span class="text-gray-400 text-[11px]">
                                Added by {{ $task->creator?->name ?? 'System' }} • {{ $task->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Attachments Display -->
                        @if ($task->attachments && $task->attachments->isNotEmpty())
                        <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center gap-2 flex-wrap">
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Attachments ({{ $task->attachments->count() }}):</span>
                            </span>
                            @foreach ($task->attachments as $att)
                            <a href="{{ route('tasks.attachments.download', [$task, $att]) }}"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50/80 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 text-[11px] font-medium transition"
                                title="Download {{ $att->file_name }} (Uploaded by {{ $att->user?->name ?? 'User' }})">
                                <svg class="w-3 h-3 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="truncate max-w-[150px]">{{ $att->file_name }}</span>
                                <span class="text-[10px] text-indigo-400">({{ $att->file_size_formatted }})</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <!-- Quick Attach File Button -->
                    <button type="button" onclick="openAttachModal({{ $task->id }}, '{{ addslashes($task->title) }}')"
                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition cursor-pointer"
                        title="Upload / Attach File">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </button>

                    <a href="{{ route('tasks.show', $task) }}"
                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition"
                        title="View Task Details">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>

                    @can('update', $task)
                    <button type="button" onclick='openEditTaskModal(@json($task))'
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition"
                        title="Edit Task">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    @endcan

                    @can('delete', $task)
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline"
                        onsubmit="return confirm('Delete this task?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition"
                            title="Delete Task">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">No tasks found in this project</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                    {{ request()->anyFilled(['search', 'status', 'priority']) ? 'No tasks match your selected filter criteria.' : 'Get started by creating the first work item for this project.' }}
                </p>
                @can('create', App\Models\Task::class)
                <button type="button" onclick="openCreateTaskModal()"
                    class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add First Task</span>
                </button>
                @endcan
            </div>
            @endforelse
        </div>

        @if ($tasks->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mt-4">
            {{ $tasks->links('partials.pagination', ['label' => 'tasks']) }}
        </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

<!-- Create Task Modal -->
<div id="create-task-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeCreateTaskModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="p-1.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            Add Task to {{ $project->title }}
                        </h3>
                        <button type="button" onclick="closeCreateTaskModal()" class="text-gray-400 hover:text-gray-500 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Task Title -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Task Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Implement OAuth login, Design Landing Page..."
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                    </div>

                    <!-- Assignee -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Assign To Staff *</label>
                            <button type="button" onclick="assignToMyself()" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                ⚡ Assign to Myself
                            </button>
                        </div>
                        <select name="assigned_to" id="create-task-assigned-to" onchange="handleProjectTaskAssigneeChange(this)" required
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                            <option value="">Select Employee...</option>
                            @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $emp->id === auth()->id() ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->designation ?: 'Staff' }} - {{ ucfirst($emp->department ?: 'General') }})
                            </option>
                            @endforeach
                        </select>
                        @if (auth()->user()->isAdmin())
                        <p id="project_admin_urgent_hint" class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">
                            ⚡ Admin assigning to staff automatically marks task as <strong>Urgent</strong>.
                        </p>
                        @endif
                    </div>

                    <!-- Priority & Due Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                            <select name="priority" id="create-task-priority" required
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                                <option value="low">Low</option>
                                <option value="medium" {{ auth()->user()->isAdmin() ? '' : 'selected' }}>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="urgent" {{ auth()->user()->isAdmin() ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                            <input type="date" name="due_date" value="{{ $project->due_date ? $project->due_date->format('Y-m-d') : '' }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Task Instructions / Description</label>
                        <textarea name="description" rows="3" placeholder="Provide actionable requirements for this work item..."
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateTaskModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div id="edit-task-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditTaskModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
            <form id="edit-task-form" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Task</h3>
                        <button type="button" onclick="closeEditTaskModal()" class="text-gray-400 hover:text-gray-500 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Task Title *</label>
                        <input type="text" id="edit-title" name="title" required
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2.5 px-3">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Assign To</label>
                        <select id="edit-assigned-to" name="assigned_to" required
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                            @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->designation ?: 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea id="edit-description" name="description" rows="3"
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                            <select id="edit-priority" name="priority" required
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select id="edit-status" name="status" required
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                            <input type="date" id="edit-due-date" name="due_date"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeEditTaskModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div id="edit-project-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditProjectModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
            <form id="edit-project-form" method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Project Card</h3>
                        <button type="button" onclick="closeEditProjectModal()" class="text-gray-400 hover:text-gray-500 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Project Name *</label>
                        <input type="text" id="edit-proj-title" name="title" value="{{ $project->title }}" required
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2.5 px-3">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">POC</label>
                            <input type="text" id="edit-proj-client" name="client_name" value="{{ $project->client_name }}" placeholder="e.g. John Doe, Acme Corp..."
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Department</label>
                            <select id="edit-proj-department" name="department"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                                <option value="">Select Department...</option>
                                @foreach (($departments ?? []) as $dept)
                                <option value="{{ $dept }}" {{ $project->department === $dept ? 'selected' : '' }}>{{ ucfirst($dept) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Color Theme</label>
                            <select id="edit-proj-color" name="color"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                                <option value="indigo" {{ $project->color === 'indigo' ? 'selected' : '' }}>Indigo</option>
                                <option value="violet" {{ $project->color === 'violet' ? 'selected' : '' }}>Violet</option>
                                <option value="emerald" {{ $project->color === 'emerald' ? 'selected' : '' }}>Emerald</option>
                                <option value="amber" {{ $project->color === 'amber' ? 'selected' : '' }}>Amber</option>
                                <option value="rose" {{ $project->color === 'rose' ? 'selected' : '' }}>Rose</option>
                                <option value="sky" {{ $project->color === 'sky' ? 'selected' : '' }}>Sky</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select id="edit-proj-status" name="status"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                                <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="archived" {{ $project->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                            <input type="date" id="edit-proj-due-date" name="due_date" value="{{ $project->due_date ? $project->due_date->format('Y-m-d') : '' }}"
                                class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description / Project Scope</label>
                        <textarea id="edit-proj-description" name="description" rows="3"
                            class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">{{ $project->description }}</textarea>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeEditProjectModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        Update Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal controls
    function openCreateTaskModal() {
        document.getElementById('create-task-modal').classList.remove('hidden');
    }
    function closeCreateTaskModal() {
        document.getElementById('create-task-modal').classList.add('hidden');
    }
    function assignToMyself() {
        const select = document.getElementById('create-task-assigned-to');
        if (select) {
            select.value = '{{ auth()->id() }}';
            handleProjectTaskAssigneeChange(select);
        }
    }

    function handleProjectTaskAssigneeChange(select) {
        @if (auth()->user()->isAdmin())
        const prioritySelect = document.getElementById('create-task-priority');
        const hint = document.getElementById('project_admin_urgent_hint');
        const isSelf = (select.value === '{{ auth()->id() }}');
        if (!isSelf && select.value) {
            if (prioritySelect) prioritySelect.value = 'urgent';
            if (hint) hint.style.display = 'block';
        } else {
            if (hint) hint.style.display = 'none';
        }
        @endif
    }

    function openEditProjectModal(project) {
        document.getElementById('edit-project-modal').classList.remove('hidden');
    }
    function closeEditProjectModal() {
        document.getElementById('edit-project-modal').classList.add('hidden');
    }

    function openEditTaskModal(task) {
        const form = document.getElementById('edit-task-form');
        form.action = '/tasks/' + task.id;
        document.getElementById('edit-title').value = task.title || '';
        document.getElementById('edit-description').value = task.description || '';
        const assignedSelect = document.getElementById('edit-assigned-to');
        if (assignedSelect) assignedSelect.value = task.assigned_to || '';
        document.getElementById('edit-priority').value = task.priority || 'medium';
        document.getElementById('edit-status').value = task.status || 'pending';
        document.getElementById('edit-due-date').value = task.due_date ? task.due_date.substring(0, 10) : '';

        document.getElementById('edit-task-modal').classList.remove('hidden');
    }
    function closeEditTaskModal() {
        document.getElementById('edit-task-modal').classList.add('hidden');
    }

    // Tick Mark Status Toggle via AJAX
    function toggleTaskStatus(taskId, projectId) {
        const btn = document.getElementById('task-tick-btn-' + taskId);
        const titleEl = document.getElementById('task-title-' + taskId);
        const rowEl = document.getElementById('task-row-' + taskId);
        const badgeEl = document.getElementById('task-status-badge-' + taskId);

        if (btn) btn.classList.add('scale-90');
        setTimeout(() => { if (btn) btn.classList.remove('scale-90'); }, 150);

        fetch('/tasks/' + taskId + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (btn) {
                    if (data.is_completed) {
                        btn.classList.remove('border-gray-300', 'dark:border-gray-600', 'text-transparent', 'bg-white', 'dark:bg-gray-750');
                        btn.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm', 'shadow-emerald-500/30');
                        if (titleEl) {
                            titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                        }
                        if (rowEl) {
                            rowEl.classList.add('border-emerald-200/80', 'dark:border-emerald-900/50', 'bg-emerald-50/10');
                            rowEl.classList.remove('border-gray-200/90', 'dark:border-gray-700');
                        }
                        if (badgeEl) {
                            badgeEl.textContent = 'completed';
                        }
                    } else {
                        btn.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm', 'shadow-emerald-500/30');
                        btn.classList.add('border-gray-300', 'dark:border-gray-600', 'text-transparent', 'bg-white', 'dark:bg-gray-750');
                        if (titleEl) {
                            titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                        }
                        if (rowEl) {
                            rowEl.classList.remove('border-emerald-200/80', 'dark:border-emerald-900/50', 'bg-emerald-50/10');
                            rowEl.classList.add('border-gray-200/90', 'dark:border-gray-700');
                        }
                        if (badgeEl) {
                            badgeEl.textContent = data.status || 'pending';
                        }
                    }
                }

                // Update Project Progress Bar & Numbers
                if (data.project_stats && data.project_id) {
                    const progressBar = document.getElementById('project-progress-bar-' + data.project_id);
                    const progressText = document.getElementById('project-progress-text-' + data.project_id);
                    const statCompleted = document.getElementById('stat-completed-tasks');
                    const statPending = document.getElementById('stat-pending-tasks');

                    if (progressBar) {
                        progressBar.style.width = data.project_stats.percentage + '%';
                    }
                    if (progressText) {
                        progressText.textContent = data.project_stats.completed + ' / ' + data.project_stats.total + ' tasks completed (' + data.project_stats.percentage + '%)';
                    }
                    if (statCompleted) {
                        statCompleted.textContent = data.project_stats.completed;
                    }
                    if (statPending) {
                        statPending.textContent = data.project_stats.total - data.project_stats.completed;
                    }
                }
            }
        })
        .catch(err => {
            console.error('Toggle error:', err);
            alert('Failed to update task status. Please try again.');
        });
    }

<!-- ======================= QUICK ATTACH FILE MODAL ======================= -->
<div id="attachFileModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Attach File to Task</h3>
                <p id="attachModalTaskTitle" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold truncate max-w-[280px] mt-0.5"></p>
            </div>
            <button type="button" onclick="closeAttachModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="attachFileForm" method="POST" action="" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Select File <span class="text-rose-500">*</span>
                </label>
                <input type="file" name="file" required
                    class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 cursor-pointer">
                <p class="text-[11px] text-gray-400 mt-1">Supports PDF, DOC, Excel, Images (PNG, JPG, WebP), ZIP, TXT up to 20MB.</p>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeAttachModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">
                    Upload &amp; Attach
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAttachModal(taskId, title) {
        const modal = document.getElementById('attachFileModal');
        const form = document.getElementById('attachFileForm');
        const titleEl = document.getElementById('attachModalTaskTitle');

        form.action = `/tasks/${taskId}/attachments`;
        titleEl.textContent = title;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAttachModal() {
        const modal = document.getElementById('attachFileModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateTaskModal();
            closeEditProjectModal();
            closeEditTaskModal();
            closeAttachModal();
        }
    });
</script>
@endsection
