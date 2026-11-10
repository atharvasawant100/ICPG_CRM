@extends('layouts.app')

@section('title', 'Follow-Up & Meeting Calendar')
@section('page-title', 'Follow-Up & Meeting Calendar')

@section('content')
<div class="space-y-5">

    <!-- Top Toolbar & Filter Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/90 dark:border-slate-700/80 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                    Sales Calendar & Meetings
                </span>
                @if ($overdueCount > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                        {{ $overdueCount }} Overdue
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tracking-tight">
                Follow-Up & Meeting Calendar
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Track appointments, schedule virtual meetings with automatic link generation, and review reminders.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($canFilterEmployees && $teamMembers->isNotEmpty())
                <div class="w-full sm:w-auto">
                    <select id="employee-calendar-filter" onchange="filterCalendarByEmployee(this.value)"
                        class="w-full sm:w-56 text-xs font-medium rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-2 px-3 focus:ring-2 focus:ring-purple-500">
                        <option value="">All Team Calendars</option>
                        @foreach ($teamMembers as $m)
                            <option value="{{ $m->id }}" {{ request('employee_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="button" onclick="openScheduleModal()"
                class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-purple-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Schedule Meeting / Call</span>
            </button>
        </div>
    </div>

    <!-- Due Today Reminders Banner (Document 5.2: Reminder for follow-ups due today) -->
    @if ($dueToday->isNotEmpty())
    <div class="bg-gradient-to-r from-purple-900/10 via-indigo-900/10 to-blue-900/10 border border-purple-200 dark:border-purple-800/60 rounded-2xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-600 animate-ping"></span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Scheduled for Today ({{ $dueToday->count() }})</h3>
            </div>
            <span class="text-xs text-purple-700 dark:text-purple-300 font-semibold">{{ now()->format('l, F j') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($dueToday as $item)
                <div class="bg-white dark:bg-slate-800 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[150px]">
                            {{ $item->lead?->name ?? 'Lead' }}
                        </span>
                        <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400">
                            {{ $item->followup_date?->format('h:i A') }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 truncate">{{ $item->lead?->company ?: $item->lead?->phone }}</p>

                    <div class="flex items-center justify-between pt-1">
                        @if ($item->followup_type === 'meeting' && $item->meeting_link)
                            <a href="{{ $item->meeting_link }}" target="_blank"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-white bg-purple-600 hover:bg-purple-700 px-2.5 py-1 rounded-lg">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                Join Room
                            </a>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                Call
                            </span>
                        @endif

                        <button type="button" onclick="quickComplete({{ $item->lead_id }}, {{ $item->id }})"
                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                            Mark Done &check;
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Calendar Container & Legend -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/80 p-4 sm:p-6 min-h-[600px] flex flex-col">
        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600 dark:text-slate-300 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700/60">
            <span class="font-bold text-slate-700 dark:text-slate-200">Color Legend:</span>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-purple-500"></span> Meeting</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Follow-Up Call</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Completed</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Overdue / Missed</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-400"></span> Cancelled</div>
        </div>

        <div id="calendar" class="flex-1 w-full min-h-[550px]"></div>
    </div>

</div>

<!-- Schedule Meeting / Call Modal -->
<div id="schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-w-lg w-full p-6 text-left relative animate-in fade-in zoom-in-95 duration-150">
        <button type="button" onclick="closeScheduleModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Schedule Follow-Up / Meeting</h3>
        <p class="text-xs text-slate-500 mt-0.5">Select a lead and pick a time. Meeting links and lead invitation emails will be sent automatically.</p>

        <form id="schedule-form" method="POST" action="" class="mt-4 space-y-3.5 text-xs">
            @csrf

            <!-- Searchable Lead Selector -->
            <div class="relative" id="lead-select-wrapper">
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300">
                        Select Lead <span class="text-rose-500">*</span>
                    </label>
                    <span id="lead-available-count" class="text-[10px] text-slate-400 font-medium">
                        {{ $availableLeads->count() }} leads available
                    </span>
                </div>

                <!-- Hidden input holding selected lead_id for form submission -->
                <input type="hidden" name="lead_id" id="modal-lead-select" value="">

                <!-- Trigger box styled like the form inputs -->
                <div id="lead-combobox-trigger"
                    tabindex="0"
                    onclick="toggleLeadDropdown(event)"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleLeadDropdown();}"
                    class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 flex items-center justify-between cursor-pointer hover:border-purple-400 dark:hover:border-purple-500 transition focus:outline-none focus:ring-2 focus:ring-purple-500/30">
                    
                    <div class="flex items-center gap-2 overflow-hidden flex-1">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span id="lead-display-label" class="text-slate-400 dark:text-slate-500 truncate font-normal">
                            -- Choose a lead --
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <!-- Clear Button -->
                        <button type="button" id="lead-clear-btn" onclick="clearLeadSelection(event)"
                            title="Clear lead selection"
                            class="hidden p-0.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <!-- Chevron icon -->
                        <svg id="lead-chevron-icon" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Dropdown panel -->
                <div id="lead-dropdown-panel"
                    class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    
                    <!-- Search Input Header -->
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-700/80">
                        <div class="relative flex items-center">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="lead-search-input"
                                placeholder="Search by name, company, email, or phone..."
                                autocomplete="off"
                                oninput="filterLeadOptions(this.value)"
                                onkeydown="handleLeadSearchNav(event)"
                                class="w-full text-xs pl-8 pr-7 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">
                            
                            <button type="button" id="lead-search-reset-btn" onclick="resetLeadSearchInput()"
                                class="hidden absolute right-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between mt-1 px-0.5 text-[10px] text-slate-400">
                            <span id="lead-match-count">Showing {{ $availableLeads->count() }} leads</span>
                            <span>Type to search &bull; &uarr;&darr; Enter</span>
                        </div>
                    </div>

                    <!-- Scrollable Lead List -->
                    <div id="lead-options-list" class="max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 p-1">
                        @foreach ($availableLeads as $l)
                            <div class="lead-option-row rounded-lg px-3 py-2 cursor-pointer transition flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-purple-50 dark:hover:bg-purple-950/40 hover:text-purple-700 dark:hover:text-purple-300"
                                data-id="{{ $l->id }}"
                                data-name="{{ $l->name }}"
                                data-company="{{ $l->company ?? '' }}"
                                data-email="{{ $l->email ?? '' }}"
                                data-phone="{{ $l->phone ?? '' }}"
                                onclick="selectLeadOption('{{ $l->id }}', this.getAttribute('data-name'), this.getAttribute('data-company'), this.getAttribute('data-email'))">
                                <div class="min-w-0 pr-2">
                                    <div class="font-bold text-slate-900 dark:text-white truncate text-xs lead-title">
                                        {{ $l->name }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate flex items-center gap-1.5 mt-0.5">
                                        @if($l->company)
                                            <span class="inline-flex items-center font-medium text-purple-600 dark:text-purple-400">
                                                {{ $l->company }}
                                            </span>
                                        @endif
                                        @if($l->email)
                                            <span>{{ $l->email }}</span>
                                        @endif
                                        @if($l->phone && !$l->company && !$l->email)
                                            <span>{{ $l->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="lead-check shrink-0 hidden text-purple-600 dark:text-purple-400 pl-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- No Results Box -->
                    <div id="lead-empty-state" class="hidden p-5 text-center text-xs text-slate-400">
                        <svg class="w-7 h-7 mx-auto text-slate-300 dark:text-slate-600 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-semibold text-slate-600 dark:text-slate-300">No leads found</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">No match for "<span id="lead-searched-query"></span>"</p>
                    </div>
                </div>

                <!-- Validation message if user attempts to submit without picking a lead -->
                <p id="lead-validation-error" class="hidden text-[11px] text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Please choose a lead from the list to schedule.
                </p>
            </div>

            <!-- Follow-up Type -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Type *</label>
                    <select name="followup_type" id="modal-type-select" onchange="toggleMeetingFields(this.value)"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                        <option value="meeting">Online Meeting</option>
                        <option value="call">Phone Call</option>
                        <option value="email">Email</option>
                        <option value="visit">In-Person Visit</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Date & Time *</label>
                    <input type="datetime-local" name="followup_date" required min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
                </div>
            </div>

            <!-- Meeting Platform & Link -->
            <div id="meeting-fields-box" class="space-y-3 p-3 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/40">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-purple-900 dark:text-purple-300 mb-1">Meeting Platform</label>
                        <select name="meeting_platform"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                            <option value="jitsi">Jitsi Meet (Instant auto-link)</option>
                            <option value="google_meet">Google Meet</option>
                            <option value="zoom">Zoom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-purple-900 dark:text-purple-300 mb-1">Custom Link (Optional)</label>
                        <input type="url" name="meeting_link" placeholder="Leave empty to auto-generate"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="send_email_to_lead" value="1" checked id="send-email-check"
                        class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                    <label for="send-email-check" class="text-xs text-purple-900 dark:text-purple-300 font-medium">
                        Automatically email invitation with link to lead's address
                    </label>
                </div>
            </div>

            <!-- Agenda / Topic -->
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Agenda / Topic</label>
                <input type="text" name="agenda" placeholder="e.g. Website Discovery Call & Pricing Discussion"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5">
            </div>

            <!-- Description -->
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Internal Notes</label>
                <textarea name="description" rows="2" placeholder="Any internal notes for yourself..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold shadow-md shadow-purple-600/30">
                    Schedule & Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Event Detail Modal (Document 5.2: Click an event to see lead details, reschedule, cancel or mark as done) -->
<div id="event-detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-w-md w-full p-6 text-left relative animate-in fade-in zoom-in-95 duration-150">
        <button type="button" onclick="closeDetailModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="flex items-center gap-2 mb-2">
            <span id="detail-type-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                Meeting
            </span>
            <span id="detail-status-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-800">
                Pending
            </span>
        </div>

        <h3 id="detail-lead-name" class="text-xl font-bold text-slate-900 dark:text-white">Lead Name</h3>
        <p id="detail-company" class="text-xs text-slate-500 mb-4"></p>

        <div class="space-y-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 border border-slate-100 dark:border-slate-800 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Scheduled Time:</span>
                <span id="detail-time" class="font-bold text-slate-800 dark:text-slate-200"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Host / Employee:</span>
                <span id="detail-employee" class="font-semibold text-slate-700 dark:text-slate-300"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Contact Email:</span>
                <span id="detail-email" class="text-slate-700 dark:text-slate-300"></span>
            </div>
            <div id="detail-agenda-box" class="pt-2 border-t border-slate-200 dark:border-slate-700/60 hidden">
                <span class="text-slate-400 font-medium block mb-1">Agenda:</span>
                <p id="detail-agenda" class="text-slate-700 dark:text-slate-300 italic"></p>
            </div>
        </div>

        <!-- Join Meeting Button -->
        <div id="detail-link-box" class="mt-4 hidden">
            <a id="detail-meeting-btn" href="#" target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-bold text-xs shadow-md shadow-purple-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Join Video Meeting
            </a>
        </div>

        <!-- Reschedule Drawer (Toggled) -->
        <div id="reschedule-drawer" class="hidden mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
            <form id="reschedule-form" onsubmit="submitReschedule(event)" class="space-y-2.5 text-xs">
                @csrf
                <label class="block font-semibold text-slate-700 dark:text-slate-300">Pick New Date & Time</label>
                <input type="datetime-local" id="reschedule-datetime" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 p-2">
                <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-xl font-bold">
                    Confirm & Send Reschedule Email
                </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleRescheduleDrawer()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg font-semibold">
                    Reschedule
                </button>
                <button type="button" onclick="cancelCurrentFollowup()" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="completeCurrentFollowup()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">
                    Mark Done &check;
                </button>
                <a id="detail-lead-link" href="#" class="px-3 py-1.5 bg-slate-900 text-white dark:bg-slate-700 rounded-lg font-semibold">
                    Open Lead
                </a>
            </div>
        </div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
let calendarInstance;
let currentFollowupId = null;
let currentLeadId = null;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const isMobile = window.innerWidth < 640;

    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: isMobile ? 'listWeek' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            const filterEl = document.getElementById('employee-calendar-filter');
            const empId = filterEl ? filterEl.value : '';
            const url = new URL("{{ route('leads.calendar') }}", window.location.origin);
            url.searchParams.set('start', fetchInfo.startStr);
            url.searchParams.set('end', fetchInfo.endStr);
            if (empId) url.searchParams.set('employee_id', empId);

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(err => failureCallback(err));
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            openDetailModal(info.event);
        },
        eventDidMount: function (info) {
            if (info.event.extendedProps.status === 'completed') {
                info.el.style.opacity = '0.75';
            }
        }
    });

    calendarInstance.render();
});

function filterCalendarByEmployee(employeeId) {
    if (calendarInstance) {
        calendarInstance.refetchEvents();
    }
}

function openScheduleModal(preselectedLeadId = null) {
    closeLeadDropdown();
    const errorMsg = document.getElementById('lead-validation-error');
    if (errorMsg) errorMsg.classList.add('hidden');
    const trigger = document.getElementById('lead-combobox-trigger');
    if (trigger) trigger.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/30');

    if (preselectedLeadId) {
        const row = document.querySelector(`.lead-option-row[data-id="${preselectedLeadId}"]`);
        if (row) {
            selectLeadOption(
                preselectedLeadId,
                row.getAttribute('data-name'),
                row.getAttribute('data-company'),
                row.getAttribute('data-email')
            );
        }
    }

    document.getElementById('schedule-modal').classList.remove('hidden');
}

function closeScheduleModal() {
    closeLeadDropdown();
    document.getElementById('schedule-modal').classList.add('hidden');
}

function updateFormAction(leadId) {
    const form = document.getElementById('schedule-form');
    if (form) {
        form.action = leadId ? `/leads/${leadId}/followups` : '';
    }
}

// ----------------- Searchable Lead Selector Logic -----------------
let selectedLeadId = null;
let activeHighlightIndex = -1;
let leadSearchTimeout = null;

function toggleLeadDropdown(e) {
    if (e) e.stopPropagation();
    const panel = document.getElementById('lead-dropdown-panel');
    const chevron = document.getElementById('lead-chevron-icon');
    if (!panel) return;

    const isHidden = panel.classList.contains('hidden');
    if (isHidden) {
        panel.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
        const searchInput = document.getElementById('lead-search-input');
        if (searchInput) {
            setTimeout(() => {
                searchInput.focus();
                searchInput.select();
            }, 50);
        }
        activeHighlightIndex = -1;
    } else {
        closeLeadDropdown();
    }
}

function closeLeadDropdown() {
    const panel = document.getElementById('lead-dropdown-panel');
    const chevron = document.getElementById('lead-chevron-icon');
    if (panel) panel.classList.add('hidden');
    if (chevron) chevron.classList.remove('rotate-180');
    activeHighlightIndex = -1;
}

function filterLeadOptions(query) {
    const q = (query || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.lead-option-row');
    const resetBtn = document.getElementById('lead-search-reset-btn');
    const emptyState = document.getElementById('lead-empty-state');
    const matchCountEl = document.getElementById('lead-match-count');
    const querySpan = document.getElementById('lead-searched-query');

    if (resetBtn) {
        resetBtn.classList.toggle('hidden', q === '');
    }

    let visibleCount = 0;
    rows.forEach(row => {
        const name = (row.getAttribute('data-name') || '').toLowerCase();
        const company = (row.getAttribute('data-company') || '').toLowerCase();
        const email = (row.getAttribute('data-email') || '').toLowerCase();
        const phone = (row.getAttribute('data-phone') || '').toLowerCase();

        const match = q === '' || name.includes(q) || company.includes(q) || email.includes(q) || phone.includes(q);
        if (match) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    if (matchCountEl) {
        matchCountEl.textContent = q === '' 
            ? `Showing all ${visibleCount} leads` 
            : `${visibleCount} lead${visibleCount === 1 ? '' : 's'} found`;
    }

    if (visibleCount === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        if (querySpan) querySpan.textContent = query;

        // Remote search if not in preloaded batch
        if (q.length >= 2) {
            clearTimeout(leadSearchTimeout);
            leadSearchTimeout = setTimeout(() => {
                searchLeadsRemote(q);
            }, 300);
        }
    } else {
        if (emptyState) emptyState.classList.add('hidden');
    }
    activeHighlightIndex = -1;
}

function searchLeadsRemote(query) {
    const url = new URL("{{ route('leads.calendar') }}", window.location.origin);
    url.searchParams.set('search_lead', query);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(leads => {
            if (!Array.isArray(leads) || leads.length === 0) return;
            const list = document.getElementById('lead-options-list');
            leads.forEach(l => {
                if (!document.querySelector(`.lead-option-row[data-id="${l.id}"]`)) {
                    const row = document.createElement('div');
                    row.className = 'lead-option-row rounded-lg px-3 py-2 cursor-pointer transition flex items-center justify-between text-slate-700 dark:text-slate-200 hover:bg-purple-50 dark:hover:bg-purple-950/40 hover:text-purple-700 dark:hover:text-purple-300';
                    row.setAttribute('data-id', l.id);
                    row.setAttribute('data-name', l.name || '');
                    row.setAttribute('data-company', l.company || '');
                    row.setAttribute('data-email', l.email || '');
                    row.setAttribute('data-phone', l.phone || '');
                    row.onclick = function() {
                        selectLeadOption(l.id, this.getAttribute('data-name'), this.getAttribute('data-company'), this.getAttribute('data-email'));
                    };

                    const comp = l.company ? `<span class="inline-flex items-center font-medium text-purple-600 dark:text-purple-400">${escapeLeadHtml(l.company)}</span>` : '';
                    const email = l.email ? `<span>${escapeLeadHtml(l.email)}</span>` : '';

                    row.innerHTML = `
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-slate-900 dark:text-white truncate text-xs lead-title">${escapeLeadHtml(l.name)}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate flex items-center gap-1.5 mt-0.5">
                                ${comp} ${email}
                            </div>
                        </div>
                        <span class="lead-check shrink-0 hidden text-purple-600 dark:text-purple-400 pl-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    `;
                    list.appendChild(row);
                }
            });
            filterLeadOptions(query);
        })
        .catch(() => {});
}

function escapeLeadHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function resetLeadSearchInput() {
    const input = document.getElementById('lead-search-input');
    if (input) {
        input.value = '';
        input.focus();
    }
    filterLeadOptions('');
}

function selectLeadOption(id, name, company, email) {
    selectedLeadId = id;
    const hiddenInput = document.getElementById('modal-lead-select');
    if (hiddenInput) hiddenInput.value = id;

    updateFormAction(id);

    const label = document.getElementById('lead-display-label');
    const clearBtn = document.getElementById('lead-clear-btn');
    const errorMsg = document.getElementById('lead-validation-error');
    const trigger = document.getElementById('lead-combobox-trigger');

    if (errorMsg) errorMsg.classList.add('hidden');
    if (trigger) trigger.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/30');

    const details = [company, email].filter(Boolean).join(' • ');
    if (label) {
        label.innerHTML = `
            <span class="font-bold text-slate-900 dark:text-white">${escapeLeadHtml(name)}</span>
            ${details ? `<span class="text-slate-500 dark:text-slate-400 text-[11px] ml-1.5 font-normal">(${escapeLeadHtml(details)})</span>` : ''}
        `;
    }

    if (clearBtn) clearBtn.classList.remove('hidden');

    document.querySelectorAll('.lead-option-row').forEach(row => {
        const isSelected = row.getAttribute('data-id') == id;
        const check = row.querySelector('.lead-check');
        if (check) check.classList.toggle('hidden', !isSelected);
        row.classList.toggle('bg-purple-50/70', isSelected);
        row.classList.toggle('dark:bg-purple-950/40', isSelected);
    });

    closeLeadDropdown();
}

function clearLeadSelection(e) {
    if (e) e.stopPropagation();
    selectedLeadId = null;
    const hiddenInput = document.getElementById('modal-lead-select');
    if (hiddenInput) hiddenInput.value = '';

    updateFormAction('');

    const label = document.getElementById('lead-display-label');
    if (label) {
        label.innerHTML = '<span class="text-slate-400 dark:text-slate-500 font-normal">-- Choose a lead --</span>';
    }

    const clearBtn = document.getElementById('lead-clear-btn');
    if (clearBtn) clearBtn.classList.add('hidden');

    document.querySelectorAll('.lead-option-row').forEach(row => {
        const check = row.querySelector('.lead-check');
        if (check) check.classList.add('hidden');
        row.classList.remove('bg-purple-50/70', 'dark:bg-purple-950/40');
    });

    resetLeadSearchInput();
}

function handleLeadSearchNav(e) {
    const visibleRows = Array.from(document.querySelectorAll('.lead-option-row:not(.hidden)'));
    if (visibleRows.length === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeHighlightIndex = (activeHighlightIndex + 1) % visibleRows.length;
        updateHighlightedRow(visibleRows);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeHighlightIndex = (activeHighlightIndex - 1 + visibleRows.length) % visibleRows.length;
        updateHighlightedRow(visibleRows);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeHighlightIndex >= 0 && activeHighlightIndex < visibleRows.length) {
            visibleRows[activeHighlightIndex].click();
        } else if (visibleRows.length === 1) {
            visibleRows[0].click();
        }
    } else if (e.key === 'Escape') {
        e.preventDefault();
        closeLeadDropdown();
    }
}

function updateHighlightedRow(visibleRows) {
    visibleRows.forEach((row, idx) => {
        if (idx === activeHighlightIndex) {
            row.classList.add('bg-purple-100', 'dark:bg-purple-900/60');
            row.scrollIntoView({ block: 'nearest' });
        } else {
            row.classList.remove('bg-purple-100', 'dark:bg-purple-900/60');
        }
    });
}

// Global click outside listener for lead dropdown
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('lead-select-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        closeLeadDropdown();
    }
});

// Intercept form submit to ensure a lead is selected
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('schedule-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const leadInput = document.getElementById('modal-lead-select');
            if (!leadInput || !leadInput.value) {
                e.preventDefault();
                const err = document.getElementById('lead-validation-error');
                if (err) err.classList.remove('hidden');
                const trigger = document.getElementById('lead-combobox-trigger');
                if (trigger) {
                    trigger.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/30');
                }
                toggleLeadDropdown();
                return false;
            }
        });
    }
});

