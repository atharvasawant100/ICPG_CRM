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

        $query = Lead::with(['creator']);

        // Data scope. No-op for the admin, whose scope is "all".
        $query->applyDataScope($request->user(), 'leads');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(15);

        return view('leads.index', compact('leads'));
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

        return view('leads.create');
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

        $lead = Lead::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        $this->logActivity($lead, 'created', 'Lead created');

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
        } catch (\Exception $e) {
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

        $lead->load(['creator', 'activities.user', 'followups.user', 'convertedCustomer', 'notes.user']);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        return view('leads.edit', compact('lead'));
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

        $lead->update([
            'status' => 'converted',
            'converted_to_customer_id' => $customer->id,
        ]);

        $this->logActivity($lead, 'status_changed', "Lead status changed from {$oldStatus} to converted", $oldStatus, 'converted');

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
