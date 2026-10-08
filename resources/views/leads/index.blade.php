@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">All Leads</h2>
        <div class="flex flex-row gap-3">
            <a href="{{ route('leads.create') }}"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 text-center">
                Add Lead
            </a>
            <button type="button" onclick="toggleFilters()"
                class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                <span>Filters</span>
                <svg id="filter-arrow"
                    class="w-4 h-4 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'source']) ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filters Panel (Hidden by default, show if filters are active) -->
    <div id="filters-panel"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700"
        style="display: {{ request()->hasAny(['search', 'status', 'source']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('leads.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name, Email, Company..."
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
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

                <!-- Source Filter -->
                <div>
                    <label for="source" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Source</label>
                    <select name="source" id="source"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">All Sources</option>
                        <option value="Website" {{ request('source') === 'Website' ? 'selected' : '' }}>Website</option>
                        <option value="Referral" {{ request('source') === 'Referral' ? 'selected' : '' }}>Referral</option>
                        <option value="Social Media" {{ request('source') === 'Social Media' ? 'selected' : '' }}>Social Media
                        </option>
                        <option value="Email Campaign" {{ request('source') === 'Email Campaign' ? 'selected' : '' }}>Email
                            Campaign</option>
                        <option value="Cold Call" {{ request('source') === 'Cold Call' ? 'selected' : '' }}>Cold Call</option>
                        <option value="Trade Show" {{ request('source') === 'Trade Show' ? 'selected' : '' }}>Trade Show
                        </option>
                        <option value="Advertisement" {{ request('source') === 'Advertisement' ? 'selected' : '' }}>
                            Advertisement</option>
                        <option value="Partner" {{ request('source') === 'Partner' ? 'selected' : '' }}>Partner</option>
                        <option value="Other" {{ request('source') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Apply Filters
                </button>
                <a href="{{ route('leads.index') }}"
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

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Follow-ups</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                @php
                    $statusBadgeColors = [
                        'new' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'contacted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                        'interested' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300',
                        'qualified' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                        'converted' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'lost' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                    ];
                @endphp
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($leads as $lead)
                        <tr>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ url('/leads/' . $lead->id) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">{{ $lead->name }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $lead->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $lead->company ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $lead->source ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadgeColors[$lead->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $pendingFollowups = $lead->pendingFollowups;
                                    $overdueCount = $pendingFollowups->filter(fn($f) => $f->isOverdue())->count();
                                @endphp
                                @if($pendingFollowups->count() > 0)
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded {{ $overdueCount > 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $pendingFollowups->count() }} {{ $overdueCount > 0 ? 'Overdue' : 'Pending' }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ url('/leads/' . $lead->id) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                                    <a href="{{ url('/leads/' . $lead->id . '/edit') }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                                    @if($lead->isConverted())
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Converted</span>
                                    @elseif($lead->status !== 'lost')
                                        <form action="{{ route('leads.convert', $lead) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Convert to customer?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">Convert</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-6 py-4 text-center text-gray-500">No leads found.</td>
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
                        <div class="flex items-start gap-2 flex-1">

                            <a href="{{ url('/leads/' . $lead->id) }}"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 hover:underline font-semibold text-sm">{{ $lead->name }}</a>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadgeColors[$lead->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($lead->status) }}
                            </span>
                            @php
                                $pendingFollowups = $lead->pendingFollowups;
                                $overdueCount = $pendingFollowups->filter(fn($f) => $f->isOverdue())->count();
                            @endphp
                            @if($pendingFollowups->count() > 0)
                                <span
                                    class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $overdueCount > 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $pendingFollowups->count() }} {{ $overdueCount > 0 ? 'Overdue' : 'Pending' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400 mb-3">
                        <p><span class="font-semibold text-gray-700 dark:text-gray-300">Email:</span> {{ $lead->email }}</p>
                        @if($lead->company)
                            <p><span class="font-semibold text-gray-700 dark:text-gray-300">Company:</span> {{ $lead->company }}</p>
                        @endif
                        @if($lead->source)
                            <p><span class="font-semibold text-gray-700 dark:text-gray-300">Source:</span> {{ $lead->source }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ url('/leads/' . $lead->id) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        <a href="{{ url('/leads/' . $lead->id . '/edit') }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                        @if($lead->isConverted())
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Converted</span>
                        @elseif($lead->status !== 'lost')
                            <form action="{{ route('leads.convert', $lead) }}" method="POST" class="inline"
                                onsubmit="return confirm('Convert?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60 transition-colors">Convert</button>
                            </form>
                        @endif
                        <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this lead?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No leads found.</p>
            @endforelse
        </div>

        @if($leads->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
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