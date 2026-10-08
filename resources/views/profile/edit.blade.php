@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 max-w-2xl mx-auto border border-gray-100 dark:border-gray-700 transition-colors duration-200">
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-5">
            <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                   class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            @error('name')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-5">
            <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Email *</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                   class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            @error('email')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('profile.show') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors">Cancel</a>
            <button type="submit" class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200">
                Update Profile
            </button>
        </div>
    </form>
</div>
@endsection

