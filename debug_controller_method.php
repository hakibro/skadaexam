<?php

/**
 * Direct controller test
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== CONTROLLER METHOD TEST ===\n\n";

// Import the controller at top level
use App\Http\Controllers\Features\Ujian\UjianController;
use Illuminate\Http\Request;

try {

    // Create instances
    $controller = new UjianController();
    $request = new Request();

    // Test if method exists
    if (method_exists($controller, 'exam')) {
        echo "✓ UjianController::exam method exists\n";

        // Get method reflection
        $reflection = new ReflectionMethod($controller, 'exam');
        $parameters = $reflection->getParameters();

        echo "   Method signature: exam(";
        foreach ($parameters as $i => $param) {
            if ($i > 0) echo ", ";
            echo ($param->hasType() ? $param->getType() . " " : "") . '$' . $param->getName();
            if ($param->isOptional()) {
                echo " = " . ($param->getDefaultValue() ?? 'null');
            }
        }
        echo ")\n";

        echo "   Parameter count: " . count($parameters) . "\n";

        // Test calling with parameters
        echo "\n2. Testing controller call...\n";

        // Simulate the call that should work
        try {
            $result = $controller->exam($request, 4);
            echo "   ✓ Controller call successful\n";
            echo "   Return type: " . gettype($result) . "\n";
        } catch (Exception $e) {
            echo "   ✗ Controller call failed: " . $e->getMessage() . "\n";
            echo "   File: " . $e->getFile() . "\n";
            echo "   Line: " . $e->getLine() . "\n";
        }
    } else {
        echo "✗ UjianController::exam method NOT found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
