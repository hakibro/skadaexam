<?php

/**
 * Simple server test
 */

echo "=== SERVER STATUS TEST ===\n\n";

// Test if server is responding
$testUrls = [
    'http://skadaexam.test',
    'http://skadaexam.test/login',
    'http://skadaexam.test/ujian/exam/4'
];

foreach ($testUrls as $url) {
    echo "Testing: {$url}\n";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response !== false) {
        $httpCode = 'Unknown';
        if (isset($http_response_header)) {
            $httpCode = $http_response_header[0];
        }
        echo "   Status: {$httpCode}\n";
        echo "   Response length: " . strlen($response) . " bytes\n";

        // Check for specific error in response
        if (strpos($response, 'Missing required parameter') !== false) {
            echo "   *** FOUND: Missing required parameter error ***\n";

            // Extract error context
            $start = strpos($response, 'Missing required parameter');
            $context = substr($response, max(0, $start - 100), 300);
            echo "   Error context: " . trim(strip_tags($context)) . "\n";
        }

        if (strpos($response, 'ujian.exam') !== false) {
            echo "   *** FOUND: ujian.exam reference ***\n";
        }
    } else {
        echo "   ERROR: Could not reach URL\n";
    }
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
