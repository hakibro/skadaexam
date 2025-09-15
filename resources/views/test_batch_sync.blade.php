<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>Batch Sync Test</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Batch Sync Test</h1>

        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-semibold mb-3">About This Test</h2>
            <p class="mb-2">This page tests the batch sync functionality with proper URL handling and Toast
                notifications.</p>
            <p class="mb-2">Environment Information:</p>
            <ul class="list-disc ml-5 mb-4">
                <li>APP_URL: {{ config('app.url') }}</li>
                <li>Current URL: {{ url()->current() }}</li>
                <li>Base URL: <span id="detected-base-url">Detecting...</span></li>
                <li>Session Driver: {{ config('session.driver') }}</li>
            </ul>
        </div>

        <!-- Controls -->
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-semibold mb-3">Test Controls</h2>
            <button id="batch-sync-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Start Batch Sync
            </button>
        </div>

        <!-- Progress Section -->
        <div id="batch-sync-section" class="bg-white p-4 rounded shadow mb-6 hidden">
            <h2 class="text-lg font-semibold mb-3">Batch Sync Progress</h2>
            <p id="batch-sync-status-text" class="mb-2">Initializing sync...</p>

            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                <div id="batch-sync-progress-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>

            <p class="mb-1">Progress: <span id="batch-sync-percentage">0%</span></p>
            <p class="mb-1">Batch: <span id="batch-sync-current-batch">0</span> of <span
                    id="batch-sync-total-batches">0</span></p>
            <p id="batch-sync-message" class="mb-3">Starting batch sync...</p>

            <div class="mb-3">
                <p class="font-semibold">Current Results:</p>
                <p>Created Classes: <span id="batch-sync-created-kelas">0</span></p>
                <p>Updated Classes: <span id="batch-sync-updated-kelas">0</span></p>
                <p>Created Students: <span id="batch-sync-created-siswa">0</span></p>
                <p>Updated Students: <span id="batch-sync-updated-siswa">0</span></p>
            </div>

            <button id="cancel-batch-sync-btn"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-4 rounded">
                Cancel
            </button>
        </div>

        <!-- Results Section -->
        <div id="batch-sync-results-section" class="bg-white p-4 rounded shadow mb-6 hidden">
            <h2 class="text-lg font-semibold mb-3">Batch Sync Complete</h2>
            <p id="batch-sync-results-content" class="mb-3">Sync completed successfully!</p>

            <div class="mb-3">
                <p class="font-semibold">Results:</p>
                <p>Created Classes: <span id="sync-results-created-kelas">0</span></p>
                <p>Updated Classes: <span id="sync-results-updated-kelas">0</span></p>
                <p>Created Students: <span id="sync-results-created-siswa">0</span></p>
                <p>Updated Students: <span id="sync-results-updated-siswa">0</span></p>
            </div>

            <div id="batch-sync-errors-container" class="mb-3 hidden">
                <p class="font-semibold text-red-600">Errors:</p>
                <div id="batch-sync-errors" class="bg-red-50 border border-red-200 p-2 rounded"></div>
            </div>

            <button id="close-batch-sync-results-btn"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded">
                Close
            </button>
        </div>

        <!-- Error Section -->
        <div id="batch-sync-error-section" class="bg-white p-4 rounded shadow mb-6 hidden">
            <h2 class="text-lg font-semibold text-red-600 mb-3">Batch Sync Error</h2>
            <p id="batch-sync-error-content" class="mb-3 text-red-600">An error occurred during the sync operation.</p>

            <div class="flex space-x-2">
                <button id="retry-batch-sync-btn"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded">
                    Retry
                </button>
                <button id="close-batch-sync-error-btn"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded">
                    Close
                </button>
            </div>
        </div>

        <!-- Debug Console Section -->
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-3">Debug Console</h2>
            <pre id="debug-console" class="bg-gray-100 p-2 rounded text-xs h-40 overflow-auto"></pre>
        </div>
    </div>

    <!-- Import JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Debug console implementation
        const debugConsole = document.getElementById('debug-console');

        // Store original console methods
        const originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn,
            info: console.info
        };

        // Override console methods to capture output
        console.log = function() {
            const args = Array.from(arguments);
            debugConsole.textContent += '▶ LOG: ' + args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(
                ' ') + '\n';
            debugConsole.scrollTop = debugConsole.scrollHeight;
            originalConsole.log.apply(console, arguments);
        };

        console.error = function() {
            const args = Array.from(arguments);
            debugConsole.textContent += '❌ ERROR: ' + args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(
                ' ') + '\n';
            debugConsole.scrollTop = debugConsole.scrollHeight;
            originalConsole.error.apply(console, arguments);
        };

        // When the page loads, display the detected base URL
        document.addEventListener('DOMContentLoaded', () => {
            // Get the base URL using the same method as batch-siswa-fixed.js
            const getBaseUrl = () => {
                const baseUrlMeta = document.querySelector('meta[name="base-url"]');
                if (baseUrlMeta) {
                    return baseUrlMeta.getAttribute("content");
                }

                const protocol = window.location.protocol;
                const hostname = window.location.hostname;
                const port = window.location.port ? `:${window.location.port}` : "";
                return `${protocol}//${hostname}${port}`;
            };

            const baseUrl = getBaseUrl();
            document.getElementById('detected-base-url').textContent = baseUrl;

            console.log(`Detected base URL: ${baseUrl}`);
            console.log(`CSRF Token present: ${!!document.querySelector('meta[name="csrf-token"]')}`);
        });
    </script>
    <script src="{{ asset('js/batch-siswa-fixed.js') }}"></script>
</body>

</html>
