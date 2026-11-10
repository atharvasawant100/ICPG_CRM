@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Roles &amp; Permissions</h2>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach (['Role', 'Users', 'Permissions', 'Actions'] as $heading)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($roles as $entry)
                        @php
                            $roleName = $entry['model']->name;
                            $displayName = ucwords(str_replace('_', ' ', $roleName));
                            if (str_starts_with($displayName, 'Hr ') || $displayName === 'Hr') {
                                $displayName = preg_replace('/^Hr\b/', 'HR', $displayName);
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800 dark:text-white">
                                    {{ $displayName }}
                                </div>
                                <span class="sr-only">{{ ucfirst($roleName) }}</span>
                                @if ($roleName === 'admin')
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Always has full access (System)
                                    </div>
                                @elseif (str_ends_with($roleName, '_manager') || $roleName === 'manager')
                                    <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5 font-medium">
                                        Department Management
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Department Staff
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry['users_count'] }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry['permissions_count'] }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @can('roles.edit')
                                        @if ($entry['model']->name === 'admin')
                                            <span class="text-xs text-gray-400 italic">Locked</span>
                                        @else
                                            <a href="{{ route('admin.roles.edit', $entry['model']) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                                Edit Permissions
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">View only</span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($roles as $entry)
                @php
                    $roleName = $entry['model']->name;
                    $displayName = ucwords(str_replace('_', ' ', $roleName));
                    if (str_starts_with($displayName, 'Hr ') || $displayName === 'Hr') {
                        $displayName = preg_replace('/^Hr\b/', 'HR', $displayName);
                    }
                @endphp
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="font-semibold text-gray-800 dark:text-white">
                            {{ $displayName }}</span>
                        <span class="sr-only">{{ ucfirst($roleName) }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $entry['users_count'] }} users &middot; {{ $entry['permissions_count'] }} perms
                        </span>
                    </div>
                    @can('roles.edit')
                        @if ($entry['model']->name !== 'admin')
                            <a href="{{ route('admin.roles.edit', $entry['model']) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                Edit Permissions</a>
                        @else
                            <span class="text-xs text-gray-400 italic">Locked</span>
                        @endif
                    @endcan
                </div>
            @endforeach
        </div>
    </div>
@endsection