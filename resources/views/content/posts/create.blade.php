@extends('layouts.app')

@section('title', 'Create Content Post')
@section('page-title', 'Create Content Post')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Create New Content Post</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Plan, brief, and schedule social media content for client brands.</p>
        </div>

        <a href="{{ route('content.posts.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
            Back to Posts
        </a>
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

    <form action="{{ route('content.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Card 1: Core Details -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">1. Brand & Basic Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Brand -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand *</label>
                    <select name="brand_id" id="brand_id" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="">Select Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" data-client="{{ $b->client_id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} (Client: {{ $b->client->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Post Title -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Post Concept / Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Diwali festive offer banner or Product teaser carousel"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Post Type -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Post Type *</label>
                    <select name="post_type" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        @foreach($postTypes as $pt)
                            <option value="{{ $pt }}" {{ old('post_type') == $pt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $pt)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Initial Status -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="idea" {{ old('status') == 'idea' ? 'selected' : '' }}>Idea</option>
                        <option value="planned" {{ old('status', 'planned') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="in_creation" {{ old('status') == 'in_creation' ? 'selected' : '' }}>In Creation</option>
                        <option value="pending_review" {{ old('status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    </select>
                </div>
            </div>

            <!-- Target Platforms (Multi-select) -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-2">Publishing Platforms * (Select one or more)</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                    @foreach($platforms as $p)
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/40 cursor-pointer transition-all">
                        <input type="checkbox" name="platforms[]" value="{{ $p }}"
                            {{ (is_array(old('platforms')) && in_array($p, old('platforms'))) || (!old('platforms') && in_array($p, ['instagram', 'facebook'])) ? 'checked' : '' }}
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200 capitalize">{{ $p }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Card 2: Content Creative & Copy -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">2. Content & Creative Assets</h2>

            <!-- Caption -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Caption / Copy</label>
                <textarea name="caption" rows="4" placeholder="Write caption copy, call to action, and promotional text..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('caption') }}</textarea>
            </div>

            <!-- Hashtags -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Hashtags</label>
                <input type="text" name="hashtags" value="{{ old('hashtags') }}" placeholder="#Marketing #SocialMedia #BrandCampaign #Growth"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>

            <!-- Media Uploads -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Attach Media Files (Images / Videos / Graphics)</label>
                <input type="file" name="media[]" multiple
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900">
                <span class="text-[11px] text-slate-400 mt-1 block">Supported: JPG, PNG, GIF, MP4, MOV, WEBP. Max 25MB each.</span>
            </div>

            <!-- Internal Notes -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Internal Notes & Production Instructions</label>
                <textarea name="notes" rows="2" placeholder="Notes for designers, video editors, or account managers..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Card 3: Scheduling & Team Assignment -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">3. Schedule & Assignment</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Scheduled Date -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Target Date</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $selectedDate) }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>

                <!-- Scheduled Time -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Target Time</label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time', '18:00') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>

                <!-- Timezone -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', config('app.timezone', 'Asia/Kolkata')) }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Assignee -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Assign Content Work To</label>
                    <select name="assigned_to" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="">Unassigned</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('assigned_to', auth()->id()) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reviewer -->
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Reviewer / Approver</label>
                    <select name="reviewer_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                        <option value="">Optional Reviewer</option>
                        @foreach($reviewers as $rev)
                            <option value="{{ $rev->id }}" {{ old('reviewer_id') == $rev->id ? 'selected' : '' }}>{{ $rev->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Approval Requirement -->
            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="require_approval" value="1" {{ old('require_approval', true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Require manager review and approval before scheduling or publishing</span>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('content.posts.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/30 transition-all">
                Save Content Post
            </button>
        </div>
    </form>

</div>
@endsection

