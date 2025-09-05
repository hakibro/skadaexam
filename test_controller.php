<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Http\Controllers\Features\Naskah\HasilUjianController;
use Illuminate\Http\Request;

$app = app();
$controller = new HasilUjianController();
try {
    // Create a mock request
    $request = Request::create('/naskah/hasil-ujian', 'GET');

    // Try to create a new instance and call index method
    $result = $controller->index($request);

    echo "HasilUjianController test passed successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
