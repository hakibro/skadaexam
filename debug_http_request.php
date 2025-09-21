<?php

/**
 * Direct HTTP request test
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== HTTP REQUEST TEST ===\n\n";

// Test the exact URL that's failing
$testUrl = 'http://skadaexam.test/ujian/exam/4';

echo "Testing URL: {$testUrl}\n\n";

// Use cURL to make a request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel Debug Test');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: {$httpCode}\n";

if ($error) {
    echo "cURL Error: {$error}\n";
}

echo "\nResponse Headers:\n";
// Split response into headers and body
$parts = explode("\r\n\r\n", $response, 2);
$headers = $parts[0] ?? '';
$body = $parts[1] ?? $response;

echo $headers . "\n";

echo "\nResponse Body (first 1000 chars):\n";
echo substr($body, 0, 1000) . "\n";

if (strlen($body) > 1000) {
    echo "\n... (truncated)\n";
}

// Check for specific error patterns
if (strpos($body, 'Missing required parameter') !== false) {
    echo "\n*** FOUND: Missing required parameter error ***\n";
}

if (strpos($body, 'ujian.exam') !== false) {
    echo "\n*** FOUND: ujian.exam route reference ***\n";
}

echo "\n=== TEST COMPLETE ===\n";
