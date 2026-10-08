@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Audit Log</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            A record of who did what, and when. Entries cannot be edited or removed.
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="user_id"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">User</label>
                <select name="user_id" id="user_id"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All users</option>
                    @foreach ($users as $filterUser)
                        <option value="{{ $filterUser->id }}"
                            {{ (int) request('user_id') === $filterUser->id ? 'selected' : '' }}>
                            {{ $filterUser->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="module" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Module</label>
                <select name="module" id="module"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="action" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Action</label>
                <select name="action" id="action"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All actions</option>
                    @foreach ($actions as $option)
                        <option value="{{ $option }}" {{ request('action') === $option ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            </div>

            <div>
                <label for="date_to" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Apply Filters
                </button>
                <a href="{{ route('admin.audit-logs.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach (['When', 'User', 'Action', 'Module', 'Record', 'IP Address'] as $heading)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($logs as $log)
                        @php
                            $old = $log->old_values ?? [];
                            $new = $log->new_values ?? [];
                            $hasDetail = $old !== [] || $new !== [];
                            $changed = array_keys(
                                array_filter(array_keys($new), fn ($k) => ($old[$k] ?? null) !== ($new[$k] ?? null))
                            );
                        @endphp
                        <tr id="record-{{ $log->id }}" data-record="{{ $log->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->created_at?->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @php
                                    $actionColour = match ($log->action) {
                                        'create' => 'bg-green-100 text-green-800',
                                        'update' => 'bg-blue-100 text-blue-800',
                                        'delete' => 'bg-red-100 text-red-800',
                                        'login', 'logout' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-amber-100 text-amber-800',
                                    };
                                @endphp
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $actionColour }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst($log->module) }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->record_id ?? '-' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>

                        @if ($hasDetail)
                            <tr class="bg-gray-50/60 dark:bg-gray-900/30">
                                <td colspan="6" class="px-6 py-4">
                                    <details>
                                        <summary
                                            class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            Show values
                                        </summary>

                                        <div
                                            class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3 text-xs font-mono">
                                            <div>
                                                <div
                                                    class="font-semibold text-gray-600 dark:text-gray-300 mb-1 font-sans">
                                                    Old values</div>
                                                @forelse ($old as $key => $value)
                                                    <div
                                                        class="px-2 py-1 mb-0.5 rounded {{ in_array($key, $changed, true) ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400' }} border border-gray-200 dark:border-gray-700">
                                                        <span class="font-semibold">{{ $key }}:</span>
                                                        {{ is_scalar($value) || $value === null ? var_export($value, true) : json_encode($value) }}
                                                    </div>
                                                @empty
                                                    <div class="text-gray-400 italic font-sans">None</div>
                                                @endforelse
                                            </div>

                                            <div>
                                                <div
                                                    class="font-semibold text-gray-600 dark:text-gray-300 mb-1 font-sans">
                                                    New values</div>
                                                @forelse ($new as $key => $value)
                                                    <div
                                                        class="px-2 py-1 mb-0.5 rounded {{ in_array($key, $changed, true) ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400' }} border border-gray-200 dark:border-gray-700">
                                                        <span class="font-semibold">{{ $key }}:</span>
                                                        {{ is_scalar($value) || $value === null ? var_export($value, true) : json_encode($value) }}
                                                    </div>
                                                @empty
                                                    <div class="text-gray-400 italic font-sans">None</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No audit entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($logs as $log)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <span class="font-semibold text-gray-800 dark:text-white text-sm">
                            {{ $log->user?->name ?? 'System' }}</span>
                        <span
                            class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 flex-shrink-0">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ ucfirst($log->module) }} #{{ $log->record_id ?? '-' }} &middot;
                        {{ $log->created_at?->format('d M Y H:i') }}
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">No audit entries found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection