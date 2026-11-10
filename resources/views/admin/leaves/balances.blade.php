@extends('layouts.app')

@section('title', 'Employee Leave Balances')
@section('page-title', 'Employee Leave Balances')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
                @if(!empty($isManager))
                    {{ ucfirst($selectedDepartment) }} Department Leave Balances
                @else
                    Employee Leave Balances
                @endif
            </h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">


            @can('leaves.settings')
                <a href="{{ route('admin.leaves.types') }}"
                    class="inline-flex items-center bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manage Quotas
                </a>
            @endcan

            @can('leaves.create')
                <a href="{{ route('admin.leaves.create') }}"
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md shadow-blue-500/20 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Record Leave
                </a>
            @endcan
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-5">
        <form method="GET" action="{{ route('admin.leaves.balances') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Calendar Year</label>
                <select name="year" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200" onchange="this.form.submit()">
                    @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Department</label>
                @if(!empty($isManager))
                    @if(count($departments) <= 1)
                        <div class="w-full text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg px-3 py-2 flex items-center justify-between">
                            <span>{{ ucfirst($selectedDepartment ?: 'Your Department') }}</span>
                            <span class="text-[10px] font-bold uppercase bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200 px-1.5 py-0.5 rounded">Your Team</span>
                        </div>
                        <input type="hidden" name="department" value="{{ $selectedDepartment }}">
                    @else
                        <select name="department" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200" onchange="this.form.submit()">
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>{{ ucfirst($dept) }}</option>
                            @endforeach
                        </select>
                    @endif
                @else
                    <select name="department" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>{{ ucfirst($dept) }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Search Employee</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Name or EMP ID..."
                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm shadow-md shadow-blue-500/20 transition">
                    Filter
                </button>
                <a href="{{ route('admin.leaves.balances') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Balances Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 min-w-[200px]">Employee</th>
                        @foreach($leaveTypes as $type)
                            <th class="px-3 py-3 text-center min-w-[140px]">
                                <div class="font-bold">{{ $type->code }}</div>
                                <div class="text-[10px] font-normal text-gray-400">{{ $type->name }}</div>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($employees as $emp)
                        @php
                            $userBalances = $emp->leaveBalances->keyBy('leave_type_id');
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-700 text-white flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr($emp->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $emp->name }}</div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            @if($emp->emp_id)
                                                <span class="inline-flex px-1.5 py-0.2 rounded font-mono text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold border border-gray-200 dark:border-gray-600">
                                                    {{ $emp->emp_id }}
                                                </span>
                                            @endif
                                            <span>{{ ucfirst($emp->department ?? 'General') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            @foreach($leaveTypes as $type)
                                @php
                                    $bal = $userBalances->get($type->id);
                                    $allocated = $bal ? (float)$bal->allocated_days : (float)$type->days_allowed_per_year;
                                    $used = $bal ? (float)$bal->used_days : 0;
                                    $rem = $bal ? (float)$bal->remaining_days : $allocated;
                                @endphp
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                            {{ $rem }} <span class="text-xs font-normal text-gray-400">/ {{ $allocated }} left</span>
                                        </div>
                                        @if($bal)
                                            @can('leaves.settings')
                                                <button type="button"
                                                    onclick="openAdjustModal({{ $bal->id }}, '{{ addslashes($emp->name) }}', '{{ $type->name }}', {{ $allocated }})"
                                                    class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-0.5 rounded transition"
                                                    title="Adjust allocated days for this employee">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Used: <span class="font-medium text-amber-600 dark:text-amber-400">{{ $used }}</span>
                                    </div>
                                </td>
                            @endforeach

                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('admin.leaves.create', ['user_id' => $emp->id]) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors">
                                    Record Leave
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($leaveTypes) + 2 }}" class="px-4 py-10 text-center text-gray-400">
                                No active employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Adjust Employee Balance Modal -->
    <div id="adjustModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-sm w-full p-6 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700 mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Adjust Leave Quota</h3>
                    <p class="text-xs text-gray-400" id="adjustModalSubtitle">For Year {{ $year }}</p>
                </div>
                <button type="button" onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="adjustForm" method="POST" action="" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Allocated Days <span class="text-red-500">*</span></label>
                    <div class="relative rounded-lg shadow-sm">
                        <input type="number" step="0.5" id="adjust_allocated" name="allocated_days" required min="0" max="365"
                            class="w-full text-base font-bold border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 pr-16 text-indigo-600 dark:text-indigo-400">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-gray-400">
                            days
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Remaining balance will be automatically recomputed based on used days.</p>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeAdjustModal()"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg text-sm shadow-md shadow-blue-500/20 transition">
                        Save Quota
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdjustModal(balanceId, empName, leaveTypeName, currentAllocated) {
            const form = document.getElementById('adjustForm');
            form.action = '/admin/leaves/balances/' + balanceId;
            document.getElementById('adjustModalSubtitle').innerText = empName + ' • ' + leaveTypeName;
            document.getElementById('adjust_allocated').value = parseFloat(currentAllocated);
            document.getElementById('adjustModal').classList.remove('hidden');
        }

        function closeAdjustModal() {
            document.getElementById('adjustModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAdjustModal();
        });
        document.getElementById('adjustModal').addEventListener('click', function(e) {
            if (e.target === this) closeAdjustModal();
        });
    </script>
@endsection
