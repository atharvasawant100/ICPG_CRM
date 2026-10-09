@extends('layouts.app')

@section('title', 'Edit ' . ucfirst($department->name))
@section('page-title', 'Edit Department')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <a href="{{ route('admin.departments.index') }}"
            class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-3">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Departments
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
            Edit {{ ucfirst($department->name) }}
        </h2>
    </div>

    @if ($errors->any())
        <div
            class="mb-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-0.5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 max-w-4xl">
        <form method="POST" action="{{ route('admin.departments.update', $department) }}">
            @csrf
            @method('PUT')

            {{-- Name is shown but not editable: users.department stores it as a
                 string, so renaming would strand every member. --}}
            <div class="mb-5">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Department name</label>
                <div
                    class="px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium">
                    {{ ucfirst($department->name) }}
                </div>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    The name cannot be changed: users store it, so renaming would orphan them.
                </p>
            </div>

            <div class="mb-5">
                <label for="description"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Description</label>
                <input type="text" name="description" id="description" maxlength="255"
                    placeholder="What this department does"
                    value="{{ old('description', $department->description) }}"
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            </div>

            <div class="mb-5">
                <label for="manager_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Manager *</label>
                <select name="manager_id" id="manager_id" required
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    {{-- Keep the current manager selectable even if they no
                         longer meet the eligibility rule, so saving does not
                         silently swap them. --}}
                    @if ($department->manager && ! $managers->contains($department->manager))
                        <option value="{{ $department->manager->id }}" selected>
                            {{ $department->manager->name }} — {{ $department->manager->email }}
                        </option>
                    @endif
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}"
                            {{ (int) old('manager_id', $department->manager_id) === $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }} — {{ $manager->email }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Active users holding the admin or manager role. Every member of this department
                    reports to whoever is set here.
                </p>
            </div>

            <div class="mb-5">
                <label for="is_active"
                    class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        @checked(old('is_active', $department->is_active))
                        class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
                    <span class="font-semibold">Department is active</span>
                </label>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 ml-6">
                    Turning this off stops the department being offered for new users. Existing members
                    keep their assignment.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Save Changes
                </button>
                <a href="{{ route('admin.departments.show', $department) }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection