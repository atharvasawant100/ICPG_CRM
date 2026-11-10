@extends('layouts.app')

@section('title', 'Holiday Management')
@section('page-title', 'Holiday Management')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Company Holidays
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Manage company holidays. On scheduled holidays, employees are not required to punch in and will not be marked absent.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.attendance.monthly') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                &larr; Monthly Attendance
            </a>
            <a href="{{ route('admin.attendance.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                Daily Sheet
            </a>
        </div>
    </div>

    <!-- Stat Cards & Filter Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Holidays ({{ $year }})</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $summary['total'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Upcoming Holidays</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $summary['upcoming'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-400">Past Holidays</p>
                <p class="text-2xl font-bold text-gray-500 dark:text-gray-400 mt-1">{{ $summary['past'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('admin.holidays.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label for="year" class="text-xs font-semibold text-gray-600 dark:text-gray-300">Year:</label>
                <select name="year" id="year" onchange="this.form.submit()"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 px-3 text-xs text-gray-700 dark:text-gray-200 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search holiday..."
                        class="w-full text-xs border border-gray-300 dark:border-gray-600 rounded-lg py-1.5 pl-8 pr-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.holidays.index', ['year' => $year]) }}" class="text-xs text-gray-500 hover:underline">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Content: Holidays List + Add Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Holidays Table (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 dark:text-white text-base flex items-center gap-2">
                        <span>List of Holidays ({{ $year }})</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                            {{ $holidays->count() }} total
                        </span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3">Date & Day</th>
                                <th class="px-4 py-3">Holiday Name</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($holidays as $holiday)
                                @php
                                    $isToday = $holiday->date->isToday();
                                    $isPast = $holiday->date->isPast() && !$isToday;
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition {{ $isToday ? 'bg-cyan-50/50 dark:bg-cyan-950/20' : '' }}">
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-sm {{ $isToday ? 'text-cyan-700 dark:text-cyan-300' : 'text-gray-900 dark:text-white' }}">
                                            {{ $holiday->date->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $holiday->date->format('l') }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5">
                                        <div class="font-semibold text-gray-900 dark:text-white text-sm">
                                            {{ $holiday->name }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $holiday->description ?: '—' }}
                                    </td>

                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if ($isToday)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-300 animate-pulse">
                                                ★ Today
                                            </span>
                                        @elseif ($isPast)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                Past
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                Upcoming
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                onclick='openEditModal(@json($holiday))'
                                                class="px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition">
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}"
                                                onsubmit="return confirm('Are you sure you want to remove the holiday \'{{ addslashes($holiday->name) }}\'?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-2.5 py-1 text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="font-medium">No holidays scheduled for {{ $year }}.</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Use the form on the right to schedule a holiday.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Holiday Form (1 Col) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 h-fit sticky top-24">
            <h3 class="font-bold text-gray-800 dark:text-white text-base mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Schedule Holiday
            </h3>
            <p class="text-xs text-gray-400 mb-4">Set an official company holiday for staff exemption.</p>

            <form method="POST" action="{{ route('admin.holidays.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Holiday Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required placeholder="e.g. Diwali / Republic Day"
                        value="{{ old('name') }}"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" required
                        value="{{ old('date', now()->toDateString()) }}"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    @error('date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Description / Occasion (Optional)
                    </label>
                    <textarea id="description" name="description" rows="3" placeholder="Optional notes for employees..."
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                    <p class="text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
                        💡 <strong>Automatic Exemption:</strong> Employees will see a holiday notice on this date and will not be penalized or marked absent.
                    </p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm shadow-md shadow-blue-500/20 transition-all duration-200">
                        Add Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Holiday Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full p-6 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Holiday</h3>
                    <p class="text-xs text-gray-400">Update holiday details</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Holiday Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" id="edit_date" name="date" required
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea id="edit_description" name="description" rows="3"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg text-sm shadow-md shadow-blue-500/20 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(holiday) {
            const form = document.getElementById('editForm');
            form.action = '/admin/holidays/' + holiday.id;
            document.getElementById('edit_name').value = holiday.name;
            // format date YYYY-MM-DD
            const d = new Date(holiday.date);
            const dateStr = holiday.date.split('T')[0];
            document.getElementById('edit_date').value = dateStr;
            document.getElementById('edit_description').value = holiday.description || '';

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endsection
