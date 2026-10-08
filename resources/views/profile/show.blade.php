@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="space-y-4 sm:space-y-5 md:space-y-6">
    <!-- Profile Information -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Profile Information</h2>
            <a href="{{ route('profile.edit') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 dark:from-blue-700 dark:to-blue-800 dark:hover:from-blue-600 dark:hover:to-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md transition-all duration-200">
                Edit Profile
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Name</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $user->name }}</p>
            </div>
            
            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Email</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $user->email }}</p>
            </div>
            
            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Role</label>
                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 capitalize">
                    {{ $user->role }}
                </span>
            </div>
            
            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Member Since</label>
                <p class="text-gray-800 dark:text-gray-100 font-medium">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Change Password</h3>
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-5">
                <label for="current_password" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Current Password *</label>
                <input type="password" name="current_password" id="current_password" required
                       class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                @error('current_password')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-5">
                <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">New Password *</label>
                <input type="password" name="password" id="password" required
                       class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                @error('password')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-5">
                <label for="password_confirmation" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Confirm New Password *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            
            <button type="submit" class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection

