@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Departments</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                The departments a user can be assigned to. Every department has a manager.
            </p>
        </div>

        @can('departments.create')
            <button type="button" onclick="toggleAddDepartment()"
                class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </button>
        @endcan
    </div>

    @if (session('success'))
        <div
            class="mb-4 sm:mb-5 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- A manager is required, so say it plainly when there is nobody to pick.
         Without this the dropdown would be empty and the page unusable. --}}
    @can('departments.create')
        @if ($managers->isEmpty())
            <div
                class="mb-4 sm:mb-5 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 px-4 py-3 rounded-lg text-sm">
                No active user holds the admin or manager role, so there is nobody to assign as a
                department manager. Create or promote one on the
                <a href="{{ route('admin.users.index') }}" class="underline font-semibold">Users</a> page first.
            </div>
        @endif
    @endcan

    {{-- Add form, revealed by the button above. --}}
    @can('departments.create')
        <div id="add-department-panel"
            class="hidden mb-4 sm:mb-5 md:mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-bold text-gray-800 dark:text-white mb-4">New department</h3>

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

            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="name"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            Department name *</label>
                        <input type="text" name="name" id="name" required maxlength="100"
                            placeholder="e.g. Finance"
                            value="{{ old('name') }}"
                            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                            Stored lower-case, e.g. "Finance" becomes "finance".
                        </p>
                    </div>

                    <div>
                        <label for="description"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            Description</label>
                        <input type="text" name="description" id="description" maxlength="255"
                            placeholder="What this department does" value="{{ old('description') }}"
                            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label for="manager_id"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            Manager *</label>
                        <select name="manager_id" id="manager_id" required
                            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                            {{-- Disabled placeholder: a manager is required, so an
                                 empty manager_id must not be submittable. --}}
                            <option value="" disabled @selected(! old('manager_id'))>Choose a manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ (int) old('manager_id') === $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} — {{ $manager->email }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                            Active users holding the admin or manager role.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="submit"
                        class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                        Create Department
                    </button>
                    <button type="button" onclick="toggleAddDepartment()"
                        class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- List. No Manager column and no inline controls: the row links to a
         detail page for the team and an edit page for the settings. --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach (['Department', 'Description', 'Members', 'Status', 'Actions'] as $heading)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($departments as $department)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.departments.show', $department) }}"
                                    class="font-semibold text-gray-800 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ ucfirst($department->name) }}
                                </a>
                                @unless ($department->manager_id)
                                    <span
                                        class="ml-2 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 text-[10px] font-bold uppercase">
                                        No manager</span>
                                @endunless
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $department->description ?: '—' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $department->users_count }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @include('admin.departments._status', ['department' => $department])
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.departments.show', $department) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">
                                        View
                                    </a>

                                    @can('departments.edit')
                                        <a href="{{ route('admin.departments.edit', $department) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:hover:bg-blue-900/60 transition-colors">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No departments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards: the table would be unreadable at phone width. --}}
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($departments as $department)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <a href="{{ route('admin.departments.show', $department) }}"
                            class="font-semibold text-gray-800 dark:text-white">{{ ucfirst($department->name) }}</a>
                        @include('admin.departments._status', ['department' => $department])
                    </div>

                    @if ($department->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                            {{ $department->description }}</p>
                    @endif

                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                        {{ $department->users_count }} member(s)</p>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.departments.show', $department) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                            View
                        </a>
                        @can('departments.edit')
                            <a href="{{ route('admin.departments.edit', $department) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No departments yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Inside the section on purpose: a script placed after @endsection is
         emitted before the doctype by Blade. --}}
    <script>
        // Shows the add form when returning with a validation error, so the
        // messages are not hidden behind a collapsed panel.
        if (@json($errors->any() && old('name') !== null)) {
            document.addEventListener('DOMContentLoaded', toggleAddDepartment);
        }

        function toggleAddDepartment() {
            var panel = document.getElementById('add-department-panel');
            if (panel) panel.classList.toggle('hidden');
        }
    </script>
@endsection