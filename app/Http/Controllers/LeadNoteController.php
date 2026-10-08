<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadNoteController extends Controller
{
    /**
     * Store a newly created note.
     */
    public function store(Request $request, Lead $lead)
    {
        $this->authorize('addNote', $lead);
        $user = Auth::user();

        $request->validate([
            'note' => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        $isInternal = $request->boolean('is_internal', false);

        $note = LeadNote::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'note' => $request->note,
            'is_internal' => $isInternal,
        ]);

        // Log activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'note_added',
            'description' => $isInternal ? 'Internal note added' : 'Note added',
            'old_value' => null,
            'new_value' => substr($request->note, 0, 100),
        ]);

        // Auto-update lead status based on activities (only for public notes)
        if (!$isInternal) {
            $lead->refresh();
            $lead->autoUpdateStatus($user->id);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Note added successfully.');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Lead $lead, LeadNote $note)
    {
        $this->authorize('deleteNote', $lead);
        $user = Auth::user();

        // Verify note belongs to lead
        if ($note->lead_id !== $lead->id) {
            abort(404);
        }

        $note->delete();

        // Log activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'note_deleted',
            'description' => 'Note deleted',
            'old_value' => null,
            'new_value' => null,
        ]);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Note deleted successfully.');
    }
}
