<?php

/**
 * Direct method reflection test
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\Features\Ujian\UjianController;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== REFLECTION TEST ===\n\n";

try {

    $reflection = new ReflectionClass(UjianController::class);
    echo "✓ UjianController class found\n";

    if ($reflection->hasMethod('exam')) {
        echo "✓ exam method exists\n";

        $method = $reflection->getMethod('exam');
        $parameters = $method->getParameters();

        echo "   Method signature: exam(";
        foreach ($parameters as $i => $param) {
            if ($i > 0) echo ", ";
            echo ($param->hasType() ? $param->getType() . " " : "") . '$' . $param->getName();
            if ($param->isOptional()) {
                echo " = " . var_export($param->getDefaultValue(), true);
            }
        }
        echo ")\n";

        echo "   Parameter count: " . count($parameters) . "\n";

        // Check each parameter
        foreach ($parameters as $i => $param) {
            echo "   Param {$i}: \${$param->getName()}";
            if ($param->hasType()) {
                echo " (type: " . $param->getType() . ")";
            }
            if ($param->isOptional()) {
                echo " (optional, default: " . var_export($param->getDefaultValue(), true) . ")";
            }
            echo "\n";
        }
    } else {
        echo "✗ exam method NOT found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
