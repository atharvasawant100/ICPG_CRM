@extends('layouts.app')

@section('title', 'Edit Service - ' . $service->name)
@section('page-title', 'Edit Service: ' . $service->name)

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('services.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Services</a>
                    <span>/</span>
                    <a href="{{ route('services.show', $service) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $service->name }}</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Service: {{ $service->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update billing rate, recurrence schedule, and service details.
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('services.show', $service) }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Service
                </a>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Elements -->
        <form method="POST" action="{{ route('services.update', $service) }}" class="space-y-7">
            @csrf
            @method('PUT')

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Service Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Service Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required
                            placeholder="e.g. Website Development, SEO Retainer"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('name') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Official name of the deliverable or service</p>
                </div>

                <!-- Rate -->
                <div>
                    <label for="rate" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Billing Rate (₹) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <span class="text-sm font-bold text-gray-400">₹</span>
                        </div>
                        <input type="number" name="rate" id="rate" value="{{ old('rate', $service->rate) }}" step="0.01" min="0" required
                            placeholder="0.00"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('rate') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Default price in INR excluding taxes</p>
                </div>

                <!-- Frequency -->
                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Billing Frequency <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <select name="frequency" id="frequency" required onchange="toggleCustomInterval()"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('frequency') border-red-500 @enderror">
                            <option value="one-time" {{ old('frequency', $service->frequency) === 'one-time' ? 'selected' : '' }}>One Time</option>
                            <option value="weekly" {{ old('frequency', $service->frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('frequency', $service->frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency', $service->frequency) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency', $service->frequency) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency', $service->frequency) === 'custom' ? 'selected' : '' }}>Custom Interval</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Determines recurring invoice intervals</p>
                </div>

                <!-- Custom Interval Days (conditional) -->
                <div id="custom_interval_container" style="display: {{ old('frequency', $service->frequency) === 'custom' ? 'block' : 'none' }};">
                    <label for="custom_interval_days" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Interval (Days) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="number" name="custom_interval_days" id="custom_interval_days"
                            value="{{ old('custom_interval_days', $service->custom_interval_days) }}" min="1"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Days between renewal billing</p>
                </div>

                <!-- Active Status -->
                <div class="md:col-span-2 pt-2">
                    <label for="is_active" class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            @checked(old('is_active', $service->is_active))
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Active Service</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Only active services appear when generating new invoices</p>
                        </div>
                    </label>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Description
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="description" id="description" rows="4"
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('description', $service->description) }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Scope of service for invoice itemization</p>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('services.show', $service) }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Update Service
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    function toggleCustomInterval() {
        const frequency = document.getElementById('frequency').value;
        const container = document.getElementById('custom_interval_container');
        const input = document.getElementById('custom_interval_days');

        if (frequency === 'custom') {
            container.style.display = 'block';
            input.required = true;
        } else {
            container.style.display = 'none';
            input.required = false;
        }
    }

    // Initialize on page load
    toggleCustomInterval();
</script>
@endsection