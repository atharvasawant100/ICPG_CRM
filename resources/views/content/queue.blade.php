@extends('layouts.app')

@section('title', 'Social Media Publishing Queue')
@section('page-title', 'Publishing Queue')

@section('content')
<div class="space-y-6" x-data="{
    publishModal: false,
    failModal: false,
    activePost: null,
    copiedText: null,

    copyToClipboard(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            this.copiedText = label;
            setTimeout(() => { this.copiedText = null; }, 2000);
        });
    },

    openPublish(post) {
        this.activePost = post;
        this.publishModal = true;
    },

    openFail(post) {
        this.activePost = post;
        this.failModal = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manual Publishing Queue</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review approved content ready to be published, copy media and captions, and log publishing confirmations.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('content.calendar') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Calendar</span>
            </a>
        </div>
    </div>

    <!-- Copied toast feedback -->
    <div x-show="copiedText" x-transition.opacity class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white text-xs px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2" style="display: none;">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>Copied <strong x-text="copiedText"></strong> to clipboard!</span>
    </div>

    <!-- Filters Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form method="GET" action="{{ route('content.queue') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand</label>
                <select name="brand_id" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Platform</label>
                <select name="platform" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Platforms</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p }}" {{ request('platform') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('content.queue') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-colors">
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Overdue / Missed Alert Banner -->
    @php
        $overdueCount = $posts->filter(fn($p) => $p->isOverdue())->count();
    @endphp
    @if($overdueCount > 0)
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-rose-900 dark:text-rose-200">{{ $overdueCount }} post(s) in this queue are overdue</h4>
                <p class="text-[11px] text-rose-700 dark:text-rose-300">Publish immediately or reschedule to a new target time.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($posts as $post)
        @php
            $isOverdue = $post->isOverdue();
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-3xl border {{ $isOverdue ? 'border-rose-300 dark:border-rose-800' : 'border-slate-200 dark:border-slate-700' }} shadow-sm overflow-hidden flex flex-col justify-between">
            <!-- Header section -->
            <div class="p-5 space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $post->brand->name ?? 'No Brand' }}</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mt-0.5 line-clamp-1">
                            <a href="{{ route('content.posts.show', $post) }}" class="hover:text-blue-600 transition-colors">
                                {{ $post->title }}
                            </a>
                        </h3>
                    </div>

                    @if($isOverdue)
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 shrink-0">
                            Overdue
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200 shrink-0">
                            Scheduled
                        </span>
                    @endif
                </div>

                <!-- Platform Schedules -->
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach($post->platformSchedules as $ps)
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $ps->platform_badge_class }}">
                            {{ ucfirst($ps->platform) }}
                        </span>
                    @endforeach
                    <span class="text-xs text-slate-400 capitalize">&bull; {{ str_replace('_', ' ', $post->post_type) }}</span>
                </div>

                <!-- Scheduled Timing -->
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Scheduled For</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ Carbon\Carbon::parse($post->scheduled_date)->format('M d, Y') }}
                            at {{ $post->scheduled_time ? Carbon\Carbon::parse($post->scheduled_time)->format('h:i A') : 'Anytime' }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Assigned</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $post->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                </div>

                <!-- Media Attachments Preview -->
                @if($post->media->isNotEmpty())
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1.5">Attachments ({{ $post->media->count() }})</span>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1">
                        @foreach($post->media as $m)
                        <div class="relative group shrink-0">
                            @if(str_starts_with($m->mime_type, 'image/'))
                                <img src="{{ $m->file_url }}" alt="Media" class="w-14 h-14 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <a href="{{ route('content.posts.media.download', [$post, $m]) }}" title="Download {{ $m->original_filename }}"
                                class="absolute inset-0 bg-slate-900/60 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Caption Preview & Quick Copy -->
                @if($post->caption)
                <div class="relative p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/50 text-xs text-slate-700 dark:text-slate-300">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Caption</span>
                        <button type="button" @click="copyToClipboard({{ json_encode($post->caption . "\n\n" . $post->hashtags) }}, 'Caption & Hashtags')"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-[11px] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span>Copy All</span>
                        </button>
                    </div>
                    <p class="line-clamp-3 text-slate-600 dark:text-slate-300">{{ $post->caption }}</p>
                    @if($post->hashtags)
                        <p class="text-blue-600 dark:text-blue-400 font-medium text-[11px] mt-1 line-clamp-1">{{ $post->hashtags }}</p>
                    @endif
                </div>
                @endif
            </div>

            <!-- Footer Action Buttons -->
            <div class="p-4 bg-slate-50/80 dark:bg-slate-900/40 border-t border-slate-100 dark:border-slate-700/70 flex items-center justify-between gap-2">
                <a href="{{ route('content.posts.show', $post) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-blue-600">
                    View Details
                </a>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openFail({ id: {{ $post->id }}, title: '{{ addslashes($post->title) }}' })"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                        Mark Failed
                    </button>
                    
                    <button type="button" @click="openPublish({ id: {{ $post->id }}, title: '{{ addslashes($post->title) }}' })"
                        class="px-4 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Mark Published</span>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">No Posts in Publishing Queue</h3>
            <p class="text-xs text-slate-400 mt-1">Posts appear here once they are approved and scheduled.</p>
        </div>
        @endforelse
    </div>

    <!-- Mark as Published Modal -->
    <div x-show="publishModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="publishModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Confirm Manual Publishing</h3>
            <p class="text-xs text-slate-500 mt-1">Record the publishing timestamp and live post URL for post: <strong x-text="activePost ? activePost.title : ''"></strong></p>

            <form :action="'/content/posts/' + (activePost ? activePost.id : '') + '/publish'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Published At</label>
                    <input type="datetime-local" name="published_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Live Post URL (Optional)</label>
                    <input type="url" name="live_url" placeholder="https://instagram.com/p/..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Publishing Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Published as carousel on Instagram, reach boosted..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="publishModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">Save Published Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mark as Failed Modal -->
    <div x-show="failModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="failModal = false">
            <h3 class="text-lg font-bold text-rose-600 dark:text-rose-400">Mark Post as Failed</h3>
            <p class="text-xs text-slate-500 mt-1">Please provide the reason why publishing failed or could not be completed.</p>

            <form :action="'/content/posts/' + (activePost ? activePost.id : '') + '/fail'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Failure Reason *</label>
                    <textarea name="failure_reason" rows="3" required placeholder="e.g. Media format rejected by platform, account locked, client cancelled campaign..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="failModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm">Confirm Post Failed</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

