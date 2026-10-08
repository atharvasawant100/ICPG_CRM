<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Http\Requests\StoreLeadFollowupRequest;
use App\Http\Requests\UpdateLeadFollowupRequest;
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

        $followup = LeadFollowup::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'followup_date' => $request->followup_date,
            'followup_type' => $request->followup_type,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_scheduled',
            'description' => "Follow-up scheduled: {$request->followup_type} on " . date('M d, Y', strtotime($request->followup_date)),
            'old_value' => null,
            'new_value' => $request->followup_type,
        ]);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up scheduled successfully.');
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
        $followup->update($request->validated());

        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $followup->markAsCompleted();
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_updated',
            'description' => "Follow-up updated: {$request->followup_type}",
            'old_value' => $oldStatus,
            'new_value' => $request->status ?? $oldStatus,
        ]);

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

        $followup->markAsCompleted();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'followup_completed',
            'description' => "Follow-up completed: {$followup->followup_type}",
            'old_value' => 'pending',
            'new_value' => 'completed',
        ]);

        $lead->refresh();
        $lead->autoUpdateStatus($user->id);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up marked as completed.');
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
        $query = LeadFollowup::with(['lead', 'user']);
        $query->applyDataScope($request->user(), 'followups');

        if ($request->ajax() || ($request->has('start') && $request->has('end'))) {
            $start = $request->start;
            $end = $request->end;

            if ($start && $end) {
                $query->whereBetween('followup_date', [$start, $end]);
            }

            $followups = $query->get();

            $events = [];
            foreach ($followups as $followup) {
                $color = '#3B82F6';
                if ($followup->status == 'completed')
                    $color = '#10B981';
                elseif ($followup->isOverdue())
                    $color = '#EF4444';
                elseif ($followup->followup_type == 'meeting')
                    $color = '#8B5CF6';

                $title = ($followup->lead ? $followup->lead->name : 'Unknown Lead') . ' - ' . ucfirst($followup->followup_type);

                $events[] = [
                    'id' => $followup->id,
                    'title' => $title,
                    'start' => $followup->followup_date->toIso8601String(),
                    'url' => $followup->lead ? route('leads.show', $followup->lead_id) : '#',
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'description' => $followup->description,
                    'extendedProps' => [
                        'status' => $followup->status,
                        'description' => $followup->description,
                        'leadName' => $followup->lead ? $followup->lead->name : 'Unknown'
                    ]
                ];
            }

            return response()->json($events);
        }

        $upcomingFollowups = $query->where('status', 'pending')
            ->orderBy('followup_date', 'asc')
            ->take(10)
            ->get();

        return view('leads.calendar', compact('upcomingFollowups'));
    }
}
