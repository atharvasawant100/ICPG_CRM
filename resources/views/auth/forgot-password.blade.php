<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - ICPG Office</title>
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
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-200 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center min-h-screen p-4 transition-colors duration-200">
    <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 md:p-10 rounded-xl shadow-lg w-full max-w-md border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        
        <!-- Header & Logo -->
        <div class="text-center mb-6">
            <div class="inline-block dark:bg-white/95 dark:rounded-xl dark:px-5 dark:py-3 mb-4 transition-colors duration-200">
                <img src="https://iconicpages.com/wp-content/themes/iconicpages-theme/assets/images/logo.png"
                    alt="ICPG Office Logo" class="mx-auto h-14 w-auto">
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Password?</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Enter your registered work email and we will send a 6-digit verification OTP from <strong class="text-indigo-600 dark:text-indigo-400 font-medium">no-reply@iconicpages.com</strong>.
            </p>
        </div>

        @if(session('status'))
            <div class="mb-4 bg-gradient-to-r from-emerald-50 to-green-100 dark:from-emerald-950/40 dark:to-green-900/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-lg shadow-sm text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/40 dark:to-red-800/40 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li class="font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-5">
                <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                    Registered Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        placeholder="you@iconicpages.com"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 pl-11 pr-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 mb-4">
                <span id="btnText">Send Verification OTP</span>
                <svg id="btnArrow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <svg id="btnSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </form>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Login
            </a>

            <button type="button" id="theme-toggle" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Toggle dark/light mode" aria-label="Toggle theme">
                <svg id="theme-icon-light" class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                <svg id="theme-icon-dark" class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
            </button>
        </div>
    </div>

    <script>
        const form = document.querySelector('form');
        form?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnArrow = document.getElementById('btnArrow');
            const btnSpinner = document.getElementById('btnSpinner');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                if (btnText) btnText.textContent = 'Sending OTP...';
                if (btnArrow) btnArrow.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');
            }
        });

        document.getElementById('theme-toggle')?.addEventListener('click', function () {
            var html = document.documentElement;
            var isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html>
