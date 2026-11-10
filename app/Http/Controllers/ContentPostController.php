<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentPostRequest;
use App\Http\Requests\UpdateContentPostRequest;
use App\Models\Brand;
use App\Models\ContentMedia;
use App\Models\ContentPost;
use App\Models\Customer;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\ContentCalendarService;
use App\Services\ContentTaskIntegrationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentPostController extends Controller
{
    public function __construct(
        protected ContentCalendarService $calendarService,
        protected ContentTaskIntegrationService $taskIntegrationService
    ) {}

    /**
     * Display content posts in list / table view.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ContentPost::class);

        $query = ContentPost::query()
            ->with(['brand', 'client', 'assignee', 'media', 'platformSchedules'])
            ->forUser($request->user());

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->filled('post_type')) {
            $query->where('post_type', $request->input('post_type'));
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->input('search')) . '%';
            $query->where(function ($sq) use ($s) {
                $sq->where('title', 'like', $s)
                   ->orWhere('caption', 'like', $s)
                   ->orWhere('hashtags', 'like', $s);
            });
        }

        $posts = $query->latest('id')->paginate(15)->withQueryString();

        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $assignees = User::active()->where(function ($q) {
            $q->where('department', 'marketing')
              ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['marketing', 'marketing_manager']))
              ->orWhere('designation', 'like', '%marketing%');
        })->orderBy('name')->get();
        $employees = $assignees;

        $statuses = array_keys(ContentPost::STATUSES);
        $postTypes = array_keys(ContentPost::POST_TYPES);

        return view('content.posts.index', compact('posts', 'brands', 'assignees', 'employees', 'statuses', 'postTypes'));
    }

    /**
     * Get marketing staff assignable by the given user.
     * Employees can only assign to fellow marketing employees/themselves, NOT to managers.
     */
    protected function getAssignableMarketingStaff(User $currentUser)
    {
        $marketingStaff = User::active()->where(function ($q) {
            $q->where('department', 'marketing')
              ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['marketing', 'marketing_manager']))
              ->orWhere('designation', 'like', '%marketing%');
        })->orderBy('name')->get();

        if (! $currentUser->isManager() && ! $currentUser->isAdmin()) {
            return $marketingStaff->filter(fn ($u) => ! $u->isManager() && ! $u->isAdmin())->values();
        }

        return $marketingStaff;
    }

    /**
     * Get marketing reviewers (managers/admins).
     */
    protected function getMarketingReviewers()
    {
        $reviewers = User::active()->where(function ($q) {
            $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'marketing_manager']))
              ->orWhere(function ($sq) {
                  $sq->where('department', 'marketing')
                     ->where('designation', 'like', '%manager%');
              });
        })->orderBy('name')->get();

        if ($reviewers->isEmpty()) {
            $reviewers = User::active()->where(function ($q) {
                $q->where('department', 'marketing')
                  ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['marketing_manager', 'admin']));
            })->orderBy('name')->get();
        }

        return $reviewers;
    }

    /**
     * Show the create post form.
     */
    public function create(Request $request)
    {
        $this->authorize('create', ContentPost::class);

        $brands = Brand::with(['socialAccounts', 'client'])->where('is_active', true)->orderBy('name')->get();
        $clients = Customer::orderBy('name')->get();

        $currentUser = $request->user();
        $employees = $this->getAssignableMarketingStaff($currentUser);
        $eligibleStaff = $employees;
        $reviewers = $this->getMarketingReviewers();

        $platforms = array_keys(SocialAccount::PLATFORMS);
        $postTypes = array_keys(ContentPost::POST_TYPES);
        $statuses = array_keys(ContentPost::STATUSES);

        $selectedBrandId = $request->input('brand_id');
        $selectedDate = $request->input('date');

        return view('content.posts.create', compact(
            'brands',
            'clients',
            'employees',
            'eligibleStaff',
            'reviewers',
            'platforms',
            'postTypes',
            'statuses',
            'selectedBrandId',
            'selectedDate'
        ));
    }

    /**
     * Store a newly created content post.
     */
    public function store(StoreContentPostRequest $request)
    {
        $validated = $request->validated();
        $mediaFiles = $request->file('media', []);
        $platforms = $request->input('platforms', []);

        $post = $this->calendarService->createPost($validated, $request->user(), $mediaFiles, $platforms);

        return redirect()->route('content.posts.show', $post)
            ->with('success', "Content post \"{$post->title}\" created successfully.");
    }

    /**
     * Display a single content post.
     */
    public function show(ContentPost $post)
    {
        $this->authorize('view', $post);

        $post->loadMissing([
            'brand.socialAccounts',
            'client',
            'contentCreator',
            'assignee',
            'reviewer',
            'publisher',
            'media.uploader',
            'platformSchedules.socialAccount',
            'approvals.reviewer',
            'activityLogs.user',
            'tasks.assignee',
        ]);

        $currentUser = request()->user() ?? auth()->user();
        $employees = $currentUser ? $this->getAssignableMarketingStaff($currentUser) : User::active()->where('department', 'marketing')->get();
        $eligibleStaff = $employees;
        $workTypes = ContentTaskIntegrationService::WORK_TYPES;

        return view('content.posts.show', compact('post', 'employees', 'eligibleStaff', 'workTypes'));
    }

    /**
     * Show the edit post form.
     */
    public function edit(ContentPost $post, Request $request)
    {
        $this->authorize('update', $post);

        $post->loadMissing(['brand.socialAccounts', 'client', 'platformSchedules']);

        $brands = Brand::with(['socialAccounts', 'client'])->where('is_active', true)->orderBy('name')->get();
        $clients = Customer::orderBy('name')->get();
        
        $currentUser = $request->user();
        $employees = $this->getAssignableMarketingStaff($currentUser);
        $eligibleStaff = $employees;
        $reviewers = $this->getMarketingReviewers();

        $platforms = array_keys(SocialAccount::PLATFORMS);
        $postTypes = array_keys(ContentPost::POST_TYPES);
        $statuses = array_keys(ContentPost::STATUSES);

        return view('content.posts.edit', compact(
            'post',
            'brands',
            'clients',
            'employees',
            'eligibleStaff',
            'reviewers',
            'platforms',
            'postTypes',
            'statuses'
        ));
    }

    /**
     * Update an existing content post.
     */
    public function update(UpdateContentPostRequest $request, ContentPost $post)
    {
        $this->calendarService->updatePost($post, $request->validated(), $request->user());

        return redirect()->route('content.posts.show', $post)
            ->with('success', "Post \"{$post->title}\" updated successfully.");
    }

    /**
     * Delete / Cancel a post.
     */
    public function destroy(ContentPost $post)
    {
        $this->authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        return redirect()->route('content.posts.index')
            ->with('success', "Post \"{$title}\" has been deleted.");
    }

    /**
     * Reschedule date and time (API / Quick action).
     */
    public function reschedule(Request $request, ContentPost $post)
    {
        $this->authorize('schedule', $post);

        $scheduledAt = $request->input('scheduled_at') ?? (
            $request->input('scheduled_date') ? ($request->input('scheduled_date') . ' ' . ($request->input('scheduled_time') ?? '12:00:00')) : null
        );

        if (! $scheduledAt) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Scheduled date is required.'], 422);
            }
            return redirect()->back()->withErrors(['scheduled_at' => 'Scheduled date is required.']);
        }

        $this->calendarService->reschedulePost($post, $request->user(), $scheduledAt);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post rescheduled successfully.',
                'scheduled_at' => $post->scheduled_at->toIso8601String(),
            ]);
        }

        return redirect()->back()->with('success', "Post rescheduled to {$post->scheduled_at->format('d M Y, h:i A')}.");
    }

    /**
     * Upload an additional media asset.
     */
    public function uploadMedia(Request $request, ContentPost $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'file'    => 'required|file|max:51200|mimes:png,jpg,jpeg,webp,svg,gif,mp4,mov,avi,webm,pdf',
            'caption' => 'nullable|string|max:255',
        ]);

        $this->calendarService->uploadMedia(
            $post,
            $request->file('file'),
            $request->user(),
            $request->input('caption'),
            $post->media()->count()
        );

        return redirect()->route('content.posts.show', $post)->with('success', 'Media file uploaded successfully.');
    }

    /**
     * Delete an attached media asset.
     */
    public function deleteMedia(ContentPost $post, ContentMedia $media)
    {
        $this->authorize('update', $post);

        if ((int) $media->content_post_id !== (int) $post->id) {
            abort(404);
        }

        $this->calendarService->deleteMedia($media, auth()->user());

        return redirect()->route('content.posts.show', $post)->with('success', 'Media file removed.');
    }

    /**
     * Download a media asset.
     */
    public function downloadMedia(ContentPost $post, ContentMedia $media)
    {
        $this->authorize('view', $post);

        if ((int) $media->content_post_id !== (int) $post->id) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($media->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('public')->download($media->file_path, $media->file_name);
    }

    /**
     * Submit content for approval.
     */
    public function submitReview(Request $request, ContentPost $post)
    {
        $this->authorize('submitReview', $post);

        $this->calendarService->submitForReview($post, $request->user(), $request->input('notes'));

        return redirect()->route('content.posts.show', $post)
            ->with('success', 'Content submitted for review.');
    }

    /**
     * Reviewer decision (Approve or Request Changes).
     */
    public function decideReview(Request $request, ContentPost $post)
    {
        $this->authorize('review', $post);

        $action = $request->input('action') ?? $request->input('decision');
        $comment = $request->input('comment') ?? $request->input('comments');

        if (! in_array($action, ['approved', 'changes_requested'], true)) {
            return redirect()->back()->withErrors(['action' => 'Invalid review decision.']);
        }

        $this->calendarService->decideReview(
            $post,
            $request->user(),
            $action,
            $comment
        );

        $actionText = $action === 'approved' ? 'approved' : 'returned for changes';

        return redirect()->route('content.posts.show', $post)
            ->with('success', "Content has been {$actionText}.");
    }

    /**
     * Mark content as published.
     */
    public function publish(Request $request, ContentPost $post)
    {
        $this->authorize('publish', $post);

        $request->validate([
            'published_at'  => 'nullable|date',
            'published_url' => 'nullable|url|max:255',
            'live_url'      => 'nullable|url|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $publishedUrl = $request->input('published_url') ?? $request->input('live_url');

        $this->calendarService->markPublished(
            $post,
            $request->user(),
            $request->input('published_at'),
            $publishedUrl,
            $request->input('notes')
        );

        return redirect()->back()
            ->with('success', "Post marked as published successfully.");
    }

    /**
     * Mark content as failed.
     */
    public function fail(Request $request, ContentPost $post)
    {
        $this->authorize('publish', $post);

        $request->validate([
            'failure_reason' => 'required|string|max:1000',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $this->calendarService->markFailed(
            $post,
            $request->user(),
            $request->input('failure_reason'),
            $request->input('notes')
        );

        return redirect()->back()
            ->with('warning', "Post has been flagged as failed.");
    }

    /**
     * Create and link a real Task from the Task Management Module.
     */
    public function createLinkedTask(Request $request, ContentPost $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'content_task_type' => 'required|string|in:' . implode(',', array_keys(ContentTaskIntegrationService::WORK_TYPES)),
            'title'             => 'nullable|string|max:150',
            'assigned_to'       => 'required|exists:users,id',
            'due_date'          => 'nullable|date',
            'priority'          => 'required|in:low,medium,high,urgent,critical',
            'description'       => 'nullable|string|max:1000',
            'expected_outcomes' => 'nullable|string|max:1000',
            'review_required'   => 'nullable|boolean',
        ]);

        $target = User::find($validated['assigned_to']);
        if ($target) {
            $isMarketing = strtolower((string) $target->department) === 'marketing'
                || $target->hasRole('marketing')
                || $target->hasRole('marketing_manager')
                || str_contains(strtolower((string) $target->designation), 'marketing');

            if (! $isMarketing) {
                return back()->withErrors(['assigned_to' => 'The assigned employee must belong to the Marketing department.']);
            }

            if (! $request->user()->isManager() && ! $request->user()->isAdmin()) {
                if ($target->isManager() || $target->isAdmin()) {
                    return back()->withErrors(['assigned_to' => 'Employees cannot assign tasks to managers. Please assign to yourself or a fellow team member.']);
                }
            }
        }

        $task = $this->taskIntegrationService->createLinkedTask($post, $request->user(), $validated);

        return redirect()->route('content.posts.show', $post)
            ->with('success', "Linked task \"{$task->title}\" created successfully in Task Pool.");
    }
}
