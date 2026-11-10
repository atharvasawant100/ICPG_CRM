@extends('layouts.app')

@php
    $roleDisplayName = ucwords(str_replace('_', ' ', $role->name));
    if (str_starts_with($roleDisplayName, 'Hr ') || $roleDisplayName === 'Hr') {
        $roleDisplayName = preg_replace('/^Hr\b/', 'HR', $roleDisplayName);
    }
@endphp

@section('title', 'Edit ' . $roleDisplayName)
@section('page-title', 'Edit ' . $roleDisplayName)

@section('content')
@php
    $standardActions = ['view', 'create', 'edit', 'delete', 'export'];
    $modules = array_keys($matrix);
    $specialModules = array_filter($modules, fn ($m) => array_diff(array_keys($matrix[$m]), $standardActions) !== []);
    $specialActions = [];
    foreach ($matrix as $actions) {
        foreach (array_diff(array_keys($actions), $standardActions) as $extra) {
            $specialActions[$extra] = true;
        }
    }
@endphp

<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.roles.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Roles & Permissions</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $roleDisplayName }}</span>
                    <span class="sr-only">{{ ucfirst($role->name) }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $roleDisplayName }} Permissions Matrix</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Fine-tune granular CRUD permissions, specialized actions, and data scopes for this role.
                </p>
            </div>

            <a href="{{ route('admin.roles.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Roles
            </a>
        </div>

        @if ($locked)
            <div class="mb-8 bg-blue-50 dark:bg-blue-950/40 border-l-4 border-blue-500 text-blue-800 dark:text-blue-200 p-4 rounded-xl text-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-bold">Admin always has full access.</span>
                        This system role is locked to prevent accidental lockout and its permissions cannot be modified.
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Module Access Table -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-2xs">
                <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Standard CRUD & Export Capabilities
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" data-permission-matrix>
                        <thead class="bg-gray-50/50 dark:bg-gray-750">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Module
                                </th>
                                @foreach ($standardActions as $action)
                                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div>{{ ucfirst($action) }}</div>
                                        @unless ($locked)
                                            <button type="button" data-toggle-column="{{ $action }}"
                                                class="mt-1 text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                toggle
                                            </button>
                                        @endunless
                                    </th>
                                @endforeach
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    All
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($modules as $module)
                                @php $available = array_keys($matrix[$module]); @endphp
                                <tr data-module-row="{{ $module }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/40">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                                        </span>
                                    </td>
                                    @foreach ($standardActions as $action)
                                        <td class="px-3 py-3.5 text-center">
                                            @if (in_array($action, $available, true))
                                                <input type="checkbox" name="permissions[{{ $module }}][{{ $action }}]"
                                                    value="1" data-action="{{ $action }}" data-module="{{ $module }}"
                                                    @checked(in_array("{$module}.{$action}", $granted, true)) {{ $locked ? 'disabled' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600 text-xs">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-3.5 text-center">
                                        @unless ($locked)
                                            <input type="checkbox" data-toggle-row="{{ $module }}"
                                                aria-label="Select all for {{ $module }}"
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Special Actions Table -->
            @if ($specialModules !== [])
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Specialized Actions
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/50 dark:bg-gray-750">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Module
                                    </th>
                                    @foreach (array_keys($specialActions) as $action)
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <div>{{ ucfirst(str_replace('_', ' ', $action)) }}</div>
                                            @unless ($locked)
                                                <button type="button" data-toggle-column="{{ $action }}"
                                                    class="mt-1 text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                    toggle
                                                </button>
                                            @endunless
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($specialModules as $module)
                                    @php $available = array_keys($matrix[$module]); @endphp
                                    <tr data-module-row="{{ $module }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/40">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ ucfirst(str_replace('_', ' ', $module)) }}
                                            </span>
                                        </td>
                                        @foreach (array_keys($specialActions) as $action)
                                            <td class="px-3 py-3.5 text-center">
                                                @if (in_array($action, $available, true))
                                                    <input type="checkbox" name="permissions[{{ $module }}][{{ $action }}]"
                                                        value="1" data-action="{{ $action }}" data-module="{{ $module }}"
                                                        @checked(in_array("{$module}.{$action}", $granted, true)) {{ $locked ? 'disabled' : '' }}
                                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600 text-xs">&mdash;</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Data Scopes -->
            @if (isset($scopes) && is_array($scopes) && count($scopes) > 0)
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Data Scopes (Visibility Filters)
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($modules as $module)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/40">
                                        <td class="px-5 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            @if ($locked)
                                                <span class="text-xs font-semibold text-gray-500">Everything (Locked)</span>
                                            @else
                                                <select name="scopes[{{ $module }}]"
                                                    class="py-1.5 px-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-blue-500">
                                                    <option value="own" {{ ($scopes[$module] ?? 'own') === 'own' ? 'selected' : '' }}>Own records only</option>
                                                    <option value="department" {{ ($scopes[$module] ?? 'own') === 'department' ? 'selected' : '' }}>Department</option>
                                                    <option value="team" {{ ($scopes[$module] ?? 'own') === 'team' ? 'selected' : '' }}>Own + direct reports</option>
                                                    <option value="all" {{ ($scopes[$module] ?? 'own') === 'all' ? 'selected' : '' }}>Everything</option>
                                                </select>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Bottom Action Bar -->
            @unless ($locked)
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('admin.roles.index') }}"
                        class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                        Cancel
                    </a>
                    <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                        <button type="submit"
                            class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                            Save Permissions
                        </button>
                    </div>
                </div>
            @endunless

        </form>

    </div>
</div>

<script>
    document.addEventListener('change', function (e) {
        var rowToggle = e.target.closest('[data-toggle-row]');
        if (rowToggle) {
            var row = rowToggle.closest('[data-module-row]');
            row.querySelectorAll('input[type="checkbox"][data-action]').forEach(function (box) {
                if (!box.disabled) box.checked = rowToggle.checked;
            });
            return;
        }
    });

    document.querySelectorAll('[data-toggle-column]').forEach(function (button) {
        button.addEventListener('click', function () {
            var action = this.getAttribute('data-toggle-column');
            var boxes = Array.prototype.slice.call(
                document.querySelectorAll('input[data-action="' + action + '"]:not([disabled])')
            );
            var allChecked = boxes.length > 0 && boxes.every(function (b) { return b.checked; });
            boxes.forEach(function (b) { b.checked = !allChecked; });
        });
    });
</script>
@endsection