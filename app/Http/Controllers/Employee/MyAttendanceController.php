<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyAttendanceController extends Controller
{
    /**
     * Display personal attendance history and monthly summary for the logged-in user.
     */
    public function index(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.attendance.index')->with('info', 'Admin accounts do not track personal attendance.');
        }

        abort_unless(Gate::allows('my_attendance.view'), 403);

        $user = $request->user();
        $year = (int) ($request->input('year') ?: now()->year);
        $month = (int) ($request->input('month') ?: now()->month);

        $selectedMonth = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $selectedMonth->copy()->startOfMonth();
        $endOfMonth = $selectedMonth->copy()->endOfMonth();

        // Today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        // Month's attendance records (ordered newest to oldest)
        $monthlyRecords = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->get();

        // Monthly KPI statistics
        $presentDays = $monthlyRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
        $lateDays = $monthlyRecords->where('status', 'late')->count();
        $halfDays = $monthlyRecords->where('status', 'half_day')->count();
        $leaveDays = $monthlyRecords->where('status', 'on_leave')->count();

        $totalMinutes = $monthlyRecords->sum('total_minutes');
        $totalHours = floor($totalMinutes / 60);
        $remainingMins = $totalMinutes % 60;
        $totalWorkingHoursFormatted = "{$totalHours}h {$remainingMins}m";

        return view('employee.attendance.index', [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'todayAttendance' => $todayAttendance,
            'monthlyRecords' => $monthlyRecords,
            'presentDays' => $presentDays,
            'lateDays' => $lateDays,
            'halfDays' => $halfDays,
            'leaveDays' => $leaveDays,
            'totalWorkingHoursFormatted' => $totalWorkingHoursFormatted,
            'years' => range(now()->year - 2, now()->year + 1),
        ]);
    }
}
