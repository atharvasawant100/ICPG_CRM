@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Settings Hub</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure company-wide policies, shift timings, and system parameters.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Attendance & Shift Timing Settings Card -->
        <a href="{{ route('admin.attendance.settings') }}"
            class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 dark:border-gray-700 p-6 transition-all duration-200 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    Attendance & Shift Settings
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                    Set official shift timings, automated late mark thresholds, half-day login rules, and auto punch-out policies for forgotten checkouts.
                </p>
            </div>

            <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                <span>Configure Timings & Policies</span>
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- Leave Types & Holiday Settings Card -->
        <a href="{{ route('admin.leaves.types') }}"
            class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 dark:border-gray-700 p-6 transition-all duration-200 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    Leave Types & Quotas
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                    Manage annual paid leave categories (Casual, Sick, Maternity), yearly allowances, and carry-forward limits.
                </p>
            </div>

            <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <span>Manage Leave Policies</span>
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- General / Email Settings Placeholder -->
        <div class="bg-gray-50/80 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between opacity-80">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-600 dark:text-gray-300">
                    Email & Invoice Defaults
                </h3>
                <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                    Custom SMTP settings, company logos, invoice numbering prefixes, and automatic payment notification templates.
                </p>
            </div>

            <div class="mt-5 pt-3 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-[11px] font-semibold text-gray-400">
                <span>Coming Soon</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-[10px]">Planned</span>
            </div>
        </div>
    </div>
@endsection