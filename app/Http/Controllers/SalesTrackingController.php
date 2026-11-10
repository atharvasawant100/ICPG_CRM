<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatusLog;
use App\Models\User;
use App\Rbac\DataScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesTrackingController extends Controller
{
    /**
     * Display the Sales Tracking performance dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->can('sales_tracking.view'), 403);

        $isManager = $user->isAdmin() || $user->isManager();

        // 1. Resolve date range filter (today, this_week, this_month, last_month, custom)
        $range = $request->input('range', 'this_month');
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $rangeLabel = 'Today (' . $startDate->format('M d, Y') . ')';
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $rangeLabel = 'This Week (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                $rangeLabel = 'Last Month (' . $startDate->format('F Y') . ')';
                break;
            case 'custom':
                $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
                $rangeLabel = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
                break;
            case 'this_month':
            default:
                $range = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $rangeLabel = 'This Month (' . $startDate->format('F Y') . ')';
                break;
        }

        // 2. Resolve team scope
        $teamMembers = collect();
        $selectedEmployeeId = $request->input('employee_id');

        if ($isManager) {
            $teamIds = DataScope::teamIds($user);
            $teamMembers = User::query()
                ->whereIn('id', $teamIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar', 'department', 'designation']);

            if ($selectedEmployeeId && ! in_array((int) $selectedEmployeeId, $teamIds, true) && ! $user->isAdmin()) {
                $selectedEmployeeId = null;
            }
        } else {
            $selectedEmployeeId = $user->id;
        }

        // 3. Target User IDs for metrics
        $targetUserIds = $selectedEmployeeId
            ? [(int) $selectedEmployeeId]
            : ($isManager ? $teamMembers->pluck('id')->all() : [$user->id]);

        // 4. Calculate Aggregate Metrics for the Target Scope & Date Range
        $leadsQuery = Lead::query()
            ->where(function ($q) use ($targetUserIds) {
                $q->whereIn('assigned_to', $targetUserIds)
                  ->orWhere(function ($q2) use ($targetUserIds) {
                      $q2->whereNull('assigned_to')
                         ->whereIn('created_by', $targetUserIds);
                  });
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalLeads = (clone $leadsQuery)->count();
        $newLeads = (clone $leadsQuery)->where('status', 'new')->count();
        $contactedLeads = (clone $leadsQuery)->where('status', 'contacted')->count();
        $followUpLeads = (clone $leadsQuery)->whereIn('status', ['follow_up', 'interested', 'qualified'])->count();
        $wonLeads = (clone $leadsQuery)->whereIn('status', ['won', 'converted'])->count();
        $lostLeads = (clone $leadsQuery)->where('status', 'lost')->count();

        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;
        $contactRate = $totalLeads > 0 ? round((($totalLeads - $newLeads) / $totalLeads) * 100, 1) : 0;

        // Follow-ups in date range
        $followupsQuery = LeadFollowup::query()
            ->whereIn('user_id', $targetUserIds)
            ->whereBetween('followup_date', [$startDate, $endDate]);

        $followupsScheduled = (clone $followupsQuery)->count();
        $followupsCompleted = (clone $followupsQuery)->where('status', 'completed')->count();
        $followupsOverdue = (clone $followupsQuery)->where('status', 'pending')->where('followup_date', '<', now())->count();
        $followupsCancelled = (clone $followupsQuery)->where('status', 'cancelled')->count();
        $meetingsScheduled = (clone $followupsQuery)->where('followup_type', 'meeting')->count();

        $followupCompletionRate = $followupsScheduled > 0 ? round(($followupsCompleted / $followupsScheduled) * 100, 1) : 0;

        // Revenue from invoices for the scoped customers / rep in date range
        $scopedCustomerIds = Customer::query()
            ->where(function ($q) use ($targetUserIds) {
                $q->whereIn('assigned_to', $targetUserIds)
                  ->orWhereIn('created_by', $targetUserIds);
            })
            ->pluck('id')
            ->merge(
                Lead::query()
                    ->where(function ($q) use ($targetUserIds) {
                        $q->whereIn('assigned_to', $targetUserIds)
                          ->orWhereIn('created_by', $targetUserIds);
                    })
                    ->whereNotNull('converted_to_customer_id')
                    ->pluck('converted_to_customer_id')
            )
            ->unique()
            ->filter();

        $revenueQuery = Invoice::query()
            ->whereBetween('invoice_date', [$startDate, $endDate]);

        if ($selectedEmployeeId) {
            $revenueQuery->where(function ($q) use ($scopedCustomerIds, $selectedEmployeeId) {
                $q->whereIn('customer_id', $scopedCustomerIds)
                  ->orWhere('created_by', $selectedEmployeeId);
            });
        } elseif (! $user->isAdmin()) {
            if ($scopedCustomerIds->isNotEmpty()) {
                $revenueQuery->where(function ($q) use ($scopedCustomerIds, $targetUserIds) {
                    $q->whereIn('customer_id', $scopedCustomerIds)
                      ->orWhereIn('created_by', $targetUserIds);
                });
            } else {
                $revenueQuery->whereIn('created_by', $targetUserIds);
            }
        }

        $totalRevenue = (float) $revenueQuery->sum('total_amount');

        // Status breakdown counts
        $statusBreakdown = [
            'new' => $newLeads,
            'contacted' => $contactedLeads,
            'follow_up' => $followUpLeads,
            'won' => $wonLeads,
            'lost' => $lostLeads,
        ];

        // 5. If Manager: Per-Employee Comparison Table
        $employeeComparison = collect();
        if ($isManager) {
            foreach ($teamMembers as $member) {
                $mLeadsQuery = Lead::query()
                    ->where('assigned_to', $member->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);

                $mTotal = (clone $mLeadsQuery)->count();
                $mWon = (clone $mLeadsQuery)->whereIn('status', ['won', 'converted'])->count();
                $mContacted = (clone $mLeadsQuery)->where('status', 'contacted')->count();
                $mLost = (clone $mLeadsQuery)->where('status', 'lost')->count();
                $mConvRate = $mTotal > 0 ? round(($mWon / $mTotal) * 100, 1) : 0;

                $mFollowups = LeadFollowup::query()
                    ->where('user_id', $member->id)
                    ->whereBetween('followup_date', [$startDate, $endDate]);

                $mScheduled = (clone $mFollowups)->count();
                $mCompleted = (clone $mFollowups)->where('status', 'completed')->count();
                $mMeetings = (clone $mFollowups)->where('followup_type', 'meeting')->count();

                $employeeComparison->push((object) [
                    'user' => $member,
                    'total_leads' => $mTotal,
                    'contacted' => $mContacted,
                    'won' => $mWon,
                    'lost' => $mLost,
                    'conversion_rate' => $mConvRate,
                    'followups_scheduled' => $mScheduled,
                    'followups_completed' => $mCompleted,
                    'meetings_scheduled' => $mMeetings,
                ]);
            }

            // Sort comparison table by Won leads descending
            $employeeComparison = $employeeComparison->sortByDesc('won')->values();
        }

        // 6. Recent Won Leads
        $recentWonLeads = Lead::with(['assignedUser', 'creator'])
            ->where(function ($q) use ($targetUserIds) {
                $q->whereIn('assigned_to', $targetUserIds)
                  ->orWhereIn('created_by', $targetUserIds);
            })
            ->whereIn('status', ['won', 'converted'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // 7. Upcoming Follow-ups & Meetings for the scoped user(s)
        $upcomingEvents = LeadFollowup::with(['lead', 'user'])
            ->whereIn('user_id', $targetUserIds)
            ->where('status', 'pending')
            ->where('followup_date', '>=', now())
            ->orderBy('followup_date', 'asc')
            ->take(5)
            ->get();

        return view('sales-tracking.index', compact(
            'isManager',
            'range',
            'rangeLabel',
            'startDate',
            'endDate',
            'teamMembers',
            'selectedEmployeeId',
            'totalLeads',
            'newLeads',
            'contactedLeads',
            'followUpLeads',
            'wonLeads',
            'lostLeads',
            'conversionRate',
            'contactRate',
            'followupsScheduled',
            'followupsCompleted',
            'followupsOverdue',
            'followupsCancelled',
            'meetingsScheduled',
            'followupCompletionRate',
            'statusBreakdown',
            'totalRevenue',
            'employeeComparison',
            'recentWonLeads',
            'upcomingEvents'
        ));
    }
}
