@extends('layouts.app')

@section('title', 'Add Department')
@section('page-title', 'Add Department')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.departments.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Departments</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">New Department</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Add New Department</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure a new company department with its manager, designations, and automated security access.
                </p>
            </div>

            <a href="{{ route('admin.departments.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Departments
            </a>
        </div>

        {{-- Warning if no manager is eligible --}}
        @if ($managers->isEmpty())
            <div class="mb-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 px-4 py-3 rounded-xl text-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <span class="font-bold">No eligible manager found:</span>
                        No active user holds the admin or manager role. Please create or promote one on the
                        <a href="{{ route('admin.users.index') }}" class="underline font-semibold text-blue-600 dark:text-blue-400">Users</a> page first before assigning a department manager.
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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

        <!-- Form Elements -->
        <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-7">
            @csrf

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Department Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Department Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <input type="text" name="name" id="name" required maxlength="100"
                            placeholder="e.g. Operations"
                            value="{{ old('name') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('name') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                        Stored clean and lowercase, e.g. "Operations" becomes "operations".
                    </p>
                </div>

                <!-- Manager Dropdown -->
                <div>
                    <label for="manager_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Department Manager <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <select name="manager_id" id="manager_id" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('manager_id') border-red-500 @enderror">
                            <option value="" disabled @selected(! old('manager_id'))>Choose a department manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" {{ (int) old('manager_id') === $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                        Active users holding the Admin or Manager role.
                    </p>
                </div>

                <!-- Description (Full row or left column) -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Description
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                        </div>
                        <input type="text" name="description" id="description" maxlength="255"
                            placeholder="Brief summary of department responsibilities and functions"
                            value="{{ old('description') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Brief summary for employee onboarding and role visibility</p>
                </div>

                <!-- Designations (Job Titles) -->
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <label for="designations" class="block text-sm font-bold text-gray-800 dark:text-gray-200">
                            Designations (Job Titles)
                        </label>
                        <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            Role Automated
                        </span>
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <input type="text" name="designations" id="designations"
                            placeholder="e.g. Manager, Senior Specialist, Junior Specialist, Intern"
                            value="{{ old('designations', 'Manager, Team Leader, Executive, Intern') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <div class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <p>&bull; <strong>Comma-separated titles:</strong> These designations will populate the job title dropdown when adding/editing employees.</p>
                        <p>&bull; <strong>Manager role:</strong> Any title containing "Manager" automatically receives the Manager role.</p>
                        <p>&bull; <strong>Spatie Role Sync:</strong> A matching security role will be created automatically and will appear immediately on the Roles & Permissions dashboard.</p>
                    </div>
                </div>

                <!-- Active Status -->
                <div class="md:col-span-2 pt-2">
                    <label for="is_active" class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            @checked(old('is_active', true))
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Department is Active</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Active departments are immediately offered in the employee creation dropdown.</p>
                        </div>
                    </label>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.departments.index') }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Create Department
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection
