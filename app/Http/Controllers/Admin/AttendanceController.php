<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\User;
use App\Rbac\Rbac;
use App\Services\AttendanceAutoAbsentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    use AuthorizesByPermission;

    /**
     * Daily attendance sheet for employees.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('attendance.view');

        $date = $request->input('date') ?: now()->toDateString();
        $department = $request->string('department')->trim()->value();
        $statusFilter = $request->string('status')->trim()->value();
        $search = $request->string('search')->trim()->value();

        // Query active employees (excluding admins)
        $employeesQuery = User::query()
            ->active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name');

        // Managers only see their supervised team members; Admin and HR can view all departments and employees
        if (! $request->user()->isAdmin() && ! $request->user()->isHr()) {
            $currentUser = $request->user();
            $employeesQuery->where(function ($q) use ($currentUser) {
                $q->where('manager_id', $currentUser->id);
                if ($currentUser->department) {
                    $q->orWhere(function ($sub) use ($currentUser) {
                        $sub->where('department', $currentUser->department)
                            ->where('id', '!=', $currentUser->id);
                    });
                }
            });
        }

        if ($department) {
            $employeesQuery->where('department', $department);
        }

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->get();

        // Existing attendance records for this date
        $attendances = Attendance::whereDate('date', $date)
            ->whereIn('user_id', $employees->pluck('id'))
            ->get()
            ->keyBy('user_id');

        // Apply status filter in-memory across employees
        if ($statusFilter) {
            $employees = $employees->filter(function ($emp) use ($attendances, $statusFilter) {
                $att = $attendances->get($emp->id);
                if ($statusFilter === 'unmarked') {
                    return $att === null;
                }
                return $att && $att->status === $statusFilter;
            });
        }

        // Summary counts for this date
        $allDateAttendances = Attendance::whereDate('date', $date)->get();
        $totalActiveEmployees = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->count();

        $presentCount = $allDateAttendances->where('status', 'present')->count();
        $lateCount = $allDateAttendances->where('status', 'late')->count();
        $halfDayCount = $allDateAttendances->where('status', 'half_day')->count();
        $leaveCount = $allDateAttendances->where('status', 'on_leave')->count();
        $holidayCount = $allDateAttendances->where('status', 'holiday')->count();
        $explicitAbsentCount = $allDateAttendances->where('status', 'absent')->count();
        $markedCount = $allDateAttendances->count();
        $unmarkedCount = max(0, $totalActiveEmployees - $markedCount);

        $dateCarbon = Carbon::parse($date);
        $holiday = Holiday::whereDate('date', $date)->first();
        $isSunday = $dateCarbon->isSunday();

        $openShiftsCount = $allDateAttendances->whereNotNull('check_in')->whereNull('check_out')->count();

        $summary = [
            'total_employees' => $totalActiveEmployees,
            'present' => $presentCount,
            'late' => $lateCount,
            'half_day' => $halfDayCount,
            'on_leave' => $leaveCount,
            'holiday' => $holidayCount,
            'absent' => $explicitAbsentCount + ($isSunday || $holiday ? 0 : $unmarkedCount),
            'unmarked' => $unmarkedCount,
            'open_shifts' => $openShiftsCount,
            'is_holiday' => (bool) $holiday,
            'holiday_name' => $holiday?->name,
            'is_sunday' => $isSunday,
        ];

        return view('admin.attendance.index', [
            'date' => $date,
            'employees' => $employees,
            'attendances' => $attendances,
            'summary' => $summary,
            'holiday' => $holiday,
            'isSunday' => $isSunday,
            'departments' => Department::names(activeOnly: false),
            'selectedDepartment' => $department,
            'selectedStatus' => $statusFilter,
            'attendanceSettings' => \App\Models\AttendanceSetting::get(),
        ]);
    }

    /**
     * Show attendance configuration settings (shift timings, late mark, auto punch-out).
     */
    public function settings()
    {
        $this->authorizePermission('attendance.create');

        $settings = \App\Models\AttendanceSetting::get();

        return view('admin.attendance.settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update attendance configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'shift_start_time' => ['required', 'string', 'max:5'],
            'shift_end_time' => ['required', 'string', 'max:5'],
            'block_punch_in_after_shift_end' => ['nullable', 'boolean'],
            'late_mark_after' => ['required', 'string', 'max:5'],
            'half_day_login_after' => ['required', 'string', 'max:5'],
            'full_day_min_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'half_day_min_hours' => ['required', 'numeric', 'min:1', 'max:12'],
            'auto_half_day_on_short_hours' => ['nullable', 'boolean'],
            'enable_auto_punch_out' => ['nullable', 'boolean'],
            'auto_punch_out_time' => ['required', 'string', 'max:5'],
            'auto_punch_out_mode' => ['required', 'string', 'in:fixed_time,shift_duration'],
        ]);

        $settings = \App\Models\AttendanceSetting::get();
        $settings->fill([
            'shift_start_time' => $validated['shift_start_time'],
            'shift_end_time' => $validated['shift_end_time'],
            'block_punch_in_after_shift_end' => $request->boolean('block_punch_in_after_shift_end'),
            'late_mark_after' => $validated['late_mark_after'],
            'half_day_login_after' => $validated['half_day_login_after'],
            'full_day_min_hours' => (float) $validated['full_day_min_hours'],
            'half_day_min_hours' => (float) $validated['half_day_min_hours'],
            'auto_half_day_on_short_hours' => $request->boolean('auto_half_day_on_short_hours'),
            'enable_auto_punch_out' => $request->boolean('enable_auto_punch_out'),
            'auto_punch_out_time' => $validated['auto_punch_out_time'],
            'auto_punch_out_mode' => $validated['auto_punch_out_mode'],
            'updated_by' => auth()->id(),
        ]);
        $settings->save();

        return redirect()->route('admin.attendance.settings')
            ->with('success', 'Attendance timing and auto-punch-out settings updated successfully.');
    }

    /**
     * Trigger auto punch-out on-demand for unclosed shifts.
     */
    public function triggerAutoPunchOut(Request $request, \App\Services\AttendanceAutoPunchOutService $service)
    {
        $this->authorizePermission('attendance.create');

        $date = $request->input('date') ?: now()->toDateString();
        $scope = $request->input('scope', 'date');

        if ($scope === 'all_past') {
            $res = $service->autoPunchOutPastUnclosedDays();
            return redirect()->back()
                ->with('success', "Auto punch-out completed: {$res['total_processed']} unclosed shift(s) closed across {$res['days_processed']} day(s).");
        }

        $res = $service->autoPunchOutForDate($date);

        if ($res['status'] === 'disabled') {
            return redirect()->back()->with('error', 'Auto punch-out is currently disabled in Attendance Settings.');
        }

        if ($res['status'] === 'none_pending') {
            return redirect()->back()->with('info', "No unclosed shifts found for " . Carbon::parse($date)->format('d M Y') . ".");
        }

        $namesList = implode(', ', $res['employees']);
        return redirect()->back()
            ->with('success', "Auto-clocked out {$res['processed']} employee(s) for " . Carbon::parse($date)->format('d M Y') . " ({$namesList}).");
    }

    /**
     * Form to mark or batch-mark attendance for a date.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $date = $request->input('date') ?: now()->toDateString();

        $employees = User::query()
            ->active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name')
            ->get();

        $attendances = Attendance::whereDate('date', $date)
            ->whereIn('user_id', $employees->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('admin.attendance.create', [
            'date' => $date,
            'employees' => $employees,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Store or update batch attendance for a date.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.status' => ['nullable', 'string', 'in:present,late,half_day,absent,on_leave'],
            'records.*.check_in' => ['nullable', 'date_format:H:i'],
            'records.*.check_out' => ['nullable', 'date_format:H:i'],
            'records.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $validated['date'];
        $records = $validated['records'];
        $savedCount = 0;

        DB::transaction(function () use ($date, $records, &$savedCount) {
            foreach ($records as $userId => $data) {
                if (empty($data['status'])) {
                    continue;
                }

                $attendance = Attendance::firstOrNew([
                    'user_id' => $userId,
                    'date' => $date,
                ]);

                $attendance->status = $data['status'];
                $attendance->check_in = ! empty($data['check_in']) ? $data['check_in'] : null;
                $attendance->check_out = ! empty($data['check_out']) ? $data['check_out'] : null;
                $attendance->notes = ! empty($data['notes']) ? $data['notes'] : null;
                $attendance->marked_by = auth()->id();

                $attendance->computeTotalMinutes();
                $attendance->save();

                $savedCount++;
            }
        });

        return redirect()->route('admin.attendance.index', ['date' => $date])
            ->with('success', "Attendance updated for {$savedCount} employees on " . Carbon::parse($date)->format('d M Y') . '.');
    }

    /**
     * Show form to edit a single attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $this->authorizePermission('attendance.edit');

        $attendance->load('user');

        return view('admin.attendance.edit', [
            'attendance' => $attendance,
        ]);
    }

    /**
     * Update a single attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->authorizePermission('attendance.edit');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:present,late,half_day,absent,on_leave'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $attendance->fill($validated);
        $attendance->marked_by = auth()->id();
        $attendance->computeTotalMinutes();
        $attendance->save();

        return redirect()->route('admin.attendance.index', ['date' => $attendance->date->toDateString()])
            ->with('success', "Attendance updated for {$attendance->user->name}.");
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        $this->authorizePermission('attendance.delete');

        $date = $attendance->date->toDateString();
        $name = $attendance->user->name;
        $attendance->delete();

        return redirect()->route('admin.attendance.index', ['date' => $date])
            ->with('success', "Attendance record for {$name} removed.");
    }

    /**
     * Monthly attendance summary per employee.
     */
    public function monthly(Request $request)
    {
        $this->authorizePermission('attendance.view');

        $year = (int) ($request->input('year') ?: now()->year);
        $month = (int) ($request->input('month') ?: now()->month);
        $department = $request->string('department')->trim()->value();

        $employeesQuery = User::query()
            ->active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->orderBy('name');

        if (! $request->user()->isAdmin() && ! $request->user()->isHr()) {
            $currentUser = $request->user();
            $employeesQuery->where(function ($q) use ($currentUser) {
                $q->where('manager_id', $currentUser->id);
                if ($currentUser->department) {
                    $q->orWhere(function ($sub) use ($currentUser) {
                        $sub->where('department', $currentUser->department)
                            ->where('id', '!=', $currentUser->id);
                    });
                }
            });
        }

        if ($department) {
            $employeesQuery->where('department', $department);
        }

        $employees = $employeesQuery->get();

        // Fetch all attendances for the given month & year
        $monthlyRecords = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('user_id', $employees->pluck('id'))
            ->get()
            ->groupBy('user_id');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($h) => $h->date->toDateString());

        return view('admin.attendance.monthly', [
            'year' => $year,
            'month' => $month,
            'daysInMonth' => $daysInMonth,
            'employees' => $employees,
            'monthlyRecords' => $monthlyRecords,
            'holidays' => $holidays,
            'departments' => Department::names(activeOnly: false),
            'selectedDepartment' => $department,
            'years' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    /**
     * Trigger auto-absent processing on-demand.
     */
    public function markAbsents(Request $request, AttendanceAutoAbsentService $service)
    {
        $this->authorizePermission('attendance.create');

        $scope = $request->input('scope', 'date');

        if ($scope === 'month') {
            $year = (int) ($request->input('year') ?: now()->year);
            $month = (int) ($request->input('month') ?: now()->month);
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth()->min(now()->startOfDay());

            $result = $service->markAbsentsForRange($start, $end);

            return redirect()->back()
                ->with('success', "Processed past days of {$start->format('F Y')}: {$result['marked_absent']} marked absent, {$result['marked_leave']} on leave, {$result['marked_holiday']} holiday records.");
        }

        $date = $request->input('date') ?: now()->toDateString();
        $result = $service->markAbsentsForDate($date);

        $dateFormatted = Carbon::parse($date)->format('d M Y');
        if (! empty($result['is_sunday'])) {
            return redirect()->back()->with('info', "{$dateFormatted} is Sunday (Weekly Off). No absent records created.");
        }
        if (! empty($result['is_holiday'])) {
            return redirect()->back()->with('info', "{$dateFormatted} is an official holiday ({$result['holiday_name']}). {$result['marked_holiday']} employee(s) marked as holiday.");
        }

        return redirect()->back()
            ->with('success', "Attendance verified for {$dateFormatted}: {$result['marked_absent']} employee(s) marked absent, {$result['marked_leave']} on leave.");
    }
}
