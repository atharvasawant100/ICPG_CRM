@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-3">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Edit User</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $user->name }} &middot; {{ $user->email }}
        </p>
    </div>

    {{-- Self-protection notice: the server rejects these anyway, this just
         explains why the controls are inert for your own account. --}}
    @if (auth()->id() === $user->id)
        <div class="mb-5 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 px-4 py-3 rounded-lg text-sm">
            This is your own account. You cannot change your own role or deactivate yourself.
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 max-w-4xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form')
        </form>
    </div>
@endsection