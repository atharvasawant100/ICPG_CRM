@extends('layouts.app')

@section('title', $task->title . ' - Task Details')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Breadcrumb & Top Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('tasks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Tasks</span>
            </a>
            <span>/</span>
            <span class="font-mono text-indigo-600 dark:text-indigo-400 font-semibold">{{ $task->formatted_id }}</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('delegate', $task)
            <button type="button" onclick="openDelegateModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-xs sm:text-sm font-semibold hover:bg-teal-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Delegate</span>
            </button>
            @endcan

            @can('escalate', $task)
            <button type="button" onclick="openEscalateModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-800 text-xs sm:text-sm font-bold hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Escalate</span>
            </button>
            @endcan

            @can('rework', $task)
            <button type="button" onclick="openReworkModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-xs sm:text-sm font-bold hover:bg-amber-100 transition-colors cursor-pointer shadow-2xs"
                title="Send task back for revisions or rework (even if completed)">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Request Re-work</span>
            </button>
            @endcan

            @can('requestHelp', $task)
            <button type="button" onclick="openHelpRequestModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs sm:text-sm font-semibold hover:bg-amber-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Request Help</span>
            </button>
            @endcan

            @if (! auth()->user()->isAdmin())
            <a href="{{ route('tickets.create', ['task_id' => $task->id]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-xs sm:text-sm font-semibold hover:bg-purple-100 transition-colors cursor-pointer"
                title="Facing missing requirements from manager or unfair blame? Report directly to Admin bypassing manager.">
                <span>🛡️ Report Blocker to Admin</span>
            </a>
            @endif

            @can('update', $task)
            <button type="button" onclick="openEditTaskModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit</span>
            </button>
            @endcan

            @can('delete', $task)
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Are you sure you want to permanently delete this task?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="p-2 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 border border-transparent hover:border-red-200 transition-colors cursor-pointer"
                    title="Delete Task">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Escalation Alert Banner (If task has been escalated by Admin) -->
    @if ($task->isEscalated())
    <div class="bg-gradient-to-r from-red-500/10 via-rose-500/15 to-orange-500/10 dark:from-red-950/60 dark:via-rose-950/50 dark:to-orange-950/40 rounded-2xl p-5 border-2 border-red-500 dark:border-red-600 shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-red-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm text-lg font-bold">
                    🚨
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-2xs">
                            ESCALATED TO URGENT
                        </span>
                        <span class="text-xs text-red-700 dark:text-red-300 font-semibold">
                            By Administrator {{ $task->escalatedBy?->name ?? 'Admin' }}
                        </span>
                        @if ($task->escalated_at)
                        <span class="text-[11px] text-red-500 dark:text-red-400">
                            • {{ $task->escalated_at->diffForHumans() }} ({{ $task->escalated_at->format('M d, Y · h:i A') }})
                        </span>
                        @endif
                    </div>
                    <div class="mt-2.5 bg-white/80 dark:bg-gray-900/80 rounded-xl p-3 border border-red-200 dark:border-red-900/60 text-sm font-medium text-gray-800 dark:text-gray-100">
                        <span class="text-xs font-bold text-red-600 dark:text-red-400 block mb-1 uppercase tracking-wide">Admin Escalation Directive:</span>
                        {{ $task->escalation_reason }}
                    </div>
                </div>
            </div>
            @can('escalate', $task)
            <button type="button" onclick="openEscalateModal()"
                class="self-start sm:self-center px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition-colors whitespace-nowrap cursor-pointer">
                Update Escalation Directive
            </button>
            @endcan
        </div>
    </div>
    @endif

    <!-- Re-work Alert Banner (If task is currently sent back for rework) -->
    @if ($task->isRework())
    <div class="bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-yellow-500/15 dark:from-amber-950/60 dark:via-orange-950/50 dark:to-yellow-950/40 rounded-2xl p-5 border-2 border-amber-400 dark:border-amber-600 shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm text-lg font-bold">
                    🔄
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white shadow-2xs">
                            TASK UNDER RE-WORK
                        </span>
                        <span class="text-xs text-amber-800 dark:text-amber-300 font-semibold">
                            Sent back by {{ $task->reworkBy?->name ?? 'Management' }}
                        </span>
                        @if ($task->rework_count > 1)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-900 dark:bg-amber-900 dark:text-amber-200">
                            Revision #{{ $task->rework_count }}
                        </span>
                        @endif
                        @if ($task->rework_at)
                        <span class="text-[11px] text-amber-600 dark:text-amber-400">
                            • {{ $task->rework_at->diffForHumans() }} ({{ $task->rework_at->format('M d, Y · h:i A') }})
                        </span>
                        @endif
                    </div>
                    @if ($task->rework_notes)
                    <div class="mt-2.5 bg-white/80 dark:bg-gray-900/80 rounded-xl p-3 border border-amber-300 dark:border-amber-800/60 text-sm font-medium text-gray-800 dark:text-gray-100">
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400 block mb-1 uppercase tracking-wide">Required Changes & Re-work Instructions:</span>
                        {{ $task->rework_notes }}
                    </div>
                    @endif
                </div>
            </div>
            @can('rework', $task)
            <button type="button" onclick="openReworkModal()"
                class="self-start sm:self-center px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm transition-colors whitespace-nowrap cursor-pointer">
                Add Revision Notes
            </button>
            @endcan
        </div>
    </div>
    @endif

    <!-- Review Action Banner (If task requires review and pending) -->
    @if ($task->review_required)
        @if ($task->isUnderReview())
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/40 dark:to-indigo-950/40 rounded-2xl p-5 border border-purple-200 dark:border-purple-800 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-purple-900 dark:text-purple-200">This Task is Currently in Review</h3>
                            <p class="text-xs text-purple-700 dark:text-purple-300 mt-0.5">
                                Awaiting reviewer decision before it can be marked completed.
                            </p>
                        </div>
                    </div>

                    @can('decideReview', $task)
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openReviewModal('approved')"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer">
                            Approve Task
                        </button>
                        <button type="button" onclick="openReviewModal('changes_requested')"
                            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer">
                            Request Changes
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
        @elseif (! $task->isCompleted() && ! $task->isUnderReview())
            @can('submitReview', $task)
            <div class="bg-purple-50/60 dark:bg-purple-950/20 rounded-2xl p-4 border border-purple-200/80 dark:border-purple-800/40 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs sm:text-sm font-semibold text-purple-900 dark:text-purple-200">Review is required on this task when finished.</span>
                </div>
                <button type="button" onclick="openSubmitReviewModal()"
                    class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer">
                    Submit for Review
                </button>
            </div>
            @endcan
        @endif
    @endif

    <!-- Pending Help Request Action Card (For the helper) -->
    @php
        $myPendingHelp = $task->helpRequests->where('helper_id', auth()->id())->where('status', 'pending')->first();
    @endphp
    @if ($myPendingHelp)
        <div class="bg-amber-50 dark:bg-amber-950/40 rounded-2xl p-5 border border-amber-300 dark:border-amber-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ $myPendingHelp->requester?->name }} requested your help</span>
                </h3>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-1 italic">
                    "{{ $myPendingHelp->message }}"
                </p>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('tasks.help-requests.respond', [$task, $myPendingHelp]) }}">
                    @csrf
                    <input type="hidden" name="status" value="accepted">
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm cursor-pointer">
                        Accept Help
                    </button>
                </form>
                <form method="POST" action="{{ route('tasks.help-requests.respond', [$task, $myPendingHelp]) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold text-xs cursor-pointer">
                        Decline
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($task->contentPost)
    <!-- Linked Social Media Content Post Banner -->
    <div class="bg-gradient-to-r from-pink-50 via-purple-50 to-pink-50 dark:from-pink-950/40 dark:via-purple-950/40 dark:to-pink-950/40 rounded-2xl p-4 border border-pink-200 dark:border-pink-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-pink-100 dark:bg-pink-900/60 text-pink-600 dark:text-pink-300 flex items-center justify-center text-lg shrink-0">
                📱
            </div>
            <div>
                <span class="text-[10px] font-bold text-pink-600 dark:text-pink-400 uppercase tracking-wider">Social Media Content Post #{{ $task->contentPost->id }}</span>
                <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">{{ $task->contentPost->title }}</h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Brand: {{ $task->contentPost->brand?->name ?? 'General' }} &bull; Scheduled: {{ $task->contentPost->scheduled_at?->format('d M Y, h:i A') ?? 'Not scheduled' }} &bull; Status: <span class="capitalize">{{ str_replace('_', ' ', $task->contentPost->status) }}</span></p>
            </div>
        </div>
        <a href="{{ route('content.posts.show', $task->contentPost) }}" class="self-start sm:self-center px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-semibold shadow-sm transition-all whitespace-nowrap">
            View Post & Creative ➔
        </a>
    </div>
    @endif

    <!-- Main Title Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                {{ $task->formatted_id }}
            </span>
            @if (auth()->user()->isAdmin())
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $task->departmentBadgeClasses() }}">
                <span>{{ $task->departmentIcon() }}</span>
                <span>Dept: {{ $task->departmentLabel() }}</span>
            </span>
            @endif
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $task->priorityBadgeClasses() }}">
                Priority: {{ $task->priorityLabel() }}
            </span>
            @if ($task->isEscalated())
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-red-600 text-white shadow-2xs animate-pulse">
                🚨 Escalated
            </span>
            @endif
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $task->statusBadgeClasses() }}">
                Status: {{ $task->statusLabel() }}
            </span>
            @if ($task->review_required)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Review Required
                </span>
            @endif
            @if ($task->isOverdue())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200 border border-red-300 animate-pulse">
                    <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $task->overdueBadgeLabel() }}
                </span>
            @endif
        </div>

        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
            {{ $task->title }}
        </h1>
    </div>

    <!-- 2-Column Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Expected Outcomes / Acceptance Criteria Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-indigo-100 dark:border-indigo-900/40 shadow-sm">
                <div class="flex items-center gap-2 mb-3 text-indigo-700 dark:text-indigo-300 font-bold text-sm">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3>Expected Outcomes & Acceptance Criteria</h3>
                </div>
                <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed bg-indigo-50/40 dark:bg-indigo-950/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/30 whitespace-pre-line">
                    {{ $task->expected_outcomes ?: 'No specific acceptance criteria provided for this task.' }}
                </div>
            </div>

            <!-- Task Description Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <span>Description</span>
                </h3>
                <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $task->description ?: 'No detailed description provided.' }}
                </div>
            </div>

            <!-- Collaboration & Help Requests Hub -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Collaborators & Help Requests</span>
                    </h3>
                    @can('manageCollaborators', $task)
                    <button type="button" onclick="openAddCollaboratorModal()"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Collaborator</span>
                    </button>
                    @endcan
                </div>

                <!-- Collaborators List -->
                @if ($task->collaborators->isNotEmpty())
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach ($task->collaborators as $collab)
                            <div class="inline-flex items-center gap-2 pl-2 pr-3 py-1 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 text-xs">
                                <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center">
                                    {{ strtoupper(substr($collab->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $collab->user?->name }}</span>
                                <span class="text-[10px] text-gray-400 capitalize">({{ $collab->role }})</span>
                                @can('manageCollaborators', $task)
                                <form method="POST" action="{{ route('tasks.collaborators.destroy', [$task, $collab->user_id]) }}" class="inline ml-1" onsubmit="return confirm('Remove collaborator?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">No additional collaborators added yet.</p>
                @endif

                <!-- Help Requests History -->
                @if ($task->helpRequests->isNotEmpty())
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Help Request History</p>
                        @foreach ($task->helpRequests as $hr)
                            <div class="p-3 rounded-xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $hr->requester?->name }} requested help from {{ $hr->helper?->name }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $hr->statusBadgeClasses() }}">
                                        {{ ucfirst($hr->status) }}
                                    </span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mt-1">"{{ $hr->message }}"</p>
                                @if ($hr->response_notes)
                                    <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-1 pl-2 border-l-2 border-gray-300 dark:border-gray-600">
                                        Response: "{{ $hr->response_notes }}"
                                    </p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1">
                                    {{ $hr->created_at->format('d M Y, h:i A') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Comments & Discussion Stream -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Discussion & Comments ({{ $task->comments->count() }})</span>
                    </h3>
                </div>

                <!-- Post Comment Form -->
                @can('comment', $task)
                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="space-y-2">
                    @csrf
                    <textarea name="comment" rows="2" required placeholder="Add a comment or update..."
                        class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500"></textarea>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm cursor-pointer">
                            Post Comment
                        </button>
                    </div>
                </form>
                @endcan

                <!-- Comments List -->
                <div class="space-y-3.5 pt-2">
                    @forelse ($task->comments as $comment)
                        @php
                            $isEscalation = str_contains($comment->comment, 'ADMIN ESCALATION NOTICE');
                            $isRework = str_contains($comment->comment, 'TASK SENT BACK FOR RE-WORK');
                        @endphp
                        <div class="p-3.5 rounded-xl {{ $isEscalation ? 'bg-red-50/90 dark:bg-red-950/40 border-2 border-red-400 dark:border-red-600 shadow-xs' : ($isRework ? 'bg-amber-50/90 dark:bg-amber-950/40 border-2 border-amber-400 dark:border-amber-600 shadow-xs' : 'bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700') }} text-xs sm:text-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full {{ $isEscalation ? 'bg-red-600 text-white font-bold' : ($isRework ? 'bg-amber-500 text-white font-bold' : 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold') }} text-xs flex items-center justify-center">
                                        {{ $isEscalation ? '🚨' : ($isRework ? '🔄' : strtoupper(substr($comment->user?->name ?? 'U', 0, 1))) }}
                                    </div>
                                    <span class="font-bold {{ $isEscalation ? 'text-red-900 dark:text-red-200' : ($isRework ? 'text-amber-900 dark:text-amber-200' : 'text-gray-900 dark:text-white') }}">{{ $comment->user?->name }}</span>
                                    @if ($isEscalation)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-600 text-white">Escalation Notice</span>
                                    @elseif ($isRework)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white">Re-work Directive</span>
                                    @endif
                                    <span class="text-[11px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                @if (auth()->user()->isAdmin() || (int) $comment->user_id === (int) auth()->id())
                                    <form method="POST" action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" onsubmit="return confirm('Delete comment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line pl-8">
                                {{ $comment->comment }}
                            </p>

                            <!-- Nested Replies -->
                            @if ($comment->replies->isNotEmpty())
                                <div class="pl-8 pt-2 space-y-2 border-t border-gray-100 dark:border-gray-700">
                                    @foreach ($comment->replies as $reply)
                                        <div class="bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $reply->user?->name }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line">{{ $reply->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Quick Reply Toggle Button -->
                            <div class="pl-8 pt-1">
                                <button type="button" onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')"
                                    class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                    Reply
                                </button>
                                <form id="reply-form-{{ $comment->id }}" method="POST" action="{{ route('tasks.comments.store', $task) }}" class="hidden mt-2 space-y-2">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="comment" rows="1" required placeholder="Write a reply..."
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"></textarea>
                                    <div class="flex justify-end gap-1.5">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-semibold">Reply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No comments yet. Start the conversation!</p>
                    @endforelse
                </div>
            </div>

            <!-- Attachments Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>Attachments ({{ $task->attachments->count() }})</span>
                    </h3>
                </div>

                @can('uploadAttachment', $task)
                <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2">
                    @csrf
                    <input type="file" name="file" required
                        class="text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer w-full sm:w-auto">
                    <button type="submit"
                        class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer w-full sm:w-auto">
                        Attach File
                    </button>
                </form>
                @endcan

                <div class="space-y-2 pt-1">
                    @forelse ($task->attachments as $att)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $att->file_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $att->formattedFileSize() }} • Uploaded by {{ $att->user?->name }} • {{ $att->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('tasks.attachments.download', [$task, $att]) }}"
                                    class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-semibold"
                                    title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                                @can('deleteAttachment', [$task, $att])
                                <form method="POST" action="{{ route('tasks.attachments.destroy', [$task, $att]) }}" onsubmit="return confirm('Remove attachment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 cursor-pointer" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No attachments uploaded.</p>
                    @endforelse
                </div>
            </div>

            <!-- Assignment History & Delegation Chain -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Accountability & Assignment Chain</span>
                    </h3>
                    <span class="text-[11px] font-semibold text-gray-400">
                        {{ $task->assignments->count() }} {{ \Illuminate\Support\Str::plural('assignment', $task->assignments->count()) }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($task->assignments as $assignment)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                            <div class="w-7 h-7 rounded-full bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                {{ strtoupper(substr($assignment->assignee?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0 text-xs sm:text-sm">
                                <p class="text-gray-900 dark:text-white font-semibold">
                                    Assigned to <span class="text-teal-600 dark:text-teal-400">{{ $assignment->assignee?->name ?? 'Unknown' }}</span>
                                    @if ($assignment->assigner && $assignment->assigner->id !== $assignment->assignee?->id)
                                        <span class="text-gray-500 dark:text-gray-400 font-normal">by {{ $assignment->assigner->name }}</span>
                                    @endif
                                </p>
                                @if ($assignment->notes)
                                    <p class="text-gray-600 dark:text-gray-300 text-xs mt-1 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                        "{{ $assignment->notes }}"
                                    </p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1">
                                    {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y, h:i A') : $assignment->created_at->format('d M Y, h:i A') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Initial assignment recorded directly on task.</p>
                    @endforelse
                </div>
            </div>

            <!-- Activity & Audit Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Activity Trail</span>
                </h3>

                <div class="space-y-3">
                    @forelse ($task->activityLogs as $log)
                        <div class="flex items-start gap-3 text-xs sm:text-sm border-l-2 border-indigo-200 dark:border-indigo-900 pl-3 py-1">
                            <div class="flex-1">
                                <p class="text-gray-800 dark:text-gray-200 font-medium">
                                    {{ $log->comment ?: ucfirst($log->action) }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ $log->user?->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right 1 Column: Metadata, Owner, Status Form & Actions -->
        <div class="space-y-6">

            <!-- Accountable Owner Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-teal-200/60 dark:border-teal-900/40 shadow-sm space-y-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">
                    Accountable Owner (Current Assignee)
                </p>

                @if ($task->assignee)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 font-bold text-sm flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $task->assignee->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->assignee->designation ?? 'Employee' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $task->assignee->department ?? 'General' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">No assignee assigned</p>
                @endif

                @can('delegate', $task)
                <button type="button" onclick="openDelegateModal()"
                    class="w-full mt-2 py-2 px-3 rounded-xl bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/40 dark:hover:bg-teal-900/60 text-teal-700 dark:text-teal-300 text-xs font-semibold border border-teal-200 dark:border-teal-800 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Reassign / Delegate</span>
                </button>
                @endcan
            </div>

            <!-- Task Status & Progress Updater -->
            @can('update', $task)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Update Task Status
                </h3>

                <form method="POST" action="{{ route('tasks.status', $task) }}" class="space-y-3.5">
                    @csrf
                    @method('PATCH')

                    <!-- Status Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                            @foreach (\App\Models\Task::STATUSES as $val => $lbl)
                                <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Optional Note/Comment -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Update Note (Optional)</label>
                        <textarea name="comment" rows="2" placeholder="Note why status was updated..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition-colors cursor-pointer">
                        Save Status
                    </button>
                </form>
            </div>
            @endcan

            <!-- Details & Dates Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm space-y-3.5 text-xs sm:text-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Task Metadata
                </h3>

                <div class="space-y-2.5 divide-y divide-gray-100 dark:divide-gray-700/60">
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created By:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $task->creator?->name ?? 'System' }}</span>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created Date:</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $task->created_at->format('d M Y') }}</span>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Start Date:</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $task->start_date ? $task->start_date->format('d M Y') : '—' }}</span>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Due Date:</span>
                        <span class="font-medium {{ $task->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-800 dark:text-gray-200' }}">
                            {{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}
                        </span>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Time Logged:</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $task->formattedTimeSpent() }}</span>
                    </div>

                    @if ($task->project)
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Project:</span>
                        <a href="{{ route('projects.show', $task->project) }}"
                            class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline truncate max-w-[170px] inline-flex items-center gap-1"
                            title="Open Project Details">
                            <span>{{ $task->project->title ?? $task->project->name }}</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ======================= DELEGATE TASK MODAL ======================= -->
<div id="delegateTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Delegate Task</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Transfer accountable ownership to an eligible colleague.</p>
            </div>
            <button type="button" onclick="closeDelegateModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.delegate', $task) }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Select New Assignee <span class="text-red-500">*</span>
                </label>
                <select name="assigned_to" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Choose colleague...</option>
                    @foreach ($eligibleEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->designation ?? ($emp->department ?? 'General') }}</option>
                    @endforeach
                </select>
                @if (auth()->user()->isAdmin())
                <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">
                    ⚡ Delegating as Administrator automatically sets task priority to <strong>Urgent</strong>.
                </p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Handoff / Delegation Notes</label>
                <textarea name="notes" rows="3" placeholder="Provide instructions, background, or goals..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeDelegateModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">Confirm Delegation</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= REQUEST HELP MODAL ======================= -->
