@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - ' . $ticket->subject)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('tickets.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Tickets</span>
            </a>
            <span>/</span>
            <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $ticket->ticket_number }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $ticket->statusBadgeClasses() }}">
                Status: {{ $ticket->statusLabel() }}
            </span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs {{ $ticket->priorityBadgeClasses() }}">
                Priority: {{ $ticket->priorityLabel() }}
            </span>
        </div>
    </div>

    <!-- Ticket Header Title Banner -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $ticket->categoryBadgeClasses() }}">
                        {{ $ticket->categoryLabel() }}
                    </span>
                    <span class="text-xs text-gray-400">
                        Submitted {{ $ticket->created_at->diffForHumans() }} ({{ $ticket->created_at->format('M d, Y · h:i A') }})
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-snug">
                    {{ $ticket->subject }}
                </h1>
            </div>

            <!-- Confidentiality Stamp -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold whitespace-nowrap self-start">
                <span>🛡️ Direct Admin Channel</span>
            </div>
        </div>

        <!-- Submitter & Reporting Context Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/80 text-xs">
            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-gray-400 block mb-0.5">Submitted By</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $ticket->user?->name }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $ticket->user?->email }}</span>
            </div>
            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-gray-400 block mb-0.5">Department & Role</span>
                <span class="font-bold text-gray-900 dark:text-white capitalize">{{ $ticket->user?->department ?? 'General' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $ticket->user?->designation ?? 'Staff Member' }}</span>
            </div>
            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-gray-400 block mb-0.5">Reporting Manager (Bypassed)</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $ticket->user?->manager?->name ?? 'No Manager Assigned' }}</span>
                <span class="text-[11px] text-rose-500 dark:text-rose-400 font-semibold block">Has Zero Access to this Ticket</span>
            </div>
        </div>
    </div>

    <!-- Linked Task Card (If ticket was raised for a specific task) -->
    @if ($ticket->task)
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/40 dark:to-purple-950/40 rounded-3xl p-5 border border-indigo-200 dark:border-indigo-800/80 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                        Linked Task Under Contention
                    </span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-200 dark:border-indigo-700">
                        {{ $ticket->task->formatted_id }}
                    </span>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    {{ $ticket->task->title }}
                </h3>
                <div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400 flex-wrap pt-0.5">
                    <span>Task Priority: <strong class="capitalize {{ $ticket->task->priority === 'urgent' ? 'text-red-600 font-black' : '' }}">{{ $ticket->task->priority }}</strong></span>
                    <span>•</span>
                    <span>Status: <strong class="capitalize">{{ str_replace('_', ' ', $ticket->task->status) }}</strong></span>
                    <span>•</span>
                    <span>Assignee: <strong>{{ $ticket->task->assignee?->name ?? 'Unassigned' }}</strong></span>
                </div>
            </div>
            <a href="{{ route('tasks.show', $ticket->task) }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700 text-xs font-bold shadow-xs hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
                <span>Inspect Task</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>
    @endif

    <!-- Main Issue Description -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Issue Description & Facts
        </h3>
        <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line bg-gray-50/60 dark:bg-gray-900/40 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800">
            {{ $ticket->description }}
        </div>

        <!-- Attachments List -->
        @if ($ticket->attachments->isNotEmpty())
        <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2.5">
                Supporting Attachments & Proof ({{ $ticket->attachments->count() }})
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach ($ticket->attachments as $att)
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $att->file_name }}</span>
                        <span class="text-[10px] text-gray-400">({{ $att->formattedSize() }})</span>
                    </div>
                    <a href="{{ route('tickets.attachments.download', [$ticket, $att]) }}"
                        class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 font-semibold transition"
                        title="Download Attachment">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Official Admin Resolution Box -->
    @if ($ticket->isResolved() && $ticket->admin_response)
    <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/15 to-green-500/10 dark:from-emerald-950/50 dark:via-teal-950/40 dark:to-green-950/30 rounded-3xl p-5 sm:p-6 border-2 border-emerald-400 dark:border-emerald-700 shadow-sm space-y-2">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
            <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-200 uppercase tracking-wide">
                Official Administration Resolution and Decision
            </h3>
            @if ($ticket->resolved_at)
            <span class="text-xs text-emerald-700 dark:text-emerald-300">
                • {{ $ticket->resolved_at->format('M d, Y · h:i A') }} ({{ $ticket->resolved_at->diffForHumans() }})
            </span>
            @endif
        </div>
        <div class="bg-white/80 dark:bg-gray-900/80 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800 text-sm text-gray-900 dark:text-gray-100 font-medium leading-relaxed whitespace-pre-line">
            {{ $ticket->admin_response }}
        </div>
    </div>
    @endif

    <!-- Admin Management Card (Admin Only) -->
    @can('update', $ticket)
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 sm:p-6 border-2 border-indigo-200 dark:border-indigo-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-base">⚙️</span>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                    Admin Action & Resolution Panel
                </h3>
            </div>
            <span class="text-[11px] text-gray-400">Visible strictly to Administrators</span>
        </div>

        <form method="POST" action="{{ route('tickets.resolve', $ticket) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Update Ticket Status
                    </label>
                    <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                        @foreach (\App\Models\InternalTicket::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" {{ $ticket->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Private Admin Investigation Notes (Internal only)
                    </label>
                    <input type="text" name="admin_notes" value="{{ old('admin_notes', $ticket->admin_notes) }}"
                        placeholder="e.g. Spoke to CEO; Manager confirmed delay in wireframes. Employee is exonerated."
                        class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Official Admin Resolution Directive (Visible to Employee)
                </label>
                <textarea name="admin_response" rows="3"
                    placeholder="Provide official feedback, clarification, or resolution to the employee..."
                    class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">{{ old('admin_response', $ticket->admin_response) }}</textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer">
                    Save Resolution & Update Ticket
                </button>
            </div>
        </form>
    </div>
    @endcan

    <!-- Two-Way Discussion Thread -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Confidential Discussion & Updates</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 font-semibold">
                    {{ $ticket->messages->count() }}
                </span>
            </h3>
            <span class="text-xs text-gray-400">Direct between Employee and Admin</span>
        </div>

        <!-- Messages Stream -->
        <div class="space-y-3.5">
            @forelse ($ticket->messages as $msg)
                @if ($msg->is_admin_note && ! auth()->user()->isAdmin())
                    @continue
                @endif
                <div class="p-4 rounded-2xl {{ $msg->is_admin_note ? 'bg-amber-50/80 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-800' : ((int) $msg->user_id === (int) auth()->id() ? 'bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/60 ml-4 sm:ml-8' : 'bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/80 mr-4 sm:mr-8') }} space-y-1.5 text-xs sm:text-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full {{ $msg->user?->isAdmin() ? 'bg-purple-600 text-white' : 'bg-indigo-600 text-white' }} font-bold text-[10px] flex items-center justify-center">
                                {{ strtoupper(substr($msg->user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $msg->user?->name }}</span>
                            @if ($msg->user?->isAdmin())
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">Admin</span>
                            @endif
                            @if ($msg->is_admin_note)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Internal Note</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line pl-8">
                        {{ $msg->message }}
                    </p>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic py-2 text-center">No discussion messages yet. You can post updates or ask questions below.</p>
            @endforelse
        </div>

        <!-- Reply Form -->
        <form method="POST" action="{{ route('tickets.reply', $ticket) }}" class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-700">
            @csrf
            <div>
                <textarea name="message" rows="3" required placeholder="Type your message, follow-up information, or question..."
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-between flex-wrap gap-2">
                @if (auth()->user()->isAdmin())
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                    <input type="checkbox" name="is_admin_note" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Save as private Admin note (invisible to employee)</span>
                </label>
                @else
                <div></div>
                @endif

                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer">
                    Post Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
