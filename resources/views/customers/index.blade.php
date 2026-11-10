@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="space-y-4">

    <!-- Top Bar with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">All Customers</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage client accounts, contact details, brand portfolios, and assigned managers.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleFilters()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter Customers</span>
                <svg id="filter-arrow" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->hasAny(['search', 'status', 'assigned_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            @can('customers.create')
            <a href="{{ route('customers.create') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/30 transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Customer</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filters-panel" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 sm:p-5 transition-all"
        style="display: {{ request()->hasAny(['search', 'status', 'assigned_to']) ? 'block' : 'none' }};">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name, Email, Phone, Brand..."
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                    <select name="status" id="status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Designated Person Filter -->
                <div>
                    <label for="assigned_to" class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Designated Person</label>
                    <select name="assigned_to" id="assigned_to" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="">All Designated Persons</option>
                        @if(isset($assignableUsers))
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}" {{ (string)request('assigned_to') === (string)$u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Apply Filters
                    </button>
                    @if (request()->hasAny(['search', 'status', 'assigned_to']))
                    <a href="{{ route('customers.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>



    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brands
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Designated Person
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('customers.show', $customer) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-medium">{{ $customer->name }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $customer->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $brandList = $customer->brands_list;
                                    $brandCount = count($brandList);
                                @endphp
                                @if($brandCount === 0)
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                @elseif($brandCount <= 2)
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($brandList as $b)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-750 text-gray-800 dark:text-gray-200 border border-gray-200/60 dark:border-gray-650">
                                                {{ $b }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5" title="{{ implode(', ', $brandList) }}">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-750 text-gray-800 dark:text-gray-200 border border-gray-200/60 dark:border-gray-650">
                                            {{ $brandList[0] }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-750 text-gray-800 dark:text-gray-200 border border-gray-200/60 dark:border-gray-650">
                                            {{ $brandList[1] }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800 cursor-help"
                                              title="Additional brands: {{ implode(', ', array_slice($brandList, 2)) }}">
                                            +{{ $brandCount - 2 }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $displayName = $customer->designated_person ?: ($customer->assignedPerson?->name ?: ($customer->creator?->name ?? '—'));
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($displayName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 block">
                                            {{ $displayName }}
                                        </span>
                                        @if($customer->assignedPerson?->designation)
                                            <span class="text-[11px] text-gray-400 block">
                                                {{ $customer->assignedPerson->designation }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('customers.show', $customer) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                                    @can('customers.edit')
                                        <a href="{{ route('customers.edit', $customer) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                                    @endcan
                                    @can('customers.delete')
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($customers as $customer)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('customers.show', $customer) }}"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-semibold text-sm">{{ $customer->name }}</a>
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300 mb-3">
                        <p><span class="font-semibold">Email:</span> {{ $customer->email }}</p>
                        @if($customer->phone)
                            <p><span class="font-semibold">Phone:</span> {{ $customer->phone }}</p>
                        @endif
                        @php
                            $mobileBrandList = $customer->brands_list;
                            $mobileBrandCount = count($mobileBrandList);
                        @endphp
                        @if($mobileBrandCount > 0)
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-semibold">Brands:</span>
                                @if($mobileBrandCount <= 2)
                                    @foreach($mobileBrandList as $mb)
                                        <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $mb }}</span>
                                    @endforeach
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $mobileBrandList[0] }}</span>
                                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $mobileBrandList[1] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">+{{ $mobileBrandCount - 2 }}</span>
                                @endif
                            </div>
                        @endif
                        <p><span class="font-semibold">Designated Person:</span> {{ $customer->designated_person ?: ($customer->assignedPerson?->name ?: ($customer->creator?->name ?? '—')) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('customers.show', $customer) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        @can('customers.edit')
                            <a href="{{ route('customers.edit', $customer) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</a>
                        @endcan
                        @can('customers.delete')
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                onsubmit="return confirm('Delete this customer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No customers found.</p>
            @endforelse
        </div>

        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleFilters() {
    const panel = document.getElementById('filters-panel');
    const arrow = document.getElementById('filter-arrow');
    if (!panel) return;
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        if (arrow) arrow.classList.add('rotate-180');
    } else {
        panel.style.display = 'none';
        if (arrow) arrow.classList.remove('rotate-180');
    }
}
</script>
@endsection