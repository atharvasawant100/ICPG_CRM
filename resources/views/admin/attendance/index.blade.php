@extends('layouts.app')

@section('title', 'Employee Attendance')
@section('page-title', 'Employee Attendance')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Employee Attendance</h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if(auth()->user()->can('attendance.create'))
                <form method="POST" action="{{ route('admin.attendance.mark-absents') }}" class="inline">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit"
                        onclick="return confirm('Verify and auto-mark all unpunched staff on {{ \Carbon\Carbon::parse($date)->format('d M Y') }} as Absent (or holiday/on-leave)?');"
                        class="inline-flex items-center bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all duration-200">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Auto-Mark Absents
                    </button>
                </form>
            @endif

            @can('holidays.view')
                <a href="{{ route('admin.holidays.index') }}"
                    class="inline-flex items-center bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 hover:bg-cyan-100 text-cyan-800 dark:text-cyan-200 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Holidays
                </a>
            @endcan

            @if(!auth()->user()->isAdmin() && auth()->user()->can('attendance.create'))
                <a href="{{ route('admin.attendance.create', ['date' => $date]) }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-md transition-all duration-200">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Manual Entry
                </a>
            @endif

            @if(auth()->user()->can('attendance.create'))
                <a href="{{ route('admin.attendance.settings') }}"
                    class="inline-flex items-center bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 text-indigo-800 dark:text-indigo-200 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Timing Settings
                </a>
            @endif

            <a href="{{ route('admin.attendance.monthly') }}"
                class="inline-flex items-center bg-white border border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Monthly Summary
            </a>
        </div>
    </div>

    @if (!empty($summary['open_shifts']) && $summary['open_shifts'] > 0)
        <div class="mb-5 p-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-purple-900 dark:text-purple-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ $summary['open_shifts'] }}
                </span>
                <div>
                    <h4 class="font-bold text-sm text-purple-900 dark:text-purple-100">
                        {{ $summary['open_shifts'] }} Employee(s) Currently Clocked In Without Punch-Out
                    </h4>
                    <p class="text-xs text-purple-700 dark:text-purple-300 mt-0.5">
                        These employees clocked in but haven't clocked out yet. If they forgot to punch out, you can auto clock them out below.
                    </p>
                </div>
            </div>
            @if(auth()->user()->can('attendance.create'))
                <form action="{{ route('admin.attendance.auto-punch-out') }}" method="POST" class="inline self-start sm:self-auto">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" onclick="return confirm('Auto-clock out all open shifts for {{ \Carbon\Carbon::parse($date)->format('d M Y') }} at {{ \Carbon\Carbon::parse($attendanceSettings->auto_punch_out_time)->format('h:i A') }}?')"
                        class="px-3.5 py-2 text-xs font-bold rounded-xl text-purple-700 dark:text-purple-200 bg-white dark:bg-gray-800 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-300 dark:border-purple-700 shadow-sm transition">
                        ⚡ Auto Clock-Out Open Shifts
                    </button>
                </form>
            @endif
        </div>
    @endif

    @if ($holiday)
        <div class="mb-5 p-4 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 text-cyan-900 dark:text-cyan-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎉</span>
                <div>
                    <h4 class="font-bold text-sm">Official Company Holiday: {{ $holiday->name }}</h4>
                    <p class="text-xs text-cyan-700 dark:text-cyan-300 mt-0.5">
                        {{ $holiday->description ?: 'Employees are officially exempt from punch-in and will not be marked absent.' }}
                    </p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-200 dark:bg-cyan-800 text-cyan-900 dark:text-cyan-100">
                Holiday Exemption
            </span>
        </div>
    @elseif ($isSunday)
        <div class="mb-5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex items-center gap-2 text-xs font-medium">
            <span>📅</span>
            <span><strong>Weekly Off:</strong> Selected date is a Sunday. Employees are not expected on duty.</span>
        </div>
    @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Staff</div>
            <div class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mt-0.5">{{ $summary['total_employees'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Active team</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider">Present</div>
            <div class="text-xl sm:text-2xl font-bold text-green-600 dark:text-green-400 mt-0.5">{{ $summary['present'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">On-time duty</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Late</div>
            <div class="text-xl sm:text-2xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $summary['late'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">After {{ \Carbon\Carbon::parse($attendanceSettings->late_mark_after)->format('h:i A') }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Half Day</div>
            <div class="text-xl sm:text-2xl font-bold text-blue-600 dark:text-blue-400 mt-0.5">{{ $summary['half_day'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">&lt; 4 hours</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">On Leave</div>
            <div class="text-xl sm:text-2xl font-bold text-purple-600 dark:text-purple-400 mt-0.5">{{ $summary['on_leave'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Approved leave</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 sm:p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Absent / Unmarked</div>
            <div class="text-xl sm:text-2xl font-bold text-red-600 dark:text-red-400 mt-0.5">{{ $summary['absent'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">{{ $summary['unmarked'] }} pending punch</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 mb-5 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="date" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Attendance Date *</label>
                <input type="date" name="date" id="date" value="{{ $date }}"
                    onchange="this.form.submit()"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
            </div>

            <div>
                <label for="search" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Search Employee</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Name or EMP ID..."
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
            </div>

            <div>
                <label for="department" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Department</label>
                <select name="department" id="department"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>
                            {{ ucfirst($dept) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-gray-700 dark:text-gray-300 text-xs font-semibold mb-1">Status</label>
                <select name="status" id="status"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-xs focus:ring-2 focus:ring-gray-500">
                    <option value="">All statuses</option>
                    <option value="present" {{ $selectedStatus === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="late" {{ $selectedStatus === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ $selectedStatus === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="on_leave" {{ $selectedStatus === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="absent" {{ $selectedStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="unmarked" {{ $selectedStatus === 'unmarked' ? 'selected' : '' }}>Unmarked</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2 px-4 rounded-lg text-xs shadow-md transition-all duration-200">
                    Apply Filter
                </button>
                <a href="{{ route('admin.attendance.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2 px-4 rounded-lg text-xs transition-all duration-200">
                    Today
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EMP ID</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Check In</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Check Out</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Working Hours</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($employees as $emp)
                        @php
                            $att = $attendances->get($emp->id);
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
                                @if ($emp->designation)
                                    <span class="text-gray-400 text-[11px] block">{{ \App\Rbac\Designation::label($emp->designation) }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-800 dark:text-gray-200">
                                @if ($att && $att->check_in)
                                    {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                    @if ($att->status === 'late')
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold block">Late Entry</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-800 dark:text-gray-200">
                                @if ($att && $att->check_out)
                                    {{ \Carbon\Carbon::parse($att->check_out)->format('h:i A') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $att ? $att->working_hours_formatted : '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($att)
                                    @php $badge = $att->status_badge; @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        Not Marked
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $att?->notes ?? '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                @can('attendance.edit')
                                    @if ($att)
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.attendance.edit', $att) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                                Edit
                                            </a>
                                            @can('attendance.delete')
                                                <form action="{{ route('admin.attendance.destroy', $att) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Delete attendance record for {{ $emp->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">
                                                        Reset
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    @else
                                        @can('attendance.create')
                                            <a href="{{ route('admin.attendance.create', ['date' => $date]) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">
                                                Mark Punch
                                            </a>
                                        @endcan
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500">
                                No active employees found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($employees as $emp)
                @php
                    $att = $attendances->get($emp->id);
                @endphp
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-white flex items-center gap-1.5">
                                {{ $emp->name }}
                                @if ($emp->emp_id)
                                    <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gray-100 dark:bg-gray-700">{{ $emp->emp_id }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $emp->department ? ucfirst($emp->department) : 'No dept' }}</div>
                        </div>

                        <div>
                            @if ($att)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $att->status_badge['class'] }}">
                                    {{ $att->status_badge['label'] }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Not Marked</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-xs bg-gray-50 dark:bg-gray-700/30 p-2 rounded-lg font-mono">
                        <div>
                            <span class="text-gray-500 block text-[10px] font-sans">In</span>
                            <span>{{ $att && $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px] font-sans">Out</span>
                            <span>{{ $att && $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px] font-sans">Duration</span>
                            <span class="font-bold font-sans">{{ $att ? $att->working_hours_formatted : '-' }}</span>
                        </div>
                    </div>

                    @can('attendance.edit')
                        <div class="pt-1 flex gap-1.5">
                            @if ($att)
                                <a href="{{ route('admin.attendance.edit', $att) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 transition-colors">Edit</a>
                            @else
                                <a href="{{ route('admin.attendance.create', ['date' => $date]) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 transition-colors">Mark</a>
                            @endif
                        </div>
                    @endcan
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-500">No employees found.</div>
            @endforelse
        </div>
    </div>
@endsection
