<?php

namespace App\Http\Controllers;

use App\Services\AttendancePunchService;
use Illuminate\Http\Request;

class AttendancePunchController extends Controller
{
    public function __construct(private readonly AttendancePunchService $punchService) {}

    /**
     * Handle manual punch in.
     */
    public function punchIn(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return back()->with('info', 'Admin accounts do not require attendance punching.');
        }

        $notes = $request->string('notes')->trim()->value() ?: 'Manual punch-in';

        try {
            $record = $this->punchService->punchIn($user, $notes);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($record && $record->wasRecentlyCreated) {
            return back()->with('success', "Clocked in successfully at " . \Carbon\Carbon::parse($record->check_in)->format('h:i A') . ".");
        }

        return back()->with('info', "You are already clocked in today (at " . \Carbon\Carbon::parse($record->check_in)->format('h:i A') . ").");
    }

    /**
     * Handle manual punch out.
     */
    public function punchOut(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return back()->with('info', 'Admin accounts do not require attendance punching.');
        }

        $notes = $request->string('notes')->trim()->value() ?: 'Manual punch-out';
        $workedTaskIds = (array) $request->input('worked_task_ids', []);
        $completedTaskIds = (array) $request->input('completed_task_ids', []);
        $workNotes = $request->string('work_notes')->trim()->value();

        $record = $this->punchService->punchOut(
            $user,
            $notes,
            $workedTaskIds,
            $completedTaskIds,
            $workNotes ?: null
        );

        if ($record && $record->check_out) {
            $formattedTime = \Carbon\Carbon::parse($record->check_out)->format('h:i A');
            $hours = floor($record->total_minutes / 60);
            $mins = $record->total_minutes % 60;
            $taskCount = count($workedTaskIds);
            $taskMsg = $taskCount > 0 ? " ({$taskCount} task" . ($taskCount > 1 ? 's' : '') . " logged)" : '';
            return back()->with('success', "Clocked out successfully at {$formattedTime}. Total time: {$hours}h {$mins}m{$taskMsg}.");
        }

        return back()->with('error', 'Unable to clock out. You must be clocked in first today.');
    }

    /**
     * Handle automatic logout and punch-out when user closes the browser tab.
     */
    /**
     * Tab closure lifecycle handler: keeps attendance active without auto-punching out.
     */
    public function tabCloseLogout(Request $request)
    {
        // Closing a tab or browser does not punch out the user.
        // The punch-in remains active so the user can return in the evening and punch out with task logs.
        return response()->json(['status' => 'session_kept_active']);
    }

    /**
     * Lightweight heartbeat to keep session active while tab is open or waking from sleep,
     * and cancel any pending tab closure if the page was reloaded.
     */
    public function heartbeat(Request $request)
    {
        $user = $request->user();
        $tabId = (string) $request->input('tab_id', '');

        if ($user) {
            \Illuminate\Support\Facades\Cache::put("user_last_heartbeat_{$user->id}", now()->timestamp, 60);

            if ($tabId !== '') {
                \Illuminate\Support\Facades\Cache::put("tab_reloaded_{$tabId}", true, 15);
            }
        }

        return response()->json([
            'status' => 'active',
            'user_id' => $user?->id,
            'time' => now()->toISOString(),
        ]);
    }
}
