@extends('layouts.app')

@section('title', 'Trash Leads')
@section('page-title', 'Trash Leads')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Deleted Leads</h2>
        <div class="flex flex-row gap-3">
            <a href="{{ route('leads.index') }}"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 text-center">
                ← Back to Leads
            </a>
            <button type="button" onclick="toggleFilters()"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                <span>Filters</span>
                <svg id="filter-arrow"
                    class="w-4 h-4 transition-transform duration-200 {{ request()->hasAny(['search', 'status']) ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filters Panel (Hidden by default, show if filters are active) -->
    <div id="filters-panel"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700"
        style="display: {{ request()->hasAny(['search', 'status']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('leads.trash') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search -->
                <div>
                    <label for="search"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name, Email, Company..."
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                    <select name="status" id="status"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                        <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Apply Filters
                </button>
                <a href="{{ route('leads.trash') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Clear
                </a>
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
                                <form action="{{ route('leads.restore', $lead->id) }}" method="POST" class="inline mr-2">
                                    @csrf
                                    <button type="submit"
                                        class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-semibold">Restore</button>
                                </form>
                                @can('leads.delete')
                                    <form action="{{ route('leads.force-delete', $lead->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Permanently delete this lead? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-semibold">Delete
                                            Permanently</button>
                                    </form>
                                @endcan
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
                    <div class="flex gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <form action="{{ route('leads.restore', $lead->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm font-semibold">Restore</button>
                        </form>
                        @can('leads.delete')
                            <form action="{{ route('leads.force-delete', $lead->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Delete permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-semibold">Delete</button>
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
