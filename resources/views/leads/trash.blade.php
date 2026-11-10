@extends('layouts.app')

@section('title', 'Trash Leads')
@section('page-title', 'Trash Leads')

@section('content')
<div class="space-y-4">

    <!-- Top Bar with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Deleted Leads</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">View and restore soft-deleted leads or permanently remove them.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Leads</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <a href="{{ route('leads.index') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Leads</span>
            </a>
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('leads.trash') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, Email, Company..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" id="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                        <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Apply Filters
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('leads.trash') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Name</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Company</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Deleted At</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-800 dark:text-white font-medium">{{ $lead->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $lead->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $lead->company ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $lead->deleted_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <form action="{{ route('leads.restore', $lead->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                            Restore
                                        </button>
                                    </form>
                                    @can('leads.delete')
                                        <form action="{{ route('leads.force-delete', $lead->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Permanently delete this lead? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors">
                                                Delete Permanently
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No deleted leads
                                found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($leads as $lead)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-gray-800 dark:text-white font-semibold text-sm">{{ $lead->name }}</span>
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                            {{ ucfirst($lead->status) }}
                        </span>
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400 mb-3">
                        <p><span class="font-semibold text-gray-700 dark:text-gray-300">Email:</span> {{ $lead->email }}</p>
                        @if($lead->company)
                            <p><span class="font-semibold text-gray-700 dark:text-gray-300">Company:</span> {{ $lead->company }}</p>
                        @endif
                        <p><span class="font-semibold text-gray-700 dark:text-gray-300">Deleted:</span>
                            {{ $lead->deleted_at->format('M d, Y') }}</p>
                    </div>
                    <div class="flex gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-700 flex-wrap">
                        <form action="{{ route('leads.restore', $lead->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition-colors">
                                Restore
                            </button>
                        </form>
                        @can('leads.delete')
                            <form action="{{ route('leads.force-delete', $lead->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Delete permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition-colors">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No deleted leads found.</p>
            @endforelse
        </div>

        @if($leads->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>

    <script>
        function toggleFilters() {
            const panel = document.getElementById('filters-panel');
            const arrow = document.getElementById('filter-arrow');

            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
                arrow.classList.add('rotate-180');
            } else {
                panel.style.display = 'none';
                arrow.classList.remove('rotate-180');
            }
        }
    </script>
@endsection
