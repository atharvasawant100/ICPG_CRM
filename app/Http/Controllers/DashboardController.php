<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\Expense;
use App\Models\Invoice;
use App\Rbac\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        /*
         | CHANGED: the dashboard is now permission-gated on dashboard.view.
         | Checked via Gate directly rather than authorize() with a bare policy
         | class, because Gate cannot resolve an unregistered class argument
         | and would deny every non-admin. Admin is unaffected via Gate::before.
         */
        abort_unless(Gate::allows('dashboard.view'), 403);

        $user = $request->user();

        $customerQuery = Customer::query();
        $leadQuery = Lead::query();
        $expenseQuery = Expense::query();
        $invoiceQuery = Invoice::query();

        /*
         | Apply the data scope to every KPI query.
         |
         | For the admin the scope is "all", so applyDataScope() returns the
         | query untouched and every number below is identical to before this
         | change. Other roles only ever see figures for records they are
         | allowed to see.
         */
        $customerQuery->applyDataScope($user, 'customers');
        $leadQuery->applyDataScope($user, 'leads');
        $expenseQuery->applyDataScope($user, 'expenses');
        $invoiceQuery->applyDataScope($user, 'invoices');

        $totalCustomers = (clone $customerQuery)->count();
        $totalLeads = (clone $leadQuery)->count();
        $totalExpenses = (clone $expenseQuery)->count();
        $totalInvoices = (clone $invoiceQuery)->count();

        $newLeads = (clone $leadQuery)->where('status', 'new')->count();
        $contactedLeads = (clone $leadQuery)->where('status', 'contacted')->count();
        $interestedLeads = (clone $leadQuery)->where('status', 'interested')->count();
        $qualifiedLeads = (clone $leadQuery)->where('status', 'qualified')->count();
        $convertedLeads = (clone $leadQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $leadQuery)->where('status', 'lost')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        $leadsByStatus = [
            'New' => $newLeads,
            'Contacted' => $contactedLeads,
            'Interested' => $interestedLeads,
            'Qualified' => $qualifiedLeads,
            'Converted' => $convertedLeads,
            'Lost' => $lostLeads,
        ];

        $leadsBySource = (clone $leadQuery)
            ->select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source');

        $monthlyLeadTrends = (clone $leadQuery)
            ->select(DB::raw(\App\Support\MonthExpression::for('created_at').' as month'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalExpenseAmount = (clone $expenseQuery)->sum('amount');
        $approvedExpenses = (clone $expenseQuery)->where('status', 'approved')->sum('amount');
        $pendingExpenses = (clone $expenseQuery)->where('status', 'pending')->sum('amount');
        $rejectedExpenses = (clone $expenseQuery)->where('status', 'rejected')->sum('amount');

        $expensesByStatus = [
            'Approved' => (clone $expenseQuery)->where('status', 'approved')->count(),
            'Pending' => (clone $expenseQuery)->where('status', 'pending')->count(),
            'Rejected' => (clone $expenseQuery)->where('status', 'rejected')->count(),
        ];

        $expensesByCategory = Expense::query()
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->applyDataScope($user, 'expenses')
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->where('expenses.status', 'approved')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'category');

        $monthlyExpenseTrends = (clone $expenseQuery)
            ->select(DB::raw(\App\Support\MonthExpression::for('expense_date').' as month'), DB::raw('SUM(amount) as total'))
            ->where('expense_date', '>=', now()->subMonths(6)->startOfMonth())
            ->where('status', 'approved')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalInvoiceAmount = (clone $invoiceQuery)->whereNotIn('status', ['cancelled'])->sum('total_amount');
        /*
         | The remaining aggregates below are raw query-builder joins, which
         | cannot use the Eloquent scope helper. Compute the visible owner ids
         | once and filter on them. For the admin this is null, meaning "no
         | restriction", so the totals are byte-identical to before.
         */
        $invoiceOwnerIds = null;
        $customerOwnerIds = null;

        if (\App\Rbac\DataScope::scopeFor($user, 'invoices') !== \App\Rbac\DataScope::ALL) {
            $invoiceOwnerIds = \App\Rbac\DataScope::scopeFor($user, 'invoices') === \App\Rbac\DataScope::TEAM
                ? \App\Rbac\DataScope::teamIds($user)
                : [$user->id];
        }

        if (\App\Rbac\DataScope::scopeFor($user, 'customers') !== \App\Rbac\DataScope::ALL) {
            $customerOwnerIds = \App\Rbac\DataScope::scopeFor($user, 'customers') === \App\Rbac\DataScope::TEAM
                ? \App\Rbac\DataScope::teamIds($user)
                : [$user->id];
        }

        $paidInvoices = (float) DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->when(
                $invoiceOwnerIds !== null,
                fn ($q) => $q->whereIn('invoices.created_by', $invoiceOwnerIds)
            )
            ->sum('invoice_payments.amount');

        $paidByInvoiceSub = DB::table('invoice_payments')
            ->select('invoice_id', DB::raw('SUM(amount) as paid_amount'))
            ->groupBy('invoice_id');

        // Outstanding = invoice total minus payments, for any unpaid invoice past due date
        $overdueInvoices = (float) DB::table('invoices')
            ->leftJoinSub($paidByInvoiceSub, 'p', function ($join) {
                $join->on('p.invoice_id', '=', 'invoices.id');
            })
            ->whereDate('invoices.due_date', '<', now()->toDateString())
            ->whereNotIn('invoices.status', ['paid', 'cancelled'])
            ->when(
                $invoiceOwnerIds !== null,
                fn ($q) => $q->whereIn('invoices.created_by', $invoiceOwnerIds)
            )
            ->selectRaw('COALESCE(SUM('.\App\Support\MonthExpression::greatest(
                'invoices.total_amount - COALESCE(p.paid_amount, 0)',
                '0'
            ).'), 0) as overdue_due_total')
            ->value('overdue_due_total');

        $pendingInvoices = (clone $invoiceQuery)->whereIn('status', ['sent', 'partial'])->sum('total_amount');
        $unpaidInvoices = max(0, (float) $totalInvoiceAmount - (float) $paidInvoices);

        $invoiceCountNonCancelled = (clone $invoiceQuery)->whereNotIn('status', ['cancelled'])->count();
        $averageInvoiceValue = $invoiceCountNonCancelled > 0
            ? round((float) $totalInvoiceAmount / $invoiceCountNonCancelled, 2)
            : 0;

        $invoicesByStatus = [
            'Draft' => (clone $invoiceQuery)->where('status', 'draft')->count(),
            'Sent' => (clone $invoiceQuery)->where('status', 'sent')->count(),
            'Partial' => (clone $invoiceQuery)->where('status', 'partial')->count(),
            'Paid' => (clone $invoiceQuery)->where('status', 'paid')->count(),
            'Overdue' => (clone $invoiceQuery)->overdue()->count(),
        ];

        $monthlyInvoiceTrends = (clone $invoiceQuery)
            ->select(DB::raw(\App\Support\MonthExpression::for('invoice_date').' as month'), DB::raw('SUM(total_amount) as total'))
            ->where('invoice_date', '>=', now()->subMonths(6)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topPayingCustomers = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->when(
                $invoiceOwnerIds !== null,
                fn ($q) => $q->whereIn('invoices.created_by', $invoiceOwnerIds)
            )
            ->when(
                $customerOwnerIds !== null,
                fn ($q) => $q->whereIn('customers.created_by', $customerOwnerIds)
            )
            ->select(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.company',
                DB::raw('SUM(invoice_payments.amount) as total_paid')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.company')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->get();

        // Month-over-month metrics
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthEnd = now()->endOfMonth()->toDateString();
        $prevMonthStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $prevMonthEnd = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $currentMonthLeads = Lead::applyDataScope($user, 'leads')
            ->whereBetween('created_at', [$currentMonthStart . ' 00:00:00', $currentMonthEnd . ' 23:59:59'])->count();
        $prevMonthLeads = Lead::applyDataScope($user, 'leads')
            ->whereBetween('created_at', [$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59'])->count();

        $currentMonthConverted = Lead::applyDataScope($user, 'leads')->where('status', 'converted')
            ->whereBetween('updated_at', [$currentMonthStart . ' 00:00:00', $currentMonthEnd . ' 23:59:59'])
            ->count();
        $prevMonthConverted = Lead::applyDataScope($user, 'leads')->where('status', 'converted')
            ->whereBetween('updated_at', [$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59'])
            ->count();

        $currentMonthInvoices = Invoice::applyDataScope($user, 'invoices')->whereNotIn('status', ['cancelled'])
            ->whereBetween('invoice_date', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $prevMonthInvoices = Invoice::applyDataScope($user, 'invoices')->whereNotIn('status', ['cancelled'])
            ->whereBetween('invoice_date', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $currentMonthRevenue = (float) DB::table('invoice_payments')
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');
        $prevMonthRevenue = (float) DB::table('invoice_payments')
            ->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        $currentMonthExpenses = (float) Expense::applyDataScope($user, 'expenses')->where('status', 'approved')
            ->whereBetween('expense_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');
        $prevMonthExpenses = (float) Expense::applyDataScope($user, 'expenses')->where('status', 'approved')
            ->whereBetween('expense_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        $currentMonthProfit = $currentMonthRevenue - $currentMonthExpenses;
        $prevMonthProfit = $prevMonthRevenue - $prevMonthExpenses;

        $growth = [
            'leads' => $this->percentChange($currentMonthLeads, $prevMonthLeads),
            'converted' => $this->percentChange($currentMonthConverted, $prevMonthConverted),
            'invoices' => $this->percentChange($currentMonthInvoices, $prevMonthInvoices),
            'revenue' => $this->percentChange($currentMonthRevenue, $prevMonthRevenue),
            'expenses' => $this->percentChange($currentMonthExpenses, $prevMonthExpenses),
            'profit' => $this->percentChange($currentMonthProfit, $prevMonthProfit),
            'conversion' => $this->percentChange(
                $currentMonthLeads > 0 ? ($currentMonthConverted / $currentMonthLeads) * 100 : 0,
                $prevMonthLeads > 0 ? ($prevMonthConverted / $prevMonthLeads) * 100 : 0
            ),
            'paid' => $this->percentChange($currentMonthRevenue, $prevMonthRevenue),
            'overdue' => null,
        ];

        // Monthly P&L (last 6 months)
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));
        $revenueByMonth = DB::table('invoice_payments')
            ->select(DB::raw(\App\Support\MonthExpression::for('payment_date').' as month'), DB::raw('SUM(amount) as total'))
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->groupBy('month')
            ->pluck('total', 'month');
        $expenseByMonth = Expense::query()
            ->applyDataScope($user, 'expenses')
            ->select(DB::raw(\App\Support\MonthExpression::for('expense_date').' as month'), DB::raw('SUM(amount) as total'))
            ->where('status', 'approved')
            ->where('expense_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyPnL = $months->map(function ($month) use ($revenueByMonth, $expenseByMonth) {
            $rev = (float) ($revenueByMonth[$month] ?? 0);
            $exp = (float) ($expenseByMonth[$month] ?? 0);
            return [
                'month' => $month,
                'revenue' => $rev,
                'expenses' => $exp,
                'profit' => $rev - $exp,
            ];
        })->values();

        $activeCustomers = (clone $customerQuery)->where('status', 'active')->count();
        $inactiveCustomers = (clone $customerQuery)->where('status', 'inactive')->count();

        $recentActivities = LeadActivity::has('lead')->with([
            'lead:id,name,email,company',
            'user:id,name'
        ])
            ->whereHas('lead', fn ($q) => $q->applyDataScope($user, 'leads'))
            ->latest()
            ->take(8)
            ->get();

        $upcomingFollowups = LeadFollowup::has('lead')
            ->where('status', 'pending')
            ->where('followup_date', '>=', now())
            ->with(['lead:id,name,email,company', 'user:id,name'])
            ->whereHas('lead', fn ($q) => $q->applyDataScope($user, 'leads'))
            ->orderBy('followup_date')
            ->take(10)
            ->get();

        $upcomingMeetings = LeadFollowup::has('lead')
            ->where('status', 'pending')
            ->where('followup_type', 'meeting')
            ->where('followup_date', '>=', now())
            ->with(['lead:id,name,email,company,phone', 'user:id,name'])
            ->whereHas('lead', fn ($q) => $q->applyDataScope($user, 'leads'))
            ->orderBy('followup_date')
            ->take(10)
            ->get();

        $roleSummary = $this->buildRoleSummary();

        // KPI Detail Lists for Click-through Breakdown
        $collectedPaymentsList = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->when(
                $invoiceOwnerIds !== null,
                fn ($q) => $q->whereIn('invoices.created_by', $invoiceOwnerIds)
            )
            ->select(
                'invoice_payments.id',
                'invoice_payments.invoice_id',
                'invoice_payments.amount',
                'invoice_payments.payment_date',
                'invoice_payments.payment_method',
                'invoice_payments.reference_number',
                'invoice_payments.notes',
                'invoices.invoice_number',
                'invoices.status as invoice_status',
                'invoices.total_amount as invoice_total',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.company as customer_company'
            )
            ->orderByDesc('invoice_payments.payment_date')
            ->orderByDesc('invoice_payments.id')
            ->limit(50)
            ->get();

        $overdueInvoicesList = Invoice::with(['customer', 'payments'])
            ->applyDataScope($user, 'invoices')
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($invoice) {
                $paid = (float) $invoice->payments->sum('amount');
                $due = max(0, (float) $invoice->total_amount - $paid);
                $daysOverdue = abs(now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($invoice->due_date)->startOfDay(), false));
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer?->name,
                    'customer_company' => $invoice->customer?->company,
                    'due_date' => $invoice->due_date?->format('M d, Y'),
                    'days_overdue' => $daysOverdue,
                    'total_amount' => (float) $invoice->total_amount,
                    'paid_amount' => $paid,
                    'due_amount' => $due,
                    'status' => $invoice->status,
                ];
            });

        $monthlyRevenueList = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereBetween('invoice_payments.payment_date', [$currentMonthStart, $currentMonthEnd])
            ->when(
                $invoiceOwnerIds !== null,
                fn ($q) => $q->whereIn('invoices.created_by', $invoiceOwnerIds)
            )
            ->select(
                'invoice_payments.id',
                'invoice_payments.invoice_id',
                'invoice_payments.amount',
                'invoice_payments.payment_date',
                'invoice_payments.payment_method',
                'invoice_payments.reference_number',
                'invoices.invoice_number',
                'invoices.status as invoice_status',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.company as customer_company'
            )
            ->orderByDesc('invoice_payments.payment_date')
            ->orderByDesc('invoice_payments.id')
            ->get();

        $monthlyExpensesList = Expense::with('category')
            ->applyDataScope($user, 'expenses')
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$currentMonthStart, $currentMonthEnd])
            ->orderByDesc('expense_date')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'title' => $expense->description ?: 'Expense',
                    'category' => $expense->category?->name ?? 'General',
                    'expense_date' => $expense->expense_date?->format('M d, Y'),
                    'amount' => (float) $expense->amount,
                    'status' => $expense->status,
                ];
            });

        // HR & Employee workforce statistics
        $todayDate = \Carbon\Carbon::today()->toDateString();
        $hrTotalEmployees = \App\Models\User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->count();

        $hrPresentToday = \App\Models\Attendance::whereDate('date', $todayDate)
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->count();

        $hrLateToday = \App\Models\Attendance::whereDate('date', $todayDate)
            ->where('status', 'late')
            ->count();

        $hrApprovedLeaveToday = \App\Models\LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->count();
        $hrAttendanceLeaveToday = \App\Models\Attendance::whereDate('date', $todayDate)
            ->where('status', 'on_leave')
            ->count();
        $hrOnLeaveToday = max($hrApprovedLeaveToday, $hrAttendanceLeaveToday);

        $hrPendingLeaves = \App\Models\LeaveRequest::where('status', 'pending')->count();

        $hrStats = [
            'total_employees' => $hrTotalEmployees,
            'present_today' => $hrPresentToday,
            'late_today' => $hrLateToday,
            'on_leave_today' => $hrOnLeaveToday,
            'pending_leaves' => $hrPendingLeaves,
        ];

        $employeesByDepartment = \App\Models\User::active()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();

        // Manager portal overview metrics (specifically for department managers such as Development and Marketing)
        $isManager = $user->isManager();

        // 1. Team container metrics: team members of their own department
        $deptTeamQuery = \App\Models\User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE));

        if ($user->department) {
            $deptTeamQuery->whereRaw('LOWER(department) = ?', [strtolower($user->department)])
                ->where('id', '!=', $user->id);
        } else {
            $deptTeamQuery->where('manager_id', $user->id);
        }

        $deptTeamMembersCount = (clone $deptTeamQuery)->count();
        $deptTeamMemberIds = (clone $deptTeamQuery)->pluck('id')->all();

        $deptTeamPresentToday = \App\Models\Attendance::whereIn('user_id', $deptTeamMemberIds)
            ->whereDate('date', $todayDate)
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->count();

        $deptTeamOnLeaveToday = \App\Models\LeaveRequest::whereIn('user_id', $deptTeamMemberIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->count();

        // 2. Task container metrics: tasks assigned to their team
        $allTeamIds = array_unique(array_merge($deptTeamMemberIds, [$user->id]));
        $teamTasksQuery = \App\Models\Task::with(['assignee', 'creator'])
            ->where(function ($q) use ($allTeamIds, $user) {
                $q->whereIn('assigned_to', $allTeamIds)
                    ->orWhere('assigned_by', $user->id);
                if ($user->department) {
                    $q->orWhereRaw('LOWER(department) = ?', [strtolower($user->department)]);
                }
            });

        $teamTasksTotalCount = (clone $teamTasksQuery)->count();
        $teamTasksActiveCount = (clone $teamTasksQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $teamTasksInProgressCount = (clone $teamTasksQuery)->where('status', 'in_progress')->count();
        $teamTasksPendingCount = (clone $teamTasksQuery)->where('status', 'pending')->count();
        $teamTasksCompletedCount = (clone $teamTasksQuery)->where('status', 'completed')->count();

        $teamTasksList = (clone $teamTasksQuery)
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->latest('created_at')
            ->take(6)
            ->get();

        // Daily tasks completed today by team (automatically vanishes next day)
        $dailyTeamTasksCompleted = (clone $teamTasksQuery)
            ->where('status', 'completed')
            ->whereDate('completed_at', $todayDate)
            ->latest('completed_at')
            ->get();
        $dailyTeamTasksCompletedCount = $dailyTeamTasksCompleted->count();

        // 3. Pending paid leaves of the manager (leave balance)
        $managerPaidLeaveBalances = \App\Models\EmployeeLeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->whereHas('leaveType', fn ($q) => $q->where('is_paid', true)->where('is_active', true))
            ->with('leaveType')
            ->get();

        if ($managerPaidLeaveBalances->isEmpty() && ! $user->isAdmin()) {
            $paidTypes = \App\Models\LeaveType::where('is_active', true)->where('is_paid', true)->get();
            foreach ($paidTypes as $type) {
                \App\Models\EmployeeLeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $type->id, 'year' => now()->year],
                    [
                        'allocated_days' => (float) $type->days_allowed_per_year,
                        'used_days' => 0.0,
                        'pending_days' => 0.0,
                        'remaining_days' => (float) $type->days_allowed_per_year,
                    ]
                );
            }
            $managerPaidLeaveBalances = \App\Models\EmployeeLeaveBalance::where('user_id', $user->id)
                ->where('year', now()->year)
                ->whereHas('leaveType', fn ($q) => $q->where('is_paid', true)->where('is_active', true))
                ->with('leaveType')
                ->get();
        }

        $managerPaidLeavesRemaining = (float) $managerPaidLeaveBalances->sum('remaining_days');
        $managerPaidLeavesAllocated = (float) $managerPaidLeaveBalances->sum('allocated_days');
        $managerPaidLeavesUsed = (float) $managerPaidLeaveBalances->sum('used_days');

        $managerPendingLeaveRequestsCount = \App\Models\LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $managerPendingLeaveRequestsDays = (float) \App\Models\LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('total_days');

        // Employee dashboard metrics (for employees in Development, Marketing, etc.)
        $isEmployee = ! $user->isAdmin() && ! $isManager;

        // 1. Working hours (today & current month)
        $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();

        $employeeTodayWorkingMinutes = 0;
        if ($todayAttendance) {
            if ($todayAttendance->total_minutes > 0) {
                $employeeTodayWorkingMinutes = (int) $todayAttendance->total_minutes;
            } elseif ($todayAttendance->check_in && ! $todayAttendance->check_out) {
                $checkInTime = \Carbon\Carbon::parse($todayAttendance->check_in);
                $employeeTodayWorkingMinutes = max(0, $checkInTime->diffInMinutes(now()));
            }
        }
        $employeeTodayHours = floor($employeeTodayWorkingMinutes / 60);
        $employeeTodayMins = $employeeTodayWorkingMinutes % 60;
        $employeeTodayWorkingHoursFormatted = "{$employeeTodayHours}h {$employeeTodayMins}m";

        $employeeMonthWorkingMinutes = (int) \App\Models\Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_minutes');

        if ($todayAttendance && $todayAttendance->check_in && ! $todayAttendance->check_out) {
            $employeeMonthWorkingMinutes += $employeeTodayWorkingMinutes;
        }
        $employeeMonthHours = floor($employeeMonthWorkingMinutes / 60);
        $employeeMonthMins = $employeeMonthWorkingMinutes % 60;
        $employeeMonthWorkingHoursFormatted = "{$employeeMonthHours}h {$employeeMonthMins}m";

        // 2. Leave Remaining (for employee)
        $employeePaidLeaveBalances = \App\Models\EmployeeLeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->whereHas('leaveType', fn ($q) => $q->where('is_paid', true)->where('is_active', true))
            ->with('leaveType')
            ->get();

        if ($employeePaidLeaveBalances->isEmpty() && ! $user->isAdmin()) {
            $paidTypes = \App\Models\LeaveType::where('is_active', true)->where('is_paid', true)->get();
            foreach ($paidTypes as $type) {
                \App\Models\EmployeeLeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $type->id, 'year' => now()->year],
                    [
                        'allocated_days' => (float) $type->days_allowed_per_year,
                        'used_days' => 0.0,
                        'pending_days' => 0.0,
                        'remaining_days' => (float) $type->days_allowed_per_year,
                    ]
                );
            }
            $employeePaidLeaveBalances = \App\Models\EmployeeLeaveBalance::where('user_id', $user->id)
                ->where('year', now()->year)
                ->whereHas('leaveType', fn ($q) => $q->where('is_paid', true)->where('is_active', true))
                ->with('leaveType')
                ->get();
        }

        $employeeLeaveRemaining = (float) $employeePaidLeaveBalances->sum('remaining_days');
        $employeeLeaveAllocated = (float) $employeePaidLeaveBalances->sum('allocated_days');
        $employeeLeaveUsed = (float) $employeePaidLeaveBalances->sum('used_days');

        // 3. Attendance (current month summary)
        $employeeMonthPresentDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->count();
        $employeeMonthLateDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->where('status', 'late')
            ->count();
        $employeeMonthOnLeaveDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->where('status', 'on_leave')
            ->count();

        // 4. Tasks Completed
        $employeeTasksCompleted = \App\Models\Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->count();
        $employeeTasksTotal = \App\Models\Task::where('assigned_to', $user->id)->count();

        // 5. Overdue Tasks
        $employeeTasksOverdue = \App\Models\Task::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $todayDate)
            ->count();
        $employeeTasksActive = \App\Models\Task::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // Employee's tasks list
        $employeeTasksList = \App\Models\Task::where('assigned_to', $user->id)
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'isManager',
            'isEmployee',
            'todayAttendance',
            'employeeTodayWorkingHoursFormatted',
            'employeeMonthWorkingHoursFormatted',
            'employeeLeaveRemaining',
            'employeeLeaveAllocated',
            'employeeLeaveUsed',
            'employeeMonthPresentDays',
            'employeeMonthLateDays',
            'employeeMonthOnLeaveDays',
            'employeeTasksCompleted',
            'employeeTasksTotal',
            'employeeTasksOverdue',
            'employeeTasksActive',
            'employeeTasksList',
            'deptTeamMembersCount',
            'deptTeamPresentToday',
            'deptTeamOnLeaveToday',
            'teamTasksTotalCount',
            'teamTasksActiveCount',
            'teamTasksInProgressCount',
            'teamTasksPendingCount',
            'teamTasksCompletedCount',
            'teamTasksList',
            'dailyTeamTasksCompleted',
            'dailyTeamTasksCompletedCount',
            'managerPaidLeavesRemaining',
            'managerPaidLeavesAllocated',
            'managerPaidLeavesUsed',
            'managerPendingLeaveRequestsCount',
            'managerPendingLeaveRequestsDays',
            'managerPaidLeaveBalances',
            'hrStats',
            'employeesByDepartment',
            'collectedPaymentsList',
            'overdueInvoicesList',
            'monthlyRevenueList',
            'monthlyExpensesList',
            'totalCustomers',
            'totalLeads',
            'totalExpenses',
            'totalInvoices',
            'newLeads',
            'contactedLeads',
            'interestedLeads',
            'qualifiedLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate',
            'leadsByStatus',
            'leadsBySource',
            'monthlyLeadTrends',
            'totalExpenseAmount',
            'approvedExpenses',
            'pendingExpenses',
            'rejectedExpenses',
            'expensesByStatus',
            'expensesByCategory',
            'monthlyExpenseTrends',
            'totalInvoiceAmount',
            'paidInvoices',
            'unpaidInvoices',
            'overdueInvoices',
            'pendingInvoices',
            'averageInvoiceValue',
            'invoicesByStatus',
            'monthlyInvoiceTrends',
            'topPayingCustomers',
            'monthlyPnL',
            'growth',
            'currentMonthRevenue',
            'currentMonthExpenses',
            'currentMonthProfit',
            'activeCustomers',
            'inactiveCustomers',
            'recentActivities',
            'upcomingFollowups',
            'upcomingMeetings',
            'roleSummary'
        ));
    }

    /**
     * Build the "Users & Roles" summary shown at the bottom of the dashboard.
     *
     * Both columns are derived from real data, never hard-coded text:
     *  - "Can access" groups the role's actual permissions by module.
     *  - "Data scope" comes from the role_module_scopes table via DataScope,
     *    so it always matches what the scope helper will enforce.
     */
    private function buildRoleSummary(): array
    {
        $scopeLabels = [
            \App\Rbac\DataScope::OWN => 'Own records',
            \App\Rbac\DataScope::TEAM => 'Own + direct reports',
            \App\Rbac\DataScope::ALL => 'All records',
        ];

        $summary = [];

        foreach (Role::orderBy('name')->get() as $role) {
            $isAdmin = $role->name === Rbac::ADMIN_ROLE;

            if ($isAdmin) {
                $access = 'All modules, users, roles, settings';
                $scope = 'All records';
            } else {
                // Group the role's real permissions by module.
                $byModule = [];

                foreach ($role->permissions->pluck('name') as $permissionName) {
                    [$module, $action] = array_pad(explode('.', $permissionName, 2), 2, null);

                    $byModule[$module][] = $action;
                }

                if ($byModule === []) {
                    $access = 'No modules yet';
                } else {
                    $parts = [];

                    foreach ($byModule as $module => $actions) {
                        // sort(), not ksort(): the action list is a sequential
                        // array, so ksort is a no-op and would leave the
                        // output in database order.
                        $actions = array_unique($actions);
                        sort($actions);

                        $label = ucfirst(str_replace('_', ' ', $module));
                        $parts[] = $label.' ('.implode(', ', $actions).')';
                    }

                    $access = implode(' · ', $parts);
                }

                $scopes = [];

                foreach (array_keys(config('rbac.owner_columns', [])) as $module) {
                    $resolved = \App\Rbac\DataScope::scopeForRole($role->name, $module) ?? \App\Rbac\DataScope::OWN;

                    $scopes[$resolved][] = str_replace('_', ' ', $module);
                }

                if ($scopes === []) {
                    $scope = 'Not applicable';
                } else {
                    $scopeParts = [];

                    foreach ($scopes as $value => $modules) {
                        $scopeParts[] = ($scopeLabels[$value] ?? 'Own records').': '.implode(', ', $modules);
                    }

                    $scope = implode(' · ', $scopeParts);
                }
            }

            $summary[] = [
                'model' => $role,
                'name' => $role->name,
                'is_admin' => $isAdmin,
                'users_count' => $role->users()->count(),
                'access' => $access,
                'scope' => $scope,
            ];
        }

        return $summary;
    }

    /**
     * Calculate percentage change between two values.
     */
    private function percentChange(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            if ($current == 0) {
                return 0.0;
            }
            return 100.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }
}
