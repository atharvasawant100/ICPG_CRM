@extends('layouts.app')

@section('title', 'Add New Expense')
@section('page-title', 'Add New Expense')

@section('content')
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 max-w-4xl mx-auto border border-gray-100 dark:border-gray-700">
        <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="category_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category
                        *</label>
                    <select name="category_id" id="category_id" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Amount (₹)
                        *</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                        required placeholder="0.00"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expense_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Expense
                        Date *</label>
                    <input type="date" name="expense_date" id="expense_date"
                        value="{{ old('expense_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('expense_date')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="receipt" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Receipt
                        <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                    <input type="file" name="receipt" id="receipt" accept="image/*,.pdf"
                        class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2 px-3 text-sm text-gray-700 dark:text-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 dark:file:bg-gray-600 file:text-gray-700 dark:file:text-gray-200 file:text-sm file:font-semibold file:cursor-pointer">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">Images or PDF · Max 5MB</p>
                    @error('receipt')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description
                        <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                    <textarea name="description" id="description" rows="3" placeholder="What was this expense for?"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all resize-none">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('expenses.index') }}"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors">Cancel</a>
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200">
                    Submit Expense
                </button>
            </div>
        </form>
    </div>
@endsection
