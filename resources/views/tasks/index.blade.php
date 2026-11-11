@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200 dark:border-gray-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Task Management</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    Phase 1 Core
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Manage personal tasks, track delegated work, and collaborate with accountability.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if (auth()->user()->isAdmin() || auth()->user()->isManager())
            <a href="{{ route('reports.performance') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold text-xs sm:text-sm border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Performance</span>
            </a>

            <!-- Create New Project Button -->
            <button type="button" onclick="openCreateProjectModal()"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold text-xs sm:text-sm border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:-translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>New Project</span>
            </button>
            @endif

            <!-- Create New Task Button -->
            <button type="button" onclick="openCreateTaskModal()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-600/30 transition-all hover:shadow-indigo-600/50 hover:-translate-y-0.5 flex-shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Task</span>
            </button>
        </div>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <a href="{{ route('tasks.index', ['tab' => 'my']) }}"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border {{ $tab === 'my' ? 'ring-2 ring-indigo-500 border-indigo-400' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-indigo-400 transition-all">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">My Tasks</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($counters['my_total']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Assigned to you</p>
        </a>

        <a href="{{ route('tasks.index', ['tab' => 'my', 'status' => 'in_progress']) }}"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm hover:border-indigo-400 transition-all">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">In Progress</p>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($counters['my_in_progress']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Currently active</p>
        </a>

        <a href="{{ route('tasks.index', ['tab' => 'my', 'status' => 'in_review']) }}"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm hover:border-purple-400 transition-all">
            <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">In Review</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ number_format($counters['my_in_review']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Awaiting check</p>
        </a>

        <a href="{{ route('tasks.index', ['tab' => 'overdue']) }}"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border {{ $tab === 'overdue' ? 'ring-2 ring-red-500 border-red-400' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-red-400 transition-all">
            <p class="text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Overdue</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($counters['my_overdue']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Past due date</p>
        </a>

        <a href="{{ route('tasks.index', ['tab' => 'created']) }}"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border {{ $tab === 'created' ? 'ring-2 ring-indigo-500 border-indigo-400' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-indigo-400 transition-all col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">Created By Me</p>
            <p class="text-2xl font-bold text-teal-600 dark:text-teal-400 mt-1">{{ number_format($counters['created_by_me']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Self & delegated</p>
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4 overflow-x-auto">
        <nav class="flex space-x-6 sm:space-x-8" aria-label="Tabs">
            <a href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'my'])) }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors {{ $tab === 'my' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>My Tasks</span>
            </a>

            <a href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'created'])) }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors {{ $tab === 'created' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Created By Me</span>
            </a>

            <a href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'overdue'])) }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors {{ $tab === 'overdue' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Overdue</span>
            </a>

            @if ($isManager)
            <a href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'team'])) }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors {{ $tab === 'team' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>{{ auth()->user()->isAdmin() ? 'All Company Tasks' : 'Team Tasks' }}</span>
            </a>

            <a href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'daily'])) }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors {{ $tab === 'daily' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400 dark:border-emerald-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Daily Done</span>
            </a>
            @endif
        </nav>
    </div>

    @if (auth()->user()->isAdmin() && count($departments) > 0)
    <!-- Department Quick-Classification Pills Bar (Admin Only) -->
    <div class="bg-gradient-to-r from-gray-50 via-indigo-50/20 to-gray-50 dark:from-gray-800/80 dark:via-indigo-950/20 dark:to-gray-800/80 rounded-2xl p-3 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex items-center justify-between flex-wrap gap-2.5">
        <div class="flex items-center gap-1.5 flex-wrap text-xs">
            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1 flex items-center gap-1">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Department:
            </span>

            {{-- All Departments Pill --}}
            <a href="{{ route('tasks.index', array_merge(request()->except(['department', 'page']), ['tab' => ($tab === 'my' && auth()->user()->isAdmin()) ? 'team' : $tab])) }}"
                class="px-3 py-1.5 rounded-xl font-semibold text-xs transition-all inline-flex items-center gap-1.5 {{ !request('department') ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                <span>🏢 All Departments</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('department') ? 'bg-indigo-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                    {{ $totalCompanyTasks }}
                </span>
            </a>

            {{-- Per-department Pills --}}
            @foreach ($departments as $deptName)
                @php
                    $isDeptActive = strtolower((string) request('department')) === strtolower((string) $deptName);
                    $deptCount = $departmentCounters[strtolower((string) $deptName)] ?? 0;
                    $deptIcon = match (strtolower((string) $deptName)) {
                        'development', 'dev', 'engineering' => '💻',
                        'marketing', 'growth' => '📢',
                        'sales', 'business' => '📈',
                        'hr', 'human resources' => '👥',
                        'design', 'ui/ux', 'creative' => '🎨',
                        'finance', 'accounts' => '💳',
                        'support', 'operations', 'ops' => '🛠️',
                        default => '📂',
                    };
                @endphp
                <a href="{{ route('tasks.index', array_merge(request()->except(['department', 'page']), ['department' => $deptName, 'tab' => ($tab === 'my' && auth()->user()->isAdmin()) ? 'team' : $tab])) }}"
                    class="px-3 py-1.5 rounded-xl font-semibold text-xs transition-all inline-flex items-center gap-1.5 {{ $isDeptActive ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                    <span>{{ $deptIcon }} {{ ucfirst($deptName) }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $isDeptActive ? 'bg-indigo-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        {{ $deptCount }}
                    </span>
                </a>
            @endforeach
        </div>

        @if(request()->filled('department'))
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Filtering:</span>
            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 flex items-center gap-1">
                <span>{{ ucfirst(request('department')) }}</span>
                <a href="{{ route('tasks.index', request()->except(['department', 'page'])) }}" class="text-indigo-400 hover:text-indigo-700 ml-1 font-bold" title="Clear department filter">&times;</a>
            </span>
        </div>
        @endif
    </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm space-y-3">
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="view" value="{{ $view }}">
            @if(request()->filled('department'))
            <input type="hidden" name="department" value="{{ request('department') }}">
            @endif

            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search Task ID (e.g. 10 or TSK-00010), title, notes..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Project Filter (Searchable) -->
            @php
                $selectedProjId = (string) request('project_id', '');
                $selectedProjModel = $selectedProjId !== '' ? $projects->firstWhere('id', $selectedProjId) : null;
                $selectedProjTitle = $selectedProjModel ? ($selectedProjModel->title ?? $selectedProjModel->name) : 'All Projects';
                $projectsJsonData = $projects->map(fn($p) => [
                    'id' => (string) $p->id,
                    'title' => (string) ($p->title ?? $p->name ?? ('Project #' . $p->id)),
                    'department' => (string) ($p->department ?? '')
                ])->values();
            @endphp
            <div class="relative" x-data="{
                open: false,
                search: '',
                selectedId: @js($selectedProjId),
                selectedLabel: @js($selectedProjTitle),
                allProjects: @js($projectsJsonData),
                get filteredProjects() {
                    if (!this.search.trim()) return this.allProjects;
                    const q = this.search.toLowerCase().trim();
                    return this.allProjects.filter(p => p.title.toLowerCase().includes(q) || (p.department && p.department.toLowerCase().includes(q)));
                },
                select(id, label) {
                    this.selectedId = id;
                    this.selectedLabel = label;
                    this.open = false;
                    this.search = '';
                },
                clear() {
                    this.selectedId = '';
                    this.selectedLabel = 'All Projects';
                    this.open = false;
                    this.search = '';
                }
            }" @click.outside="open = false" @keydown.escape.window="open = false">
                <input type="hidden" name="project_id" :value="selectedId" value="{{ $selectedProjId }}">

                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Project</label>

                <!-- Trigger Button -->
                <button type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.projectSearchInput.focus()); }"
                    class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 flex items-center justify-between gap-1 text-left transition-colors hover:border-gray-300 dark:hover:border-gray-600">
                    <span class="truncate" :class="selectedId ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'" x-text="selectedLabel">
                        {{ $selectedProjTitle }}
                    </span>
                    <div class="flex items-center gap-1 shrink-0 text-gray-400">
                        <template x-if="selectedId">
                            <span @click.stop="clear()" class="hover:text-red-500 cursor-pointer p-0.5" title="Clear selection">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                        </template>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full mt-1 w-64 sm:w-72 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                    
                    <!-- Search Input in Dropdown -->
                    <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <div class="relative">
                            <input type="text"
                                x-ref="projectSearchInput"
                                x-model="search"
                                placeholder="Type to search projects..."
                                @keydown.enter.prevent="if (filteredProjects.length === 1) { select(filteredProjects[0].id, filteredProjects[0].title); } else if (filteredProjects.length > 0 && !search.trim()) { select('', 'All Projects'); }"
                                class="w-full pl-8 pr-7 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="button" x-show="search" @click="search = ''; $refs.projectSearchInput.focus()" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                                &times;
                            </button>
                        </div>
                    </div>

                    <!-- Options List -->
                    <div class="max-h-60 overflow-y-auto py-1 text-xs divide-y divide-gray-50 dark:divide-gray-800">
                        <!-- All Projects option -->
                        <button type="button"
                            @click="select('', 'All Projects')"
                            class="w-full px-3 py-2 text-left flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors"
                            :class="!selectedId ? 'bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                            <span class="flex items-center gap-2">
                                <span class="text-gray-400">🌐</span>
                                <span>All Projects</span>
                            </span>
                            <span x-show="!selectedId" class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                        </button>

                        <!-- Project list items -->
                        <template x-for="project in filteredProjects" :key="project.id">
                            <button type="button"
                                @click="select(project.id, project.title)"
                                class="w-full px-3 py-2 text-left flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors"
                                :class="selectedId == project.id ? 'bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                <div class="truncate pr-2">
                                    <div class="truncate font-medium" x-text="project.title"></div>
                                    <template x-if="project.department">
                                        <div class="text-[10px] text-gray-400 capitalize" x-text="project.department"></div>
                                    </template>
                                </div>
                                <span x-show="selectedId == project.id" class="text-indigo-600 dark:text-indigo-400 font-bold shrink-0">✓</span>
                            </button>
                        </template>

                        <!-- Empty State -->
                        <div x-show="filteredProjects.length === 0" class="px-3 py-4 text-center text-gray-400">
                            <svg class="w-5 h-5 mx-auto mb-1 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[11px]">No projects matching "<span class="font-medium text-gray-600 dark:text-gray-300" x-text="search"></span>"</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\Task::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Priority</label>
                <select name="priority" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Priorities</option>
                    @foreach (\App\Models\Task::PRIORITIES as $val => $lbl)
                        <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Sort By</label>
                <select name="sort_by" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                    <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Creation Date</option>
                    <option value="due_date" {{ request('sort_by') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                    <option value="priority" {{ request('sort_by') === 'priority' ? 'selected' : '' }}>Priority</option>
                    <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                </select>
            </div>

            <!-- Actions: Filter & Reset -->
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition-colors text-center cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('tasks.index', ['tab' => $tab]) }}"
                    class="py-2 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 font-semibold text-xs sm:text-sm transition-colors"
                    title="Clear filters">
                    Reset
                </a>
            </div>
        </form>

        @if (request()->filled('project_id') && ($activeProject = $projects->firstWhere('id', request('project_id'))))
        <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2 text-xs">
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                <span>Filtered by project:</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800">
                    📁 {{ $activeProject->title ?? $activeProject->name }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $activeProject) }}"
                    class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                    <span>Open Dedicated Project Page &amp; Checklist</span>
                    <span>&rarr;</span>
                </a>
                <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                <a href="{{ route('tasks.index', request()->except(['project_id', 'page'])) }}"
                    class="text-gray-400 hover:text-red-500 transition-colors">
                    Clear Project Filter &times;
                </a>
            </div>
        </div>
        @endif
    </div>

    @if ($tab === 'daily')
    <div class="px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-between text-emerald-800 dark:text-emerald-300">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="text-sm font-bold">Daily Tasks Completed by Your Team</h2>
        </div>
        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Completed Today</span>
    </div>
    @endif

    <!-- Tasks Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden">
        @if ($tasks->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">No tasks found</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                    {{ request()->hasAny(['search', 'status', 'priority']) ? 'No tasks matched your current search filters.' : 'There are no tasks in this view right now.' }}
                </p>
                <button type="button" onclick="openCreateTaskModal()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold text-xs sm:text-sm shadow-sm hover:bg-indigo-700 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create a Task</span>
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs sm:text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th scope="col" class="px-4 py-3.5">Task ID</th>
                            <th scope="col" class="px-4 py-3.5">Title & Project</th>
                            @if (auth()->user()->isAdmin())
                            <th scope="col" class="px-3 py-3.5">Department</th>
                            @endif
                            <th scope="col" class="px-3 py-3.5">Priority</th>
                            <th scope="col" class="px-3 py-3.5">Status</th>
                            <th scope="col" class="px-3 py-3.5">Due Date</th>
                            <th scope="col" class="px-3 py-3.5">Owner (Assignee)</th>
                            <th scope="col" class="px-3 py-3.5">Creator</th>
                            <th scope="col" class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                        @foreach ($tasks as $task)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-750/50 transition-colors">
                            <!-- Task ID -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                <a href="{{ route('tasks.show', $task) }}" class="hover:underline">
                                    {{ $task->formatted_id }}
                                </a>
                            </td>

                            <!-- Title & Project -->
                            <td class="px-4 py-3.5">
                                <div class="max-w-xs md:max-w-md">
                                    <a href="{{ route('tasks.show', $task) }}"
                                        class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-1">
                                        {{ $task->title }}
                                    </a>
                                    @if ($task->project)
                                        <a href="{{ route('projects.show', $task->project) }}"
                                            class="inline-flex items-center gap-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline mt-0.5"
                                            title="Open dedicated project view and tasks checklist">
                                            <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                            <span>{{ $task->project->title ?? $task->project->name }}</span>
                                        </a>
                                    @endif
                                    @if ($task->content_post_id && $task->contentPost)
                                        <a href="{{ route('content.posts.show', $task->contentPost) }}"
                                            class="inline-flex items-center gap-1 text-[11px] font-medium text-pink-600 dark:text-pink-400 hover:text-pink-800 dark:hover:text-pink-300 hover:underline mt-0.5"
                                            title="Open linked social media post">
                                            <svg class="w-3 h-3 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span>📱 Post: {{ Str::limit($task->contentPost->title, 25) }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            @if (auth()->user()->isAdmin())
                            <!-- Department (Admin Only) -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $task->departmentBadgeClasses() }}">
                                    <span>{{ $task->departmentIcon() }}</span>
                                    <span>{{ $task->departmentLabel() }}</span>
                                </span>
                            </td>
                            @endif

                            <!-- Priority -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $task->priorityBadgeClasses() }}">
                                        {{ $task->priorityLabel() }}
                                    </span>
                                    @if ($task->isEscalated())
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-2xs animate-pulse" title="Escalated by Admin: {{ $task->escalation_reason }}">
                                        🚨 Escalated
                                    </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $task->statusBadgeClasses() }}">
                                    {{ $task->statusLabel() }}
                                </span>
                            </td>

                            <!-- Due Date & Overdue Badge -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                @if ($task->due_date)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ $task->due_date->format('d M Y') }}
                                        </span>
                                        @if ($task->isOverdue())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 dark:text-red-400 mt-0.5 animate-pulse">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ $task->overdueBadgeLabel() }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <!-- Assignee -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                @if ($task->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate max-w-[100px]">
                                            {{ $task->assignee->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Unassigned</span>
                                @endif
                            </td>

                            <!-- Creator -->
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                @if ($task->creator)
                                    <span class="text-xs text-gray-600 dark:text-gray-300">
                                        {{ $task->creator->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('tasks.show', $task) }}"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @can('delegate', $task)
                                    <button type="button" onclick="openDelegateModal('{{ $task->id }}', '{{ addslashes($task->title) }}')"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/30 transition-colors cursor-pointer"
                                        title="Delegate Task">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    </button>
                                    @endcan

                                    @can('escalate', $task)
                                    <button type="button" onclick="openEscalateModal('{{ $task->id }}', '{{ addslashes($task->title) }}', '{{ addslashes($task->assignee?->name ?? 'Unassigned') }}')"
                                        class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors cursor-pointer"
                                        title="Escalate Task to Urgent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </button>
                                    @endcan

                                    @can('rework', $task)
                                    <button type="button" onclick="openReworkModal('{{ $task->id }}', '{{ addslashes($task->title) }}', '{{ addslashes($task->assignee?->name ?? 'Unassigned') }}')"
                                        class="p-1.5 rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors cursor-pointer"
                                        title="Request Re-work">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                    @endcan

                                    @can('update', $task)
                                    <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 rounded-lg {{ $task->isCompleted() ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50' }} dark:hover:bg-gray-700 transition-colors cursor-pointer"
                                            title="{{ $task->isCompleted() ? 'Re-open task' : 'Mark completed' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="text-gray-500 dark:text-gray-400">
                    Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} tasks
                </div>
                <div>
                    {{ $tasks->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ======================= CREATE TASK MODAL ======================= -->
<div id="createTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full border border-gray-200 dark:border-gray-700 overflow-hidden my-8">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Create New Task</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">You are the creator and default accountable owner.</p>
            </div>
            <button type="button" onclick="closeCreateTaskModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.store') }}" class="p-5 space-y-4">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Task Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required placeholder="e.g. Implement user export or prepare Q3 deck"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Provide background or instructions..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Expected Outcomes / Acceptance Criteria -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Expected Outcomes / Acceptance Criteria
                </label>
                <textarea name="expected_outcomes" rows="2" placeholder="What must be true for this task to be considered done?"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Priority, Start Date & Due Date -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" id="task_create_priority" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="low">Low</option>
                        <option value="medium" {{ auth()->user()->isAdmin() ? '' : 'selected' }}>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                        <option value="urgent" {{ auth()->user()->isAdmin() ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input type="date" name="due_date"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>
            </div>

            @if (auth()->user()->isAdmin())
            <!-- Admin: Department, Project & Assignee -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Department Field (Admin Only) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select name="department" id="task_create_department" onchange="handleTaskDepartmentChange(this.value)" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ strtolower((string) (auth()->user()->department ?: 'development')) === strtolower((string) $dept) ? 'selected' : '' }}>
                                {{ ucfirst($dept) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Which dept this task is for</p>
                </div>

                <!-- Project -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Project (Optional)</label>
                        <button type="button" onclick="openCreateProjectModal()" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            + New Project
                        </button>
                    </div>
                    <select name="project_id" id="task_create_project_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="">No Project</option>
                        @foreach ($projects as $proj)
                            <option value="{{ $proj->id }}" data-department="{{ strtolower(trim((string) ($proj->department ?? ''))) }}">{{ $proj->title ?? $proj->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignee -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Assignee <span class="text-gray-400 font-normal">(Default: Yourself)</span>
                    </label>
                    <select name="assigned_to" id="task_create_assigned_to" onchange="handleTaskAssigneeChange(this)" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="{{ auth()->id() }}" data-department="{{ auth()->user()->department }}" data-is-self="1">Self ({{ auth()->user()->name }})</option>
                        @foreach ($eligibleEmployees->where('id', '!=', auth()->id()) as $emp)
                            <option value="{{ $emp->id }}" data-department="{{ $emp->department }}">{{ $emp->name }} ({{ $emp->department ?? 'General' }})</option>
                        @endforeach
                    </select>
                    <p id="admin_urgent_hint" class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1" style="display: none;">
                        ⚡ Admin assigning to staff automatically marks task as <strong>Urgent</strong>.
                    </p>
                </div>
            </div>
            @else
            <!-- EMP and Department Manager: Own Department automatically, 2 columns -->
            <input type="hidden" name="department" value="{{ auth()->user()->department }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Project -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Project (Optional)</label>
                        @if (auth()->user()->isManager())
                        <button type="button" onclick="openCreateProjectModal()" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            + New Project
                        </button>
                        @endif
                    </div>
                    <select name="project_id" id="emp_task_create_project_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="">No Project</option>
                        @foreach ($projects as $proj)
                            <option value="{{ $proj->id }}" data-department="{{ strtolower(trim((string) ($proj->department ?? ''))) }}">{{ $proj->title ?? $proj->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignee -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Assignee <span class="text-gray-400 font-normal">(Default: Yourself)</span>
                    </label>
                    <select name="assigned_to" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="{{ auth()->id() }}">Self ({{ auth()->user()->name }})</option>
                        @foreach ($eligibleEmployees->where('id', '!=', auth()->id()) as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <!-- Review Required Toggle -->
            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" id="review_required" name="review_required" value="1"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="review_required" class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                    Require review before marking as Completed
                </label>
            </div>

            <!-- Modal Actions -->
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeCreateTaskModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition-colors cursor-pointer">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= DELEGATE TASK MODAL ======================= -->
<div id="delegateTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Delegate Task</h3>
                <p id="delegateTaskTitle" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs mt-0.5"></p>
            </div>
            <button type="button" onclick="closeDelegateModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="delegateTaskForm" method="POST" action="" class="p-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Delegate to Colleague <span class="text-red-500">*</span>
                </label>
                <select name="assigned_to" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Select eligible colleague...</option>
                    @foreach ($eligibleEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->designation ?? ($emp->department ?? 'General') }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Ownership will transfer to the selected employee.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Assignment Notes</label>
                <textarea name="notes" rows="3" placeholder="Context, delegation instructions, or handoff notes..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeDelegateModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">
                    Delegate Now
                </button>
            </div>
        </form>
    </div>
</div>

@if (auth()->user()->isAdmin())
<!-- ======================= ESCALATE TASK MODAL (ADMIN ONLY) ======================= -->
<div id="escalateTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg w-full border border-red-200 dark:border-red-900/50 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-red-50/50 dark:bg-red-950/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-lg font-bold">
                    🚨
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Escalate Task to Urgent</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sets priority to Urgent and notifies assignee with a directive.</p>
                </div>
            </div>
            <button type="button" onclick="closeEscalateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="escalateTaskForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div class="bg-red-50/60 dark:bg-red-950/20 p-3 rounded-xl border border-red-200/60 dark:border-red-800/40 text-xs text-red-800 dark:text-red-300">
                <p class="font-bold text-gray-900 dark:text-white" id="escalateTaskTitle">Task Title</p>
                <p class="text-[11px] text-red-600 dark:text-red-400 mt-0.5" id="escalateTaskAssignee">Assignee: Name</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Escalation Directive / Message to Employee <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" id="escalateTaskReason" rows="3" required
                    placeholder="e.g. Employee is not paying attention or responding. This task is overdue/critical and must be completed immediately today."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">This message is delivered directly into the employee's notification feed and recorded as an official escalation directive in the task stream.</p>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeEscalateModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                    <span>🚨 Set Urgent & Escalate</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ======================= RE-WORK TASK MODAL ======================= -->
<div id="reworkTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg w-full border border-amber-200 dark:border-amber-900/50 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-amber-50/50 dark:bg-amber-950/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg font-bold">
                    ↺
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Send Task Back for Re-work</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Reopens task and directs the assignee with required changes.</p>
                </div>
            </div>
            <button type="button" onclick="closeReworkModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="reworkTaskForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div class="bg-amber-50/70 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200/60 dark:border-amber-800/40 text-xs text-amber-900 dark:text-amber-300">
                <p class="font-bold text-gray-900 dark:text-white" id="reworkTaskTitle">Task Title</p>
                <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5" id="reworkTaskAssignee">Assignee: Name</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Required Revisions / Instructions <span class="text-red-500">*</span>
                </label>
                <textarea name="rework_notes" id="reworkTaskNotes" rows="3" required
                    placeholder="Specify exactly what needs to be reworked, corrections required, or missing items..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">These notes will be delivered to the assignee and pinned at the top of the task.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Revised Due Date (Optional)
                </label>
                <input type="date" name="due_date" id="reworkTaskDueDate"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeReworkModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                    <span>↺ Request Re-work</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= CREATE PROJECT MODAL ======================= -->
<div id="createProjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Create New Project Card</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Group related tasks under a dedicated project card</p>
            </div>
            <button type="button" onclick="closeCreateProjectModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('projects.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Project Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required maxlength="150" placeholder="e.g. Website Overhaul, Mobile App v2"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">POC</label>
                    <input type="text" name="client_name" maxlength="150" placeholder="e.g. Client or Point of Contact"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Department</label>
                    @if (auth()->user()->isAdmin())
                    <select name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ auth()->user()->department === $dept ? 'selected' : '' }}>{{ ucfirst($dept) }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" value="{{ ucfirst(auth()->user()->department ?? 'General') }}" disabled
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500">
                    <input type="hidden" name="department" value="{{ auth()->user()->department }}">
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date (Optional)</label>
                    <input type="date" name="due_date"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Card Accent Color</label>
                    <select name="color" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        <option value="indigo">Indigo</option>
                        <option value="emerald">Emerald</option>
                        <option value="violet">Violet</option>
                        <option value="rose">Rose</option>
                        <option value="amber">Amber</option>
                        <option value="sky">Sky Blue</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description (Optional)</label>
                <textarea name="description" rows="3" placeholder="Brief project goals, scope, and objectives..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeCreateProjectModal()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">
                    Create Project Card
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateProjectModal() {
        const modal = document.getElementById('createProjectModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateProjectModal() {
        const modal = document.getElementById('createProjectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openCreateTaskModal() {
        const modal = document.getElementById('createTaskModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        @if (auth()->user()->isAdmin())
        const deptSelect = document.getElementById('task_create_department');
        if (deptSelect) {
            handleTaskDepartmentChange(deptSelect.value);
        }
        @endif
    }

    function closeCreateTaskModal() {
        const modal = document.getElementById('createTaskModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openDelegateModal(taskId, title) {
        const modal = document.getElementById('delegateTaskModal');
        const form = document.getElementById('delegateTaskForm');
        const titleEl = document.getElementById('delegateTaskTitle');

        form.action = `/tasks/${taskId}/delegate`;
        titleEl.textContent = title;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDelegateModal() {
        const modal = document.getElementById('delegateTaskModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEscalateModal(taskId, title, assignee) {
        const modal = document.getElementById('escalateTaskModal');
        const form = document.getElementById('escalateTaskForm');
        const titleEl = document.getElementById('escalateTaskTitle');
        const assigneeEl = document.getElementById('escalateTaskAssignee');

        if (form) form.action = `/tasks/${taskId}/escalate`;
        if (titleEl) titleEl.textContent = title;
        if (assigneeEl) assigneeEl.textContent = 'Current Assignee: ' + assignee;

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeEscalateModal() {
        const modal = document.getElementById('escalateTaskModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openReworkModal(taskId, title, assignee) {
        const modal = document.getElementById('reworkTaskModal');
        const form = document.getElementById('reworkTaskForm');
        const titleEl = document.getElementById('reworkTaskTitle');
        const assigneeEl = document.getElementById('reworkTaskAssignee');

        if (form) form.action = `/tasks/${taskId}/rework`;
        if (titleEl) titleEl.textContent = title;
        if (assigneeEl) assigneeEl.textContent = 'Current Assignee: ' + assignee;

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeReworkModal() {
        const modal = document.getElementById('reworkTaskModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function handleTaskDepartmentChange(selectedDept) {
        const assigneeSelect = document.getElementById('task_create_assigned_to');
        if (!assigneeSelect) return;

        if (!window.taskAssigneeMasterList) {
            window.taskAssigneeMasterList = Array.from(assigneeSelect.options).map(opt => ({
                value: opt.value,
                text: opt.text,
                department: (opt.getAttribute('data-department') || '').trim().toLowerCase(),
                isSelf: opt.getAttribute('data-is-self') === '1' || opt.value === '{{ auth()->id() }}'
            }));
        }

        const lowerDept = (selectedDept || '').trim().toLowerCase();
        const prevValue = assigneeSelect.value;

        // Clear and rebuild options
        assigneeSelect.innerHTML = '';

        let matchedCurrent = false;
        let countInDept = 0;

        window.taskAssigneeMasterList.forEach(item => {
            if (item.isSelf) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.text = item.text;
                opt.setAttribute('data-is-self', '1');
                opt.setAttribute('data-department', item.department);
                assigneeSelect.appendChild(opt);
                if (item.value === prevValue) matchedCurrent = true;
            } else if (!lowerDept || item.department === lowerDept) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.text = item.text;
                opt.setAttribute('data-department', item.department);
                assigneeSelect.appendChild(opt);
                countInDept++;
                if (item.value === prevValue) matchedCurrent = true;
            }
        });

        if (countInDept === 0 && lowerDept) {
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.text = '— No staff in this department —';
            emptyOpt.disabled = true;
            assigneeSelect.appendChild(emptyOpt);
        }

        if (matchedCurrent) {
            assigneeSelect.value = prevValue;
        } else {
            assigneeSelect.value = '{{ auth()->id() }}';
        }

        handleTaskAssigneeChange(assigneeSelect);

        // Also filter Project dropdown for the selected department
        const projectSelect = document.getElementById('task_create_project_id');
        if (projectSelect) {
            if (!window.taskProjectMasterList) {
                window.taskProjectMasterList = Array.from(projectSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    department: (opt.getAttribute('data-department') || '').trim().toLowerCase(),
                    isNoProject: !opt.value
                }));
            }

            const prevProjValue = projectSelect.value;
            projectSelect.innerHTML = '';
            let matchedProj = false;

            window.taskProjectMasterList.forEach(item => {
                if (item.isNoProject) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.text = item.text;
                    projectSelect.appendChild(opt);
                    if (prevProjValue === '') matchedProj = true;
                } else if (!lowerDept || !item.department || item.department === lowerDept) {
                    const opt = document.createElement('option');
                    opt.value = item.value;
                    opt.text = item.text;
                    opt.setAttribute('data-department', item.department);
                    projectSelect.appendChild(opt);
                    if (item.value === prevProjValue) matchedProj = true;
                }
            });

            projectSelect.value = matchedProj ? prevProjValue : '';
        }
    }

    function handleTaskAssigneeChange(select) {
        @if (auth()->user()->isAdmin())
        const prioritySelect = document.getElementById('task_create_priority');
        const hint = document.getElementById('admin_urgent_hint');
        const isSelf = (select.value === '{{ auth()->id() }}');
        if (!isSelf && select.value) {
            if (prioritySelect) prioritySelect.value = 'urgent';
            if (hint) hint.style.display = 'block';
        } else {
            if (hint) hint.style.display = 'none';
        }
        @endif
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if (auth()->user()->isAdmin())
        const deptSelect = document.getElementById('task_create_department');
        if (deptSelect) {
            handleTaskDepartmentChange(deptSelect.value);
        }
        @endif
    });
</script>
@endsection
