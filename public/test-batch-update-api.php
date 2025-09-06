<?php
// This diagnostic script tests the batch update kelas target API endpoint directly

// Basic configuration
$baseUrl = "http://skadaexam.test/";
$endpointUrl = "naskah/jadwal/batch-update-kelas-target";
$csrfToken = null;

// Function to get CSRF token from page
function getCSRFToken($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
    $html = curl_exec($ch);
    curl_close($ch);

    // Extract CSRF token
    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches);
    return $matches[1] ?? null;
}

// Function to make API request
function makeApiRequest($url, $token, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-CSRF-TOKEN: ' . $token,
        'X-Requested-With: XMLHttpRequest'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response
    ];
}

// Get CSRF token
$csrfToken = getCSRFToken($baseUrl);

echo "<h1>API Diagnostics for Batch Update Kelas Target</h1>";
echo "<pre>";

// Display configuration
echo "Configuration:\n";
echo "Base URL: $baseUrl\n";
echo "Endpoint: $endpointUrl\n";
echo "CSRF Token: " . ($csrfToken ? "Found" : "Not found") . "\n\n";

// Make test request
$requestData = [
    'limit' => 10,
    'dry_run' => true
];

echo "Sending request with data:\n";
echo json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

$fullUrl = $baseUrl . $endpointUrl;
$result = makeApiRequest($fullUrl, $csrfToken, $requestData);

echo "Response code: " . $result['code'] . "\n\n";
echo "Response body:\n";
echo $result['body'] ? json_encode(json_decode($result['body']), JSON_PRETTY_PRINT) : "No response";

echo "</pre>";
