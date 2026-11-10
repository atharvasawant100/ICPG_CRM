@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads')

@section('content')
<div class="space-y-4">

    <!-- Top Bar with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Leads Pipeline</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage leads, track status progressions, and schedule meetings.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Leads</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'source', 'assigned_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            @can('leads.import')
            <a href="{{ route('leads.create.bulk') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <span>Bulk Import</span>
            </a>
            @endcan

            @can('leads.create')
            <a href="{{ route('leads.create.manual') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Lead</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status', 'source', 'assigned_to']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('leads.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone, Company..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="follow_up" {{ request('status') === 'follow_up' ? 'selected' : '' }}>Follow-Up</option>
                        <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>Won</option>
                        <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                @if (isset($assignableUsers) && $assignableUsers->isNotEmpty())
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Assigned Salesperson</label>
                    <select name="assigned_to" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Team Leads</option>
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}" {{ (string)request('assigned_to') === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Apply Filters
                    </button>
                    @if (request()->hasAny(['search', 'status', 'source', 'assigned_to']))
                    <a href="{{ route('leads.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Manager Bulk Actions Bar (Floating when items selected) -->
    @can('leads.reassign')
    <div id="bulk-bar" class="hidden bg-slate-900 text-white rounded-2xl p-3.5 mb-3 shadow-xl flex flex-wrap items-center justify-between gap-3 border border-slate-700">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
            <span class="text-xs font-bold"><span id="selected-count">0</span> leads selected</span>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Option 1: Assign to Single Employee -->
            <form id="bulk-reassign-form" action="{{ route('leads.bulk-reassign') }}" method="POST" class="flex items-center gap-1.5">
                @csrf
                <select name="assigned_to" required class="text-xs rounded-xl bg-slate-800 border-slate-700 text-white py-1.5 px-3 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Assign to single employee...</option>
                    @if (isset($assignableUsers))
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    @endif
                </select>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 rounded-xl text-xs font-semibold transition-colors">
                    Assign
                </button>
            </form>

            <span class="text-slate-600 font-bold hidden sm:inline">|</span>

            <!-- Option 2: Distribute Evenly (Round-Robin) -->
            <button type="button" onclick="openDistributeModal()" class="px-3.5 py-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Distribute Evenly (Round-Robin) ⚡</span>
            </button>
        </div>
    </div>
    @endcan

    <!-- Leads Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/70 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 dark:bg-slate-900/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700/70">
                    <tr>
                        @can('leads.reassign')
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        @endcan
                        <th class="py-3 px-5">Lead / Contact</th>
                        <th class="py-3 px-4">Company</th>
                        <th class="py-3 px-4">Assigned Salesperson</th>
                        <th class="py-3 px-4">Status & Pipeline</th>
                        <th class="py-3 px-4 text-center">Follow-ups</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($leads as $lead)
                        @php
                            $status = $lead->status;
                            $badgeStyle = match ($status) {
                                'new' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900',
                                'contacted' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-900',
                                'follow_up', 'interested' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900',
                                'qualified' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-900',
                                'won', 'converted' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900',
                                'lost' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900',
                                default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            @can('leads.reassign')
                            <td class="py-3.5 px-4">
                                <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" form="bulk-reassign-form"
                                    class="lead-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    onchange="updateBulkBar()">
                            </td>
                            @endcan

                            <!-- Lead Name & Info -->
                            <td class="py-3.5 px-5">
                                <a href="{{ route('leads.show', $lead) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 block">
                                    {{ $lead->name }}
                                </a>
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-2 mt-0.5">
                                    <span>{{ $lead->email }}</span>
                                    @if ($lead->phone)
                                        <span>&bull; {{ $lead->phone }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Company -->
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                                {{ $lead->company ?: '-' }}
                            </td>

                            <!-- Assigned Salesperson -->
                            <td class="py-3.5 px-4">
                                @if ($lead->assignedUser)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 font-bold flex items-center justify-center text-[10px]">
                                            {{ strtoupper(substr($lead->assignedUser->name, 0, 2)) }}
                                        </div>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $lead->assignedUser->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Quick Status Update -->
                            <td class="py-3.5 px-4">
                                <form action="{{ route('leads.status.update', $lead) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="text-xs font-bold rounded-lg border py-1 px-2.5 cursor-pointer transition-colors {{ $badgeStyle }} focus:ring-2 focus:ring-indigo-500">
                                        <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                                        <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="follow_up" {{ in_array($lead->status, ['follow_up', 'interested']) ? 'selected' : '' }}>Follow-Up</option>
                                        <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                        <option value="won" {{ $lead->status === 'won' ? 'selected' : '' }}>Won</option>
                                        <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                        <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                                    </select>
                                </form>
                            </td>

                            <!-- Follow-up indicator -->
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $pending = $lead->pendingFollowups;
                                    $hasOverdue = $pending->contains(fn($f) => $f->isOverdue());
                                @endphp
                                @if ($pending->count() > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $hasOverdue ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                        {{ $pending->count() }} {{ $hasOverdue ? 'Overdue' : 'Due' }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">None</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-colors">
                                        View
                                    </a>

                                    @can('leads.edit', $lead)
                                    <a href="{{ route('leads.edit', $lead) }}"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 transition-colors">
                                        Edit
                                    </a>
                                    @endcan

                                    @can('leads.convert', $lead)
                                    @if (! $lead->isConverted() && $lead->status !== 'lost')
                                    <form action="{{ route('leads.convert', $lead) }}" method="POST" class="inline" onsubmit="return confirm('Convert this lead to a customer?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 transition-colors">
                                            Convert
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-sm text-slate-400">
                                No leads found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>

@can('leads.reassign')
<!-- Auto-Distribute (Round-Robin) Modal -->
<div id="distribute-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDistributeModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all w-full max-w-lg border border-slate-200 dark:border-slate-700">
            <form id="bulk-distribute-form" action="{{ route('leads.bulk-distribute') }}" method="POST">
                @csrf
                <div id="distribute-hidden-lead-ids"></div>

                <!-- Modal Header -->
                <div class="p-5 pb-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Auto-Distribute Leads (Round-Robin)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Cycles selected leads evenly among checked team members.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDistributeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 space-y-4">
                    <!-- Summary Badge -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
                        <span class="text-xs font-semibold text-indigo-900 dark:text-indigo-200">Selected Leads:</span>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 px-2.5 py-0.5 rounded-full bg-white dark:bg-indigo-900/60 shadow-sm">
                            <span id="modal-selected-lead-count">0</span> Leads
                        </span>
                    </div>

                    <!-- Team Selection Section -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Select Team Members to Receive Leads</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="toggleAllDistributeEmp(true)" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Select All</button>
                                <span class="text-slate-300 dark:text-slate-600 text-xs">&bull;</span>
                                <button type="button" onclick="toggleAllDistributeEmp(false)" class="text-[11px] font-semibold text-slate-500 hover:underline">Deselect All</button>
                            </div>
                        </div>

                        <div class="max-h-60 overflow-y-auto space-y-1.5 rounded-xl border border-slate-200 dark:border-slate-700 p-2 bg-slate-50/50 dark:bg-slate-900/40">
                            @if (isset($assignableUsers) && $assignableUsers->isNotEmpty())
                                @foreach ($assignableUsers as $u)
                                <label class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-indigo-50/40 dark:hover:bg-indigo-950/20 border border-slate-100 dark:border-slate-700/60 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" checked
                                            class="distribute-emp-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            onchange="updateDistributePreview()">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 font-bold flex items-center justify-center text-[10px]">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $u->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700/60">
                                        {{ $u->department ?: 'Sales' }}
                                    </span>
                                </label>
                                @endforeach
                            @else
                                <div class="text-center py-6 text-xs text-slate-400">
                                    No team members found under your supervision.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Live Calculation Banner -->
                    <div id="distribute-preview-banner" class="p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 text-xs text-indigo-700 dark:text-indigo-300 flex items-center gap-2 border border-indigo-100 dark:border-indigo-900/50">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="distribute-preview-text">Each selected member will receive approx. 0 leads.</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeDistributeModal()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 transition-all flex items-center gap-1.5">
                        <span>Distribute Leads Evenly</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<script>
function toggleFilters() {
    const panel = document.getElementById('filters-panel');
    const arrow = document.getElementById('filter-arrow');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        arrow.classList.add('rotate-180');
    } else {
        panel.style.display = 'none';
        arrow.classList.remove('rotate-180');
    }
}

function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.lead-checkbox:checked');
    const bar = document.getElementById('bulk-bar');
    const countEl = document.getElementById('selected-count');
    if (!bar) return;

    if (checked.length > 0) {
        bar.classList.remove('hidden');
        countEl.textContent = checked.length;
    } else {
        bar.classList.add('hidden');
    }
}

document.getElementById('bulk-reassign-form')?.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.lead-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Please select at least one lead to reassign.');
    }
});

