@extends('layouts.app')

@section('title', 'Direct Admin Tickets - Confidential Grievance & Escalation')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-lg shadow-xs">
                    🛡️
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ $isAdmin ? 'Confidential Employee Tickets & Grievances' : 'Direct Admin Support & Grievance Tickets' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $isAdmin ? 'Review and resolve confidential tickets submitted directly by employees bypassing managers' : 'Private channel directly to Company Administration. Bypasses department managers.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('tickets.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-sm hover:shadow transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Raise Confidential Ticket</span>
            </a>
        </div>
    </div>

    <!-- Confidentiality Reassurance Banner (Visible to non-admins) -->
    @if (! $isAdmin)
    <div class="bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-purple-500/10 dark:from-blue-950/40 dark:via-indigo-950/40 dark:to-purple-950/40 rounded-2xl p-4 sm:p-5 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs">
        <div class="flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-base shadow-xs">
                🔒
            </div>
            <div class="text-xs sm:text-sm">
                <h3 class="font-bold text-indigo-950 dark:text-indigo-200">
                    100% Confidentiality & Direct Escalation Guarantee
                </h3>
                <p class="text-indigo-800/90 dark:text-indigo-300/90 mt-1 leading-relaxed">
                    If you are facing missing task requirements from your manager, unfair blame, department bottlenecks, or internal workplace concerns that you cannot discuss with your manager, tickets submitted here go <strong>directly to Company Administration (Admin)</strong>. Your department manager <strong>cannot view, track, or intercept</strong> these tickets.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $isAdmin ? 'All Tickets' : 'My Tickets' }}</p>
            <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $counters['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-amber-200/80 dark:border-amber-800/60 shadow-xs">
            <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">Open & Pending</p>
            <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $counters['open'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs">
            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Under Admin Review</p>
            <p class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $counters['in_review'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs">
            <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Resolved</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $counters['resolved'] }}</p>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-xs">
        <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket #, subject, or keywords..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="all">All Statuses</option>
                    @foreach (\App\Models\InternalTicket::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Category</label>
                <select name="category" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">All Categories</option>
                    @foreach (\App\Models\InternalTicket::CATEGORIES as $val => $lbl)
                        <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'category', 'priority']))
                <a href="{{ route('tickets.index') }}"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tickets Table Register -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-900/50 text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Ticket #</th>
                        <th class="px-4 py-3.5">Subject & Details</th>
                        <th class="px-4 py-3.5">Category</th>
                        @if ($isAdmin)
                        <th class="px-4 py-3.5">Submitted By</th>
                        @endif
                        <th class="px-4 py-3.5">Priority</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Submitted</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                    @forelse ($tickets as $ticket)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750/50 transition-colors">
                        <!-- Ticket # -->
                        <td class="px-4 py-3.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                            <a href="{{ route('tickets.show', $ticket) }}" class="hover:underline">
                                {{ $ticket->ticket_number }}
                            </a>
                        </td>

                        <!-- Subject -->
                        <td class="px-4 py-3.5 min-w-[200px]">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 block line-clamp-1">
                                {{ $ticket->subject }}
                            </a>
                            @if ($ticket->task)
                            <div class="flex items-center gap-1.5 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                <span class="text-indigo-500 font-medium">Linked Task:</span>
                                <span class="font-mono text-gray-700 dark:text-gray-300 truncate max-w-[150px]">{{ $ticket->task->formatted_id }} - {{ $ticket->task->title }}</span>
                            </div>
                            @endif
                        </td>

                        <!-- Category -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold {{ $ticket->categoryBadgeClasses() }}">
                                {{ $ticket->categoryLabel() }}
                            </span>
                        </td>

                        @if ($isAdmin)
                        <!-- Submitter Info (Admin View) -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center">
                                    {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $ticket->user?->name ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-gray-400 capitalize">{{ $ticket->user?->department ?? 'General' }}</p>
                                </div>
                            </div>
                        </td>
                        @endif

                        <!-- Priority -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] {{ $ticket->priorityBadgeClasses() }}">
                                {{ $ticket->priorityLabel() }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $ticket->statusBadgeClasses() }}">
                                {{ $ticket->statusLabel() }}
                            </span>
                        </td>

                        <!-- Date -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-500 dark:text-gray-400 text-[11px]">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>

                        <!-- Action -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right">
                            <a href="{{ route('tickets.show', $ticket) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-semibold text-xs transition">
                                <span>View</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 8 : 7 }}" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto text-xl mb-3">
                                🛡️
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No tickets found</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                                {{ $isAdmin ? 'No confidential grievance tickets have been submitted.' : 'You have not submitted any tickets yet. If you are experiencing blockers or workplace issues, raise a ticket directly to Administration.' }}
                            </p>
                            @if (! $isAdmin)
                            <div class="mt-4">
                                <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                                    <span>+ Raise Confidential Ticket</span>
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tickets->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

