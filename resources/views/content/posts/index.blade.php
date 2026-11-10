@extends('layouts.app')

@section('title', 'Content Posts')
@section('page-title', 'Content Posts')

@section('content')
<div class="space-y-4">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Content Posts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">All planned, scheduled, and published social media content posts.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('content.calendar') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Calendar</span>
            </a>

            @can('content.create')
            <a href="{{ route('content.posts.create') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Post</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4">
        <form method="GET" action="{{ route('content.posts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, caption, hashtags..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
            </div>

            <!-- Brand -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand</label>
                <select name="brand_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach(($statuses ?? array_keys(\App\Models\ContentPost::STATUSES)) as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Post Type -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Post Type</label>
                <select name="post_type" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Types</option>
                    @foreach(($postTypes ?? array_keys(\App\Models\ContentPost::POST_TYPES)) as $pt)
                        <option value="{{ $pt }}" {{ request('post_type') == $pt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $pt)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-all">Filter</button>
                <a href="{{ route('content.posts.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-200 uppercase font-bold text-[11px] border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Post Title & Brand</th>
                        <th class="px-4 py-3.5">Platforms</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Scheduled Date</th>
                        <th class="px-4 py-3.5">Assignee</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($posts as $post)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('content.posts.show', $post) }}" class="hover:text-blue-600">
                                    {{ $post->title }}
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $post->brand->name ?? 'No Brand' }} ({{ $post->client->name ?? 'No Client' }})
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($post->platformSchedules as $ps)
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $ps->platform_badge_class }}">
                                        {{ ucfirst($ps->platform) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-4 py-3 capitalize text-slate-700 dark:text-slate-300">
                            {{ str_replace('_', ' ', $post->post_type) }}
                        </td>

                        <td class="px-4 py-3">
                            @if($post->scheduled_date)
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ Carbon\Carbon::parse($post->scheduled_date)->format('M d, Y') }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $post->scheduled_time ? Carbon\Carbon::parse($post->scheduled_time)->format('h:i A') : 'Anytime' }}
                                </div>
                            @else
                                <span class="text-slate-400">Not scheduled</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $post->assignee->name ?? 'Unassigned' }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $post->status_badge_class }}">
                                {{ ucfirst(str_replace('_', ' ', $post->status)) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('content.posts.show', $post) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>

                                @can('update', $post)
                                <a href="{{ route('content.posts.edit', $post) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-700" title="Edit Post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                @endcan

                                @can('delete', $post)
                                <form action="{{ route('content.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700" title="Delete Post">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            No content posts found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-700/80">
            {{ $posts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