function openDistributeModal() {
    const checkedLeads = document.querySelectorAll('.lead-checkbox:checked');
    if (checkedLeads.length === 0) {
        alert('Please select at least one lead from the table to distribute.');
        return;
    }

    const countEl = document.getElementById('modal-selected-lead-count');
    if (countEl) countEl.textContent = checkedLeads.length;

    // Populate hidden lead_ids inputs inside modal form
    const container = document.getElementById('distribute-hidden-lead-ids');
    if (container) {
        container.innerHTML = '';
        checkedLeads.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lead_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
    }

    updateDistributePreview();
    document.getElementById('distribute-modal')?.classList.remove('hidden');
}

function closeDistributeModal() {
    document.getElementById('distribute-modal')?.classList.add('hidden');
}

function toggleAllDistributeEmp(state) {
    document.querySelectorAll('.distribute-emp-checkbox').forEach(cb => cb.checked = state);
    updateDistributePreview();
}

function updateDistributePreview() {
    const leadCount = document.querySelectorAll('.lead-checkbox:checked').length;
    const checkedUsers = document.querySelectorAll('.distribute-emp-checkbox:checked').length;
    const previewEl = document.getElementById('distribute-preview-text');
    if (!previewEl) return;

    if (checkedUsers === 0) {
        previewEl.textContent = 'Please select at least 1 employee to distribute leads.';
        previewEl.parentElement.className = 'p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-xs text-amber-700 dark:text-amber-300 flex items-center gap-2 border border-amber-200 dark:border-amber-900/50';
    } else {
        const perUser = Math.floor(leadCount / checkedUsers);
        const remainder = leadCount % checkedUsers;
        let msg = `${leadCount} lead(s) will be distributed among ${checkedUsers} employee(s) (approx. ${perUser} lead${perUser === 1 ? '' : 's'} each`;
        if (remainder > 0) {
            msg += `, with ${remainder} employee(s) getting 1 extra`;
        }
        msg += ').';
        previewEl.textContent = msg;
        previewEl.parentElement.className = 'p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 text-xs text-indigo-700 dark:text-indigo-300 flex items-center gap-2 border border-indigo-100 dark:border-indigo-900/50';
    }
}

document.getElementById('bulk-distribute-form')?.addEventListener('submit', function(e) {
    const checkedUsers = document.querySelectorAll('.distribute-emp-checkbox:checked');
    if (checkedUsers.length === 0) {
        e.preventDefault();
        alert('Please select at least one employee to receive leads.');
    }
});
</script>
@endsection