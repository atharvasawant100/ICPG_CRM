@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 max-w-2xl mx-auto border border-gray-100 dark:border-gray-700 transition-colors duration-200">
    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="mb-5">
            <label for="category_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category *</label>
            <select name="category_id" id="category_id" required
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-5">
            <label for="amount" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Amount (₹) *</label>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required
                   class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            @error('amount')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-5">
            <label for="expense_date" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Expense Date *</label>
            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                   class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            @error('expense_date')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-5">
            <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">{{ old('description', $expense->description) }}</textarea>
            @error('description')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-5">
            <label for="receipt" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Receipt (Image)</label>
            @if($expense->receipt)
                <div class="mb-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1 font-medium">Current Receipt:</p>
                    <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline text-sm font-semibold">View Current Receipt</a>
                </div>
            @endif
            <input type="file" name="receipt" id="receipt" accept="image/*"
                   class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Leave empty to keep current receipt. Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</p>
            @error('receipt')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('expenses.show', $expense) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors">Cancel</a>
            <button type="submit" class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200">
                Update Expense
            </button>
        </div>
    </form>
</div>
@endsection
