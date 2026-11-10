@extends('layouts.app')

@section('title', 'Leave Management')
@section('page-title', 'Leave Management')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Leave Management</h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @can('leaves.create')
                <a href="{{ route('admin.leaves.create') }}"
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md shadow-blue-500/20 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Record / Apply Leave
                </a>
            @endcan

            <a href="{{ route('admin.leaves.balances') }}"
                class="inline-flex items-center bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Leave Balances
            </a>

            @can('leaves.settings')
                <a href="{{ route('admin.leaves.types') }}"
                    class="inline-flex items-center bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            @endcan
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pending Approvals</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $summary['pending'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Requires review</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">On Leave Today</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-0.5">{{ $summary['today_on_leave'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Active out of office</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider">Approved This Month</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-0.5">{{ $summary['approved_this_month'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Total approved</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Staff</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white mt-0.5">{{ $summary['total_employees'] }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Active team members</div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-5">
        <form method="GET" action="{{ route('admin.leaves.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Leave Type</label>
                <select name="leave_type_id" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ (string)$selectedLeaveType === (string)$type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Department</label>
                <select name="department" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>{{ ucfirst($dept) }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('admin.leaves.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition" title="Reset Filters">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Duration & Dates</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Approver</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($requests as $req)
                        @php
                            $badge = $req->status_badge;
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-700 to-gray-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ substr($req->user->name ?? 'U', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $req->user->name ?? 'Unknown' }}</div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            @if($req->user?->emp_id)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded font-mono text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold border border-gray-200 dark:border-gray-600">
                                                    {{ $req->user->emp_id }}
                                                </span>
                                            @endif
                                            <span>{{ ucfirst($req->user?->department ?? 'General') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $req->leaveType?->is_paid ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                    {{ $req->leaveType?->name ?? 'Leave' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}
                                    @if($req->start_date->ne($req->end_date))
                                        <span class="text-gray-400 font-normal">to</span> {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ (float)$req->total_days }} day{{ (float)$req->total_days > 1 ? 's' : '' }}</span>
                                    @if($req->is_half_day)
                                        <span class="px-1.5 py-0.2 bg-blue-50 text-blue-700 rounded text-[10px] font-medium border border-blue-200">
                                            Half Day ({{ $req->half_day_type === 'second_half' ? '2nd Half' : '1st Half' }})
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3.5 max-w-xs truncate text-xs text-gray-600 dark:text-gray-300">
                                {{ $req->reason ?: '-' }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-500">
                                @if($req->approver)
                                    <div class="font-medium text-gray-700 dark:text-gray-300">{{ $req->approver->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $req->action_at?->format('d M, h:i A') }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @can('leaves.approve')
                                        @if($req->status === 'pending')
                                            <form method="POST" action="{{ route('admin.leaves.approve', $req) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors" title="Approve Request">
                                                    Approve
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.leaves.reject', $req) }}" class="inline" onsubmit="return confirm('Reject this leave request?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors" title="Reject Request">
                                                    Reject
                                                </button>
                                            </form>
                                        @elseif($req->status === 'approved')
                                            <form method="POST" action="{{ route('admin.leaves.reject', $req) }}" class="inline" onsubmit="return confirm('Revoke and reject this approved leave? Attendance will be restored.');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200 transition-colors" title="Revoke Request">
                                                    Revoke
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('leaves.edit')
                                        <a href="{{ route('admin.leaves.edit', $req) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors" title="Edit">
                                            Edit
                                        </a>
                                    @endcan

                                    @can('leaves.delete')
                                        <form method="POST" action="{{ route('admin.leaves.destroy', $req) }}" class="inline" onsubmit="return confirm('Delete this leave record permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors" title="Delete">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-400">No leave records found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or record a new leave application.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
@endsection
