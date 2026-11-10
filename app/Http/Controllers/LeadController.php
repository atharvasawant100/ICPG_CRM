<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Imports\LeadsImport;
use App\Exports\LeadsExportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    /**
     * Log activity for a lead
     */
    private function logActivity(Lead $lead, string $activityType, string $description, ?string $oldValue = null, ?string $newValue = null)
    {
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => $activityType,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);
        $user = $request->user();

        $query = Lead::with(['creator', 'assignedUser', 'pendingFollowups']);

        // Data scope. Scoped to assigned_to for employees, team for managers, all for admin.
        $query->applyDataScope($user, 'leads');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(15)->withQueryString();

        // Assignable sales team members for manager or admin
        $assignableUsers = collect();
        if ($user->isAdmin() || $user->isManager()) {
            $teamIds = \App\Rbac\DataScope::teamIds($user);
            $assignableUsers = \App\Models\User::query()
                ->whereIn('id', $teamIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar', 'department']);
        }

        return view('leads.index', compact('leads', 'assignableUsers'));
    }

    /**
     * Show Manual vs Bulk Import choice.
     */
    public function create()
    {
        $this->authorize('create', Lead::class);

        return view('leads.create-options');
    }

    /**
     * Show manual creation form
     */
    public function createManual()
    {
        $this->authorize('create', Lead::class);
        $user = Auth::user();

        $assignableUsers = collect();
        if ($user->isAdmin() || $user->isManager()) {
            $teamIds = \App\Rbac\DataScope::teamIds($user);
            $assignableUsers = \App\Models\User::query()
                ->whereIn('id', $teamIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('leads.create', compact('assignableUsers'));
    }

    /**
     * Show bulk import form
     */
    public function createBulk()
    {
        $this->authorize('import', Lead::class);

        return view('leads.bulk-import');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request)
    {
        $this->authorize('create', Lead::class);

        $data = $request->validated();
        $user = Auth::user();

        // If manager didn't specify assigned_to, default to self or leave null
        if (! isset($data['assigned_to']) && ! $user->isAdmin()) {
            $data['assigned_to'] = $user->id;
        }

        $lead = Lead::create([
            ...$data,
            'created_by' => $user->id,
        ]);

        $this->logActivity($lead, 'created', 'Lead created manually');

        if ($lead->assigned_to) {
            $assignee = \App\Models\User::find($lead->assigned_to);
            if ($assignee) {
                $this->logActivity($lead, 'assigned', "Assigned to {$assignee->name}");
            }
        }

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Store bulk imported leads
     */
    public function bulkImportStore(Request $request)
    {
        $this->authorize('import', Lead::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        try {
            $import = new LeadsImport();
            Excel::import($import, $request->file('file'));

            $redirect = redirect()->route('leads.create.bulk')
                ->with('import_summary', [
                    'success' => $import->getSuccessCount(),
                    'duplicates' => $import->getDuplicateCount(),
                    'skipped' => $import->getSkippedCount(),
                ]);

            if (! empty($import->getErrors())) {
                $redirect->with('import_errors', $import->getErrors());
            }

            if ($import->getSuccessCount() > 0) {
                $message = $import->getSuccessCount() . ' lead(s) imported successfully.';
                if ($import->getDuplicateCount() > 0) {
                    $message .= ' ' . $import->getDuplicateCount() . ' already existed and were skipped.';
                }
                $redirect->with('success', $message);
            } elseif ($import->getDuplicateCount() > 0) {
                $redirect->with('success', 'No new leads imported — ' . $import->getDuplicateCount() . ' already exist in CRM.');
            } elseif ($import->getSkippedCount() > 0) {
                $redirect->with('error', 'No leads were imported. Please fix the errors and try again.');
            } else {
                $redirect->with('error', 'No valid rows found in the file.');
            }

            return $redirect;
        } catch (\Throwable $e) {
            return back()->with('error', 'Error importing leads: ' . $e->getMessage());
        }
    }

    /**
     * Download bulk import template
     */
    public function downloadTemplate()
    {
        $this->authorize('import', Lead::class);

        return Excel::download(new LeadsExportTemplate(), 'leads_import_template.xlsx');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load([
            'creator',
            'assignedUser',
            'activities.user',
            'statusLogs.changer',
            'followups.user',
            'convertedCustomer',
            'notes.user',
        ]);

        $user = Auth::user();
        $assignableUsers = collect();
        if ($user->isAdmin() || $user->isManager()) {
            $teamIds = \App\Rbac\DataScope::teamIds($user);
            $assignableUsers = \App\Models\User::query()
                ->whereIn('id', $teamIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('leads.show', compact('lead', 'assignableUsers'));
    }

    /**
     * Quick status update endpoint
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorize('updateStatus', $lead);

        $request->validate([
            'status' => 'required|string|in:new,contacted,follow_up,interested,qualified,converted,won,lost',
            'notes' => 'nullable|string|max:1000',
        ]);

        $lead->recordStatusChange($request->status, Auth::id(), $request->notes);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead status updated to ' . ucfirst(str_replace('_', ' ', $request->status)),
                'status' => $request->status,
                'lead' => $lead->fresh(),
            ]);
        }

        return back()->with('success', 'Lead status updated successfully.');
    }

    /**
     * Assign / reassign a lead to a team member
     */
    public function reassign(Request $request, Lead $lead)
    {
        $this->authorize('reassign', $lead);

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $newAssignee = \App\Models\User::findOrFail($request->assigned_to);
        $oldAssignee = $lead->assignedUser;

        $lead->update(['assigned_to' => $newAssignee->id]);

        $desc = "Lead assigned to {$newAssignee->name}" . ($oldAssignee ? " (previously: {$oldAssignee->name})" : "");
        if ($request->notes) {
            $desc .= " - {$request->notes}";
        }

        $this->logActivity($lead, 'assigned', $desc, $oldAssignee?->id, (string) $newAssignee->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Lead assigned to {$newAssignee->name}.",
                'assigned_user' => $newAssignee,
            ]);
        }

        return back()->with('success', "Lead assigned to {$newAssignee->name}.");
    }

    /**
     * Bulk reassign leads
     */
    public function bulkReassign(Request $request)
    {
        $this->authorize('reassign', Lead::class);

        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'required|exists:leads,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $newAssignee = \App\Models\User::findOrFail($request->assigned_to);
        $leads = Lead::whereIn('id', $request->lead_ids)->get();

        foreach ($leads as $lead) {
            $oldAssignee = $lead->assignedUser;
            $lead->update(['assigned_to' => $newAssignee->id]);
            $this->logActivity($lead, 'assigned', "Bulk reassigned to {$newAssignee->name}", $oldAssignee?->id, (string) $newAssignee->id);
        }

        return redirect()->route('leads.index')->with('success', count($leads) . " lead(s) successfully reassigned to {$newAssignee->name}.");
    }

    /**
     * Bulk distribute leads evenly (Round-Robin) among selected employees
     */
    public function bulkDistribute(Request $request)
    {
        $this->authorize('reassign', Lead::class);

        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'required|exists:leads,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
        ]);

        $users = \App\Models\User::whereIn('id', $request->user_ids)
            ->where('is_active', true)
            ->get()
            ->values();

        if ($users->isEmpty()) {
            return redirect()->route('leads.index')->with('error', 'Please select at least one active team member to distribute leads.');
        }

        $leads = Lead::whereIn('id', $request->lead_ids)->get();
        $userCount = $users->count();

        foreach ($leads as $index => $lead) {
            $assignee = $users[$index % $userCount];
            $oldAssignee = $lead->assignedUser;
            $lead->update(['assigned_to' => $assignee->id]);
            $this->logActivity(
                $lead,
                'assigned',
                "Auto-distributed (Round-Robin) to {$assignee->name}",
                $oldAssignee?->id,
                (string) $assignee->id
            );
        }

        $names = $users->pluck('name')->join(', ', ' and ');
        return redirect()->route('leads.index')->with(
            'success',
            "Successfully distributed {$leads->count()} lead(s) evenly among {$userCount} team member(s) ({$names})."
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        $user = Auth::user();
        $assignableUsers = collect();
        if ($user->isAdmin() || $user->isManager()) {
            $teamIds = \App\Rbac\DataScope::teamIds($user);
            $assignableUsers = \App\Models\User::query()
                ->whereIn('id', $teamIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('leads.edit', compact('lead', 'assignableUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $data = $request->validated();

        $changes = [];

        if (isset($data['status']) && $data['status'] !== $lead->status) {
            $changes['status'] = ['old' => $lead->status, 'new' => $data['status']];
            $lead->recordStatusChange($data['status'], Auth::id(), 'Updated from edit form');
        }

        $lead->update($data);

        foreach ($changes as $field => $change) {
            if ($field === 'status') {
                $this->logActivity($lead, 'status_changed', "Status changed from {$change['old']} to {$change['new']}", $change['old'], $change['new']);
            }
        }

        if (empty($changes)) {
            $this->logActivity($lead, 'updated', 'Lead information updated');
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $leadName = $lead->name;
        $lead->delete();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'deleted',
            'description' => "Lead '{$leadName}' moved to trash",
        ]);

        return redirect()->route('leads.index')
            ->with('success', 'Lead moved to trash.');
    }

    /**
     * Display trashed leads
     */
    public function trash(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::onlyTrashed()->with(['creator']);

        // Data scope also applies to the trash listing.
        $query->applyDataScope($request->user(), 'leads');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest('deleted_at')->paginate(15);
        return view('leads.trash', compact('leads'));
    }

    /**
     * Restore a trashed lead
     */
    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->find($id);
        if (!$lead) {
            abort(404);
        }

        $this->authorize('update', $lead);

        $lead->restore();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'restored',
            'description' => "Lead '{$lead->name}' restored from trash",
        ]);

        return redirect()->route('leads.index')
            ->with('success', 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead
     */
    public function forceDelete($id)
    {
        $lead = Lead::onlyTrashed()->find($id);
        if (!$lead) {
            abort(404);
        }

        $this->authorize('delete', $lead);

        $leadName = $lead->name;
        $lead->forceDelete();

        return redirect()->route('leads.trash')
            ->with('success', "Lead '{$leadName}' permanently deleted.");
    }

    /**
     * Convert lead to customer
     */
    public function convertToCustomer(Request $request, Lead $lead)
    {
        $this->authorize('convert', $lead);

        $user = Auth::user();

        if ($lead->isConverted()) {
            return back()->with('error', 'Lead is already converted.');
        }

        $oldStatus = $lead->status;

        $customer = \App\Models\Customer::create([
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $lead->recordStatusChange('converted', $user->id, "Converted to customer: {$customer->name}");

        $lead->update([
            'converted_to_customer_id' => $customer->id,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'converted',
            'description' => "Lead converted to customer: {$customer->name}",
            'new_value' => "Customer #{$customer->id}",
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Lead converted to customer successfully.');
    }

    /**
     * Leads dashboard
     */
    public function dashboard()
    {
        $this->authorize('viewAny', Lead::class);

        $user = request()->user();

        $baseQuery = Lead::query();

        // KPI scope. No-op for the admin, so the numbers stay identical.
        $baseQuery->applyDataScope($user, 'leads');

        $totalLeads = (clone $baseQuery)->count();
        $newLeads = (clone $baseQuery)->where('status', 'new')->count();
        $contactedLeads = (clone $baseQuery)->where('status', 'contacted')->count();
        $interestedLeads = (clone $baseQuery)->where('status', 'interested')->count();
        $qualifiedLeads = (clone $baseQuery)->where('status', 'qualified')->count();
        $convertedLeads = (clone $baseQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $baseQuery)->where('status', 'lost')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $recentLeads = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $followupQuery = LeadFollowup::query();

        // Follow-up KPIs follow the same rule.
        $followupQuery->applyDataScope($user, 'followups');

        $pendingFollowups = (clone $followupQuery)
            ->where('status', 'pending')
            ->count();

        $overdueFollowups = (clone $followupQuery)
            ->where('status', 'pending')
            ->where('followup_date', '<', now())
            ->count();

        $leadsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $leadsBySource = (clone $baseQuery)
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'source');

        $topSources = $leadsBySource;

        $monthlyTrends = (clone $baseQuery)
            ->select(
                DB::raw(\App\Support\MonthExpression::for('created_at').' as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('leads.dashboard', compact(
            'totalLeads',
            'newLeads',
            'contactedLeads',
            'interestedLeads',
            'qualifiedLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate',
            'recentLeads',
            'pendingFollowups',
            'overdueFollowups',
            'leadsByStatus',
            'leadsBySource',
            'topSources',
            'monthlyTrends'
        ));
    }

    /**
     * Track a specific action (call, whatsapp, etc.) as an activity
     */
    public function trackAction(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'action' => 'required|string|in:call,whatsapp',
        ]);

        $action = $request->action;
        $description = '';

        if ($action === 'call') {
            $description = 'Initiated a phone call';
        } elseif ($action === 'whatsapp') {
            $description = 'Initiated a WhatsApp chat';
        }

        $this->logActivity($lead, $action, $description);

        return response()->json(['success' => true]);
    }
}
