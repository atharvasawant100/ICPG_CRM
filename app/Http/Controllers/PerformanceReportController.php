<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ManagerPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReportController extends Controller
{
    public function __construct(
        protected ManagerPerformanceService $performanceService
    ) {}

    /**
     * Display the Monthly Manager Performance Tracking Report.
     */
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Access allowed only for Admin and Department Managers
        if (! $currentUser->isAdmin() && ! $currentUser->isManager()) {
            abort(403, 'Access denied. The performance report is reserved for management and administrators.');
        }

        $now = Carbon::now();
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);

        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        if ($year < 2020 || $year > 2035) {
            $year = $now->year;
        }

        $reportingManagers = $this->performanceService->getReportingManagers($currentUser);

        // Determine which manager to display
        $selectedManagerId = $request->input('manager_id');
        $isAllView = false;
        $selectedManager = null;
        $report = null;
        $allManagersSummary = [];

        if ($currentUser->isAdmin()) {
            if ($selectedManagerId === 'all' || (! $selectedManagerId && $reportingManagers->count() > 1)) {
                $isAllView = true;
                $allManagersSummary = $this->performanceService->getAllManagersSummary($year, $month);
                $selectedManagerId = 'all';
            } else {
                $targetId = $selectedManagerId ? (int) $selectedManagerId : $reportingManagers->first()?->id;
                $selectedManager = $reportingManagers->firstWhere('id', $targetId) ?? $reportingManagers->first();
                if ($selectedManager) {
                    $selectedManagerId = $selectedManager->id;
                    $report = $this->performanceService->getMonthlyReport($selectedManager, $year, $month);
                }
            }
        } else {
            // Manager viewing their own report
            $selectedManager = $currentUser;
            $selectedManagerId = $currentUser->id;
            $report = $this->performanceService->getMonthlyReport($selectedManager, $year, $month);
        }

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $years = range($now->year - 2, $now->year + 1);

        return view('reports.performance.index', compact(
            'currentUser',
            'reportingManagers',
            'selectedManager',
            'selectedManagerId',
            'isAllView',
            'report',
            'allManagersSummary',
            'year',
            'month',
            'months',
            'years'
        ));
    }
}

