@extends('layouts.app')

@section('title', $post->title . ' - Content Post')
@section('page-title', 'Content Post Details')

@section('content')
<div class="space-y-6" x-data="{
    copiedText: null,
    showTaskModal: false,
    showReviewModal: false,
    showPublishModal: false,
    showFailModal: false,
    reviewDecision: 'approved',

    copyText(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            this.copiedText = label;
            setTimeout(() => { this.copiedText = null; }, 2000);
        });
    }
}">

    <!-- Top Toast Feedback -->
    <div x-show="copiedText" x-transition.opacity class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white text-xs px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2" style="display: none;">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>Copied <strong x-text="copiedText"></strong>!</span>
    </div>

    <!-- Header & Status Pipeline Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('content.posts.index') }}" class="hover:underline">Content Posts</a>
                <span>&rsaquo;</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $post->brand->name ?? 'No Brand' }}</span>
                <span>&rsaquo;</span>
                <span class="text-slate-400">#{{ $post->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $post->title }}</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('update', $post)
            <a href="{{ route('content.posts.edit', $post) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Post</span>
            </a>
            @endcan

            <!-- Workflow Stage Specific Buttons -->
            @if(in_array($post->status, ['idea', 'planned', 'in_creation', 'changes_requested']))
                <form action="{{ route('content.posts.submit-review', $post) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Submit for Review</span>
                    </button>
                </form>
            @endif

            @if($post->status === 'pending_review' && (auth()->user()->can('content.review') || auth()->id() === $post->reviewer_id))
                <button type="button" @click="showReviewModal = true" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Review & Decide</span>
                </button>
            @endif

            @if(in_array($post->status, ['approved', 'scheduled']))
                @can('content.publish')
                <button type="button" @click="showPublishModal = true" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Mark as Published</span>
                </button>
                @endcan
            @endif
        </div>
    </div>

    <!-- Status Banner / Badges -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500">Current Status:</span>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $post->status_badge_class }}">
                {{ ucfirst(str_replace('_', ' ', $post->status)) }}
            </span>
            @if($post->isOverdue())
                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                    Overdue for Publishing
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Target Platforms:</span>
            <div class="flex items-center gap-1.5">
                @foreach($post->platformSchedules as $ps)
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $ps->platform_badge_class }}">
                        {{ ucfirst($ps->platform) }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Content, Media, Caption, Tasks -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Caption & Copy Box -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Caption & Hashtags</span>
                        <span class="text-xs font-normal text-slate-400 capitalize">({{ str_replace('_', ' ', $post->post_type) }})</span>
                    </h2>

                    @if($post->caption || $post->hashtags)
                    <button type="button" @click="copyText({{ json_encode($post->caption . "\n\n" . $post->hashtags) }}, 'Caption & Hashtags')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 hover:bg-blue-100 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span>Copy Caption</span>
                    </button>
                    @endif
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-200 space-y-3 whitespace-pre-wrap leading-relaxed">
                    @if($post->caption)
                        <div>{{ $post->caption }}</div>
                    @else
                        <span class="text-slate-400 italic">No caption drafted yet.</span>
                    @endif

                    @if($post->hashtags)
                        <div class="text-blue-600 dark:text-blue-400 font-semibold">{{ $post->hashtags }}</div>
                    @endif
                </div>

                @if($post->notes)
                <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200">
                    <strong class="font-bold block mb-0.5">Internal Notes:</strong>
                    {{ $post->notes }}
                </div>
                @endif
            </div>

            <!-- Media Gallery & Uploader -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Attached Media ({{ $post->media->count() }})
                    </h2>
                </div>

                @if($post->media->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($post->media as $m)
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                        @if(str_starts_with($m->mime_type, 'image/'))
                            <img src="{{ $m->file_url }}" alt="{{ $m->original_filename }}" class="w-full h-36 object-cover">
                        @else
                            <div class="w-full h-36 flex flex-col items-center justify-center p-3 text-center">
                                <svg class="w-10 h-10 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 truncate max-w-full">{{ $m->original_filename }}</span>
                            </div>
                        @endif

                        <!-- Media Overlay Controls -->
                        <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                            <a href="{{ route('content.posts.media.download', [$post, $m]) }}" title="Download" class="p-2 rounded-xl bg-white/20 text-white hover:bg-white/40">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            <form action="{{ route('content.posts.media.delete', [$post, $m]) }}" method="POST" onsubmit="return confirm('Remove this media file?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete" class="p-2 rounded-xl bg-rose-600/80 text-white hover:bg-rose-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>

                        <div class="p-2 text-[10px] text-slate-400 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                            <span class="truncate">{{ $m->original_filename }}</span>
                            <span class="shrink-0">{{ $m->formatted_size }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-slate-400 text-xs">
                    No media files attached yet. Upload images, graphics, or videos below.
                </div>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('content.posts.media.upload', $post) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
                    @csrf
                    <input type="file" name="media[]" multiple required
                        class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 dark:border-slate-700 rounded-xl p-1.5 bg-slate-50 dark:bg-slate-900 flex-1">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shrink-0">Upload</button>
                </form>
            </div>

            <!-- Linked Tasks Integration (Requirement 7) -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Linked Tasks ({{ $post->tasks->count() }})
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Assigned to team members via the central Task Pool</p>
                    </div>

                    @can('content.assign')
                    <button type="button" @click="showTaskModal = true"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Add Linked Task</span>
                    </button>
                    @endcan
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($post->tasks as $t)
                    <div class="py-3 flex items-center justify-between gap-4 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                    {{ ucfirst(str_replace('_', ' ', $t->content_task_type ?? 'Task')) }}
                                </span>
                                <a href="{{ route('tasks.index') }}?search={{ urlencode($t->title) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 truncate">
                                    {{ $t->title }}
                                </a>
                            </div>
                            <div class="flex items-center gap-2 text-slate-400 text-[11px] mt-1">
                                <span>Assignee: <strong class="text-slate-600 dark:text-slate-300">{{ $t->assignee->name ?? 'Unassigned' }}</strong></span>
                                <span>&bull;</span>
                                <span>Due: {{ $t->due_date ? Carbon\Carbon::parse($t->due_date)->format('M d, Y') : 'No deadline' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $t->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-400 text-xs">
                        No linked tasks assigned yet. Click "Add Linked Task" to assign copy, graphic design, video editing, or publishing tasks.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Approval History (Requirement 8) -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Approval History
                </h2>

                <div class="space-y-3">
                    @forelse($post->approvals as $app)
                    <div class="p-3.5 rounded-2xl border text-xs {{ $app->status === 'approved' ? 'border-emerald-200 bg-emerald-50/50 dark:bg-emerald-950/20 dark:border-emerald-800' : 'border-amber-200 bg-amber-50/50 dark:bg-amber-950/20 dark:border-amber-800' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $app->status === 'approved' ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white' }}">
                                    {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Reviewed by {{ $app->reviewer->name ?? 'Reviewer' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $app->created_at->format('M d, Y h:i A') }}</span>
                        </div>

                        @if($app->comments)
                        <p class="mt-2 text-slate-700 dark:text-slate-300 italic">"{{ $app->comments }}"</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No review actions recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Meta Info & Activity Timeline -->
        <div class="space-y-6">

            <!-- Post Meta Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4 text-xs">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">Publishing Information</h3>

                <div class="space-y-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Brand</span>
                        <a href="{{ route('brands.show', $post->brand) }}" class="font-bold text-blue-600 hover:underline">
                            {{ $post->brand->name ?? 'No Brand' }}
                        </a>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Client</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $post->client->name ?? 'No Client' }}</span>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Scheduled For</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ $post->scheduled_date ? Carbon\Carbon::parse($post->scheduled_date)->format('M d, Y') : 'Unscheduled' }}
                            @if($post->scheduled_time)
                                ({{ Carbon\Carbon::parse($post->scheduled_time)->format('h:i A') }})
                            @endif
                        </span>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Timezone</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $post->timezone }}</span>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Created By</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $post->creator->name ?? 'System' }}</span>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Assigned To</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $post->assignee->name ?? 'Unassigned' }}</span>
                    </div>

                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-400">Reviewer</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $post->reviewer->name ?? 'None' }}</span>
                    </div>

                    @if($post->published_at)
                    <div class="pt-2 flex justify-between text-emerald-600">
                        <span>Published At</span>
                        <span class="font-bold">{{ $post->published_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif

                    @if($post->live_url)
                    <div class="pt-2">
                        <span class="text-slate-400 block mb-1">Live Post URL</span>
                        <a href="{{ $post->live_url }}" target="_blank" class="font-semibold text-blue-600 hover:underline break-all block">
                            {{ $post->live_url }}
                        </a>
                    </div>
                    @endif

                    @if($post->failure_reason)
                    <div class="pt-2 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-200">
                        <strong class="block font-bold">Failure Reason:</strong>
                        <span>{{ $post->failure_reason }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Activity Log Audit Trail -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Activity Timeline</h3>

                <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                    @forelse($post->activityLogs as $act)
                    <div class="relative text-xs">
                        <div class="absolute -left-6 top-0.5 w-3.5 h-3.5 rounded-full bg-blue-600 border-2 border-white dark:border-slate-800"></div>
                        <p class="text-slate-800 dark:text-slate-200">
                            <strong>{{ $act->user->name ?? 'System' }}</strong>
                            {{ $act->description }}
                        </p>
                        <span class="text-[10px] text-slate-400">{{ $act->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No activity logged.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Review Decision Modal -->
    <div x-show="showReviewModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="showReviewModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Content Review Decision</h3>
            <p class="text-xs text-slate-500 mt-1">Review the content, copy, and attachments. Choose to approve or request revisions.</p>

            <form action="{{ route('content.posts.decide-review', $post) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-2">Decision *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <input type="radio" name="decision" value="approved" x-model="reviewDecision" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Approve</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <input type="radio" name="decision" value="changes_requested" x-model="reviewDecision" class="text-amber-600 focus:ring-amber-500">
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Request Changes</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Feedback / Comments</label>
                    <textarea name="comments" rows="3" placeholder="Provide actionable feedback or instructions for changes..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showReviewModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Linked Task Modal (Requirement 7) -->
    <div x-show="showTaskModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="showTaskModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create Linked Content Task</h3>
            <p class="text-xs text-slate-500 mt-1">Delegate specific work for this post into the central Task Pool.</p>

            <form action="{{ route('content.posts.tasks.store', $post) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Task Type *</label>
                    <select name="content_task_type" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="write_caption">Write Caption</option>
                        <option value="create_graphic">Create Graphic</option>
                        <option value="edit_video">Edit Video</option>
                        <option value="prepare_carousel">Prepare Carousel</option>
                        <option value="review_content">Review Content</option>
                        <option value="publish_post">Publish Post</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Task Title *</label>
                    <input type="text" name="title" value="Create creative asset for {{ $post->title }}" required
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Instructions / Description</label>
                    <textarea name="description" rows="2" placeholder="Specific guidelines, dimensions, branding rules..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Assign To *</label>
                        <select name="assigned_to" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $post->assigned_to == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $post->scheduled_date ?? now()->addDays(2)->format('Y-m-d')) }}"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showTaskModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mark as Published Modal -->
    <div x-show="showPublishModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="showPublishModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Confirm Manual Publishing</h3>
            <p class="text-xs text-slate-500 mt-1">Record the publishing timestamp and live post URL.</p>

            <form action="{{ route('content.posts.publish', $post) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Published At</label>
                    <input type="datetime-local" name="published_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Live Post URL (Optional)</label>
                    <input type="url" name="live_url" placeholder="https://..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Publishing Notes (Optional)</label>
                    <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showPublishModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">Save Published Status</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

