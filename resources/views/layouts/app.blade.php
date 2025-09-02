<!-- filepath: resources\views\layouts\app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SKADA Exam System'))</title>
    <meta name="description" content="@yield('description', 'Student Management System for SKADA Exam')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Additional Styles -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if (config('app.debug'))
        <!-- Debug Reload Detector -->
        <script src="{{ asset('js/debug-reload.js') }}"></script>
        <!-- Interval Patch -->
        <script src="{{ asset('js/interval-patch.js') }}"></script>
    @endif

    <!-- Navigation Fixes -->
    <script src="{{ asset('js/sidebar-navigation-fix.js') }}"></script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Breadcrumbs (if provided) -->
        @if (isset($breadcrumbs))
            <nav class="bg-gray-50 px-4 py-3" aria-label="Breadcrumb">
                <div class="max-w-7xl mx-auto">
                    <ol class="flex items-center space-x-2 text-sm">
                        {{ $breadcrumbs }}
                    </ol>
                </div>
            </nav>
        @endif

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
                <span class="text-gray-700">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div id="flash-messages" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="flash-message bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg" data-type="success">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
                <button onclick="closeFlashMessage(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="flash-message bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg" data-type="error">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
                <button onclick="closeFlashMessage(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Warning Message -->
    @if (session('warning'))
        <div class="flash-message bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg" data-type="warning">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                    {{ session('warning') }}
                </div>
                <button onclick="closeFlashMessage(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Info Message -->
    @if (session('info'))
        <div class="flash-message bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg" data-type="info">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    {{ session('info') }}
                </div>
                <button onclick="closeFlashMessage(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Base JavaScript -->
    <script>
        // CSRF Token setup
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Set CSRF token for all AJAX requests
        document.addEventListener('DOMContentLoaded', function() {
            // Setup CSRF token for fetch requests
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.fetch = (function(original) {
                    return function(url, options = {}) {
                        if (typeof url === 'string' && (url.startsWith('/') || url.includes(window
                                .location.hostname))) {
                            options.headers = options.headers || {};
                            if (!options.headers['X-CSRF-TOKEN']) {
                                options.headers['X-CSRF-TOKEN'] = token.content;
                            }
                        }
                        return original.apply(this, [url, options]);
                    };
                })(window.fetch);
            }

            // Auto-hide flash messages
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                const flashContainer = document.getElementById('flash-messages');
                if (flashContainer) {
                    flashContainer.appendChild(message);
                }

                setTimeout(function() {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 300);
                }, 5000);
            });
        });

        // Flash message functions
        function closeFlashMessage(button) {
            const message = button.closest('.flash-message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 300);
            }
        }

        function showFlashMessage(text, type = 'info') {
            const container = document.getElementById('flash-messages');
            if (!container) return;

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            const messageDiv = document.createElement('div');
            messageDiv.className = `flash-message ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg`;
            messageDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fa-solid ${icons[type]} mr-2"></i>
                        ${text}
                    </div>
                    <button onclick="closeFlashMessage(this)" class="ml-4 text-white hover:text-gray-200">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            `;

            container.appendChild(messageDiv);

            // Auto-hide after 5 seconds
            setTimeout(function() {
                messageDiv.style.opacity = '0';
                setTimeout(function() {
                    if (messageDiv.parentNode) {
                        messageDiv.parentNode.removeChild(messageDiv);
                    }
                }, 300);
            }, 5000);
        }

        // Loading overlay functions
        function showLoading(text = 'Loading...') {
            const overlay = document.getElementById('loading-overlay');
            const loadingText = overlay.querySelector('span');
            if (loadingText) {
                loadingText.textContent = text;
            }
            overlay.classList.remove('hidden');
        }

        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            overlay.classList.add('hidden');
        }

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });

        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>

</html>
