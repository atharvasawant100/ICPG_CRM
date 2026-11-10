<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContentPost;
use App\Models\Customer;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\ContentCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContentCalendarController extends Controller
{
    public function __construct(
        protected ContentCalendarService $calendarService
    ) {}

    /**
     * Display the main Content Calendar interface (Month, Week, Day).
     */
    public function calendar(Request $request)
    {
        $this->authorize('viewAny', ContentPost::class);

        $user = $request->user();

        // Selected viewing date / navigation
        $currentDate = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $view = $request->input('view', 'month'); // month, week, day

        if (! in_array($view, ['month', 'week', 'day'], true)) {
            $view = 'month';
        }

        // Dropdown filter options
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $clients = Customer::orderBy('company')->get();
        $assignees = User::active()->where(function ($q) {
            $q->where('department', 'marketing')
              ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['marketing', 'marketing_manager']))
              ->orWhere('designation', 'like', '%marketing%');
        })->orderBy('name')->get();

        $platforms = SocialAccount::PLATFORMS;
        $statuses = ContentPost::STATUSES;

        $employees = $assignees;

        return view('content.calendar', compact(
            'currentDate',
            'view',
            'brands',
            'clients',
            'assignees',
            'employees',
            'platforms',
            'statuses'
        ));
    }

    /**
     * JSON feed for calendar events with filtering.
     */
    public function feed(Request $request)
    {
        $this->authorize('viewAny', ContentPost::class);

        $start = $request->filled('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->startOfMonth()->subDays(7);
        $end = $request->filled('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfMonth()->addDays(7);

        $filters = $request->only(['client_id', 'brand_id', 'assigned_to', 'status', 'platform', 'search']);

        $events = $this->calendarService->getCalendarFeed($request->user(), $start, $end, $filters);

        return response()->json($events);
    }

    /**
     * Marketing Content Dashboard.
     */
    public function dashboard(Request $request)
    {
        $this->authorize('viewAny', ContentPost::class);

        $stats = $this->calendarService->getDashboardStats($request->user());
        $todayPosts = $stats['today_posts'] ?? collect();
        $upcomingPosts = $stats['upcoming_posts'] ?? collect();
        $pendingApprovals = $stats['pending_approvals'] ?? collect();
        $recentActivities = $stats['recent_activity'] ?? collect();
        $brandStats = Brand::where('is_active', true)->withCount(['socialAccounts', 'contentPosts'])->limit(6)->get();

        $platformList = [];
        foreach (($stats['platform_counts'] ?? []) as $plat => $cnt) {
            $platformList[] = (object) ['platform' => $plat, 'count' => $cnt];
        }
        $platformStats = collect($platformList);

        return view('content.dashboard', compact(
            'stats',
            'todayPosts',
            'upcomingPosts',
            'pendingApprovals',
            'recentActivities',
            'brandStats',
            'platformStats'
        ));
    }

    /**
     * Manual Publishing Queue.
     */
    public function queue(Request $request)
    {
        $this->authorize('viewAny', ContentPost::class);

        $query = ContentPost::query()
            ->with(['brand', 'client', 'assignee', 'media', 'platformSchedules.socialAccount'])
            ->forUser($request->user())
            ->whereIn('status', ['approved', 'scheduled']);

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('platform')) {
            $query->whereHas('platformSchedules', fn ($sq) => $sq->where('platform', $request->input('platform')));
        }

        $posts = $query->orderByRaw('scheduled_at IS NULL, scheduled_at ASC')->paginate(12)->withQueryString();

        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $platforms = SocialAccount::PLATFORMS;

        return view('content.queue', compact('posts', 'brands', 'platforms'));
    }
}