<div id="helpRequestModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Request Help</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Request advice or assistance without transferring ownership.</p>
            </div>
            <button type="button" onclick="closeHelpRequestModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.help-requests.store', $task) }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Select Colleague <span class="text-red-500">*</span>
                </label>
                <select name="helper_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Choose colleague...</option>
                    @foreach ($eligibleEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? 'General' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    What help do you need? <span class="text-red-500">*</span>
                </label>
                <textarea name="message" rows="3" required placeholder="Describe the blocker or question you have..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeHelpRequestModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">Send Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= ADD COLLABORATOR MODAL ======================= -->
<div id="addCollaboratorModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Add Collaborator</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Grant contribution and discussion visibility.</p>
            </div>
            <button type="button" onclick="closeAddCollaboratorModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.collaborators.store', $task) }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Select Colleague <span class="text-red-500">*</span>
                </label>
                <select name="user_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Choose colleague...</option>
                    @foreach ($eligibleEmployees->whereNotIn('id', $task->collaborators->pluck('user_id')) as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department ?? 'General' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select name="role" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="collaborator">Collaborator</option>
                    <option value="contributor">Contributor</option>
                    <option value="reviewer">Reviewer</option>
                </select>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeAddCollaboratorModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">Add Collaborator</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= SUBMIT REVIEW MODAL ======================= -->
<div id="submitReviewModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Submit Task for Review</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">The reviewer will be notified to inspect your deliverables.</p>
            </div>
            <button type="button" onclick="closeSubmitReviewModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.review.submit', $task) }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Submission Notes (Optional)</label>
                <textarea name="notes" rows="3" placeholder="Summary of completed work, links, or instructions for the reviewer..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeSubmitReviewModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= REVIEW DECISION MODAL ======================= -->
<div id="reviewDecisionModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 id="reviewDecisionTitle" class="text-base font-bold text-gray-900 dark:text-white">Review Decision</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Record your evaluation and feedback for the task owner.</p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.review.decide', $task) }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="decision" id="reviewDecisionInput" value="approved">

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Decision Feedback / Notes</label>
                <textarea name="notes" rows="3" placeholder="Provide constructive feedback or approval notes..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"></textarea>
            </div>

            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" id="reviewDecisionSubmitBtn" class="px-5 py-2 rounded-xl text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">
                    Confirm Decision
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= EDIT TASK MODAL ======================= -->
<div id="editTaskModal" class="fixed inset-0 bg-black/60 dark:bg-black/80 z-[100] hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full border border-gray-200 dark:border-gray-700 overflow-hidden my-8">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Task</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Modify title, outcomes, priority or dates.</p>
            </div>
            <button type="button" onclick="closeEditTaskModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Task Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ $task->title }}" required
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">{{ $task->description }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Expected Outcomes / Acceptance Criteria</label>
                <textarea name="expected_outcomes" rows="2"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">{{ $task->expected_outcomes }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                        @foreach (\App\Models\Task::PRIORITIES as $val => $lbl)
                            <option value="{{ $val }}" {{ $task->priority === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" id="edit_review_required" name="review_required" value="1" {{ $task->review_required ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="edit_review_required" class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                    Require review before marking as Completed
                </label>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2.5">
                <button type="button" onclick="closeEditTaskModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@can('escalate', $task)
<!-- Escalate Task Modal (Admin Only) -->
<div id="escalateTaskModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-red-200 dark:border-red-900/50 space-y-4">
        <div class="flex items-start justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-lg font-bold">
                    🚨
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Escalate Task to Urgent</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sets priority to Urgent and notifies the assignee immediately.</p>
                </div>
            </div>
            <button type="button" onclick="closeEscalateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm p-1 rounded-lg">✕</button>
        </div>

        <form method="POST" action="{{ route('tasks.escalate', $task) }}" class="space-y-4">
            @csrf
            <div class="bg-red-50/60 dark:bg-red-950/20 p-3 rounded-xl border border-red-200/60 dark:border-red-800/40 text-xs text-red-800 dark:text-red-300">
                <p class="font-bold text-gray-900 dark:text-white">{{ $task->title }}</p>
                <p class="text-[11px] text-red-600 dark:text-red-400 mt-0.5">
                    Assignee: <strong>{{ $task->assignee?->name ?? 'Unassigned' }}</strong> • Current Priority: <strong class="capitalize">{{ $task->priority }}</strong>
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Escalation Directive / Message to Employee <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="3" required
                    placeholder="e.g. Employee is not paying attention or responding. This task is overdue/critical and must be completed immediately today."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">{{ old('reason', $task->escalation_reason) }}</textarea>
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
@endcan

@can('rework', $task)
<!-- Re-work Task Modal (Creator, Manager, Admin) -->
<div id="reworkTaskModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-amber-200 dark:border-amber-900/50 space-y-4">
        <div class="flex items-start justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg font-bold">
                    ↺
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Send Task Back for Re-work</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Reopens task and directs the assignee with required changes.</p>
                </div>
            </div>
            <button type="button" onclick="closeReworkModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm p-1 rounded-lg">✕</button>
        </div>

        <form method="POST" action="{{ route('tasks.rework', $task) }}" class="space-y-4">
            @csrf
            <div class="bg-amber-50/70 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200/60 dark:border-amber-800/40 text-xs text-amber-900 dark:text-amber-300">
                <p class="font-bold text-gray-900 dark:text-white">{{ $task->title }}</p>
                <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">
                    Assignee: <strong>{{ $task->assignee?->name ?? 'Unassigned' }}</strong> • Current Status: <strong class="capitalize">{{ $task->status }}</strong>
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Required Revisions / Instructions <span class="text-red-500">*</span>
                </label>
                <textarea name="rework_notes" rows="3" required
                    placeholder="Specify exactly what needs to be reworked, corrections required, or missing items..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">These notes will be delivered to the assignee and pinned at the top of the task.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Revised Due Date (Optional)
                </label>
                <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}"
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
@endcan

<script>
    function openDelegateModal() {
        const m = document.getElementById('delegateTaskModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeDelegateModal() {
        const m = document.getElementById('delegateTaskModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openHelpRequestModal() {
        const m = document.getElementById('helpRequestModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeHelpRequestModal() {
        const m = document.getElementById('helpRequestModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openAddCollaboratorModal() {
        const m = document.getElementById('addCollaboratorModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeAddCollaboratorModal() {
        const m = document.getElementById('addCollaboratorModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openSubmitReviewModal() {
        const m = document.getElementById('submitReviewModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeSubmitReviewModal() {
        const m = document.getElementById('submitReviewModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openReviewModal(decision) {
        const m = document.getElementById('reviewDecisionModal');
        const input = document.getElementById('reviewDecisionInput');
        const title = document.getElementById('reviewDecisionTitle');
        const btn = document.getElementById('reviewDecisionSubmitBtn');

        input.value = decision;
        if (decision === 'approved') {
            title.textContent = 'Approve Task Completion';
            btn.textContent = 'Approve and Complete';
            btn.className = 'px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer';
        } else {
            title.textContent = 'Request Changes on Task';
            btn.textContent = 'Request Changes';
            btn.className = 'px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold shadow-sm cursor-pointer';
        }

        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeReviewModal() {
        const m = document.getElementById('reviewDecisionModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openEditTaskModal() {
        const m = document.getElementById('editTaskModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeEditTaskModal() {
        const m = document.getElementById('editTaskModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }

    function openEscalateModal() {
        const m = document.getElementById('escalateTaskModal');
        if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
    }
    function closeEscalateModal() {
        const m = document.getElementById('escalateTaskModal');
        if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
    }

    function openReworkModal() {
        const m = document.getElementById('reworkTaskModal');
        if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
    }
    function closeReworkModal() {
        const m = document.getElementById('reworkTaskModal');
        if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
    }
</script>
@endsection
