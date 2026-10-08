@extends('layouts.app')

@section('title', 'Edit ' . ucfirst($role->name))
@section('page-title', 'Edit ' . ucfirst($role->name))

@section('content')
    @php
        // Split the matrix into the standard CRUD block and the extras.
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

    <div class="mb-4 sm:mb-5 md:mb-6">
        <a href="{{ route('admin.roles.index') }}"
            class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-3">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Roles
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
            {{ ucfirst($role->name) }} Permissions
        </h2>
    </div>

    @if ($locked)
        <div class="mb-5 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg text-sm">
            <span class="font-semibold">Admin always has full access.</span>
            This role is locked and its permissions cannot be changed.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-4 sm:mb-5 md:mb-6">
            <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Module Access
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" data-permission-matrix>
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Module
                            </th>
                            @foreach ($standardActions as $action)
                                <th
                                    class="px-3 sm:px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <div>{{ ucfirst($action) }}</div>
                                    @unless ($locked)
                                        <button type="button" data-toggle-column="{{ $action }}"
                                            class="mt-1 text-[10px] font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            all
                                        </button>
                                    @endunless
                                </th>
                            @endforeach
                            <th
                                class="px-3 sm:px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                All
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($modules as $module)
                            @php $available = array_keys($matrix[$module]); @endphp
                            <tr data-module-row="{{ $module }}">
                                <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                                        {{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                                </td>
                                @foreach ($standardActions as $action)
                                    <td class="px-3 sm:px-4 py-3 text-center">
                                        @if (in_array($action, $available, true))
                                            <input type="checkbox" name="permissions[{{ $module }}][{{ $action }}]"
                                                value="1" data-action="{{ $action }}" data-module="{{ $module }}"
                                                @checked(in_array("{$module}.{$action}", $granted, true)) {{ $locked ? 'disabled' : '' }}
                                                class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-3 sm:px-4 py-3 text-center">
                                    @unless ($locked)
                                        <input type="checkbox" data-toggle-row="{{ $module }}"
                                            aria-label="Select all for {{ $module }}"
                                            class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Extra, module-specific permissions --}}
        @if ($specialActions !== [])
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-4 sm:mb-5 md:mb-6">
                <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Special Permissions
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Module
                                </th>
                                @foreach (array_keys($specialActions) as $action)
                                    <th
                                        class="px-3 sm:px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ ucfirst(str_replace('.', ' ', $action)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($specialModules as $module)
                                <tr>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                                    </td>
                                    @foreach (array_keys($specialActions) as $action)
                                        <td class="px-3 sm:px-4 py-3 text-center">
                                            @if (in_array($action, $matrix[$module], true))
                                                <input type="checkbox" name="permissions[{{ $module }}][{{ $action }}]"
                                                    value="1"
                                                    @checked(in_array("{$module}.{$action}", $granted, true)) {{ $locked ? 'disabled' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600 text-xs">-</span>
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

        {{-- Data scope per module --}}
        @if ($scopedModules !== [])
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-4 sm:mb-5 md:mb-6">
                <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Data Scope
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        How much of each module this role can see. Modules with no owner column are not listed.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Module
                                </th>
                                <th
                                    class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Scope
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($scopedModules as $module)
                                <tr>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3">
                                        @if ($locked)
                                            <span class="text-sm text-gray-500 dark:text-gray-400 capitalize">
                                                {{ $scopes[$module] ?? 'all' }}</span>
                                        @else
                                            <select name="scopes[{{ $module }}]"
                                                class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full sm:w-48 py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                                                <option value="own" {{ ($scopes[$module] ?? 'own') === 'own' ? 'selected' : '' }}>
                                                    Own records only</option>
                                                <option value="team" {{ ($scopes[$module] ?? 'own') === 'team' ? 'selected' : '' }}>
                                                    Own + direct reports</option>
                                                <option value="all" {{ ($scopes[$module] ?? 'own') === 'all' ? 'selected' : '' }}>
                                                    Everything</option>
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

        @unless ($locked)
            <div class="flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Save Permissions
                </button>
                <a href="{{ route('admin.roles.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Cancel
                </a>
            </div>
        @endunless
    </form>

    <script>
        // Row and column "select all". Cosmetic only: the server re-derives the
        // full permission set from the submitted checkboxes and intersects it
        // with the permissions it knows about.
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