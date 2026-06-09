<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a request
$request = \Illuminate\Http\Request::create('/features/pengawas/get-violations/2', 'GET');

// Mock authentication by setting a user session
$request->setLaravelSession($app['session.store']);

// Try to get a pengawas user from database to simulate login
$user = \App\Models\User::where('role', 'pengawas')->first();

if (!$user) {
    echo "No pengawas user found in database. Creating test pengawas user..." . PHP_EOL;

    // Check if we have a guru to use
    $guru = \App\Models\Guru::first();
    if ($guru) {
        $user = new \App\Models\User();
        $user->username = 'test_pengawas';
        $user->email = 'pengawas@test.com';
        $user->password = bcrypt('password');
        $user->role = 'pengawas';
        $user->guru_id = $guru->id;
        $user->save();
        echo "Test pengawas user created with ID: {$user->id}" . PHP_EOL;
    } else {
        echo "No guru found to create pengawas user." . PHP_EOL;
        exit(1);
    }
}

// Simulate authentication
\Illuminate\Support\Facades\Auth::login($user);
$request->setUserResolver(function () use ($user) {
    return $user;
});

echo "Testing with user: {$user->username} (Role: {$user->role})" . PHP_EOL;
echo "User can supervise: " . ($user->canSupervise() ? 'Yes' : 'No') . PHP_EOL;
echo "User is admin: " . ($user->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Manually call the controller method
$controller = new \App\Http\Controllers\Pengawas\PelanggaranController();

try {
    \Illuminate\Support\Facades\Auth::setUser($user);

    $response = $controller->getViolations($request, 2);

    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Response status: " . $response->getStatusCode() . PHP_EOL;
        echo "Response data:" . PHP_EOL;
        echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        echo "Unexpected response type: " . get_class($response) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL;
echo "=== CONTROLLER TEST COMPLETE ===" . PHP_EOL;
