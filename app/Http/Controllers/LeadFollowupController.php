<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadFollowupRequest;
use App\Http\Requests\UpdateLeadFollowupRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Rbac\DataScope;
use App\Services\LeadFollowupNotificationService;
use App\Services\MeetingLinkService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadFollowupController extends Controller
{
    /**
     * Store a newly created follow-up.
     */
    public function store(StoreLeadFollowupRequest $request, Lead $lead)
    {
        $this->authorize('create', LeadFollowup::class);
        $user = Auth::user();

        $meetingLink = null;
        if ($request->followup_type === 'meeting') {
            $meetingLink = MeetingLinkService::generate(
                $request->meeting_link,
                $request->meeting_platform ?? 'jitsi',
                "Meeting with {$lead->name}"
            );
        }

        $followup = LeadFollowup::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'followup_date' => $request->followup_date,
            'followup_type' => $request->followup_type,
            'meeting_link' => $meetingLink,
            'agenda' => $request->agenda,
            'meeting_platform' => $request->meeting_platform ?? 'jitsi',
            'description' => $request->description,
            'status' => 'pending',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_scheduled',
            'description' => "Follow-up scheduled: " . ucfirst($request->followup_type) . " on " . date('M d, Y h:i A', strtotime($request->followup_date)),
            'old_value' => null,
            'new_value' => $request->followup_type,
        ]);

        // If it's a meeting and send_email_to_lead is checked (or default true), send email
        if ($request->followup_type === 'meeting' && $request->boolean('send_email_to_lead', true)) {
            LeadFollowupNotificationService::sendMeetingInvite($followup, $lead, $user);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Follow-up scheduled successfully.',
                'followup' => $followup,
            ]);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up scheduled successfully.' . ($meetingLink ? ' Meeting invitation sent to lead.' : ''));
    }

    /**
     * Update the specified follow-up.
     */
    public function update(UpdateLeadFollowupRequest $request, Lead $lead, LeadFollowup $followup)
    {
        $this->authorize('update', $followup);
        $user = Auth::user();

        if ($followup->lead_id !== $lead->id) {
            abort(404);
        }

        $oldStatus = $followup->status;
        $oldDate = $followup->followup_date?->format('M d, Y h:i A');
        $newDateRaw = $request->followup_date;

        $data = $request->validated();

        if ($request->followup_type === 'meeting' && empty($request->meeting_link) && empty($followup->meeting_link)) {
            $data['meeting_link'] = MeetingLinkService::generate(
                null,
                $request->meeting_platform ?? 'jitsi',
                "Meeting with {$lead->name}"
            );
        }

        $followup->update($data);

        $dateChanged = $newDateRaw && $followup->wasChanged('followup_date');
        $statusChanged = $request->filled('status') && $request->status !== $oldStatus;

        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $followup->update(['completed_at' => now()]);
            $lead->autoUpdateStatus($user->id);
        }

        // Email notifications for meetings
        if ($followup->followup_type === 'meeting' && $request->boolean('send_email_to_lead', true)) {
            if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                LeadFollowupNotificationService::sendMeetingCancelled($followup, $lead, $user, $request->cancellation_reason);
            } elseif ($dateChanged && $followup->status !== 'cancelled') {
                LeadFollowupNotificationService::sendMeetingRescheduled($followup, $lead, $user, $oldDate);
            }
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_updated',
            'description' => "Follow-up updated: " . ucfirst($request->followup_type) . ($statusChanged ? " (Status: {$oldStatus} → {$request->status})" : ''),
            'old_value' => $oldStatus,
            'new_value' => $request->status ?? $oldStatus,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Follow-up updated successfully.',
                'followup' => $followup,
            ]);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up updated successfully.');
    }

    /**
     * Mark follow-up as completed.
     */
    public function complete(Request $request, Lead $lead, LeadFollowup $followup)
    {
        $this->authorize('update', $followup);
        $user = Auth::user();

        if ($followup->lead_id !== $lead->id) {
            abort(404);
        }

        $notes = $request->input('notes', '');
        $description = $followup->description;
        if (! empty($notes)) {
            $description .= ($description ? "\n\n" : "") . "[Completed on " . now()->format('M d, Y') . "]: " . $notes;
        }

        $followup->update([
            'status' => 'completed',
            'completed_at' => now(),
            'description' => $description,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_completed',
            'description' => "Follow-up completed: " . ucfirst($followup->followup_type) . ($notes ? " - {$notes}" : ""),
            'old_value' => 'pending',
            'new_value' => 'completed',
        ]);

        $lead->refresh();
        $lead->autoUpdateStatus($user->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Follow-up marked as completed.',
                'followup' => $followup,
            ]);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up marked as completed.');
    }

    /**
     * Quick reschedule from calendar or show page.
     */
    public function reschedule(Request $request, ...$args)
    {
        $followup = end($args);
        if (! ($followup instanceof LeadFollowup)) {
            $followup = LeadFollowup::findOrFail($followup);
        }

        $this->authorize('update', $followup);
        $request->validate([
            'followup_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);

        $lead = $followup->lead;
        $user = Auth::user();
        $oldDate = $followup->followup_date?->format('M d, Y h:i A');

        $followup->update([
            'followup_date' => $request->followup_date,
            'status' => 'pending',
        ]);

        if ($followup->followup_type === 'meeting' && $lead) {
            LeadFollowupNotificationService::sendMeetingRescheduled($followup, $lead, $user, $oldDate);
        }

        if ($lead) {
            $reason = $request->reschedule_reason ?: $request->notes;
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'followup_rescheduled',
                'description' => "Meeting/Follow-up rescheduled to " . date('M d, Y h:i A', strtotime($request->followup_date)) . ($reason ? " (Reason: {$reason})" : ""),
                'old_value' => $oldDate,
                'new_value' => $request->followup_date,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting rescheduled successfully.',
                'followup' => $followup,
            ]);
        }

        return back()->with('success', 'Follow-up rescheduled successfully.');
    }

    /**
     * Cancel follow-up.
     */
    public function cancel(Request $request, ...$args)
    {
        $followup = end($args);
        if (! ($followup instanceof LeadFollowup)) {
            $followup = LeadFollowup::findOrFail($followup);
        }

        $this->authorize('update', $followup);
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $lead = $followup->lead;
        $user = Auth::user();

        $followup->update([
            'status' => 'cancelled',
        ]);

        if ($followup->followup_type === 'meeting' && $lead) {
            LeadFollowupNotificationService::sendMeetingCancelled($followup, $lead, $user, $request->reason);
        }

        if ($lead) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'followup_cancelled',
                'description' => "Meeting/Follow-up cancelled" . ($request->reason ? " Reason: {$request->reason}" : ""),
                'old_value' => 'pending',
                'new_value' => 'cancelled',
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting cancelled successfully.',
                'followup' => $followup,
            ]);
        }

        return back()->with('success', 'Follow-up cancelled successfully.');
    }

    /**
     * Remove the specified follow-up.
     */
    public function destroy(Lead $lead, LeadFollowup $followup)
    {
        $this->authorize('delete', $followup);
        $user = Auth::user();

        if ($followup->lead_id !== $lead->id) {
            abort(404);
        }

        $followup->delete();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_deleted',
            'description' => 'Follow-up deleted',
            'old_value' => null,
            'new_value' => null,
        ]);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up deleted successfully.');
    }

    /**
     * Display follow-ups on a calendar
     */
    public function calendar(Request $request)
    {
        $this->authorize('viewAny', LeadFollowup::class);
        $user = $request->user();

        $query = LeadFollowup::with(['lead', 'user']);
        $query->applyDataScope($user, 'followups');

        // Allow manager or admin to filter by specific employee
        $canFilterEmployees = $user->isAdmin() || $user->isManager();
        $teamMembers = collect();

        if ($canFilterEmployees) {
            $teamIds = DataScope::teamIds($user);
            $teamMembers = User::query()
                ->whereIn('id', $teamIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar']);

            if ($request->filled('employee_id')) {
                $employeeId = (int) $request->employee_id;
                if ($user->isAdmin() || in_array($employeeId, $teamIds, true)) {
                    $query->where('user_id', $employeeId);
                }
            }
        }

        // Filter by status if requested
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // Filter by type if requested
        if ($request->filled('filter_type')) {
            $query->where('followup_type', $request->filter_type);
        }

        // Search leads AJAX endpoint for Schedule Modal searchable select
        if ($request->ajax() && $request->has('search_lead')) {
            $q = trim((string) $request->get('search_lead'));
            $searchQuery = Lead::query()
                ->select(['id', 'name', 'company', 'email', 'phone', 'status'])
                ->applyDataScope($user, 'leads');

            if ($q !== '') {
                $searchQuery->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            }

            $leads = $searchQuery->orderBy('name')->take(50)->get();

            return response()->json($leads);
        }

        if ($request->ajax() || ($request->has('start') && $request->has('end'))) {
            $start = $request->start;
            $end = $request->end;

            if ($start && $end) {
                $query->whereBetween('followup_date', [$start, $end]);
            }

            $followups = $query->get();

            $events = [];
            foreach ($followups as $followup) {
                // Color coding as per documentation:
                // green = completed, red = missed/overdue, purple = meeting, blue = call/other
                $color = '#3b82f6'; // default blue
                $badge = 'Call';

                if ($followup->status === 'completed') {
                    $color = '#10b981'; // green
                    $badge = 'Completed';
                } elseif ($followup->status === 'cancelled') {
                    $color = '#64748b'; // slate grey
                    $badge = 'Cancelled';
                } elseif ($followup->isOverdue()) {
                    $color = '#ef4444'; // red
                    $badge = 'Overdue';
                } elseif ($followup->followup_type === 'meeting') {
                    $color = '#8b5cf6'; // purple
                    $badge = 'Meeting';
                }

                $title = ($followup->lead ? $followup->lead->name : 'Unknown') . ' (' . ucfirst($followup->followup_type) . ')';

                $events[] = [
                    'id' => $followup->id,
                    'title' => $title,
                    'start' => $followup->followup_date?->toIso8601String(),
                    'end' => $followup->followup_date?->addMinutes(45)->toIso8601String(),
                    'url' => $followup->lead ? route('leads.show', $followup->lead_id) : '#',
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'followupId' => $followup->id,
                        'leadId' => $followup->lead_id,
                        'leadName' => $followup->lead?->name ?? 'Unknown',
                        'leadEmail' => $followup->lead?->email ?? '',
                        'leadPhone' => $followup->lead?->phone ?? '',
                        'leadCompany' => $followup->lead?->company ?? '',
                        'leadStatus' => $followup->lead?->status ?? '',
                        'type' => $followup->followup_type,
                        'status' => $followup->status,
                        'dateFormatted' => $followup->followup_date?->format('M d, Y h:i A') ?? '',
                        'meetingLink' => $followup->meeting_link,
                        'agenda' => $followup->agenda,
                        'description' => $followup->description,
                        'employeeName' => $followup->user?->name ?? 'Staff',
                        'badge' => $badge,
                    ],
                ];
            }

            return response()->json($events);
        }

        // Reminders for follow-ups due today (as requested in doc: "Reminder for follow-ups due today")
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $dueTodayQuery = LeadFollowup::with(['lead', 'user'])
            ->applyDataScope($user, 'followups')
            ->whereBetween('followup_date', [$todayStart, $todayEnd])
            ->where('status', 'pending')
            ->orderBy('followup_date', 'asc');

        $dueToday = $dueTodayQuery->get();

        // Upcoming pending followups
        $upcomingFollowups = (clone $query)
            ->where('status', 'pending')
            ->where('followup_date', '>=', now())
            ->orderBy('followup_date', 'asc')
            ->take(10)
            ->get();

        // Overdue count
        $overdueCount = LeadFollowup::applyDataScope($user, 'followups')
            ->where('status', 'pending')
            ->where('followup_date', '<', now())
            ->count();

        // Accessible leads for the quick schedule modal dropdown
        $leadsQuery = Lead::query()->select(['id', 'name', 'company', 'email', 'phone', 'status']);
        $leadsQuery->applyDataScope($user, 'leads');
        $availableLeads = $leadsQuery->orderBy('name')->take(500)->get();

        return view('leads.calendar', compact(
            'upcomingFollowups',
            'dueToday',
            'overdueCount',
            'canFilterEmployees',
            'teamMembers',
            'availableLeads'
        ));
    }
}
