@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="space-y-4 sm:space-y-5 md:space-y-6 max-w-5xl mx-auto">
    @php
        $initials = collect(explode(' ', $user->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
        $roleName = $user->getRoleNames()->first();
        $designation = $user->designation ? ucwords($user->designation) : ($roleName ? ucfirst($roleName) : 'Employee');
        $department = $user->department ? ucfirst($user->department) : null;
    @endphp

    <!-- Hero Card with Avatar Photo Management -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-md relative overflow-hidden transition-colors duration-200">
        <!-- Top decorative gradient line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6 pt-2">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                <!-- Large Interactive Avatar -->
                <div class="flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-tr from-gray-800 via-gray-700 to-indigo-700 text-white font-extrabold text-3xl sm:text-4xl flex items-center justify-center shadow-lg ring-4 ring-gray-100 dark:ring-gray-700 overflow-hidden relative">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; document.getElementById('profile-avatar-fallback').style.display='flex';">
                                <span id="profile-avatar-fallback" style="display:none;" class="w-full h-full items-center justify-center">{{ $initials ?: 'ME' }}</span>
                            @else
                                <span>{{ $initials ?: 'ME' }}</span>
                            @endif

                            <!-- Hover Overlay -->
                            <button type="button" onclick="document.getElementById('profile-avatar-file-input').click()"
                                class="absolute inset-0 bg-black/55 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-[11px] font-bold transition-all duration-200 cursor-pointer">
                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Change Photo</span>
                            </button>
                        </div>

                        <!-- Camera Badge Button -->
                        <button type="button" onclick="document.getElementById('profile-avatar-file-input').click()"
                            title="Upload Profile Photo"
                            class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 transition-all hover:scale-110 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>

                        <!-- Hidden Upload Form -->
                        <form id="profile-avatar-upload-form" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input type="file" id="profile-avatar-file-input" name="avatar" accept="image/jpeg,image/png,image/jpg,webp,gif" onchange="document.getElementById('profile-avatar-upload-form').submit()">
                        </form>
                    </div>

                    @if ($user->avatar)
                        <form id="profile-avatar-remove-form" action="{{ route('profile.avatar.destroy') }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to remove your profile photo?')"
                                class="text-xs font-semibold text-red-500 hover:text-red-700 dark:text-red-400 hover:underline inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Remove Photo</span>
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h1>
                        @if ($user->emp_id)
                            <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                {{ $user->emp_id }}
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Active
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $designation }}</span>
                        @if ($department)
                            <span class="text-gray-400">&bull;</span>
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                {{ $department }}
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->email }}
                    </div>

                    <p class="text-xs text-gray-400 dark:text-gray-500 pt-1">
                        Click avatar to upload or change photo (JPG, PNG, WebP up to 5MB).
                    </p>
                </div>
            </div>

            <!-- Action: Edit Profile -->
            <div class="flex-shrink-0">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-md transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Details</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Profile Information Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Account Information</h2>
        
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
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 transition-colors duration-200">
        <div class="border-b border-gray-100 dark:border-gray-700/80 pb-4 mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Change Password</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ensure your account is using a long, random password to stay secure.</p>
        </div>
        <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="max-w-xl">
                <label for="current_password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Current Password <span class="text-red-500">*</span>
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" name="current_password" id="current_password" required
                           placeholder="••••••••"
                           class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all">
                </div>
                @error('current_password')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required
                               placeholder="Minimum 8 characters"
                               class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all">
                    </div>
                    @error('password')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               placeholder="Re-enter password"
                               class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all">
                    </div>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Update Password</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

