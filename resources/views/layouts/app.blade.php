<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM') - ICPG Office</title>

    <link rel="icon" type="image/png" href="https://iconicpages.com/wp-content/uploads/2026/02/cropped-icon.png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        };
    </script>
    <script>
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <style>
        /* Dark mode content overrides so inner pages look good without editing each view */
        .dark main .bg-white { background-color: rgb(31 41 55); border-color: rgb(55 65 81); }
        .dark main .bg-gray-50 { background-color: rgba(31 41 55 / 0.6); }
        .dark main .bg-gray-100 { background-color: rgb(55 65 81); }
        .dark main .bg-gray-200 { background-color: rgb(75 85 99); }
        .dark main .text-gray-800,
        .dark main .text-gray-900 { color: rgb(243 244 246); }
        .dark main .text-gray-600,
        .dark main .text-gray-500,
        .dark main .text-gray-700 { color: rgb(209 213 219); }
        .dark main .text-gray-400 { color: rgb(156 163 175); }
        .dark main .border-gray-100,
        .dark main .border-gray-200,
        .dark main .border-gray-300 { border-color: rgb(75 85 99); }
        .dark main thead.bg-gray-50 { background-color: rgb(55 65 81); }
        .dark main tbody.bg-white,
        .dark main tr.bg-white { background-color: rgb(31 41 55); }
        .dark main tbody tr:hover { background-color: rgb(55 65 81); }
        .dark main .divide-y > *:not([hidden]) ~ * { border-color: rgb(75 85 99); }

        /* Form controls */
        .dark main input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="file"]),
        .dark main select,
        .dark main textarea {
            background-color: rgb(31 41 55) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(75 85 99) !important;
            color-scheme: dark;
        }
        .dark main input::placeholder,
        .dark main textarea::placeholder { color: rgb(156 163 175); }
        .dark main input[type="file"] {
            color: rgb(209 213 219);
            color-scheme: dark;
        }
        .dark main input[type="checkbox"],
        .dark main input[type="radio"] {
            background-color: rgb(31 41 55);
            border-color: rgb(75 85 99);
            color-scheme: dark;
        }
        .dark main option { background-color: rgb(31 41 55); color: rgb(243 244 246); }

        /* Neutral hover states */
        .dark main .hover\:bg-gray-50:hover,
        .dark main .hover\:bg-gray-100:hover { background-color: rgb(55 65 81) !important; }
        .dark main .hover\:bg-gray-200:hover { background-color: rgb(75 85 99) !important; }
        .dark main .hover\:border-gray-300:hover { border-color: rgb(107 114 128) !important; }

        /* Gradient stops that stay white without overrides */
        .dark main .from-white { --tw-gradient-from: rgb(31 41 55) var(--tw-gradient-from-position); --tw-gradient-to: rgb(31 41 55 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .dark main .to-white { --tw-gradient-to: rgb(31 41 55) var(--tw-gradient-to-position); }
        .dark main .from-gray-50 { --tw-gradient-from: rgb(31 41 55) var(--tw-gradient-from-position); --tw-gradient-to: rgb(31 41 55 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .dark main .to-gray-50 { --tw-gradient-to: rgb(31 41 55) var(--tw-gradient-to-position); }
        .dark main .from-gray-100 { --tw-gradient-from: rgb(55 65 81) var(--tw-gradient-from-position); --tw-gradient-to: rgb(55 65 81 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .dark main .to-gray-100 { --tw-gradient-to: rgb(55 65 81) var(--tw-gradient-to-position); }

        /* Status badges / pills */
        .dark main .bg-green-100 { background-color: rgb(20 83 45 / 0.4); }
        .dark main .text-green-800, .dark main .text-green-700 { color: rgb(134 239 172); }
        .dark main .bg-red-100 { background-color: rgb(127 29 29 / 0.4); }
        .dark main .text-red-800, .dark main .text-red-700 { color: rgb(252 165 165); }
        .dark main .bg-yellow-100 { background-color: rgb(113 63 18 / 0.4); }
        .dark main .text-yellow-800, .dark main .text-yellow-700 { color: rgb(253 224 71); }
        .dark main .bg-amber-100 { background-color: rgb(120 53 15 / 0.4); }
        .dark main .text-amber-800, .dark main .text-amber-700 { color: rgb(252 211 77); }
        .dark main .border-amber-200 { border-color: rgb(180 83 9 / 0.5); }
        .dark main .bg-blue-100 { background-color: rgb(30 58 138 / 0.4); }
        .dark main .text-blue-800, .dark main .text-blue-700 { color: rgb(147 197 253); }
        .dark main .bg-purple-100 { background-color: rgb(88 28 135 / 0.4); }
        .dark main .text-purple-800, .dark main .text-purple-700 { color: rgb(216 180 254); }
        .dark main .bg-indigo-100 { background-color: rgb(49 46 129 / 0.4); }
        .dark main .text-indigo-800, .dark main .text-indigo-700 { color: rgb(165 180 252); }
        .dark main .bg-orange-100 { background-color: rgb(124 45 18 / 0.4); }
        .dark main .text-orange-800, .dark main .text-orange-700 { color: rgb(253 186 116); }
        .dark main .bg-pink-100 { background-color: rgb(131 24 67 / 0.4); }
        .dark main .text-pink-800, .dark main .text-pink-700 { color: rgb(249 168 212); }
        .dark main .bg-emerald-100 { background-color: rgb(6 78 59 / 0.4); }
        .dark main .text-emerald-800, .dark main .text-emerald-700 { color: rgb(110 231 183); }
        .dark main .bg-cyan-100 { background-color: rgb(22 78 99 / 0.4); }
        .dark main .text-cyan-800, .dark main .text-cyan-700 { color: rgb(103 232 249); }
        .dark main .bg-teal-100 { background-color: rgb(19 78 74 / 0.4); }
        .dark main .text-teal-800, .dark main .text-teal-700 { color: rgb(94 234 212); }

        /* Soft colored backgrounds used on cards / alerts */
        .dark main .bg-green-50 { background-color: rgb(20 83 45 / 0.25); }
        .dark main .bg-red-50 { background-color: rgb(127 29 29 / 0.25); }
        .dark main .bg-yellow-50 { background-color: rgb(113 63 18 / 0.25); }
        .dark main .bg-amber-50 { background-color: rgb(120 53 15 / 0.25); }
        .dark main .bg-blue-50 { background-color: rgb(30 58 138 / 0.25); }
        .dark main .bg-purple-50 { background-color: rgb(88 28 135 / 0.25); }
        .dark main .bg-indigo-50 { background-color: rgb(49 46 129 / 0.25); }
        .dark main .bg-orange-50 { background-color: rgb(124 45 18 / 0.25); }

        /* Link / button hover text that stays too dark */
        .dark main .hover\:text-blue-800:hover { color: rgb(147 197 253) !important; }
        .dark main .hover\:text-green-800:hover { color: rgb(134 239 172) !important; }
        .dark main .hover\:text-red-800:hover { color: rgb(252 165 165) !important; }
        .dark main .hover\:text-gray-800:hover,
        .dark main .hover\:text-gray-900:hover { color: rgb(243 244 246) !important; }
        .dark main .hover\:text-gray-700:hover { color: rgb(229 231 235) !important; }

        /* Modern thin scrollbar — hide Windows up/down arrow buttons */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(156 163 175) transparent;
        }
        .dark * {
            scrollbar-color: rgb(75 85 99) transparent;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-button {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background-color: rgb(156 163 175);
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background-color: rgb(75 85 99);
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: rgb(107 114 128);
        }
    </style>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-200">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 z-[60] lg:hidden hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="fixed lg:static inset-y-0 left-0 z-[70] -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out w-64 max-w-[85vw]">
            @include('components.sidebar')
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden w-full lg:w-auto">
            <!-- Header -->
            <header class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-md border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-30 transition-colors duration-200">
                <div class="px-4 sm:px-5 md:px-6 py-3 sm:py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button onclick="toggleSidebar()" class="lg:hidden text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white mr-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                    </div>
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        
                        <!-- Theme Toggle -->
                        <button type="button" id="theme-toggle" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Toggle dark/light mode" aria-label="Toggle theme">
                            <svg id="theme-icon-light" class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                            <svg id="theme-icon-dark" class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                        </button>
                        <!-- Notification Bell (Admin Only) -->
                        @if(auth()->user()->isAdmin())
                        <div class="relative group">
                            <button type="button" class="relative p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500" 
                                    onclick="toggleNotifications()"
                                    title="Notifications">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <!-- Notification Badge -->
                                @php
                                    $upcomingCount = \App\Models\RecurringInvoiceTemplate::active()
                                        ->where('next_generation_date', '<=', \Carbon\Carbon::today()->addDays(3))
                                        ->count();

                                    $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
                                    $totalNotificationCount = $upcomingCount + $unreadNotificationsCount;
                                @endphp
                                <span id="notification-badge"
                                    class="{{ $totalNotificationCount > 0 ? '' : 'hidden' }} absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
                                    data-upcoming="{{ $upcomingCount }}"
                                    data-unread="{{ $unreadNotificationsCount }}">
                                    {{ $totalNotificationCount > 0 ? $totalNotificationCount : '' }}
                                </span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notification-dropdown" class="hidden fixed md:absolute left-1/2 md:left-auto -translate-x-1/2 md:translate-x-0 md:right-0 top-16 md:top-auto mt-2 w-[90vw] max-w-md md:w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notifications</h3>
                                </div>
                                <div id="notification-list" class="max-h-96 overflow-y-auto">
                                    @php
                                        $upcomingTemplates = \App\Models\RecurringInvoiceTemplate::with('customer')
                                            ->active()
                                            ->where('next_generation_date', '<=', \Carbon\Carbon::today()->addDays(3))
                                            ->orderBy('next_generation_date')
                                            ->limit(10)
                                            ->get();
                                            
                                        $notifications = auth()->user()->unreadNotifications()->latest()->limit(10)->get();
                                    @endphp
                                    
                                    <div id="notification-unread-items">
                                        @foreach($notifications as $notification)
                                            <div id="notification-{{ $notification->id }}" data-notification-item class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 transition-colors bg-blue-50/50 dark:bg-blue-900/20 relative group">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50">
                                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="ml-3 flex-1 pr-6">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $notification->data['message'] ?? 'New Notification' }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </p>
                                                        @if(isset($notification->data['url']))
                                                        <a href="{{ $notification->data['url'] }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 inline-block">
                                                            View Details
                                                        </a>
                                                        @endif
                                                    </div>
                                                    <button type="button" onclick="markAsRead('{{ $notification->id }}')" class="absolute top-2 right-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Mark as read">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div id="notification-upcoming-items" data-upcoming-count="{{ $upcomingTemplates->count() }}">
                                        @foreach($upcomingTemplates as $template)
                                            @php
                                                $isDue = $template->next_generation_date->lte(\Carbon\Carbon::today());
                                                $daysUntil = $template->next_generation_date->diffInDays(\Carbon\Carbon::today());
                                            @endphp
                                            <a href="{{ route('recurring-invoices.show', $template) }}" data-upcoming-item class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 transition-colors">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        @if($isDue)
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50">
                                                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/50">
                                                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $template->customer->name ?? 'Customer' }}
                                                        </p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                            @if($isDue)
                                                                <span class="text-red-600 font-semibold">Due today</span>
                                                            @else
                                                                Due in {{ $daysUntil }} {{ $daysUntil == 1 ? 'day' : 'days' }}
                                                            @endif
                                                            - ₹{{ number_format($template->subtotal + $template->tax_amount - $template->discount_amount, 2) }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                            {{ $template->name }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                        
                                        @if($upcomingTemplates->count() > 0)
                                        <a href="{{ route('recurring-invoices.index') }}" id="notification-upcoming-footer" class="block px-4 py-3 text-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                            View All Templates
                                        </a>
                                        @endif
                                    </div>

                                    <div id="notification-empty" class="{{ ($upcomingTemplates->count() > 0 || $notifications->count() > 0) ? 'hidden' : '' }} px-4 py-8 text-center">
                                        <svg class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Current Time and Date -->
                        <div id="current-datetime" class="text-right">
                            <div class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200" id="current-time"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400" id="current-date"></div>
                        </div>
                        <!-- Profile Link -->
                        <a href="{{ route('profile.show') }}" class="hidden sm:flex items-center space-x-2 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-sm font-medium hidden md:inline">{{ Auth::user()->name }}</span>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-5 lg:p-6 pb-6 sm:pb-8 md:pb-10 bg-gray-50/50 dark:bg-gray-900/50 transition-colors duration-200">
                @if(session('success'))
                    <div class="flash-message mb-4 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg shadow-sm transition-all duration-500 ease-out" role="alert">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center min-w-0">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium">{{ session('success') }}</span>
                            </div>
                            <button type="button" onclick="dismissFlash(this)" class="flex-shrink-0 p-1 rounded hover:bg-green-200/60 dark:hover:bg-green-800/40 transition-colors" aria-label="Dismiss">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="flash-message mb-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg shadow-sm transition-all duration-500 ease-out" role="alert">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center min-w-0">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium">{{ session('error') }}</span>
                            </div>
                            <button type="button" onclick="dismissFlash(this)" class="flex-shrink-0 p-1 rounded hover:bg-red-200/60 dark:hover:bg-red-800/40 transition-colors" aria-label="Dismiss">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="flash-message mb-4 bg-gradient-to-r from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/30 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 px-4 py-3 rounded-lg shadow-sm transition-all duration-500 ease-out" role="alert">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center min-w-0">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium">{{ session('warning') }}</span>
                            </div>
                            <button type="button" onclick="dismissFlash(this)" class="flex-shrink-0 p-1 rounded hover:bg-amber-200/60 dark:hover:bg-amber-800/40 transition-colors" aria-label="Dismiss">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Invoice email popup (Success / Failed) - outside flex so it always shows on top --}}
@if(session('invoice_email_popup'))
    @php $popup = session('invoice_email_popup'); $isSuccess = ($popup['type'] ?? '') === 'success'; @endphp
    <div id="invoice-email-popup-overlay" class="fixed inset-0 bg-black/60 dark:bg-black/70 z-[9999] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="popup-title">
        <div id="invoice-email-popup" class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6">
            @if($isSuccess)
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 id="popup-title" class="text-lg font-bold text-green-800 dark:text-green-200">Success</h3>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $popup['message'] ?? 'Email sent successfully.' }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 id="popup-title" class="text-lg font-bold text-red-800 dark:text-red-200">Failed</h3>
                        <p class="mt-1 text-gray-600 dark:text-gray-300 text-sm break-words">{{ $popup['message'] ?? 'Email could not be sent.' }}</p>
                    </div>
                </div>
            @endif
            <div class="mt-6 flex justify-end">
                <button type="button" onclick="closeInvoiceEmailPopup()" class="px-5 py-2.5 rounded-lg font-semibold text-white {{ $isSuccess ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                    OK
                </button>
            </div>
        </div>
    </div>
    <script>
        function closeInvoiceEmailPopup() {
            var el = document.getElementById('invoice-email-popup-overlay');
            if (el) el.remove();
        }
        (function(){
            var overlay = document.getElementById('invoice-email-popup-overlay');
            if (overlay) overlay.addEventListener('click', function(e) {
                if (e.target === this) closeInvoiceEmailPopup();
            });
        })();
    </script>
@endif

    <script>
        function getNotificationBadge() {
            return document.getElementById('notification-badge');
        }

        function syncNotificationBadge(unreadCount, upcomingCount) {
            var badge = getNotificationBadge();
            if (!badge) return;

            unreadCount = Math.max(0, parseInt(unreadCount, 10) || 0);
            if (typeof upcomingCount === 'undefined' || upcomingCount === null) {
                upcomingCount = parseInt(badge.getAttribute('data-upcoming') || '0', 10) || 0;
            } else {
                upcomingCount = Math.max(0, parseInt(upcomingCount, 10) || 0);
            }

            badge.setAttribute('data-unread', String(unreadCount));
            badge.setAttribute('data-upcoming', String(upcomingCount));

            var total = unreadCount + upcomingCount;
            if (total > 0) {
                badge.textContent = String(total);
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }

            refreshNotificationEmptyState();
        }

        function refreshNotificationEmptyState() {
            var unreadItems = document.querySelectorAll('#notification-unread-items [data-notification-item]');
            var upcomingItems = document.querySelectorAll('#notification-upcoming-items [data-upcoming-item]');
            var empty = document.getElementById('notification-empty');
            if (!empty) return;

            if (unreadItems.length === 0 && upcomingItems.length === 0) {
                empty.classList.remove('hidden');
            } else {
                empty.classList.add('hidden');
            }
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function renderUnreadNotifications(notifications) {
            var container = document.getElementById('notification-unread-items');
            if (!container) return;

            var html = '';
            (notifications || []).forEach(function (n) {
                html += '<div id="notification-' + escapeHtml(n.id) + '" data-notification-item class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 transition-colors bg-blue-50/50 dark:bg-blue-900/20 relative group">'
                    + '<div class="flex items-start">'
                    + '<div class="flex-shrink-0"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50">'
                    + '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'
                    + '</span></div>'
                    + '<div class="ml-3 flex-1 pr-6">'
                    + '<p class="text-sm font-medium text-gray-900 dark:text-gray-100">' + escapeHtml(n.message) + '</p>'
                    + '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">' + escapeHtml(n.created_at || '') + '</p>'
                    + (n.url ? '<a href="' + escapeHtml(n.url) + '" class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 inline-block">View Details</a>' : '')
                    + '</div>'
                    + '<button type="button" onclick="markAsRead(\'' + escapeHtml(n.id) + '\')" class="absolute top-2 right-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Mark as read">'
                    + '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
                    + '</button>'
                    + '</div></div>';
            });

            container.innerHTML = html;
            refreshNotificationEmptyState();
        }

        // Mark notification as read
        function markAsRead(id) {
            fetch(`/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data || !data.success) return;

                var element = document.getElementById('notification-' + id);
                if (element) {
                    element.remove();
                }

                var badge = getNotificationBadge();
                var unread = badge ? (parseInt(badge.getAttribute('data-unread') || '0', 10) || 0) : 0;
                var upcoming = badge ? (parseInt(badge.getAttribute('data-upcoming') || '0', 10) || 0) : 0;
                syncNotificationBadge(Math.max(0, unread - 1), upcoming);
            })
            .catch(function (error) { console.error('Error:', error); });
        }

        // Show/hide overlay when sidebar is toggled
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                // Prevent body scroll when sidebar is open
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                // Restore body scroll when sidebar is closed
                document.body.style.overflow = '';
            }
        }
        
        // Toggle notification dropdown
        function toggleNotifications() {
            const dropdown = document.getElementById('notification-dropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notification-dropdown');
            const button = event.target.closest('button[onclick="toggleNotifications()"]');
            
            if (!button && dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        
        // Update current time and date
        function updateDateTime() {
            const now = new Date();
            
            // Format time (HH:MM:SS AM/PM)
            const hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            const timeString = `${String(displayHours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
            
            // Format date (DD MMM YYYY)
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const dayName = days[now.getDay()];
            const day = String(now.getDate()).padStart(2, '0');
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            const dateString = `${dayName}, ${day} ${month} ${year}`;
            
            // Update DOM
            const timeElement = document.getElementById('current-time');
            const dateElement = document.getElementById('current-date');
            if (timeElement) timeElement.textContent = timeString;
            if (dateElement) dateElement.textContent = dateString;
        }
        
        // Follow-up reminder polling (checks server every 60s, alerts 30/15/10/5 min before)
        (function () {
            var shownKey = 'shown_notification_ids';

            function getShown() {
                try { return JSON.parse(sessionStorage.getItem(shownKey)) || []; } catch (e) { return []; }
            }

            function markShown(ids) {
                try { sessionStorage.setItem(shownKey, JSON.stringify(ids.slice(-100))); } catch (e) {}
            }

            function pollNotifications() {
                fetch('{{ route('notifications.poll') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data) return;

                    syncNotificationBadge(data.unread_count, data.upcoming_count);
                    renderUnreadNotifications(data.notifications || []);

                    var shown = getShown();
                    (data.notifications || []).forEach(function (n) {
                        if (shown.indexOf(n.id) !== -1) return;
                        shown.push(n.id);
                        if (n.type === 'followup_reminder' && 'Notification' in window && Notification.permission === 'granted') {
                            var notif = new Notification('Follow-up Reminder', { body: n.message, icon: 'https://iconicpages.com/wp-content/uploads/2026/02/cropped-icon.png' });
                            if (n.url) notif.onclick = function () { window.focus(); window.location.href = n.url; };
                        }
                    });
                    markShown(shown);
                })
                .catch(function () {});
            }

            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            pollNotifications();
            setInterval(pollNotifications, 60000);
        })();

        // Theme toggle
        function toggleTheme() {
            var html = document.documentElement;
            var isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
        
        document.getElementById('theme-toggle')?.addEventListener('click', toggleTheme);

        // Flash messages: auto-dismiss after 3 seconds (+ manual close)
        function dismissFlash(btnOrEl) {
            var el = btnOrEl && btnOrEl.classList && btnOrEl.classList.contains('flash-message')
                ? btnOrEl
                : (btnOrEl && btnOrEl.closest ? btnOrEl.closest('.flash-message') : null);
            if (!el || el.dataset.dismissing) return;
            el.dataset.dismissing = '1';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            el.style.maxHeight = el.offsetHeight + 'px';
            el.style.overflow = 'hidden';
            // Force reflow then collapse height/margin
            requestAnimationFrame(function () {
                el.style.maxHeight = '0';
                el.style.marginBottom = '0';
                el.style.paddingTop = '0';
                el.style.paddingBottom = '0';
            });
            setTimeout(function () {
                if (el.parentNode) el.remove();
            }, 500);
        }

        function initFlashAutoDismiss() {
            document.querySelectorAll('.flash-message').forEach(function (el) {
                setTimeout(function () {
                    dismissFlash(el);
                }, 3000);
            });
        }
        
        // Initialize and update time every second
        document.addEventListener('DOMContentLoaded', function() {
            // Initial update
            updateDateTime();
            
            // Update every second
            setInterval(updateDateTime, 1000);

            initFlashAutoDismiss();
            
            // Close sidebar when clicking on overlay
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
        });
    </script>
</body>
</html>
