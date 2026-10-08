<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadController extends BaseApiController
{
    /**
     * Log activity for a lead.
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
        $query = Lead::with('creator');

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

        return $this->sendResponse(
            LeadResource::collection($leads)->response()->getData(true),
            'Leads retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();

        $lead = Lead::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        $this->logActivity($lead, 'created', 'Lead created');

        return $this->sendResponse(new LeadResource($lead), 'Lead created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'creator',
            'activities.user',
            'followups.user',
            'convertedCustomer',
            'notes.user',
        ]);

        return $this->sendResponse(new LeadResource($lead), 'Lead retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $data = $request->validated();

        $changes = [];

        if (isset($data['status']) && $data['status'] !== $lead->status) {
            $changes['status'] = ['old' => $lead->status, 'new' => $data['status']];
        }

        $lead->update($data);

        if (isset($changes['status'])) {
            $change = $changes['status'];
            $this->logActivity(
                $lead,
                'status_changed',
                "Status changed from {$change['old']} to {$change['new']}",
                $change['old'],
                $change['new']
            );
        }

        if (empty($changes)) {
            $this->logActivity($lead, 'updated', 'Lead information updated');
        }

        return $this->sendResponse(new LeadResource($lead), 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $leadName = $lead->name;
        $lead->delete();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'deleted',
            'description' => "Lead '{$leadName}' moved to trash",
            'old_value' => null,
            'new_value' => null,
        ]);

        return $this->sendResponse([], 'Lead deleted successfully.');
    }

    /**
     * Display trashed leads
     */
    public function trash(Request $request)
    {
        $query = Lead::onlyTrashed()->with('creator');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest('deleted_at')->paginate(15);

        return $this->sendResponse(
            LeadResource::collection($leads)->response()->getData(true),
            'Trashed leads retrieved successfully.'
        );
    }

    /**
     * Restore a trashed lead
     */
    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->find($id);
        if (!$lead) {
            return $this->sendError('Lead not found in trash.');
        }

        $lead->restore();

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'restored',
            'description' => "Lead '{$lead->name}' restored from trash",
            'old_value' => null,
            'new_value' => null,
        ]);

        return $this->sendResponse(new LeadResource($lead), 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead
     */
    public function forceDelete($id)
    {
        $lead = Lead::onlyTrashed()->find($id);
        if (!$lead) {
            return $this->sendError('Lead not found in trash.');
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->sendError('Unauthorized action. Only admin can permanently delete leads.', [], 403);
        }

        $leadName = $lead->name;
        $lead->forceDelete();

        return $this->sendResponse([], "Lead '{$leadName}' permanently deleted.");
    }

    /**
     * Convert lead to customer
     */
    public function convertToCustomer(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return $this->sendError('Lead not found.');
        }

        $user = Auth::user();

        if ($lead->isConverted()) {
            return $this->sendError('Lead is already converted to a customer.', [], 400);
        }

        $oldStatus = $lead->status;

        $customer = Customer::create([
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
            'old_value' => null,
            'new_value' => "Customer #{$customer->id}",
        ]);

        return $this->sendResponse([
            'lead' => new LeadResource($lead),
            'customer_id' => $customer->id,
        ], 'Lead converted to customer successfully.');
    }

    /**
     * Display leads dashboard with statistics.
     */
    public function dashboard(Request $request)
    {
        $baseQuery = Lead::query();

        $totalLeads = (clone $baseQuery)->count();
        $newLeads = (clone $baseQuery)->where('status', 'new')->count();
        $contactedLeads = (clone $baseQuery)->where('status', 'contacted')->count();
        $interestedLeads = (clone $baseQuery)->where('status', 'interested')->count();
        $qualifiedLeads = (clone $baseQuery)->where('status', 'qualified')->count();
        $convertedLeads = (clone $baseQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $baseQuery)->where('status', 'lost')->count();

        $leadsBySource = (clone $baseQuery)
            ->select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source');

        $recentLeads = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $followupQuery = LeadFollowup::where('status', 'pending');
        $pendingFollowups = $followupQuery->count();
        $overdueFollowups = (clone $followupQuery)->where('followup_date', '<', now())->count();

        $stats = [
            'total_leads' => $totalLeads,
            'status_counts' => [
                'new' => $newLeads,
                'contacted' => $contactedLeads,
                'interested' => $interestedLeads,
                'qualified' => $qualifiedLeads,
                'converted' => $convertedLeads,
                'lost' => $lostLeads,
            ],
            'recent_leads_count' => $recentLeads,
            'leads_by_source' => $leadsBySource,
            'pending_followups' => $pendingFollowups,
            'overdue_followups' => $overdueFollowups,
        ];

        return $this->sendResponse($stats, 'Lead statistics retrieved successfully.');
    }
}
