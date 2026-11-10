@extends('layouts.app')

@section('title', 'Social Media Content Dashboard')
@section('page-title', 'Social Media Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Social Media Dashboard</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cross-brand social content workflow, publishing pipeline, and performance tracking.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('content.calendar') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Calendar View</span>
            </a>

            @can('content.publish')
            <a href="{{ route('content.queue') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Publishing Queue</span>
                @if($stats['scheduled'] > 0)
                    <span class="px-1.5 py-0.5 text-[10px] rounded-full bg-emerald-600 text-white font-bold">{{ $stats['scheduled'] }}</span>
                @endif
            </a>
            @endcan
        </div>
    </div>

    <!-- Alert for Missed Posts -->
    @if($stats['missed'] > 0)
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 flex items-start gap-3">
        <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-200">Attention: {{ $stats['missed'] }} scheduled post(s) have passed their scheduled time</h4>
            <p class="text-xs text-amber-700 dark:text-amber-300/90 mt-0.5">These posts need to be published manually or rescheduled in the queue.</p>
        </div>
        <a href="{{ route('content.queue') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition-colors">
            Resolve in Queue
        </a>
    </div>
    @endif

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Planned -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>Planned</span>
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ $stats['planned'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Ideas & briefs</div>
        </div>

        <!-- In Creation -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>In Creation</span>
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ $stats['in_creation'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Copy & creatives</div>
        </div>

        <!-- Pending Review -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>Pending Review</span>
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">{{ $stats['pending_review'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Awaiting approval</div>
        </div>

        <!-- Scheduled -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>Scheduled</span>
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">{{ $stats['scheduled'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Ready on calendar</div>
        </div>

        <!-- Published -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>Published</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ $stats['published'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Live posts</div>
        </div>

        <!-- Missed / Failed -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-medium">
                <span>Missed / Failed</span>
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">{{ $stats['missed'] + $stats['failed'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $stats['missed'] }} overdue, {{ $stats['failed'] }} failed</div>
        </div>
    </div>

    <!-- Main 2-Column Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Schedules & Pending Approvals -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Today's Schedule -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Today's Posting Schedule</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Scheduled for today, {{ now()->format('l, M d, Y') }}</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        {{ $todayPosts->count() }} Posts
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($todayPosts as $post)
                    <div class="p-4 hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Platform pill -->
                            <div class="shrink-0 flex items-center gap-1.5">
                                @foreach($post->platformSchedules as $ps)
                                    <span class="px-2 py-1 text-[11px] font-semibold rounded-lg {{ $ps->platform_badge_class }}">
                                        {{ ucfirst($ps->platform) }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('content.posts.show', $post) }}" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                    {{ $post->title }}
                                </a>
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $post->brand->name ?? 'No Brand' }}</span>
                                    <span>&bull;</span>
                                    <span>Time: {{ $post->scheduled_time ? Carbon\Carbon::parse($post->scheduled_time)->format('h:i A') : 'Anytime' }}</span>
                                    <span>&bull;</span>
                                    <span>Assigned: {{ $post->assignee->name ?? 'Unassigned' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $post->status_badge_class }}">
                                {{ ucfirst(str_replace('_', ' ', $post->status)) }}
                            </span>
                            <a href="{{ route('content.posts.show', $post) }}" class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-sm font-medium">No posts scheduled for today</p>
                        <p class="text-xs text-slate-400 mt-0.5">Schedule posts from the calendar or posts menu.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Pending Review / Approvals -->
            @can('content.review')
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Content Pending Review</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Posts waiting for reviewer approval</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        {{ $pendingApprovals->count() }} Waiting
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($pendingApprovals as $post)
                    <div class="p-4 hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('content.posts.show', $post) }}" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                {{ $post->title }}
                            </a>
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <span>Brand: <strong class="text-slate-700 dark:text-slate-300">{{ $post->brand->name ?? 'N/A' }}</strong></span>
                                <span>&bull;</span>
                                <span>By: {{ $post->assignee->name ?? $post->creator->name ?? 'N/A' }}</span>
                                <span>&bull;</span>
                                <span>Target: {{ $post->scheduled_date ? Carbon\Carbon::parse($post->scheduled_date)->format('M d') : 'Unscheduled' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('content.posts.show', $post) }}"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                                Review Post
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <p class="text-sm font-medium">All caught up! No content pending review.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endcan

            <!-- Upcoming Posts (Next 7 Days) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Upcoming Publishing Pipeline</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Approved and scheduled posts over the next 7 days</p>
                    </div>
                    <a href="{{ route('content.posts.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        View All
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($upcomingPosts as $post)
                    <div class="p-4 hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('content.posts.show', $post) }}" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                {{ $post->title }}
                            </a>
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $post->brand->name ?? 'N/A' }}</span>
                                <span>&bull;</span>
                                <span>{{ Carbon\Carbon::parse($post->scheduled_date)->format('M d, Y') }} ({{ $post->scheduled_time ? Carbon\Carbon::parse($post->scheduled_time)->format('h:i A') : 'TBD' }})</span>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $post->status_badge_class }} shrink-0">
                            {{ ucfirst(str_replace('_', ' ', $post->status)) }}
                        </span>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <p class="text-sm font-medium">No upcoming posts in the next 7 days.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Platform Breakdown, Brand Overview, Recent Activity -->
        <div class="space-y-6">

            <!-- Platform Distribution -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Platform Distribution</h3>
                <div class="space-y-2.5">
                    @forelse($platformStats as $stat)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="font-medium text-slate-700 dark:text-slate-300 capitalize">{{ $stat->platform }}</span>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $stat->count }} posts</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No platform activity yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Brand Overview -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Active Brands</h3>
                    <a href="{{ route('brands.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Manage</a>
                </div>
                <div class="space-y-3">
                    @forelse($brandStats as $b)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60">
                        <div>
                            <div class="text-xs font-semibold text-slate-900 dark:text-white">{{ $b->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $b->social_accounts_count }} accounts</div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                            {{ $b->content_posts_count }} posts
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No brands registered yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Recent Activity</h3>
                <div class="space-y-3.5">
                    @forelse($recentActivities as $act)
                    <div class="flex items-start gap-2.5 text-xs">
                        <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-slate-800 dark:text-slate-200 leading-snug">
                                <strong class="font-semibold">{{ $act->user->name ?? 'System' }}</strong>
                                {{ $act->description }}
                            </p>
                            <span class="text-[10px] text-slate-400">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No recent activity.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

