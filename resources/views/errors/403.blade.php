<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access denied - ICPG Office</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg text-center">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10">
            <!-- Icon -->
            <div class="w-20 h-20 mx-auto rounded-2xl bg-red-100 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
            </div>

            <!-- Heading -->
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Access denied</h1>
            <p class="text-sm sm:text-base text-gray-600 mb-1">
                You do not have permission to view this page.
            </p>
            <p class="text-xs text-gray-500 mb-8">
                If you believe this is a mistake, please contact an administrator.
            </p>

            <!-- Error detail, if the exception carried one -->
            @isset($exception)
                @if ($exception->getMessage() && $exception->getMessage() !== 'Unauthorized action.')
                    <div class="mb-6 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-800 text-sm rounded-lg text-left">
                        {{ $exception->getMessage() }}
                    </div>
                @endif
            @endisset

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Back to Dashboard
                </a>
                <button type="button" onclick="history.back()"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    Go Back
                </button>
            </div>
        </div>

        <p class="mt-6 text-xs text-gray-400">
            ICPG Office CRM &middot; Reference {{ uniqid() }}
        </p>
    </div>
</body>
</html>