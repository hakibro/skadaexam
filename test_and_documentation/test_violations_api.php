<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

// Test as admin user
$adminUser = App\Models\User::where('role', 'admin')->first();
if (!$adminUser) {
    echo "No admin user found, creating one..." . PHP_EOL;
    // You might need to create an admin user for testing
    exit;
}

// Simulate API request similar to what happens in the dashboard
$request = new Illuminate\Http\Request();
$request->setMethod('GET');

// Create controller instance
$controller = new App\Http\Controllers\Pengawas\PelanggaranController();

// Simulate authentication
Illuminate\Support\Facades\Auth::login($adminUser);

echo "Testing getViolations as admin user..." . PHP_EOL;

try {
    // Call the controller method
    $response = $controller->getViolations($request);

    // Get the response content
    $responseData = json_decode($response->getContent(), true);

    echo "Response status: " . $response->getStatusCode() . PHP_EOL;
    echo "Response data: " . PHP_EOL;
    print_r($responseData);

    if (isset($responseData['violations'])) {
        echo "Number of violations returned: " . count($responseData['violations']) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
