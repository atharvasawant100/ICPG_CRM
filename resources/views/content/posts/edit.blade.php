@extends('layouts.app')

@section('title', 'Edit Content Post')
@section('page-title', 'Edit Content Post')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Post: {{ $post->title }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Update post details, caption, schedule, or workflow state.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('content.posts.show', $post) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
                View Post
            </a>
            <a href="{{ route('content.posts.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
                All Posts
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
        <strong class="font-bold">Please correct the following errors:</strong>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('content.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Core Details -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">1. Brand & Basic Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Brand -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand *</label>
                    <select name="brand_id" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id', $post->brand_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} (Client: {{ $b->client->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Post Title -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Post Concept / Title *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Post Type -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Post Type *</label>
                    <select name="post_type" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        @foreach($postTypes as $pt)
                            <option value="{{ $pt }}" {{ old('post_type', $post->post_type) == $pt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $pt)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', $post->status) == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Target Platforms -->
            <div>
                @php
                    $activePlatforms = $post->platformSchedules->pluck('platform')->toArray();
                @endphp
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-2">Publishing Platforms *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                    @foreach($platforms as $p)
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 cursor-pointer transition-all">
                        <input type="checkbox" name="platforms[]" value="{{ $p }}"
                            {{ (is_array(old('platforms')) && in_array($p, old('platforms'))) || (!old('platforms') && in_array($p, $activePlatforms)) ? 'checked' : '' }}
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200 capitalize">{{ $p }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Card 2: Content & Copy -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">2. Content & Creative Assets</h2>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Caption / Copy</label>
                <textarea name="caption" rows="4" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('caption', $post->caption) }}</textarea>
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Hashtags</label>
                <input type="text" name="hashtags" value="{{ old('hashtags', $post->hashtags) }}"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Add More Media Files</label>
                <input type="file" name="media[]" multiple
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900">
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Internal Notes</label>
                <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('notes', $post->notes) }}</textarea>
            </div>
        </div>

        <!-- Card 3: Schedule & Team -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">3. Schedule & Assignment</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Target Date</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $post->scheduled_date ?? '') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Target Time</label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time', $post->scheduled_time ? Carbon\Carbon::parse($post->scheduled_time)->format('H:i') : '') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $post->timezone ?? config('app.timezone', 'Asia/Kolkata')) }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Assignee</label>
                    <select name="assigned_to" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="">Unassigned</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('assigned_to', $post->assigned_to) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Reviewer</label>
                    <select name="reviewer_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="">Optional Reviewer</option>
                        @foreach($reviewers as $rev)
                            <option value="{{ $rev->id }}" {{ old('reviewer_id', $post->reviewer_id) == $rev->id ? 'selected' : '' }}>{{ $rev->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="require_approval" value="1" {{ old('require_approval', $post->require_approval) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Require manager review and approval</span>
                </label>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('content.posts.show', $post) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Save Changes</button>
        </div>
    </form>

</div>
@endsection

