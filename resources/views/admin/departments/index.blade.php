@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Departments</h2>
        </div>

        @can('departments.create')
            <a href="{{ route('admin.departments.create') }}"
                class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 sm:mb-5 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- A manager is required, so say it plainly when there is nobody to pick. --}}
    @can('departments.create')
        @if ($managers->isEmpty())
            <div class="mb-4 sm:mb-5 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 px-4 py-3 rounded-lg text-sm">
                No active user holds the admin or manager role, so there is nobody to assign as a
                department manager. Create or promote one on the
                <a href="{{ route('admin.users.index') }}" class="underline font-semibold">Users</a> page first.
            </div>
        @endif
    @endcan

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Departments</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white mt-0.5">{{ $departments->count() }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Configured units</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Staff</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-0.5">{{ $totalMembers }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Assigned team members</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider">Active Units</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-0.5">{{ $departments->where('is_active', true)->count() }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Operational departments</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Manager Status</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $departments->whereNotNull('manager_id')->count() }} / {{ $departments->count() }}</div>
            <div class="text-[11px] text-gray-400 mt-1">
                @if ($departmentsWithoutManager > 0)
                    <span class="text-amber-600 font-semibold">{{ $departmentsWithoutManager }} need manager</span>
                @else
                    All departments staffed
                @endif
            </div>
        </div>
    </div>

    <!-- Section Title: Department Cards -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Department Cards
        </h3>
        <span class="text-xs text-gray-400">Click any card to view its team members below</span>
    </div>

    <!-- Department Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @forelse ($departments as $department)
            @php
                $deptSlug = strtolower($department->name);
                // Assign tailored palette and icons per department
                $palette = match ($deptSlug) {
                    'development' => [
                        'bg' => 'from-indigo-500/10 to-indigo-600/5',
                        'border' => 'hover:border-indigo-400 dark:hover:border-indigo-500',
                        'icon_bg' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                        'badge_bg' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                    ],
                    'hr' => [
                        'bg' => 'from-purple-500/10 to-purple-600/5',
                        'border' => 'hover:border-purple-400 dark:hover:border-purple-500',
                        'icon_bg' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                        'badge_bg' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                    ],
                    'marketing' => [
                        'bg' => 'from-sky-500/10 to-sky-600/5',
                        'border' => 'hover:border-sky-400 dark:hover:border-sky-500',
                        'icon_bg' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
                        'badge_bg' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
                    ],
                    'sales' => [
                        'bg' => 'from-emerald-500/10 to-emerald-600/5',
                        'border' => 'hover:border-emerald-400 dark:hover:border-emerald-500',
                        'icon_bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'badge_bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                    ],
                    default => [
                        'bg' => 'from-gray-500/10 to-gray-600/5',
                        'border' => 'hover:border-gray-400 dark:hover:border-gray-500',
                        'icon_bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        'badge_bg' => 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300',
                    ],
                };
            @endphp

            <div id="dept-card-{{ $deptSlug }}"
                onclick="selectDepartment('{{ $deptSlug }}')"
                class="dept-card cursor-pointer group bg-white dark:bg-gray-800 rounded-xl p-5 border-2 border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden {{ $palette['border'] }}">
                
                <!-- Decorative top gradient bar -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $palette['bg'] }}"></div>

                <div>
                    <!-- Top row: Icon & Status -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-lg {{ $palette['icon_bg'] }} shadow-sm">
                            @if ($deptSlug === 'development')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            @elseif ($deptSlug === 'hr')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            @elseif ($deptSlug === 'sales')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            @elseif ($deptSlug === 'marketing')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5">
                            @include('admin.departments._status', ['department' => $department])
                        </div>
                    </div>

                    <!-- Department Title -->
                    <div class="mb-1">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors flex items-center justify-between">
                            <span>{{ ucfirst($department->name) }}</span>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full {{ $palette['badge_bg'] }}">
                                {{ $department->users_count }} {{ Str::plural('member', $department->users_count) }}
                            </span>
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5">
                            {{ $department->description ?: 'Operational business unit' }}
                        </p>
                    </div>

                    <!-- Manager Box -->
                    <div class="mt-3 p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700 text-xs">
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Department Manager</div>
                        @if ($department->manager)
                            <a href="{{ route('admin.users.show', $department->manager) }}"
                                class="flex items-center gap-2 group p-1 -m-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/60 transition">
                                <div class="w-6 h-6 rounded-full bg-gray-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                    {{ substr($department->manager->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $department->manager->name }}</div>
                                    <div class="text-[11px] text-gray-500 truncate">{{ $department->manager->email }}</div>
                                </div>
                            </a>
                        @else
                            <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>No manager assigned</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs" onclick="event.stopPropagation()">
                    <button type="button" onclick="selectDepartment('{{ $deptSlug }}')"
                        class="text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                        <span>Show Employees</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ route('admin.departments.show', $department) }}"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors"
                            title="View Department Details">
                            Details
                        </a>

                        @can('departments.edit')
                            <a href="{{ route('admin.departments.edit', $department) }}"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors"
                                title="Edit Department Settings">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 p-8 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-xl border border-gray-200">
                No departments configured yet.
            </div>
        @endforelse
    </div>

    <!-- Employees of Department Section -->
    <div id="employees-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
        <!-- Section Header & Filter Tabs -->
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 id="employees-section-title" class="text-base sm:text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Employees Directory</span>
                </h3>
                <p id="employees-section-subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Viewing all team members across all departments
                </p>
            </div>

            <!-- Search & Add User Bar -->
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <input type="text" id="employee-search" oninput="filterEmployeeTable()"
                        placeholder="Search employee, ID, role..."
                        class="w-full text-xs pl-8 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @if(!auth()->user()->isAdmin() && auth()->user()->can('users.create'))
                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all duration-200 whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add User
                    </a>
                @endif
            </div>
        </div>

        <!-- Department Tabs Row -->
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex items-center gap-1.5 overflow-x-auto text-xs">
            <button type="button" onclick="selectDepartment('all')" id="tab-all"
                class="dept-tab px-3 py-1.5 rounded-lg font-semibold transition-all duration-150 bg-gray-800 text-white dark:bg-white dark:text-gray-900 shadow-sm flex items-center gap-1.5 flex-shrink-0">
                <span>All Departments</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20 dark:bg-black/20">{{ $totalMembers }}</span>
            </button>

            @foreach ($departments as $department)
                @php $slug = strtolower($department->name); @endphp
                <button type="button" onclick="selectDepartment('{{ $slug }}')" id="tab-{{ $slug }}"
                    class="dept-tab px-3 py-1.5 rounded-lg font-semibold transition-all duration-150 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center gap-1.5 flex-shrink-0">
                    <span>{{ ucfirst($department->name) }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        {{ $department->users_count }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Employees Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                <thead class="bg-gray-50/80 dark:bg-gray-700/70 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3">EMP ID</th>
                        <th class="px-5 py-3">Employee</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Designation</th>
                        <th class="px-5 py-3">Reports To</th>
                        <th class="px-5 py-3">Access Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="employee-table-body" class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @php $allUsersCount = 0; @endphp
                    @foreach ($departments as $department)
                        @foreach ($department->users as $member)
                            @php
                                $allUsersCount++;
                                $memberRole = $member->roles->first()?->name ?? $member->role;
                                $deptSlug = strtolower($department->name);
                            @endphp
                            <tr class="emp-row hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition-colors"
                                data-dept="{{ $deptSlug }}"
                                data-search="{{ strtolower($member->name . ' ' . $member->email . ' ' . $member->emp_id . ' ' . $member->designation . ' ' . $department->name) }}">
                                
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-semibold">
                                    <a href="{{ route('admin.users.show', $member) }}"
                                        class="inline-block group" title="View {{ $member->name }}'s Profile">
                                        @if ($member->emp_id)
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 transition shadow-2xs font-bold">
                                                {{ $member->emp_id }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 group-hover:text-indigo-600 transition">—</span>
                                        @endif
                                    </a>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <a href="{{ route('admin.users.show', $member) }}"
                                        class="flex items-center gap-2.5 group" title="View {{ $member->name }}'s Profile">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-gray-700 to-gray-500 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 group-hover:ring-2 group-hover:ring-indigo-500 transition">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-1.5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                <span>{{ $member->name }}</span>
                                                @if ($member->id === $department->manager_id)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 uppercase">
                                                        Manager
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[11px] group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">{{ $member->email }}</div>
                                        </div>
                                    </a>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ ucfirst($department->name) }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ \App\Rbac\Designation::label($member->designation) ?: ($member->designation ? ucwords($member->designation) : '—') }}
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    @if ($member->id === $department->manager_id)
                                        <span class="text-gray-400 italic">Self (Dept Head)</span>
                                    @else
                                        {{ $member->manager?->name ?? ($department->manager?->name ?? '—') }}
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($memberRole)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                            {{ ucfirst($memberRole) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Employee</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($member->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            <span class="w-1.5 h-1.5 mr-1 bg-green-500 rounded-full"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                            <span class="w-1.5 h-1.5 mr-1 bg-red-500 rounded-full"></span> Deactivated
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.users.show', $member) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors"
                                        title="View Profile">
                                        Profile
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if ($allUsersCount === 0)
                        <tr id="empty-state-row">
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500">
                                No employees assigned to any department yet.
                            </td>
                        </tr>
                    @else
                        <tr id="no-matching-row" class="hidden">
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500">
                                No employees found matching the filter criteria.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script for Dynamic Department Selection & Filtering -->
    <script>
        var currentDepartment = @json($selectedDept ?: 'all');

        document.addEventListener('DOMContentLoaded', function () {
            if (currentDepartment && currentDepartment !== 'all') {
                selectDepartment(currentDepartment, false);
            }
        });

        function selectDepartment(deptSlug, shouldScroll) {
            currentDepartment = deptSlug;

            // 1. Update Card selection styles
            document.querySelectorAll('.dept-card').forEach(function (card) {
                card.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500', 'bg-blue-50/20', 'dark:bg-blue-900/10');
            });

            if (deptSlug !== 'all') {
                var selectedCard = document.getElementById('dept-card-' + deptSlug);
                if (selectedCard) {
                    selectedCard.classList.add('ring-2', 'ring-blue-500', 'border-blue-500', 'bg-blue-50/20', 'dark:bg-blue-900/10');
                }
            }

            // 2. Update Tab button styles
            document.querySelectorAll('.dept-tab').forEach(function (tab) {
                tab.className = 'dept-tab px-3 py-1.5 rounded-lg font-semibold transition-all duration-150 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center gap-1.5 flex-shrink-0';
            });

            var activeTab = document.getElementById('tab-' + deptSlug);
            if (activeTab) {
                activeTab.className = 'dept-tab px-3 py-1.5 rounded-lg font-semibold transition-all duration-150 bg-gray-800 text-white dark:bg-white dark:text-gray-900 shadow-sm flex items-center gap-1.5 flex-shrink-0';
            }

            // 3. Update Title & Subtitle
            var titleEl = document.getElementById('employees-section-title');
            var subtitleEl = document.getElementById('employees-section-subtitle');
            if (titleEl && subtitleEl) {
                if (deptSlug === 'all') {
                    titleEl.innerHTML = '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg><span>Employees Directory</span>';
                    subtitleEl.textContent = 'Viewing all team members across all departments';
                } else {
                    var deptNameFormatted = deptSlug.charAt(0).toUpperCase() + deptSlug.slice(1);
                    titleEl.innerHTML = '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg><span>' + deptNameFormatted + ' Department Employees</span>';
                    subtitleEl.textContent = 'Showing active team members in ' + deptNameFormatted;
                }
            }

            // 4. Filter Table Rows
            filterEmployeeTable();

            // 5. Optionally smooth scroll to table
            if (shouldScroll !== false) {
                var section = document.getElementById('employees-section');
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        function filterEmployeeTable() {
            var search = (document.getElementById('employee-search').value || '').toLowerCase().trim();
            var rows = document.querySelectorAll('.emp-row');
            var visibleCount = 0;

            rows.forEach(function (row) {
                var rowDept = row.getAttribute('data-dept');
                var rowSearch = row.getAttribute('data-search') || '';

                var matchesDept = (currentDepartment === 'all' || rowDept === currentDepartment);
                var matchesSearch = (!search || rowSearch.indexOf(search) !== -1);

                if (matchesDept && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            var noMatchRow = document.getElementById('no-matching-row');
            if (noMatchRow) {
                noMatchRow.classList.toggle('hidden', visibleCount > 0);
            }
        }
    </script>
@endsection