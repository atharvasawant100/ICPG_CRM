@extends('layouts.app')

@section('title', 'Edit Leave Record')
@section('page-title', 'Edit Leave Record')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.leaves.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Leaves</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit Record</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Leave Record</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $leave->user->name }} &middot; {{ $leave->leaveType->name }} &middot; {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }} ({{ (float)$leave->total_days }} day)
                </p>
            </div>

            <a href="{{ route('admin.leaves.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Leaves
            </a>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Employee Info Summary Card -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Employee</div>
                <div class="font-bold text-gray-900 dark:text-white text-base">{{ $leave->user->name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->user->emp_id ?? 'No EMP ID' }} &middot; {{ ucfirst($leave->user->department ?? 'General') }}</div>
            </div>
            <div class="sm:text-right">
                <div class="text-xs text-gray-500 dark:text-gray-400">Leave Duration</div>
                <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $leave->leaveType->name }} ({{ (float)$leave->total_days }} day)</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.leaves.update', $leave) }}" class="space-y-7">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Reason for Leave
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="reason" id="reason" rows="3"
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('reason', $leave->reason) }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Employee's stated reason</p>
                </div>

                <!-- Admin Remarks -->
                <div>
                    <label for="admin_remarks" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Admin Notes / Remarks
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <input type="text" name="admin_remarks" id="admin_remarks" value="{{ old('admin_remarks', $leave->admin_remarks) }}"
                            placeholder="Add approval comments or remarks..."
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Administrative review remarks</p>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.leaves.index') }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Update Record
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection
