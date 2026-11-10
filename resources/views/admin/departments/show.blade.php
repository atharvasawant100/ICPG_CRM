@extends('layouts.app')

@section('title', ucfirst($department->name))
@section('page-title', ucfirst($department->name))

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">


        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
                        {{ ucfirst($department->name) }}
                    </h2>
                    @include('admin.departments._status', ['department' => $department])
                </div>
                @if ($department->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                        {{ $department->description }}
                    </p>
                @endif
            </div>

            @can('departments.edit')
                <a href="{{ route('admin.departments.edit', $department) }}"
                    class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Department
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div
            class="mb-4 sm:mb-5 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 sm:mb-5 md:mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Team size</p>
            <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $teamCount }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $activeCount }} active
                @if ($teamCount > $activeCount)
                    · {{ $teamCount - $activeCount }} deactivated
                @endif
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Manager</p>
            @if ($department->manager)
                <p class="text-lg font-bold text-gray-800 dark:text-white truncate">
                    {{ $department->manager->name }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $department->manager->email }}
                    @unless ($department->manager->is_active)
                        · deactivated
                    @endunless
                </p>
            @else
                <p class="text-lg font-bold text-amber-600 dark:text-amber-400">Not set</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Every department needs a manager.
                </p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Status</p>
            <div class="mt-1">
                @include('admin.departments._status', ['department' => $department])
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                @if ($department->is_active)
                    Members can be assigned to this department.
                @else
                    New users cannot be assigned to it.
                @endif
            </p>
        </div>
    </div>

    {{-- Configured Designations & Role --}}
    @php
        $deptDesignations = $department->parsedDesignations();
    @endphp
    <div class="mb-4 sm:mb-5 md:mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 border border-gray-100 dark:border-gray-700">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">
                Configured Designations (Job Titles)
            </h3>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Security Role: 
                <a href="{{ route('admin.roles.index') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ ucfirst($department->name) }} &rarr;
                </a>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($deptDesignations as $slug => $label)
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $slug === 'manager' || str_contains($slug, 'manager') ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600' }}">
                    {{ $label }}
                    @if($slug === 'manager' || str_contains($slug, 'manager'))
                        <span class="ml-1.5 text-[10px] uppercase font-bold text-amber-700 dark:text-amber-400">&bull; Manager Role</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    {{-- Team --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Team</h3>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-white dark:bg-gray-800">
                    <tr>
                        @foreach (['Name', 'Designation', 'Access', 'Reports to', 'Status'] as $heading)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($team as $member)
                        @php($memberRole = $member->getRoleNames()->first())
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.users.show', $member) }}" class="group">
                                    <div class="font-semibold text-gray-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $member->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                </a>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ \App\Rbac\Designation::label($member->designation) ?? '—' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($memberRole)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">
                                        {{ ucfirst($memberRole) }}</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        No access</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if ($member->id === $department->manager_id)
                                    <span class="text-gray-400">—</span>
                                @else
                                    {{ $member->manager?->name ?? '—' }}
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($member->is_active)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        Active</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                        Deactivated</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <p class="text-gray-500 dark:text-gray-400">Nobody is assigned to this
                                    department yet.</p>
                                <a href="{{ route('admin.users.create') }}"
                                    class="inline-block mt-3 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                    Add someone
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($team as $member)
                @php($memberRole = $member->getRoleNames()->first())
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-800 dark:text-white truncate">{{ $member->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $member->email }}</div>
                        </div>
                        @if ($member->is_active)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 flex-shrink-0">
                                Active</span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 flex-shrink-0">
                                Deactivated</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ \App\Rbac\Designation::label($member->designation) ?? 'No designation' }}
                        </span>
                        @if ($memberRole)
                            <span
                                class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200 text-[10px] font-bold uppercase">
                                {{ ucfirst($memberRole) }}</span>
                        @endif
                        @if ($member->id === $department->manager_id)
                            <span
                                class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 text-[10px] font-bold uppercase">
                                Manager</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-10">
                    Nobody is assigned to this department yet.
                </p>
            @endforelse
        </div>
    </div>
@endsection