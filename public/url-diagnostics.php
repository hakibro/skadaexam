<?php

/**
 * URL Configuration Diagnostic Tool
 * 
 * This script helps diagnose URL configuration issues by displaying various
 * URL and path configurations in the application.
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Create a helper function for output
function outputSection($title, $data)
{
    echo '<div style="margin-bottom: 20px;">';
    echo '<h3 style="margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">' . $title . '</h3>';

    if (is_array($data) || is_object($data)) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    } else {
        echo '<div style="padding-left: 10px;">' . $data . '</div>';
    }

    echo '</div>';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Configuration Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        h2 {
            color: #2980b9;
            margin-top: 30px;
        }

        pre {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            overflow: auto;
        }

        .highlight {
            background-color: #ffffcc;
            padding: 2px;
        }

        .error {
            color: #e74c3c;
        }

        .success {
            color: #27ae60;
        }

        .warning {
            color: #f39c12;
        }

        .recommendation {
            background-color: #e8f4f8;
            border-left: 5px solid #3498db;
            padding: 10px 15px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>URL Configuration Diagnostic</h1>

        <div>
            <p><strong>Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
            <p><strong>Laravel Version:</strong> <?= app()->version() ?></p>
        </div>

        <h2>Environment Configuration</h2>
        <?php
        // APP_URL config
        outputSection("APP_URL", config('app.url'));

        // URL helpers
        outputSection("url('/')", url('/'));
        outputSection("asset('js/app.js')", asset('js/app.js'));
        outputSection("route('login') (if exists)", (function () {
            try {
                return route('login');
            } catch (Exception $e) {
                return 'Route not defined';
            }
        })());

        // Request info
        outputSection("Request::fullUrl()", request()->fullUrl());
        outputSection("Request::root()", request()->root());

        // Server variables
        outputSection("SERVER Variables", [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'Not set',
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Not set',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'
        ]);

        // Check for URL issues
        $appUrl = config('app.url');
        $requestRoot = request()->root();

        $issues = [];

        if ($appUrl !== $requestRoot) {
            $issues[] = "APP_URL ({$appUrl}) does not match the current request URL ({$requestRoot}).";
        }

        $hasLocalhost = strpos($appUrl, 'localhost') !== false;
        if ($hasLocalhost) {
            $issues[] = "APP_URL contains 'localhost' which may cause issues with AJAX requests from other domains.";
        }

        $hasTrailingSlash = substr($appUrl, -1) === '/';
        if ($hasTrailingSlash) {
            $issues[] = "APP_URL has a trailing slash which can sometimes cause double-slash issues in generated URLs.";
        }

        if (!empty($issues)) {
            echo '<div class="recommendation">';
            echo '<h3 class="warning">Potential Issues Found:</h3>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . $issue . '</li>';
            }
            echo '</ul>';

            echo '<h3>Recommended Solution:</h3>';
            echo '<p>Update your <code>.env</code> file with the following setting:</p>';

            $suggestedUrl = $requestRoot;
            if ($hasTrailingSlash) {
                $suggestedUrl = rtrim($suggestedUrl, '/');
            }

            echo '<pre>APP_URL=' . $suggestedUrl . '</pre>';

            echo '<p>After updating, run <code>php artisan config:clear</code> to apply the changes.</p>';
            echo '</div>';
        } else {
            echo '<div class="recommendation success">';
            echo '<h3>URL Configuration Looks Good!</h3>';
            echo '<p>Your APP_URL configuration appears to match the current request URL.</p>';
            echo '</div>';
        }
        ?>

        <h2>JavaScript URL Detection Test</h2>
        <div id="js-url-info">
            <p>JavaScript will display URL information here...</p>
        </div>

        <script>
            // Create a function to test URL detection similar to the one in batch-siswa-fixed.js
            function getBaseUrl() {
                // Get the base URL from the meta tag if available
                const baseUrlMeta = document.querySelector('meta[name="base-url"]');
                if (baseUrlMeta) {
                    return baseUrlMeta.getAttribute("content");
                }

                // Fallback to constructing from window.location
                const protocol = window.location.protocol;
                const hostname = window.location.hostname;
                const port = window.location.port ? `:${window.location.port}` : "";
                return `${protocol}//${hostname}${port}`;
            }

            // Build URL for an endpoint
            function buildUrl(path) {
                if (!path.startsWith("/")) {
                    path = "/" + path;
                }
                return `${getBaseUrl()}${path}`;
            }

            // When the document is loaded, display the URLs
            document.addEventListener('DOMContentLoaded', () => {
                const baseUrl = getBaseUrl();
                const endpointUrl = buildUrl('/data/siswa/batch-sync-status');

                const output = document.getElementById('js-url-info');
                output.innerHTML = `
                    <div style="background-color: #f8f8f8; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                        <p><strong>JS Detected Base URL:</strong> ${baseUrl}</p>
                        <p><strong>Example Endpoint URL:</strong> ${endpointUrl}</p>
                        <p><strong>window.location.href:</strong> ${window.location.href}</p>
                        <p><strong>window.location.origin:</strong> ${window.location.origin}</p>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <h4>Recommendations:</h4>
                        <ul>
                            <li>Ensure a &lt;meta name="base-url" content="<?= url('/') ?>"&gt; tag exists in your HTML head to provide consistent URLs to JavaScript.</li>
                            <li>Check if your JavaScript is using relative URLs (starting with /) instead of absolute URLs for AJAX requests.</li>
                            <li>For AJAX requests, consider using route() helper in Blade to generate correct URLs.</li>
                        </ul>
                    </div>
                `;
            });
        </script>
    </div>
</body>

</html>