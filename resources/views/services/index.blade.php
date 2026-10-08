@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
    <div class="mb-4 flex flex-row justify-between items-center gap-3 sm:gap-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Services</h2>
        @can('services.create')
        <a href="{{ route('services.create') }}"
            class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600">
            Create Service
        </a>
        @endcan
    </div>

    <!-- Filters Panel -->
    <div
        class="bg-white rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <form method="GET" action="{{ route('services.index') }}" class="flex flex-wrap gap-3">
            <select name="frequency" id="frequency" onchange="this.form.submit()"
                class="shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                <option value="">All Frequencies</option>
                <option value="one-time" {{ request('frequency') === 'one-time' ? 'selected' : '' }}>One Time</option>
                <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ request('frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="yearly" {{ request('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                <option value="custom" {{ request('frequency') === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>

            <select name="is_active" id="is_active" onchange="this.form.submit()"
                class="shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            @if(request()->hasAny(['frequency', 'is_active']))
                <a href="{{ route('services.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:border-gray-500">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service
                            Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($services as $service)
                        <tr class="hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('services.show', $service) }}"
                                    class="text-blue-600 hover:text-blue-800 hover:underline font-medium dark:text-blue-400 dark:hover:text-blue-300">{{ $service->name }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div class="max-w-xs truncate">{{ $service->description ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                ₹{{ number_format($service->rate, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ $service->frequency_label }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($service->is_active)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('services.show', $service) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
@can('services.edit')
                                    <a href="{{ route('services.edit', $service) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">Edit</a>
@endcan
@can('services.delete')
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                                    </form>
@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($services as $service)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow dark:from-gray-800 dark:to-gray-800 dark:border-gray-700">
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('services.show', $service) }}"
                            class="text-blue-600 hover:text-blue-800 hover:underline font-semibold text-sm dark:text-blue-400 dark:hover:text-blue-300">{{ $service->name }}</a>
                        @if($service->is_active)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 mb-3 dark:text-gray-400">
                        @if($service->description)
                            <p><span class="font-semibold text-gray-900 dark:text-gray-200">Description:</span>
                                {{ Str::limit($service->description, 100) }}</p>
                        @endif
                        <p><span class="font-semibold text-gray-900 dark:text-gray-200">Rate:</span>
                            ₹{{ number_format($service->rate, 2) }}</p>
                        <p><span class="font-semibold text-gray-900 dark:text-gray-200">Frequency:</span>
                            {{ $service->frequency_label }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('services.show', $service) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition-colors">View</a>
                        <a href="{{ route('services.edit', $service) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">Edit</a>
                        <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this service?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No services found.</p>
            @endforelse
        </div>

        @if($services->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection