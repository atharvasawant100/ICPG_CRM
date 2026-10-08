@extends('layouts.app')

@section('title', 'Create Service')
@section('page-title', 'Create Service')

@section('content')
    <div
        class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create New Service</h2>
        </div>

        @if($errors->any())
            <div
                class="mb-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li class="font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('services.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Service Name -->
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Service Name
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="e.g., Website Development, SEO Services"
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                </div>

                <!-- Rate -->
                <div>
                    <label for="rate" class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Rate (₹)
                        <span class="text-red-500">*</span></label>
                    <input type="number" name="rate" id="rate" value="{{ old('rate') }}" step="0.01" min="0" required
                        placeholder="0.00"
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                </div>

                <!-- Frequency -->
                <div>
                    <label for="frequency"
                        class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Frequency <span
                            class="text-red-500">*</span></label>
                    <select name="frequency" id="frequency" required onchange="toggleCustomInterval()"
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                        <option value="one-time" {{ old('frequency') === 'one-time' ? 'selected' : '' }}>One Time</option>
                        <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly
                        </option>
                        <option value="quarterly" {{ old('frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="custom" {{ old('frequency') === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <!-- Custom Interval Days (only shown when frequency is custom) -->
                <div id="custom_interval_container"
                    style="display: {{ old('frequency') === 'custom' ? 'block' : 'none' }};">
                    <label for="custom_interval_days"
                        class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Interval (Days) <span
                            class="text-red-500">*</span></label>
                    <input type="number" name="custom_interval_days" id="custom_interval_days"
                        value="{{ old('custom_interval_days', 30) }}" min="1" placeholder="e.g., 30"
                        class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">
                </div>

                <!-- Active Status -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                            class="mr-3 rounded border-gray-300 text-gray-600 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-blue-600">
                        <span class="text-gray-700 font-semibold dark:text-gray-300">Active Service</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6 dark:text-gray-400">Only active services will appear in
                        invoice creation</p>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description"
                    class="block text-gray-700 text-sm font-semibold mb-2 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="4" placeholder="Describe the service in detail..."
                    class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-4 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-gray-400">{{ old('description') }}</textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600">
                    Create Service
                </button>
                <a href="{{ route('services.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:border-gray-500">
                    Cancel
                </a>
            </div>
        </form>
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
                input.value = '';
            }
        }
    </script>
@endsection