@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Notifications
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    @if($markedCount > 0)
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Marked {{ $markedCount }} unread {{ Str::plural('notification', $markedCount) }} as read.
                        </span>
                    @else
                        Recent notifications, task updates, and approval alerts.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-sm transition">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Mark all as read</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-3">
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 {{ $filter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            <span>All</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                {{ $totalCount }}
            </span>
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'read']) }}"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 {{ $filter === 'read' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            <span>Read</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'read' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                {{ $readCount }}
            </span>
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            <span>Unread</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'unread' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                {{ $unreadCount }}
            </span>
        </a>
    </div>

    <!-- Notifications List -->
    @if ($notifications->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 mx-auto flex items-center justify-center mb-4 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">No notifications</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1.5">
            {{ $filter === 'unread' ? 'You have no unread notifications.' : 'You have no notifications right now.' }}
        </p>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/80">
        @foreach ($notifications as $notification)
        @php
            $type = $notification->data['type'] ?? '';
            $isUnread = $notification->read_at === null;
            $url = $notification->data['url'] ?? null;
            $message = $notification->data['message'] ?? 'Notification alert';
        @endphp
        <div class="p-4 sm:p-5 hover:bg-gray-50/60 dark:hover:bg-gray-750/50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-indigo-50/30 dark:bg-indigo-950/20' : '' }}" id="notification-row-{{ $notification->id }}">
            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                <!-- Icon badge according to notification type -->
                @if (str_contains($type, 'internal_ticket'))
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 border-2 border-indigo-300 dark:border-indigo-800 mt-0.5 shadow-xs text-base font-bold">
                    🛡️
                </div>
                @elseif ($type === 'task_escalated')
                <div class="w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0 border-2 border-red-300 dark:border-red-800 mt-0.5 shadow-xs text-base font-bold animate-pulse">
                    🚨
                </div>
                @elseif ($type === 'task_completed')
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 border border-emerald-200/50 dark:border-emerald-800/50 mt-0.5 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                @elseif ($type === 'task_assigned')
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-200/50 dark:border-indigo-800/50 mt-0.5 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                @elseif (str_contains($type, 'leave'))
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 border border-amber-200/50 dark:border-amber-800/50 mt-0.5 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @else
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 border border-blue-200/50 dark:border-blue-800/50 mt-0.5 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                @endif

                <div class="min-w-0 space-y-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                        {{ $message }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                        <span>&bull;</span>
                        <span>{{ $notification->created_at->format('M d, Y · h:i A') }}</span>
                        @if($notification->read_at)
                        <span>&bull;</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Read</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-center">
                @if ($url)
                <a href="{{ $url }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-semibold border border-indigo-200/60 dark:border-indigo-800/60 transition shadow-2xs">
                    <span>View</span>
                    <span>&rarr;</span>
                </a>
                @endif

                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                    onsubmit="return confirm('Remove this notification?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="p-1.5 rounded-xl text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition" title="Delete notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if ($notifications->hasPages())
    <div class="pt-4">
        {{ $notifications->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
