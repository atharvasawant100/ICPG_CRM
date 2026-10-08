<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $customerQuery = Customer::query();
        $leadQuery = Lead::query();
        $expenseQuery = Expense::query();
        $invoiceQuery = Invoice::query();

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
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
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
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->where('expenses.status', 'approved')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'category');

        $monthlyExpenseTrends = (clone $expenseQuery)
            ->select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->where('expense_date', '>=', now()->subMonths(6)->startOfMonth())
            ->where('status', 'approved')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalInvoiceAmount = (clone $invoiceQuery)->whereNotIn('status', ['cancelled'])->sum('total_amount');
        $paidInvoices = (float) DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->whereNotIn('invoices.status', ['cancelled'])
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
            ->selectRaw('COALESCE(SUM(GREATEST(invoices.total_amount - COALESCE(p.paid_amount, 0), 0)), 0) as overdue_due_total')
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
            ->select(DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as month'), DB::raw('SUM(total_amount) as total'))
            ->where('invoice_date', '>=', now()->subMonths(6)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topPayingCustomers = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereNotIn('invoices.status', ['cancelled'])
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

        $currentMonthLeads = Lead::whereBetween('created_at', [$currentMonthStart . ' 00:00:00', $currentMonthEnd . ' 23:59:59'])->count();
        $prevMonthLeads = Lead::whereBetween('created_at', [$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59'])->count();

        $currentMonthConverted = Lead::where('status', 'converted')
            ->whereBetween('updated_at', [$currentMonthStart . ' 00:00:00', $currentMonthEnd . ' 23:59:59'])
            ->count();
        $prevMonthConverted = Lead::where('status', 'converted')
            ->whereBetween('updated_at', [$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59'])
            ->count();

        $currentMonthInvoices = Invoice::whereNotIn('status', ['cancelled'])
            ->whereBetween('invoice_date', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $prevMonthInvoices = Invoice::whereNotIn('status', ['cancelled'])
            ->whereBetween('invoice_date', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $currentMonthRevenue = (float) DB::table('invoice_payments')
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');
        $prevMonthRevenue = (float) DB::table('invoice_payments')
            ->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        $currentMonthExpenses = (float) Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');
        $prevMonthExpenses = (float) Expense::where('status', 'approved')
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
            ->select(DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->groupBy('month')
            ->pluck('total', 'month');
        $expenseByMonth = Expense::query()
            ->select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
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
            ->latest()
            ->take(8)
            ->get();

        $upcomingFollowups = LeadFollowup::has('lead')
            ->where('status', 'pending')
            ->where('followup_date', '>=', now())
            ->with(['lead:id,name,email,company', 'user:id,name'])
            ->orderBy('followup_date')
            ->take(10)
            ->get();

        $upcomingMeetings = LeadFollowup::has('lead')
            ->where('status', 'pending')
            ->where('followup_type', 'meeting')
            ->where('followup_date', '>=', now())
            ->with(['lead:id,name,email,company,phone', 'user:id,name'])
            ->orderBy('followup_date')
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
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
            'upcomingMeetings'
        ));
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
