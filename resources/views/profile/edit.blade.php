@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80 gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('profile.show') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Profile</a>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit Profile</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Edit Profile
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Manage your personal details, profile picture, and contact information.
                </p>
            </div>
            <div>
                <a href="{{ route('profile.show') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>View Profile</span>
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were some problems with your submission</h3>
                        <ul class="mt-2 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            @php
                $initials = collect(explode(' ', $user->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
            @endphp

            <!-- Profile Photo Section -->
            <div class="p-6 rounded-2xl bg-gray-50/70 dark:bg-gray-750 border border-gray-200/80 dark:border-gray-700">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-4">Profile Photo</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-gray-800 to-indigo-700 text-white font-extrabold text-2xl flex items-center justify-center shadow-md flex-shrink-0 ring-4 ring-white dark:ring-gray-700 overflow-hidden">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; document.getElementById('edit-avatar-fallback').style.display='flex';">
                            <span id="edit-avatar-fallback" style="display:none;" class="w-full h-full items-center justify-center">{{ $initials ?: 'ME' }}</span>
                        @else
                            <span>{{ $initials ?: 'ME' }}</span>
                        @endif
                    </div>

                    <div class="flex-1">
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                               class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/40 dark:file:text-blue-300 transition-all cursor-pointer">
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, WebP or GIF up to 5MB.</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Standard 2-Col Input Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               placeholder="e.g. Rahul Sharma"
                               class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Your official name as displayed in the portal.</p>
                    @error('name')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               placeholder="e.g. employee@company.com"
                               class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Official work email for system notifications and login.</p>
                    @error('email')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('profile.show') }}"
                   class="order-2 sm:order-1 text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="order-1 sm:order-2 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Update Profile</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
