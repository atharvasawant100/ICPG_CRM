@extends('layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')

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
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">New User</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Add New User</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create a new employee profile with department, designation, and automatic role provisioning.
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Users
            </a>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-7">
            @csrf
            @include('admin.users._form', ['user' => null])
        </form>

    </div>
</div>
@endsection