@extends('layouts.app')

@section('title', 'Add Lead')
@section('page-title', 'Add Lead')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Choose How to Add Leads</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a method to add new leads to your CRM</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 md:gap-6">

            <!-- Option 1: Manual Entry -->
            <a href="{{ route('leads.create.manual') }}"
                class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 sm:p-6 hover:shadow-xl hover:border-gray-300 dark:hover:border-gray-500 transition-all duration-300 hover:-translate-y-1 cursor-pointer block">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/25">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                    <h3
                        class="text-base sm:text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        Manual Entry</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Add a single lead by filling out a form with
                        their details</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center">
                    <span
                        class="text-sm font-semibold text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 inline-flex items-center gap-1">
                        Add Lead
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </div>
            </a>

            <!-- Option 2: Bulk Import via XLSX -->
            @can('leads.import')
                <a href="{{ route('leads.create.bulk') }}"
                    class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 sm:p-6 hover:shadow-xl hover:border-gray-300 dark:hover:border-gray-500 transition-all duration-300 hover:-translate-y-1 cursor-pointer block">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-green-500/25">
                            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <h3
                            class="text-base sm:text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                            Bulk Import (XLSX)</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Upload an Excel file to import multiple
                            leads at once</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center">
                        <span
                            class="text-sm font-semibold text-green-600 dark:text-green-400 group-hover:text-green-700 dark:group-hover:text-green-300 inline-flex items-center gap-1">
                            Import Leads
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    </div>
                </a>
            @endcan

            <!-- Option 3: Meta Integration (Coming Soon) -->
            <div
                class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 sm:p-6 opacity-75 cursor-not-allowed relative overflow-hidden">
                <!-- Coming Soon Ribbon -->
                <div class="absolute top-3 right-3">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50">
                        Coming Soon
                    </span>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-400 to-purple-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-purple-500/25">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-500 dark:text-gray-400 mb-2">Meta Integration</h3>
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 leading-relaxed">Auto-import leads from Facebook & Instagram
                        ad campaigns</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-sm font-semibold text-gray-400 dark:text-gray-500 inline-flex items-center gap-1">
                        🔒 Available Soon
                    </span>
                </div>
            </div>

        </div>

    </div>
@endsection
