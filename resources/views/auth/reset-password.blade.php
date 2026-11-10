<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - ICPG Office</title>
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Your Password</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Enter the 6-digit OTP sent to your email and choose a strong new password.
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

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-1.5">
                    Email Address
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>

            <!-- OTP Input -->
            <div class="mb-5">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="otp" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold">
                        Verification OTP (6 digits)
                    </label>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Valid for 15 mins</span>
                </div>
                <div class="relative">
                    <input type="text" name="otp" id="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autofocus
                        placeholder="••••••"
                        value="{{ old('otp') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-3 px-4 text-center text-2xl font-mono tracking-[0.5em] font-bold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- New Password -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-1.5">
                    New Password
                </label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="8"
                        placeholder="At least 8 characters"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 pr-10 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                        <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeOffIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-1.5">
                    Confirm New Password
                </label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                        placeholder="Re-enter new password"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg w-full py-2.5 px-4 pr-10 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <button type="button" id="toggleConfirmPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                        <svg id="eyeIconConfirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeOffIconConfirm" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="resetBtn"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 mb-4">
                <span id="resetBtnText">Reset Password</span>
                <svg id="resetBtnSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </form>

        <!-- Resend OTP Form -->
        <div class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            <span>Didn't receive the OTP?</span>
            <form method="POST" action="{{ route('password.resend') }}" class="inline ml-1" id="resendOtpForm">
                @csrf
                <input type="hidden" name="email" id="resendEmail" value="{{ old('email', $email) }}">
                <button type="submit" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 underline focus:outline-none">
                    Resend Code
                </button>
            </form>
        </div>

        <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-100 dark:border-gray-700">
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
        // Synchronize email input with resend form hidden input
        const emailInput = document.getElementById('email');
        const resendEmail = document.getElementById('resendEmail');
        if (emailInput && resendEmail) {
            emailInput.addEventListener('input', function() {
                resendEmail.value = emailInput.value;
            });
        }

        // Toggle Password visibility
        function setupToggle(buttonId, inputId, eyeId, eyeOffId) {
            const btn = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                if (input.type === 'password') {
                    input.type = 'text';
                    eye.classList.add('hidden');
                    eyeOff.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    eye.classList.remove('hidden');
                    eyeOff.classList.add('hidden');
                }
            });
        }

        setupToggle('togglePassword', 'password', 'eyeIcon', 'eyeOffIcon');
        setupToggle('toggleConfirmPassword', 'password_confirmation', 'eyeIconConfirm', 'eyeOffIconConfirm');

        // Submit button loading state
        document.querySelector('form[action="{{ route('password.update') }}"]')?.addEventListener('submit', function() {
            const btn = document.getElementById('resetBtn');
            const btnText = document.getElementById('resetBtnText');
            const btnSpinner = document.getElementById('resetBtnSpinner');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                if (btnText) btnText.textContent = 'Resetting Password...';
                if (btnSpinner) btnSpinner.classList.remove('hidden');
            }
        });

        // Dark mode toggle
        document.getElementById('theme-toggle')?.addEventListener('click', function () {
            var html = document.documentElement;
            var isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html>
