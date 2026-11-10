@extends('layouts.app')

@section('title', 'Monthly Attendance Summary')
@section('page-title', 'Monthly Attendance Summary')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Monthly Attendance Summary</h2>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if(auth()->user()->can('attendance.create'))
                <form method="POST" action="{{ route('admin.attendance.mark-absents') }}" class="inline">
                    @csrf
                    <input type="hidden" name="scope" value="month">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit"
                        onclick="return confirm('Auto-mark unpunched past days for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }} as Absent (or on-leave/holiday)?');"
                        class="inline-flex items-center bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all duration-200">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Auto-Mark Month Absents
                    </button>
                </form>
            @endif

            @can('holidays.view')
                <a href="{{ route('admin.holidays.index', ['year' => $year]) }}"
                    class="inline-flex items-center bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 hover:bg-cyan-100 text-cyan-800 dark:text-cyan-200 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Holidays ({{ $holidays->count() }})
                </a>
            @endcan

            <a href="{{ route('admin.attendance.index') }}"
                class="inline-flex items-center bg-white border border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200">
                Daily Sheet
            </a>

            @if(!auth()->user()->isAdmin() && auth()->user()->can('attendance.create'))
                <a href="{{ route('admin.attendance.create') }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-md transition-all duration-200">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Mark Attendance
                </a>
            @endif
        </div>
    </div>

    <!-- Period & Department Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 mb-5 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('admin.attendance.monthly') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="month" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Month *</label>
                <select name="month" id="month" onchange="this.form.submit()"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="year" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Year *</label>
                <select name="year" id="year" onchange="this.form.submit()"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="department" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Department</label>
                <select name="department" id="department" onchange="this.form.submit()"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>
                            {{ ucfirst($dept) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('admin.attendance.monthly') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-xs shadow transition-all duration-200">
                    Current Month
                </a>
            </div>
        </form>
    </div>

    <!-- Monthly Summary Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EMP ID</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Present</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-amber-600 dark:text-amber-400 uppercase tracking-wider">Late</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Half Day</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">Leave</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">Holidays</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">Absent</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Work Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($employees as $emp)
                        @php
                            $records = $monthlyRecords->get($emp->id, collect());
                            $present = $records->where('status', 'present')->count();
                            $late = $records->where('status', 'late')->count();
                            $halfDay = $records->where('status', 'half_day')->count();
                            $leave = $records->where('status', 'on_leave')->count();
                            $recordedHolidayCount = $records->where('status', 'holiday')->count();
                            $absent = $records->where('status', 'absent')->count();

                            // Calculate unrecorded past working days as absent
                            $recordedDates = $records->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->all();
                            $startOfTargetMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
                            $endOfTargetMonth = $startOfTargetMonth->copy()->endOfMonth()->min(now()->startOfDay());

                            $unrecordedWorkingDays = 0;
                            if ($startOfTargetMonth->lte(now())) {
                                $period = \Carbon\CarbonPeriod::create($startOfTargetMonth, $endOfTargetMonth);
                                foreach ($period as $day) {
                                    $dayStr = $day->toDateString();
                                    if ($emp->created_at && \Carbon\Carbon::parse($emp->created_at)->startOfDay()->gt($day->endOfDay())) {
                                        continue;
                                    }
                                    if (!$day->isSunday() && !isset($holidays[$dayStr]) && !in_array($dayStr, $recordedDates, true)) {
                                        $unrecordedWorkingDays++;
                                    }
                                }
                            }
                            $effectiveAbsent = $absent + $unrecordedWorkingDays;
                            $effectiveHolidays = max($recordedHolidayCount, $holidays->count());

                            $totalMinutes = $records->sum('total_minutes');
                            $hours = floor($totalMinutes / 60);
                            $mins = $totalMinutes % 60;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-semibold">
                                @if ($emp->emp_id)
                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        {{ $emp->emp_id }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-sm text-gray-800 dark:text-white">{{ $emp->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $emp->email }}</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                                {{ $emp->department ? ucfirst($emp->department) : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-green-600 dark:text-green-400">
                                {{ $present }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-amber-600 dark:text-amber-400">
                                {{ $late }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-blue-600 dark:text-blue-400">
                                {{ $halfDay }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-purple-600 dark:text-purple-400">
                                {{ $leave }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-cyan-600 dark:text-cyan-400">
                                {{ $effectiveHolidays }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-center text-xs font-bold text-red-600 dark:text-red-400">
                                {{ $effectiveAbsent }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs font-mono font-bold text-gray-900 dark:text-white">
                                {{ $hours }}h {{ $mins }}m
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-sm text-gray-500">
                                No active employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