function toggleMeetingFields(type) {
    const box = document.getElementById('meeting-fields-box');
    if (type === 'meeting') {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}

function openDetailModal(event) {
    const props = event.extendedProps;
    currentFollowupId = props.followupId;
    currentLeadId = props.leadId;

    document.getElementById('detail-lead-name').textContent = props.leadName;
    document.getElementById('detail-company').textContent = props.leadCompany || props.leadPhone;
    document.getElementById('detail-time').textContent = props.dateFormatted;
    document.getElementById('detail-employee').textContent = props.employeeName;
    document.getElementById('detail-email').textContent = props.leadEmail || 'No email';

    document.getElementById('detail-type-badge').textContent = (props.type || 'Call').toUpperCase();
    document.getElementById('detail-status-badge').textContent = (props.status || 'Pending').toUpperCase();

    const linkBox = document.getElementById('detail-link-box');
    if (props.meetingLink) {
        linkBox.classList.remove('hidden');
        document.getElementById('detail-meeting-btn').href = props.meetingLink;
    } else {
        linkBox.classList.add('hidden');
    }

    const agendaBox = document.getElementById('detail-agenda-box');
    if (props.agenda) {
        agendaBox.classList.remove('hidden');
        document.getElementById('detail-agenda').textContent = props.agenda;
    } else {
        agendaBox.classList.add('hidden');
    }

    document.getElementById('detail-lead-link').href = `/leads/${props.leadId}`;
    document.getElementById('reschedule-drawer').classList.add('hidden');

    document.getElementById('event-detail-modal').classList.remove('hidden');
}

function closeDetailModal() {
    document.getElementById('event-detail-modal').classList.add('hidden');
}

function toggleRescheduleDrawer() {
    document.getElementById('reschedule-drawer').classList.toggle('hidden');
}

function submitReschedule(e) {
    e.preventDefault();
    if (!currentFollowupId) return;

    const newDate = document.getElementById('reschedule-datetime').value;
    if (!newDate) return;

    fetch(`/followups/${currentFollowupId}/reschedule`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ followup_date: newDate })
    })
    .then(r => r.json())
    .then(data => {
        alert('Meeting rescheduled. Email notification sent to lead.');
        closeDetailModal();
        if (calendarInstance) calendarInstance.refetchEvents();
    })
    .catch(err => alert('Failed to reschedule.'));
}

function cancelCurrentFollowup() {
    if (!currentFollowupId || !confirm('Cancel this meeting/follow-up? An email notice will be sent to the lead.')) return;

    const reason = prompt('Cancellation reason (optional):');

    fetch(`/followups/${currentFollowupId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        closeDetailModal();
        if (calendarInstance) calendarInstance.refetchEvents();
    });
}

function completeCurrentFollowup() {
    if (!currentFollowupId || !currentLeadId) return;
    const notes = prompt('Completion notes (optional):');

    fetch(`/leads/${currentLeadId}/followups/${currentFollowupId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(r => r.json())
    .then(() => {
        closeDetailModal();
        if (calendarInstance) calendarInstance.refetchEvents();
    });
}

function quickComplete(leadId, followupId) {
    if (!confirm('Mark this follow-up as completed?')) return;
    fetch(`/leads/${leadId}/followups/${followupId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(() => location.reload());
}
</script>
@endsection