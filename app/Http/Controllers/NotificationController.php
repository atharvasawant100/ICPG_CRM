<?php

namespace App\Http\Controllers;

use App\Services\NotificationFeed;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationFeed $feed)
    {
    }

    /**
     * Display the notifications page and automatically mark unread notifications as read.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Filter (all, unread, read)
        $filter = $request->get('filter', 'all');

        // 2. Mark unread notifications as read upon opening this page (unless explicitly filtering by unread)
        $markedCount = 0;
        if ($filter !== 'unread') {
            $markedCount = $this->feed->markAllAsRead($user);
        }

        $query = $this->feed->allQuery($user);

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $totalCount = $this->feed->allQuery($user)->count();
        $unreadCount = $this->feed->unreadCount($user);
        $readCount = max(0, $totalCount - $unreadCount);

        return view('notifications.index', compact(
            'notifications',
            'markedCount',
            'filter',
            'totalCount',
            'unreadCount',
            'readCount'
        ));
    }

    /**
     * Manually mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $count = $this->feed->markAllAsRead($user);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'marked' => $count]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Remove / dismiss a notification.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification removed.');
    }
}
