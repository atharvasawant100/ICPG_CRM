@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Settings</h2>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 sm:p-12 border border-gray-100 dark:border-gray-700 max-w-2xl text-center">
        <div
            class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>

        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Settings coming soon</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Company details, invoice defaults, notification preferences and email templates will live here.
            Nothing is configurable yet.
        </p>

        <div
            class="mt-6 inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300">
            Admin only
        </div>
    </div>
@endsection