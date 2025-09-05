<?php

/**
 * Comprehensive diagnostic tool for the jadwal show page blank issue
 * Save this file as diagnostic.php in your public directory
 */

// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set a generous memory limit
ini_set('memory_limit', '512M');

// Set a generous max execution time
ini_set('max_execution_time', 300);

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Boot the app to ensure all providers are loaded
$app->boot();

echo "<h1>Comprehensive Diagnostic Report</h1>";
echo "<div style='margin: 20px; font-family: Arial, sans-serif;'>";
echo "<h2>1. System Information</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse'>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Laravel Version</td><td>" . app()->version() . "</td></tr>";
echo "<tr><td>Environment</td><td>" . app()->environment() . "</td></tr>";
echo "<tr><td>Debug Mode</td><td>" . (config('app.debug') ? 'Enabled' : 'Disabled') . "</td></tr>";
echo "<tr><td>Cache Driver</td><td>" . config('cache.default') . "</td></tr>";
echo "<tr><td>Session Driver</td><td>" . config('session.driver') . "</td></tr>";
echo "</table>";

// Test database connection
echo "<h2>2. Database Connection</h2>";
try {
    $connection = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p style='color:green'>✓ Database connection successful</p>";
    echo "<p>Connected to database: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test for the existence of critical routes
echo "<h2>3. Critical Routes Status</h2>";
$routes = [
    'naskah.jadwal.show',
    'naskah.jadwalujian.show',
    'naskah.hasil.index',
    'naskah.hasilujian.index',
    'naskah.dashboard',
    'naskah.jadwal.index',
    'naskah.jadwal.status'
];

echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse'>";
echo "<tr><th>Route Name</th><th>Status</th></tr>";
foreach ($routes as $routeName) {
    try {
        $exists = \Illuminate\Support\Facades\Route::has($routeName);
        echo "<tr><td>{$routeName}</td><td style='color:" . ($exists ? 'green' : 'red') . "'>" .
            ($exists ? '✓ Exists' : '✗ Missing') . "</td></tr>";
    } catch (\Exception $e) {
        echo "<tr><td>{$routeName}</td><td style='color:red'>Error: {$e->getMessage()}</td></tr>";
    }
}
echo "</table>";

// Test model loading
echo "<h2>4. JadwalUjian Model Test</h2>";
try {
    // Try both IDs mentioned in the issue
    $ids = [20, 31];

    foreach ($ids as $id) {
        $jadwal = \App\Models\JadwalUjian::find($id);
        if ($jadwal) {
            echo "<h3>JadwalUjian ID {$id} Found</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse'>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            echo "<tr><td>ID</td><td>{$jadwal->id}</td></tr>";
            echo "<tr><td>Judul</td><td>{$jadwal->judul}</td></tr>";
            echo "<tr><td>Status</td><td>{$jadwal->status}</td></tr>";
            echo "<tr><td>Tanggal</td><td>" . ($jadwal->tanggal ? $jadwal->tanggal->format('Y-m-d H:i:s') : 'NULL') . "</td></tr>";
            echo "<tr><td>Waktu Mulai</td><td>" . ($jadwal->waktu_mulai ? $jadwal->waktu_mulai->format('H:i:s') : 'NULL') . "</td></tr>";
            echo "<tr><td>Waktu Selesai</td><td>" . ($jadwal->waktu_selesai ? $jadwal->waktu_selesai->format('H:i:s') : 'NULL') . "</td></tr>";
            echo "</table>";

            // Test relationships
            echo "<h4>Relationships:</h4>";
            echo "<ul>";

            // Mapel relationship
            try {
                $mapelLoaded = isset($jadwal->mapel);
                $mapelName = $mapelLoaded ? $jadwal->mapel->nama_mapel : 'N/A';
                echo "<li style='color:" . ($mapelLoaded ? 'green' : 'red') . "'>mapel: " .
                    ($mapelLoaded ? "✓ Loaded ({$mapelName})" : "✗ Not loaded") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>mapel: Error - {$e->getMessage()}</li>";
            }

            // BankSoal relationship
            try {
                $bankSoalLoaded = isset($jadwal->bankSoal);
                $bankSoalTitle = $bankSoalLoaded ? $jadwal->bankSoal->judul : 'N/A';
                echo "<li style='color:" . ($bankSoalLoaded ? 'green' : 'red') . "'>bankSoal: " .
                    ($bankSoalLoaded ? "✓ Loaded ({$bankSoalTitle})" : "✗ Not loaded") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>bankSoal: Error - {$e->getMessage()}</li>";
            }

            // Creator relationship
            try {
                $creatorLoaded = isset($jadwal->creator);
                $creatorName = $creatorLoaded ? $jadwal->creator->name : 'N/A';
                echo "<li style='color:" . ($creatorLoaded ? 'green' : 'red') . "'>creator: " .
                    ($creatorLoaded ? "✓ Loaded ({$creatorName})" : "✗ Not loaded") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>creator: Error - {$e->getMessage()}</li>";
            }

            // SesiRuangan relationship
            try {
                $sesiLoaded = $jadwal->sesiRuangan !== null;
                $sesiCount = $sesiLoaded ? $jadwal->sesiRuangan->count() : 'N/A';
                echo "<li style='color:" . ($sesiLoaded ? 'green' : 'red') . "'>sesiRuangan: " .
                    ($sesiLoaded ? "✓ Loaded (Count: {$sesiCount})" : "✗ Not loaded") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>sesiRuangan: Error - {$e->getMessage()}</li>";
            }

            echo "</ul>";

            // Test accessor methods
            echo "<h4>Accessor Methods:</h4>";
            echo "<ul>";

            // waktu_mulai accessor
            try {
                $waktuMulai = $jadwal->waktu_mulai;
                echo "<li style='color:" . ($waktuMulai !== null ? 'green' : 'orange') . "'>waktu_mulai accessor: " .
                    ($waktuMulai !== null ? "✓ Works ({$waktuMulai})" : "⚠ Returns null") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>waktu_mulai accessor: Error - {$e->getMessage()}</li>";
            }

            // waktu_selesai accessor
            try {
                $waktuSelesai = $jadwal->waktu_selesai;
                echo "<li style='color:" . ($waktuSelesai !== null ? 'green' : 'orange') . "'>waktu_selesai accessor: " .
                    ($waktuSelesai !== null ? "✓ Works ({$waktuSelesai})" : "⚠ Returns null") . "</li>";
            } catch (\Exception $e) {
                echo "<li style='color:red'>waktu_selesai accessor: Error - {$e->getMessage()}</li>";
            }

            echo "</ul>";
        } else {
            echo "<p style='color:orange'>⚠ JadwalUjian with ID {$id} not found in the database</p>";
        }
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error testing JadwalUjian model: {$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}

// Test template rendering
echo "<h2>5. View Template Test</h2>";
try {
    // First, try with the existing template
    echo "<h3>5.1 Standard View Test</h3>";

    // Try to load JadwalUjian ID 20 or 31 (both were mentioned in the original issue)
    $jadwalId = 20;
    $testJadwal = \App\Models\JadwalUjian::find($jadwalId);

    if (!$testJadwal) {
        // Try the other ID if the first one isn't found
        $jadwalId = 31;
        $testJadwal = \App\Models\JadwalUjian::find($jadwalId);
    }

    if (!$testJadwal) {
        // If still not found, get any available jadwal
        $testJadwal = \App\Models\JadwalUjian::first();
        $jadwalId = $testJadwal ? $testJadwal->id : null;
    }

    if ($testJadwal) {
        echo "<p>Testing with JadwalUjian ID: {$jadwalId}</p>";

        // First check if the template file exists
        $templatePath = resource_path('views/features/naskah/jadwal/show.blade.php');
        echo "<p>Template file exists: " . (file_exists($templatePath) ? "Yes" : "No") . "</p>";

        try {
            // First, try to render it as a string without outputting
            $viewContent = view('features.naskah.jadwal.show', [
                'jadwal' => $testJadwal,
                'debug_id' => 'DIAG-' . uniqid(),
                'debug_timestamp' => date('Y-m-d H:i:s')
            ])->render();

            echo "<p style='color:green'>✓ Main view template rendered successfully!</p>";

            // Check view extends relationship
            $usesAdminLayout = strpos(file_get_contents($templatePath), 'layouts.admin') !== false;
            echo "<p>Template extends 'layouts.admin': " . ($usesAdminLayout ? "Yes" : "No") . "</p>";

            // Check for common blade directives
            $hasSectionContent = strpos(file_get_contents($templatePath), '@section(\'content\')') !== false;
            echo "<p>Template has @section('content'): " . ($hasSectionContent ? "Yes" : "No") . "</p>";

            // Check for endsection
            $hasEndSection = strpos(file_get_contents($templatePath), '@endsection') !== false;
            echo "<p>Template has @endsection: " . ($hasEndSection ? "Yes" : "No") . "</p>";
        } catch (\Exception $e) {
            echo "<p style='color:red'>✗ Error rendering main view: {$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        }

        // Also test our ultra-simple view
        echo "<h3>5.2 Ultra Simple View Test</h3>";
        $ultraSimplePath = resource_path('views/features/naskah/jadwal/ultra_simple.blade.php');
        echo "<p>Ultra simple template file exists: " . (file_exists($ultraSimplePath) ? "Yes" : "No") . "</p>";

        if (file_exists($ultraSimplePath)) {
            try {
                $viewContent = view('features.naskah.jadwal.ultra_simple', [
                    'jadwal' => $testJadwal,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'uniqueId' => 'DIAG-' . uniqid()
                ])->render();

                echo "<p style='color:green'>✓ Ultra simple view template rendered successfully!</p>";
            } catch (\Exception $e) {
                echo "<p style='color:red'>✗ Error rendering ultra simple view: {$e->getMessage()}</p>";
                echo "<pre>{$e->getTraceAsString()}</pre>";
            }
        }

        // Test if a raw HTML response would work
        echo "<h3>5.3 Raw HTML Response Test</h3>";
        try {
            $htmlResponse = "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Test Response</title>
                </head>
                <body>
                    <h1>Raw HTML Test</h1>
                    <p>JadwalUjian ID: {$testJadwal->id}</p>
                    <p>Title: {$testJadwal->judul}</p>
                </body>
                </html>
            ";

            // Just testing if we can create this response, not actually sending it
            echo "<p style='color:green'>✓ Raw HTML response could be created successfully</p>";
        } catch (\Exception $e) {
            echo "<p style='color:red'>✗ Error creating raw HTML response: {$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        }

        // Test the admin layout
        echo "<h3>5.4 Admin Layout Test</h3>";
        if ($usesAdminLayout) {
            $layoutPath = resource_path('views/layouts/admin.blade.php');
            echo "<p>Admin layout file exists: " . (file_exists($layoutPath) ? "Yes" : "No") . "</p>";

            if (file_exists($layoutPath)) {
                try {
                    // We'll test a super-simple view that uses the admin layout
                    $simpleContentHtml = '
                        @extends("layouts.admin")
                        
                        @section("title", "Test Page")
                        @section("page-title", "Test Page")
                        @section("page-description", "This is just a test")
                        
                        @section("content")
                            <div>
                                <h1>Test Content</h1>
                                <p>This is a simple test to see if the admin layout works.</p>
                            </div>
                        @endsection
                    ';

                    // Write this to a temporary file
                    $tempViewPath = resource_path('views/temp_test_view.blade.php');
                    file_put_contents($tempViewPath, $simpleContentHtml);

                    // Try to render it
                    if (file_exists($tempViewPath)) {
                        try {
                            $layoutTest = view('temp_test_view')->render();
                            echo "<p style='color:green'>✓ Admin layout rendered successfully with test content!</p>";
                        } catch (\Exception $e) {
                            echo "<p style='color:red'>✗ Error rendering admin layout: {$e->getMessage()}</p>";
                            echo "<pre>{$e->getTraceAsString()}</pre>";
                        }

                        // Clean up
                        unlink($tempViewPath);
                    }
                } catch (\Exception $e) {
                    echo "<p style='color:red'>✗ Error testing admin layout: {$e->getMessage()}</p>";
                    echo "<pre>{$e->getTraceAsString()}</pre>";
                }
            }
        } else {
            echo "<p style='color:orange'>⚠ Admin layout test skipped (template doesn't use it)</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠ Cannot test view rendering - No JadwalUjian found in the database</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error in view tests: {$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}

// Display Laravel logs snippet
echo "<h2>6. Recent Error Logs</h2>";
try {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        // Get only the last 10 error entries
        preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?ERROR.*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s', $logContent, $matches);
        $errorLogs = array_slice($matches[0], -10);

        if (count($errorLogs) > 0) {
            echo "<div style='max-height:400px; overflow-y:auto;'>";
            echo "<pre style='background-color:#f5f5f5; padding:10px;'>";
            foreach ($errorLogs as $log) {
                echo htmlspecialchars($log) . "\n\n";
            }
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<p>No recent errors found in the logs.</p>";
        }
    } else {
        echo "<p>Log file not found at: {$logPath}</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error reading logs: {$e->getMessage()}</p>";
}

// Display the jadwal_debug.log content
echo "<h2>7. Custom Debug Logs</h2>";
try {
    $debugLogPath = storage_path('logs/jadwal_debug.log');
    if (file_exists($debugLogPath)) {
        $debugLogContent = file_get_contents($debugLogPath);

        if (!empty($debugLogContent)) {
            echo "<div style='max-height:400px; overflow-y:auto;'>";
            echo "<pre style='background-color:#f5f5f5; padding:10px;'>";
            echo htmlspecialchars($debugLogContent);
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<p>No content found in the debug log.</p>";
        }
    } else {
        echo "<p>Debug log file not found at: {$debugLogPath}</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error reading debug log: {$e->getMessage()}</p>";
}

// Diagnostic check for HTTP error logs
echo "<h2>8. HTTP Error Logs Check</h2>";
try {
    // Check HTTP server logs if they're accessible
    $possibleLogPaths = [
        'C:/laragon/logs/apache_error.log',
        'C:/laragon/logs/nginx_error.log',
        'C:/laragon/etc/apache2/logs/error.log',
        'C:/laragon/etc/nginx/logs/error.log',
        'C:/xampp/apache/logs/error.log'
    ];

    $found = false;
    foreach ($possibleLogPaths as $logPath) {
        if (file_exists($logPath)) {
            $found = true;
            $logContent = shell_exec("tail -n 50 \"{$logPath}\"");
            echo "<p>Found logs at: {$logPath}</p>";
            echo "<div style='max-height:400px; overflow-y:auto;'>";
            echo "<pre style='background-color:#f5f5f5; padding:10px;'>";
            echo htmlspecialchars($logContent ?: "No content in log file.");
            echo "</pre>";
            echo "</div>";
            break;
        }
    }

    if (!$found) {
        echo "<p>No HTTP server logs found in the common locations.</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error reading HTTP logs: {$e->getMessage()}</p>";
}

// Add a section to test if we can URL to the problem page
echo "<h2>9. URL Generation Test</h2>";
try {
    if (isset($testJadwal) && $testJadwal) {
        $url = route('naskah.jadwal.show', $testJadwal);
        echo "<p>Generated URL for naskah.jadwal.show: <a href='{$url}' target='_blank'>{$url}</a></p>";

        try {
            $altUrl = route('naskah.jadwalujian.show', $testJadwal);
            echo "<p>Generated URL for naskah.jadwalujian.show: <a href='{$altUrl}' target='_blank'>{$altUrl}</a></p>";
        } catch (\Exception $e) {
            echo "<p style='color:orange'>⚠ Could not generate URL for naskah.jadwalujian.show: {$e->getMessage()}</p>";
        }

        // Create a direct URL to try
        $directUrl = url("/naskah/jadwal/{$testJadwal->id}");
        echo "<p>Direct URL: <a href='{$directUrl}' target='_blank'>{$directUrl}</a></p>";
    } else {
        echo "<p style='color:orange'>⚠ Cannot test URL generation - No JadwalUjian available</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error generating URLs: {$e->getMessage()}</p>";
}

// Custom controller test
echo "<h2>10. Controller Manual Test</h2>";
try {
    if (isset($testJadwal) && $testJadwal) {
        // Try to manually invoke the controller logic without the view rendering
        $controller = new \App\Http\Controllers\Features\Naskah\JadwalUjianController();
        echo "<p>Controller class exists and was instantiated</p>";

        // We're not going to call the show method directly as it might output content
        // Instead just display some information
        echo "<p>To manually test the controller, uncomment the debug test line in JadwalUjianController.php:</p>";
        echo "<pre style='background-color:#f5f5f5; padding:10px;'>";
        echo "// In JadwalUjianController.php, show method:
// Uncomment this to return a bare-bones HTML response without any view rendering
/*
return response()->make('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Basic Response Test</title>
    </head>
    <body>
        <h1>Basic Response Test</h1>
        <p>This is a test to see if a simple response works.</p>
        <p>Jadwal ID: ' . \$jadwal->id . '</p>
        <p>Jadwal Title: ' . \$jadwal->judul . '</p>
        <p><a href=\"' . route('naskah.jadwal.index') . '\">Back to List</a></p>
    </body>
    </html>
');
*/";
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>⚠ Cannot test controller - No JadwalUjian available</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>✗ Error testing controller: {$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}

echo "<h2>11. Recommendations</h2>";
echo "<ol>";
echo "<li>Try the Ultra-Simple View: Uncomment the line in <code>JadwalUjianController.php</code> to use the ultra_simple blade view:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>return view('features.naskah.jadwal.ultra_simple', [
    'jadwal' => \$jadwal, 
    'timestamp' => \$timestamp,
    'uniqueId' => \$uniqueId
]);</pre>";

echo "<li>Try Direct HTML Response: If the ultra-simple view doesn't work, uncomment the section in the controller to return a direct HTML response:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>return response()->make('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Basic Response Test</title>
    </head>
    <body>
        <h1>Basic Response Test</h1>
        <p>This is a test to see if a simple response works.</p>
        <p>Jadwal ID: ' . \$jadwal->id . '</p>
        <p>Jadwal Title: ' . \$jadwal->judul . '</p>
        <p><a href=\"' . route('naskah.jadwal.index') . '\">Back to List</a></p>
    </body>
    </html>
');</pre>";

echo "<li>Clear All Caches: Ensure all Laravel caches are cleared with:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>php artisan optimize:clear</pre>";

echo "<li>Test Browser Issues: Try accessing the page with different browsers or incognito mode to rule out browser caching issues.</li>";

echo "<li>Check Server Logs: Examine your web server error logs for any PHP fatal errors that might not be visible in Laravel's logs.</li>";

echo "<li>Verify Route Issues: If the diagnostic shows that the routes are working but the page is still blank, try adding direct exit points in the controller:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>public function show(JadwalUjian \$jadwal)
{
    // At the very beginning of the method
    exit('Controller method was called!');
    
    // Rest of your controller code...
}</pre>";

echo "<li>Memory/Time Limits: If you suspect PHP configuration issues, try increasing limits:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>// Add to the top of your controller
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);</pre>";

echo "<li>Debug View Inheritance: If the issue is with view inheritance, try a view with no inheritance:</li>";
echo "<pre style='background-color:#f5f5f5; padding:10px;'>// Create a view file without @extends
&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;No Inheritance Test&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Test Page&lt;/h1&gt;
    &lt;p&gt;JadwalUjian ID: {{ \$jadwal->id }}&lt;/p&gt;
    &lt;p&gt;Title: {{ \$jadwal->judul }}&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>";

echo "<li>Check for XDebug: If you have XDebug installed, it might be causing issues with large responses. Try disabling it temporarily.</li>";

echo "</ol>";

echo "</div>";
