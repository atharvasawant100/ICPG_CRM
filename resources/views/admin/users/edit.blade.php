@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)
@section('page-title', 'Edit User: ' . $user->name)

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Users & Employees</a>
                    <span>/</span>
                    <a href="{{ route('admin.users.show', $user) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $user->name }}</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit User: {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->email }} &middot; {{ $user->emp_id ?? 'No EMP ID' }} &middot; {{ ucfirst($user->department ?? 'General') }}
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('admin.users.show', $user) }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View User
                </a>
            </div>
        </div>

        {{-- Self-protection notice --}}
        @if (auth()->id() === $user->id)
            <div class="mb-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 p-4 rounded-xl text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>This is your own account. You cannot change your own department, designation, or deactivate yourself.</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-7">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])
        </form>

    </div>
</div>
@endsection