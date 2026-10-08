@extends('layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-3">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Add User</h2>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 max-w-4xl">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['user' => null])
        </form>
    </div>
@endsection