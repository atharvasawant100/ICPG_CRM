@extends('layouts.app')

@section('title', 'Record / Apply Leave')
@section('page-title', 'Record / Apply Leave')

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
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Record Leave</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Record Employee Leave</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    File planned or unplanned leave for an employee with balance check and automatic attendance syncing.
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

        <form method="POST" action="{{ route('admin.leaves.store') }}" class="space-y-7">
            @csrf

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Employee Selection -->
                <div>
                    <label for="user_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Employee <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <select name="user_id" id="user_id" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('user_id') border-red-500 @enderror">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (string)old('user_id', $preselectedUser) === (string)$emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->emp_id ?? 'No ID' }}) - {{ ucfirst($emp->department ?? 'General') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Employee taking leave</p>
                </div>

                <!-- Leave Type -->
                <div>
                    <label for="leave_type_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <select name="leave_type_id" id="leave_type_id" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('leave_type_id') border-red-500 @enderror">
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ (string)old('leave_type_id') === (string)$type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->code }}) &mdash; {{ $type->is_paid ? 'Paid' : 'Unpaid' }} ({{ (float)$type->days_allowed_per_year }} days/yr)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Determines paid vs unpaid leave calculations</p>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="start_date" id="start_date" required value="{{ old('start_date', now()->toDateString()) }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('start_date') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">First day of absence</p>
                </div>

                <!-- End Date -->
                <div id="end_date_container">
                    <label for="end_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="end_date" id="end_date" required value="{{ old('end_date', now()->toDateString()) }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('end_date') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Last day of absence inclusive</p>
                </div>

                <!-- Half Day Options Card -->
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_half_day" id="is_half_day" value="1" {{ old('is_half_day') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Half Day Leave (0.5 day)</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Automatically locks end date to match start date</p>
                        </div>
                    </label>

                    <div id="half_day_options" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 {{ old('is_half_day') ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">Half Day Timing</label>
                        <div class="flex items-center gap-6 text-sm text-gray-700 dark:text-gray-200">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="half_day_type" value="first_half" {{ old('half_day_type', 'first_half') === 'first_half' ? 'checked' : '' }}
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>First Half (Morning)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="half_day_type" value="second_half" {{ old('half_day_type') === 'second_half' ? 'checked' : '' }}
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>Second Half (Afternoon)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Initial Status -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Approval Status <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700 dark:text-gray-200">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="approved" {{ old('status', 'approved') === 'approved' ? 'checked' : '' }}
                                class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-emerald-700 dark:text-emerald-400">Approve Immediately</span>
                            <span class="text-xs text-gray-400">(Auto-marks attendance sheet as On Leave)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="pending" {{ old('status') === 'pending' ? 'checked' : '' }}
                                class="text-amber-600 focus:ring-amber-500">
                            <span class="font-semibold text-amber-700 dark:text-amber-400">Mark as Pending</span>
                        </label>
                    </div>
                </div>

                <!-- Reason -->
                <div class="md:col-span-2">
                    <label for="reason" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Reason for Leave
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="reason" id="reason" rows="3"
                            placeholder="e.g. Medical emergency, family function, personal errands..."
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('reason') }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Submitted reason visible to managers</p>
                </div>

                <!-- Admin Remarks -->
                <div class="md:col-span-2">
                    <label for="admin_remarks" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Admin Notes / Remarks <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <input type="text" name="admin_remarks" id="admin_remarks" value="{{ old('admin_remarks') }}"
                            placeholder="e.g. Approved as per verbal discussion"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">HR / Manager verification notes</p>
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
                        Save Leave Record
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isHalfDayCheckbox = document.getElementById('is_half_day');
        const halfDayOptions = document.getElementById('half_day_options');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        function toggleHalfDay() {
            if (isHalfDayCheckbox.checked) {
                halfDayOptions.classList.remove('hidden');
                endDateInput.value = startDateInput.value;
                endDateInput.setAttribute('disabled', 'disabled');
            } else {
                halfDayOptions.classList.add('hidden');
                endDateInput.removeAttribute('disabled');
            }
        }

        isHalfDayCheckbox.addEventListener('change', toggleHalfDay);
        startDateInput.addEventListener('change', function () {
            if (isHalfDayCheckbox.checked) {
                endDateInput.value = startDateInput.value;
            }
        });

        document.querySelector('form').addEventListener('submit', function () {
            endDateInput.removeAttribute('disabled');
        });
    });
</script>
@endsection
