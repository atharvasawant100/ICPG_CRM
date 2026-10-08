@extends('layouts.app')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 max-w-2xl mx-auto border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $customer->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 dark:from-blue-700 dark:to-blue-800 dark:hover:from-blue-600 dark:hover:to-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">Edit</a>
                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to delete this customer?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 dark:from-red-700 dark:to-red-800 dark:hover:from-red-600 dark:hover:to-red-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">Delete</button>
                </form>
            </div>
        </div>

        <div class="space-y-5">
            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Email</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->email }}</p>
            </div>

            @if($customer->phone)
                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Phone</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->phone }}</p>
                </div>
            @endif

            @if($customer->company)
                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Company</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->company }}</p>
                </div>
            @endif

            @if($customer->address)
                <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Address</label>
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->address }}</p>
                </div>
            @endif

            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                <span
                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </div>



            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Created By</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->creator->name }}</p>
            </div>

            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Created At</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('customers.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 font-medium transition-colors">← Back to Customers</a>
        </div>
    </div>
@endsection