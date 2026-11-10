@extends('layouts.app')

@section('title', 'Social Media Content Calendar')
@section('page-title', 'Content Calendar')

@section('content')
<div class="space-y-4" x-data="contentCalendar()">

    <!-- Top Action Bar & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Content Calendar</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Schedule, organize, and track social media publishing across all client brands.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700">
                <button type="button" @click="setViewMode('month')"
                    :class="viewMode === 'month' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Month</button>
                <button type="button" @click="setViewMode('week')"
                    :class="viewMode === 'week' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Week</button>
                <button type="button" @click="setViewMode('day')"
                    :class="viewMode === 'day' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Day</button>
            </div>

            <!-- Date Navigation -->
            <div class="flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1 shadow-sm">
                <button type="button" @click="navigate(-1)" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button type="button" @click="goToToday()" class="px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    Today
                </button>
                <button type="button" @click="navigate(1)" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>

            <!-- Toggle Filter Button -->
            <button type="button" @click="showFilters = !showFilters"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filters</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div x-show="showFilters" x-transition class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Brand -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand</label>
                <select x-model="filters.brand_id" @change="fetchEvents()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Client -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Client</label>
                <select x-model="filters.client_id" @change="fetchEvents()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Platform -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Platform</label>
                <select x-model="filters.platform" @change="fetchEvents()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Platforms</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                <select x-model="filters.status" @change="fetchEvents()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Assignee -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Assignee</label>
                <select x-model="filters.assigned_to" @change="fetchEvents()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Employees</option>
                    @foreach(($employees ?? $assignees ?? []) as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Current Display Header -->
    <div class="flex items-center justify-between px-1">
        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100" x-text="calendarTitle"></h2>
        <div class="flex items-center gap-3 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Planned</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Review</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Scheduled</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Published</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Missed/Failed</span>
        </div>
    </div>

    <!-- Calendar Viewport -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden min-h-[580px]">
        <!-- Loading Indicator -->
        <div x-show="loading" x-cloak class="p-12 text-center text-slate-400">
            <svg class="animate-spin w-8 h-8 mx-auto text-blue-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-xs">Loading content calendar...</span>
        </div>

        <!-- Month Grid -->
        <div x-show="!loading && viewMode === 'month'" x-cloak class="divide-y divide-slate-200 dark:divide-slate-700">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 bg-slate-50 dark:bg-slate-900/50 text-center py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <!-- Calendar Cells -->
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-700/60">
                <template x-for="(day, dIdx) in monthDays" :key="dIdx">
                    <div class="min-h-[110px] p-1.5 transition-colors"
                        :class="day.isCurrentMonth ? (day.isToday ? 'bg-blue-50/30 dark:bg-blue-950/20' : 'bg-white dark:bg-slate-800') : 'bg-slate-50/50 dark:bg-slate-900/30 opacity-40'"
                        @dragover.prevent
                        @drop="handleDrop(day.dateStr)">
                        
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full"
                                :class="day.isToday ? 'bg-blue-600 text-white' : 'text-slate-700 dark:text-slate-300'"
                                x-text="day.dayNumber"></span>
                            
                            <a :href="'{{ route('content.posts.create') }}?date=' + day.dateStr"
                                class="text-slate-300 hover:text-blue-500 opacity-0 hover:opacity-100 p-0.5" title="Add post on this date">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </a>
                        </div>

                        <!-- Posts on this day -->
                        <div class="space-y-1 overflow-y-auto max-h-[85px]">
                            <template x-for="event in day.events" :key="event.id">
                                <div class="px-2 py-1 rounded-lg text-[11px] font-medium border cursor-pointer hover:shadow-md transition-all flex flex-col gap-0.5"
                                    :style="'border-left-width: 3px; border-left-color: ' + (event.brandColor || '#3b82f6') + '; background-color: ' + event.backgroundColor + '; color: ' + event.textColor"
                                    draggable="true"
                                    @dragstart="handleDragStart(event)"
                                    @click="openPostDetails(event)">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold truncate" x-text="event.title"></span>
                                        <span class="text-[9px] opacity-80" x-text="event.scheduled_time || ''"></span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[9px] opacity-90 truncate">
                                        <span class="capitalize" x-text="event.brand_name"></span>
                                        <span>&bull;</span>
                                        <span class="uppercase font-semibold" x-text="event.platforms.join(', ')"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Week Grid -->
        <div x-show="!loading && viewMode === 'week'" x-cloak class="divide-y divide-slate-200 dark:divide-slate-700">
            <div class="grid grid-cols-7 divide-x divide-slate-100 dark:divide-slate-700">
                <template x-for="day in weekDays" :key="day.dateStr">
                    <div class="min-h-[480px] p-2"
                        :class="day.isToday ? 'bg-blue-50/20 dark:bg-blue-950/20' : ''"
                        @dragover.prevent
                        @drop="handleDrop(day.dateStr)">
                        <div class="text-center pb-2 border-b border-slate-100 dark:border-slate-700 mb-2">
                            <div class="text-xs text-slate-400 font-medium" x-text="day.dayName"></div>
                            <div class="text-sm font-bold" :class="day.isToday ? 'text-blue-600' : 'text-slate-800 dark:text-slate-200'" x-text="day.formattedDate"></div>
                        </div>

                        <div class="space-y-2">
                            <template x-for="event in day.events" :key="event.id">
                                <div class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer hover:border-blue-500 transition-all space-y-1.5"
                                    :style="'border-left-width: 4px; border-left-color: ' + (event.brandColor || '#3b82f6')"
                                    draggable="true"
                                    @dragstart="handleDragStart(event)"
                                    @click="openPostDetails(event)">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold uppercase" :class="event.statusBadgeClass" x-text="event.status"></span>
                                        <span class="text-[10px] text-slate-500" x-text="event.scheduled_time"></span>
                                    </div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="event.title"></div>
                                    <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                        <span x-text="event.brand_name"></span>
                                        <span class="font-medium" x-text="event.assignee_name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Day View -->
        <div x-show="!loading && viewMode === 'day'" x-cloak class="p-6">
            <div class="max-w-2xl mx-auto space-y-3">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="currentDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></h3>
                </div>

                <template x-for="event in dayEvents" :key="event.id">
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all flex items-center justify-between gap-4 cursor-pointer"
                        @click="openPostDetails(event)">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-12 rounded-full" :style="'background-color: ' + (event.brandColor || '#3b82f6')"></div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="event.title"></div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="event.brand_name"></span>
                                    <span>&bull;</span>
                                    <span x-text="event.scheduled_time || 'Anytime'"></span>
                                    <span>&bull;</span>
                                    <span>Assigned to <span x-text="event.assignee_name"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full uppercase" :class="event.statusBadgeClass" x-text="event.status"></span>
                            <a :href="'/content/posts/' + event.id" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">View</a>
                        </div>
                    </div>
                </template>

                <div x-show="dayEvents.length === 0" class="text-center py-12 text-slate-400 text-xs">
                    No posts scheduled for this day.
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Post Drawer / Modal -->
    <div x-show="selectedEvent" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4" @click.away="selectedEvent = null">
            <template x-if="selectedEvent">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full uppercase" :class="selectedEvent.statusBadgeClass" x-text="selectedEvent.status"></span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-2" x-text="selectedEvent.title"></h3>
                        </div>
                        <button type="button" @click="selectedEvent = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-2xl">
                        <div>
                            <span class="text-slate-400 block">Brand:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedEvent.brand_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Scheduled:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedEvent.dateStr + ' ' + (selectedEvent.scheduled_time || '')"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Assigned:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedEvent.assignee_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Platforms:</span>
                            <span class="font-bold uppercase text-slate-800 dark:text-slate-200" x-text="selectedEvent.platforms.join(', ')"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="selectedEvent = null" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">Close</button>
                        <a :href="'/content/posts/' + selectedEvent.id" class="px-4 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm">View Full Post Details</a>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function formatLocalDate(d) {
    if (!d) return '';
    const dateObj = (d instanceof Date) ? d : new Date(d);
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function eventMatchesDate(e, dateStr) {
    if (!e || !dateStr) return false;
    const d = e.scheduled_date || (e.start ? e.start.split('T')[0] : '') || (e.scheduled_at ? e.scheduled_at.split('T')[0] : '');
    return d === dateStr;
}

function contentCalendar() {
    return {
        viewMode: 'month',
        loading: false,
        showFilters: false,
        currentDate: new Date(),
        events: [],
        selectedEvent: null,
        draggedEvent: null,
        filters: {
            brand_id: '',
            client_id: '',
            platform: '',
            status: '',
            assigned_to: ''
        },

        init() {
            this.fetchEvents();
        },

        setViewMode(mode) {
            this.viewMode = mode;
        },

        get calendarTitle() {
            if (this.viewMode === 'month') {
                return this.currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            } else if (this.viewMode === 'week') {
                return 'Week of ' + this.currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            } else {
                return this.currentDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            }
        },

        navigate(direction) {
            const d = new Date(this.currentDate);
            if (this.viewMode === 'month') {
                d.setMonth(d.getMonth() + direction);
            } else if (this.viewMode === 'week') {
                d.setDate(d.getDate() + (direction * 7));
            } else {
                d.setDate(d.getDate() + direction);
            }
            this.currentDate = d;
            this.fetchEvents();
        },

        goToToday() {
            this.currentDate = new Date();
            this.fetchEvents();
        },

        fetchEvents() {
            this.loading = true;
            
            // Calculate range
            const start = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const end = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);

            // Buffer range by 7 days for overlap
            start.setDate(start.getDate() - 7);
            end.setDate(end.getDate() + 7);

            const params = new URLSearchParams({
                start: formatLocalDate(start),
                end: formatLocalDate(end),
                ...this.filters
            });

            fetch('/content/calendar/feed?' + params.toString())
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    this.events = Array.isArray(data) ? data : (data.events || []);
                })
                .catch(err => {
                    console.error('Error loading calendar events:', err);
                    this.events = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        get monthDays() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDayIndex = new Date(year, month, 1).getDay();
            const lastDate = new Date(year, month + 1, 0).getDate();
            const prevLastDate = new Date(year, month, 0).getDate();

            const days = [];
            const todayStr = formatLocalDate(new Date());

            // Prev month overflow
            for (let i = firstDayIndex; i > 0; i--) {
                const prevDate = prevLastDate - i + 1;
                const d = new Date(year, month - 1, prevDate);
                const dateStr = formatLocalDate(d);
                days.push({
                    dayNumber: prevDate,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: dateStr === todayStr,
                    events: this.events.filter(e => eventMatchesDate(e, dateStr))
                });
            }

            // Current month days
            for (let i = 1; i <= lastDate; i++) {
                const d = new Date(year, month, i);
                const dateStr = formatLocalDate(d);
                days.push({
                    dayNumber: i,
                    dateStr: dateStr,
                    isCurrentMonth: true,
                    isToday: dateStr === todayStr,
                    events: this.events.filter(e => eventMatchesDate(e, dateStr))
                });
            }

            // Next month overflow
            const totalCells = Math.ceil(days.length / 7) * 7;
            const remaining = totalCells - days.length;
            for (let i = 1; i <= remaining; i++) {
                const d = new Date(year, month + 1, i);
                const dateStr = formatLocalDate(d);
                days.push({
                    dayNumber: i,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: dateStr === todayStr,
                    events: this.events.filter(e => eventMatchesDate(e, dateStr))
                });
            }

            return days;
        },

        get weekDays() {
            const curr = new Date(this.currentDate);
            const firstDay = curr.getDate() - curr.getDay();
            const days = [];
            const todayStr = formatLocalDate(new Date());

            for (let i = 0; i < 7; i++) {
                const d = new Date(curr.getFullYear(), curr.getMonth(), firstDay + i);
                const dateStr = formatLocalDate(d);
                days.push({
                    dateStr: dateStr,
                    dayName: d.toLocaleDateString('en-US', { weekday: 'short' }),
                    formattedDate: d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                    isToday: dateStr === todayStr,
                    events: this.events.filter(e => eventMatchesDate(e, dateStr))
                });
            }
            return days;
        },

        get dayEvents() {
            const dateStr = formatLocalDate(this.currentDate);
            return this.events.filter(e => eventMatchesDate(e, dateStr));
        },

        openPostDetails(event) {
            this.selectedEvent = {
                ...event,
                dateStr: event.scheduled_date || (event.start || '').split('T')[0],
                platforms: (event.extendedProps && event.extendedProps.platforms) ? event.extendedProps.platforms : (event.platforms || ['General']),
                brand_name: (event.extendedProps && event.extendedProps.brand_name) ? event.extendedProps.brand_name : (event.brand_name || ''),
                assignee_name: (event.extendedProps && event.extendedProps.assignee_name) ? event.extendedProps.assignee_name : (event.assignee_name || 'Unassigned'),
                status: (event.extendedProps && event.extendedProps.status) ? event.extendedProps.status : (event.status || ''),
                statusBadgeClass: (event.extendedProps && event.extendedProps.status_badge_class) ? event.extendedProps.status_badge_class : (event.status_badge_class || event.status_classes || 'bg-slate-100 text-slate-800')
            };
        },

        handleDragStart(event) {
            this.draggedEvent = event;
        },

        handleDrop(targetDateStr) {
            if (!this.draggedEvent) return;
            const eventId = this.draggedEvent.id;

            if (confirm(`Reschedule "${this.draggedEvent.title}" to ${targetDateStr}?`)) {
                fetch(`/content/posts/${eventId}/reschedule`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        scheduled_date: targetDateStr
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.fetchEvents();
                    } else {
                        alert(data.message || 'Failed to reschedule.');
                    }
                })
                .catch(() => alert('Network error rescheduling post.'));
            }
            this.draggedEvent = null;
        }
    };
}
window.contentCalendar = contentCalendar;
</script>
@endsection

