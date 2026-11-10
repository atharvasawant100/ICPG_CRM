<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;

class CostingReportController extends Controller
{
    /**
     * Display Employee Costing & Workforce Expenditure analytics for the Company Owner (Admin).
     */
    public function index(Request $request)
    {
        // Restricted exclusively to Company Owner (Admin)
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Employee Costing and Payroll Analytics is restricted to Company Owners.');
        }

        // All active users for high-level company metrics
        $allActiveUsers = User::query()
            ->active()
            ->with(['salary'])
            ->get();

        $totalActiveStaff = $allActiveUsers->count();
        $configuredUsers = $allActiveUsers->filter(fn ($u) => $u->salary !== null && (float) $u->salary->gross_salary > 0);
        $configuredCount = $configuredUsers->count();

        // High-level company aggregates
        $totalMonthlyGross = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->gross_salary);
        $totalAnnualRunRate = $totalMonthlyGross * 12;
        $totalMonthlyNet = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->net_salary);
        $totalMonthlyDeductions = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->total_deductions);
        $avgMonthlyCost = $configuredCount > 0 ? $totalMonthlyGross / $configuredCount : 0;
        $avgAnnualCost = $avgMonthlyCost * 12;

        // Statutory & Tax breakdowns
        $totalPf = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->provident_fund);
        $totalTds = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->tds);
        $totalPt = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->professional_tax);
        $totalOtherDeductions = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->other_deductions);

        // Salary components breakdown
        $totalBasic = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->basic_salary);
        $totalHra = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->hra);
        $totalConveyance = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->conveyance_allowance);
        $totalSpecial = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->special_allowance);
        $totalOtherAllowances = (float) $configuredUsers->sum(fn ($u) => (float) $u->salary->other_allowances);

        // Department-wise costing analytics
        $departmentNames = Department::names(activeOnly: false);
        $departmentCosts = [];

        foreach ($departmentNames as $dept) {
            $deptUsers = $allActiveUsers->filter(fn ($u) => strtolower((string) $u->department) === strtolower((string) $dept));
            $deptConfigured = $deptUsers->filter(fn ($u) => $u->salary !== null && (float) $u->salary->gross_salary > 0);
            
            $deptGross = (float) $deptConfigured->sum(fn ($u) => (float) $u->salary->gross_salary);
            $deptNet = (float) $deptConfigured->sum(fn ($u) => (float) $u->salary->net_salary);
            $deptDeductions = (float) $deptConfigured->sum(fn ($u) => (float) $u->salary->total_deductions);
            $deptHeadcount = $deptUsers->count();
            $deptAvg = $deptConfigured->count() > 0 ? $deptGross / $deptConfigured->count() : 0;
            $deptSharePct = $totalMonthlyGross > 0 ? round(($deptGross / $totalMonthlyGross) * 100, 1) : 0;

            $departmentCosts[$dept] = [
                'name' => ucfirst($dept),
                'headcount' => $deptHeadcount,
                'configured_count' => $deptConfigured->count(),
                'monthly_gross' => $deptGross,
                'annual_gross' => $deptGross * 12,
                'monthly_net' => $deptNet,
                'monthly_deductions' => $deptDeductions,
                'avg_salary' => $deptAvg,
                'share_pct' => $deptSharePct,
            ];
        }

        // Sort departments by monthly gross cost descending
        uasort($departmentCosts, fn ($a, $b) => $b['monthly_gross'] <=> $a['monthly_gross']);

        // Salary brackets / tiers distribution
        $brackets = [
            'tier_1' => ['label' => 'Under ₹25k', 'min' => 0, 'max' => 25000, 'count' => 0, 'cost' => 0],
            'tier_2' => ['label' => '₹25k - ₹50k', 'min' => 25000, 'max' => 50000, 'count' => 0, 'cost' => 0],
            'tier_3' => ['label' => '₹50k - ₹1 Lakh', 'min' => 50000, 'max' => 100000, 'count' => 0, 'cost' => 0],
            'tier_4' => ['label' => 'Above ₹1 Lakh', 'min' => 100000, 'max' => PHP_INT_MAX, 'count' => 0, 'cost' => 0],
        ];

        foreach ($configuredUsers as $u) {
            $gross = (float) $u->salary->gross_salary;
            foreach ($brackets as $key => $bracket) {
                if ($gross >= $bracket['min'] && ($bracket['max'] === PHP_INT_MAX ? true : $gross < $bracket['max'])) {
                    $brackets[$key]['count']++;
                    $brackets[$key]['cost'] += $gross;
                    break;
                }
            }
        }

        // Recent historical payroll payouts (last 6 months)
        $historicalPayouts = Payroll::query()
            ->selectRaw('payroll_year, payroll_month, SUM(gross_salary) as total_gross, SUM(net_salary) as total_net, SUM(total_deductions) as total_deductions, COUNT(*) as employee_count, SUM(CASE WHEN payment_status = "paid" THEN net_salary ELSE 0 END) as paid_net, SUM(CASE WHEN payment_status = "pending" THEN net_salary ELSE 0 END) as pending_net')
            ->groupBy('payroll_year', 'payroll_month')
            ->orderByDesc('payroll_year')
            ->orderByDesc('payroll_month')
            ->limit(6)
            ->get();

        // Filtered employee roster
        $search = $request->string('search')->trim()->value();
        $selectedDept = $request->string('department')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $rosterQuery = User::query()
            ->active()
            ->with(['salary'])
            ->orderBy('department')
            ->orderBy('name');

        if ($search) {
            $rosterQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($selectedDept) {
            $rosterQuery->where('department', $selectedDept);
        }

        // Pending salary approvals requiring Admin review
        $pendingApprovals = EmployeeSalary::query()
            ->where('approval_status', 'pending_approval')
            ->with(['user', 'submittedBy'])
            ->latest('updated_at')
            ->get();

        if ($status === 'configured') {
            $rosterQuery->whereHas('salary');
        } elseif ($status === 'approved') {
            $rosterQuery->whereHas('salary', fn ($q) => $q->where('approval_status', 'approved'));
        } elseif ($status === 'pending_approval') {
            $rosterQuery->whereHas('salary', fn ($q) => $q->where('approval_status', 'pending_approval'));
        } elseif ($status === 'rejected') {
            $rosterQuery->whereHas('salary', fn ($q) => $q->where('approval_status', 'rejected'));
        } elseif ($status === 'pending') {
            $rosterQuery->whereDoesntHave('salary');
        }

        $employees = $rosterQuery->paginate(20)->withQueryString();

        return view('admin.reports.costing', [
            'totalActiveStaff' => $totalActiveStaff,
            'configuredCount' => $configuredCount,
            'totalMonthlyGross' => $totalMonthlyGross,
            'totalAnnualRunRate' => $totalAnnualRunRate,
            'totalMonthlyNet' => $totalMonthlyNet,
            'totalMonthlyDeductions' => $totalMonthlyDeductions,
            'avgMonthlyCost' => $avgMonthlyCost,
            'avgAnnualCost' => $avgAnnualCost,
            'totalPf' => $totalPf,
            'totalTds' => $totalTds,
            'totalPt' => $totalPt,
            'totalOtherDeductions' => $totalOtherDeductions,
            'totalBasic' => $totalBasic,
            'totalHra' => $totalHra,
            'totalConveyance' => $totalConveyance,
            'totalSpecial' => $totalSpecial,
            'totalOtherAllowances' => $totalOtherAllowances,
            'departmentCosts' => $departmentCosts,
            'brackets' => $brackets,
            'historicalPayouts' => $historicalPayouts,
            'employees' => $employees,
            'pendingApprovals' => $pendingApprovals,
            'departments' => $departmentNames,
            'search' => $search,
            'selectedDept' => $selectedDept,
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Approve a pending salary structure.
     */
    public function approveSalary(Request $request, EmployeeSalary $salary)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only the Company Owner (Admin) can approve salary structures.');
        }

        $salary->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Salary structure for {$salary->user->name} has been approved.");
    }

    /**
     * Reject a pending salary structure.
     */
    public function rejectSalary(Request $request, EmployeeSalary $salary)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only the Company Owner (Admin) can reject salary structures.');
        }

        $reason = $request->string('rejection_reason')->trim()->value();

        $salary->update([
            'approval_status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => $reason ?: 'Rejected by Company Owner (Admin).',
        ]);

        return back()->with('success', "Salary structure for {$salary->user->name} has been rejected.");
    }
}
