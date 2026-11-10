@extends('layouts.app')

@section('title', 'Employee Profile - ' . $user->name)
@section('page-title', 'Employee Profile')

@section('content')
<div class="space-y-6">

    <!-- Top Action Buttons -->
    <div class="flex items-center justify-end">
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isAdmin() || auth()->user()->isHr() || auth()->user()->can('users.edit'))
                <a href="{{ route('employee-salaries.edit', $user) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-bold border border-indigo-200 dark:border-indigo-800 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Configure Salary</span>
                </a>
            @endif

            @if(auth()->user()->can('users.edit') || auth()->user()->isHr())
                <a href="{{ route('admin.users.edit', $user) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit User</span>
                </a>
            @endif

            <a href="{{ route('admin.attendance.index', ['search' => $user->name]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Attendance</span>
            </a>

            <a href="{{ route('admin.leaves.index', ['search' => $user->name]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Leaves</span>
            </a>
        </div>
    </div>

    <!-- Hero Profile Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
        @php
            $initials = collect(explode(' ', $user->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
            $deptSlug = strtolower($user->department ?: 'general');
            $deptBadgeColor = match($deptSlug) {
                'development' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'hr' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'marketing' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                'sales' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                default => 'bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
            };
        @endphp

        <!-- Top decorative border -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-5">
                <!-- Large Avatar with Photo Upload (for HR and Self) -->
                <div class="flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-20 h-20 sm:w-22 sm:h-22 rounded-2xl bg-gradient-to-tr from-gray-800 via-gray-700 to-indigo-700 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-lg flex-shrink-0 ring-4 ring-gray-100 dark:ring-gray-700 overflow-hidden relative">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; document.getElementById('admin-avatar-fallback').style.display='flex';">
                                <span id="admin-avatar-fallback" style="display:none;" class="w-full h-full items-center justify-center">{{ $initials ?: 'EM' }}</span>
                            @else
                                <span>{{ $initials ?: 'EM' }}</span>
                            @endif

                            @if ($user->canManageAvatar(auth()->user()))
                                <!-- Hover Overlay for Desktop -->
                                <button type="button" onclick="document.getElementById('avatar-file-input').click()"
                                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-[10px] font-bold transition-all duration-200 cursor-pointer">
                                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Change</span>
                                </button>
                            @endif
                        </div>

                        @if ($user->canManageAvatar(auth()->user()))
                            <!-- Camera Badge Button -->
                            <button type="button" onclick="document.getElementById('avatar-file-input').click()"
                                title="Set / Change Profile Photo"
                                class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 transition-all hover:scale-110 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>

                            <!-- Hidden Upload Form -->
                            <form id="avatar-upload-form" action="{{ route('users.avatar.update', $user) }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" id="avatar-file-input" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" onchange="document.getElementById('avatar-upload-form').submit()">
                            </form>
                        @endif
                    </div>

                    @if ($user->avatar && $user->canManageAvatar(auth()->user()))
                        <form id="avatar-remove-form" action="{{ route('users.avatar.destroy', $user) }}" method="POST" class="mt-1.5">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Remove profile photo?')"
                                class="text-[10px] font-semibold text-red-500 hover:text-red-700 dark:text-red-400 hover:underline inline-flex items-center gap-0.5">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Remove
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h1>

                        @if ($user->emp_id)
                            <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                {{ $user->emp_id }}
                            </span>
                        @endif

                        @if ($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                <span class="w-1.5 h-1.5 mr-1.5 bg-red-500 rounded-full"></span>
                                Deactivated
                            </span>
                        @endif
                    </div>

                    <!-- Designation & Department -->
                    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ \App\Rbac\Designation::label($user->designation) ?: ($user->designation ? ucwords($user->designation) : 'Staff Member') }}
                        </span>
                        <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold border {{ $deptBadgeColor }}">
                            {{ ucfirst($user->department ?: 'General') }} Department
                        </span>
                        @if($user->isManager())
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800 uppercase">
                                Dept Head / Manager
                            </span>
                        @endif
                    </div>

                    <!-- Meta Details -->
                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-gray-500 dark:text-gray-400 pt-1">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->email }}
                        </span>

                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Reports to: <strong class="text-gray-700 dark:text-gray-300 font-medium">{{ $user->manager?->name ?? 'Self / Department Head' }}</strong>
                        </span>

                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Joined {{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Highlight Badges -->
            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700">
                <div class="text-left sm:text-right">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Monthly Compensation</div>
                    @if($salary)
                        <div class="text-xl sm:text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                            ₹{{ number_format($salary->gross_salary, 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Net: <span class="font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format($salary->net_salary, 2) }}</span>
                        </div>
                    @else
                        <div class="text-sm font-bold text-amber-600 dark:text-amber-400">
                            Salary Not Configured
                        </div>
                    @endif
                </div>

                @if($salary)
                    <div class="mt-1">
                        @if ($salary->isApproved())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                ✓ Approved Structure
                            </span>
                        @elseif ($salary->isPendingApproval())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 animate-pulse">
                                ⏳ Pending Admin Approval
                            </span>
                        @elseif ($salary->isRejected())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                ✕ Rejected Structure
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Summary Highlights Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Monthly Present -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Present (This Month)</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $presentDays }} <span class="text-xs font-normal text-gray-400">days</span></p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $lateDays }} late {{ Str::plural('arrival', $lateDays) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Hours Worked -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Logged Hours</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalWorkedHours }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Current billing cycle</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Leave Balances -->
        @php
            $totalLeaveRemaining = $user->leaveBalances->sum('balance');
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Leave Balance</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($totalLeaveRemaining, 1) }} <span class="text-xs font-normal text-gray-400">days</span></p>
                <p class="text-[11px] text-gray-400 mt-0.5">Available across quotas</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Assigned Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned Tasks</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $tasks->count() }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $tasks->where('status', 'in_progress')->count() }} in progress</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for Deep Dive Sections -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-100 dark:border-gray-700 px-6 pt-4 flex items-center gap-6 overflow-x-auto text-xs sm:text-sm font-semibold">
            <button type="button" onclick="switchProfileTab('salary')" id="tab-btn-salary"
                class="profile-tab-btn pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Salary & Compensation</span>
            </button>

            <button type="button" onclick="switchProfileTab('attendance')" id="tab-btn-attendance"
                class="profile-tab-btn pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Attendance Log</span>
            </button>

            <button type="button" onclick="switchProfileTab('leaves')" id="tab-btn-leaves"
                class="profile-tab-btn pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Leave Balances & History</span>
            </button>

            @if(strtolower((string)($user->department ?? '')) !== 'sales')
            <button type="button" onclick="switchProfileTab('tasks')" id="tab-btn-tasks"
                class="profile-tab-btn pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Assigned Tasks</span>
            </button>
            @endif
        </div>

        <!-- Tab 1: Salary & Compensation Details -->
        <div id="tab-content-salary" class="p-6 space-y-6">
            @if($salary)
                <!-- Approval Notification Banner if Pending -->
                @if($salary->isPendingApproval())
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Pending Admin Approval</h4>
                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                    Submitted by <strong>{{ $salary->submittedBy?->name ?? 'HR Department' }}</strong> on {{ $salary->updated_at ? $salary->updated_at->format('d M Y, h:i A') : 'recently' }}.
                                    This salary configuration requires Company Owner approval before payroll can be processed.
                                </p>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin())
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <form action="{{ route('admin.salaries.approve', $salary) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Approve this salary structure?')"
                                        class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                                        ✓ Approve Salary
                                    </button>
                                </form>
                                <form action="{{ route('admin.salaries.reject', $salary) }}" method="POST"
                                    onsubmit="let r = prompt('Reason for rejecting:'); if (r === null) return false; this.querySelector('input[name=rejection_reason]').value = r; return true;">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="">
                                    <button type="submit"
                                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 shadow-sm transition">
                                        ✕ Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @elseif($salary->isRejected())
                    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-red-900 dark:text-red-200">Salary Structure Rejected by Admin</h4>
                            <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                                Rejection reason: <em>{{ $salary->rejection_reason ?: 'No specific reason provided' }}</em>
                            </p>
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('admin.salaries.approve', $salary) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs">
                                        Re-Approve Structure
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Earnings vs Deductions Breakdown Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Earnings Card -->
                    <div class="bg-gray-50/50 dark:bg-gray-750/50 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-3">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Monthly Earnings & Allowances
                            </h3>
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Additions</span>
                        </div>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Basic Salary</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₹{{ number_format($salary->basic_salary, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">House Rent Allowance (HRA)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₹{{ number_format($salary->hra, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Conveyance Allowance</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₹{{ number_format($salary->conveyance_allowance, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Special Allowance</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₹{{ number_format($salary->special_allowance, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Other Allowances</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₹{{ number_format($salary->other_allowances, 2) }}</span>
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm font-bold">
                                <span class="text-gray-800 dark:text-gray-200">Gross Monthly CTC</span>
                                <span class="text-indigo-600 dark:text-indigo-400">₹{{ number_format($salary->gross_salary, 2) }}</span>
                            </div>
                            <div class="text-[11px] text-gray-400 text-right">
                                Annual CTC: ₹{{ number_format($salary->gross_salary * 12, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Deductions Card -->
                    <div class="bg-gray-50/50 dark:bg-gray-750/50 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-3">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                Monthly Statutory Deductions
                            </h3>
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Subtractions</span>
                        </div>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Provident Fund (PF)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">-₹{{ number_format($salary->provident_fund, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Professional Tax (PT)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">-₹{{ number_format($salary->professional_tax, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Tax Deducted at Source (TDS)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">-₹{{ number_format($salary->tds, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-gray-600 dark:text-gray-300">Other Deductions</span>
                                <span class="font-semibold text-gray-900 dark:text-white">-₹{{ number_format($salary->other_deductions, 2) }}</span>
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm font-bold">
                                <span class="text-gray-800 dark:text-gray-200">Total Deductions</span>
                                <span class="text-red-600 dark:text-red-400">-₹{{ number_format($salary->total_deductions, 2) }}</span>
                            </div>

                            <div class="mt-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                                <span class="font-bold text-xs text-emerald-800 dark:text-emerald-300 uppercase">Net Take-Home Pay</span>
                                <span class="font-extrabold text-base text-emerald-600 dark:text-emerald-400">₹{{ number_format($salary->net_salary, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banking & Payment Disbursement Information -->
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-5 bg-gray-50/50 dark:bg-gray-750/50">
                    <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Banking & Disbursement Details
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase block">Payment Method</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ ucwords(str_replace('_', ' ', $salary->payment_method ?: 'Bank Transfer')) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase block">Bank Name</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $salary->bank_name ?: 'Not Provided' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase block">Account Number</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">{{ $salary->account_number ?: 'Not Provided' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase block">IFSC / PAN</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">
                                {{ $salary->ifsc_code ? 'IFSC: '.$salary->ifsc_code : '' }}
                                {{ $salary->ifsc_code && $salary->pan_number ? ' | ' : '' }}
                                {{ $salary->pan_number ? 'PAN: '.$salary->pan_number : '' }}
                                {{ ! $salary->ifsc_code && ! $salary->pan_number ? 'Not Provided' : '' }}
                            </span>
                        </div>
                    </div>
                    @if($salary->notes)
                        <div class="mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-700 text-xs text-gray-500">
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Notes:</span> {{ $salary->notes }}
                        </div>
                    @endif
                </div>
            @else
                <!-- No salary setup empty state -->
                <div class="p-8 text-center bg-gray-50 dark:bg-gray-750 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">No Salary Structure Configured</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                        This employee does not have a compensation structure configured yet. HR can configure the salary to initiate payroll processing.
                    </p>
                    @if(auth()->user()->isAdmin() || auth()->user()->isHr() || auth()->user()->can('users.edit'))
                        <div class="mt-4">
                            <a href="{{ route('employee-salaries.edit', $user) }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                                Set Up Salary Now
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Tab 2: Attendance History -->
        <div id="tab-content-attendance" class="p-6 space-y-4 hidden">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Recent Attendance Logs</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Latest punch-in and clock-out activity</p>
                </div>
                <a href="{{ route('admin.attendance.index', ['search' => $user->name]) }}"
                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    View Full Attendance Sheet &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                    <thead class="bg-gray-50/70 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Check In</th>
                            <th class="px-4 py-3">Check Out</th>
                            <th class="px-4 py-3">Hours Worked</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($recentAttendances as $att)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($att->date)->format('D, d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-mono">
                                    {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-mono">
                                    {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $att->total_hours_formatted ?? ($att->total_minutes ? sprintf('%dh %02dm', floor($att->total_minutes / 60), $att->total_minutes % 60) : '—') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($att->status === 'present')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Present</span>
                                    @elseif($att->status === 'late')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Late</span>
                                    @elseif($att->status === 'half_day')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Half Day</span>
                                    @elseif($att->status === 'absent')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Absent</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">{{ ucfirst($att->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No attendance records found for this employee.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Leave Balances & History -->
        <div id="tab-content-leaves" class="p-6 space-y-6 hidden">
            <!-- Quota Cards -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Allocated Leave Quotas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @forelse($user->leaveBalances as $balance)
                        <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-white">{{ $balance->leaveType?->name ?? 'Leave' }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    {{ $balance->leaveType?->code ?? 'LV' }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-baseline justify-between">
                                <div>
                                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($balance->balance, 1) }}</span>
                                    <span class="text-xs text-gray-400 ml-1">remaining</span>
                                </div>
                                <div class="text-right text-[11px] text-gray-400">
                                    <span>Used: {{ number_format($balance->used, 1) }}</span> /
                                    <span>Total: {{ number_format($balance->total, 1) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 text-center text-xs text-gray-400">
                            No leave quotas allocated to this user yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Requests Table -->
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700 mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Recent Leave Applications</h3>
                    <a href="{{ route('admin.leaves.index', ['search' => $user->name]) }}"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View All Leaves &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                        <thead class="bg-gray-50/70 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Leave Type</th>
                                <th class="px-4 py-3">Period</th>
                                <th class="px-4 py-3">Days</th>
                                <th class="px-4 py-3">Reason</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($recentLeaves as $leave)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $leave->leaveType?->name ?? 'Leave' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-700 dark:text-gray-300">
                                        {{ $leave->days }} {{ Str::plural('day', $leave->days) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 truncate max-w-xs" title="{{ $leave->reason }}">
                                        {{ $leave->reason ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($leave->status === 'approved')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Approved</span>
                                        @elseif($leave->status === 'pending')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                        @elseif($leave->status === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Rejected</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">{{ ucfirst($leave->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        No leave requests submitted by this employee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 4: Assigned Tasks -->
        @if(strtolower((string)($user->department ?? '')) !== 'sales')
        <div id="tab-content-tasks" class="p-6 space-y-4 hidden">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Assigned Tasks</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tasks assigned to this employee</p>
                </div>
                <a href="{{ route('tasks.index') }}"
                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Go to Task Pool &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                    <thead class="bg-gray-50/70 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3">Task Title</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Due Date</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $task->title }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $task->project_name ?: 'General' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->priority === 'high' || $task->priority === 'urgent')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 uppercase">{{ $task->priority }}</span>
                                    @elseif($task->priority === 'medium')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 uppercase">Medium</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 uppercase">{{ $task->priority ?: 'Low' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->status === 'completed')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Completed</span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">In Progress</span>
                                    @elseif($task->status === 'pending')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">{{ ucfirst($task->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No tasks currently assigned to this employee.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function switchProfileTab(tabName) {
        var tabs = ['salary', 'attendance', 'leaves', 'tasks'];
        tabs.forEach(function(t) {
            var content = document.getElementById('tab-content-' + t);
            var btn = document.getElementById('tab-btn-' + t);
            if (!content || !btn) return;
            if (t === tabName) {
                content.classList.remove('hidden');
                btn.className = 'profile-tab-btn pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 whitespace-nowrap font-bold';
            } else {
                content.classList.add('hidden');
                btn.className = 'profile-tab-btn pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1.5 whitespace-nowrap font-semibold';
            }
        });
    }
</script>
@endsection
