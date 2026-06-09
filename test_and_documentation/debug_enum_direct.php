<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking database column structure ===\n\n";

try {
    // Show column information
    $columnInfo = DB::select("SHOW COLUMNS FROM enrollment_ujian WHERE Field = 'status_enrollment'");

    if (!empty($columnInfo)) {
        $column = $columnInfo[0];
        echo "✅ Column info:\n";
        echo "   - Field: {$column->Field}\n";
        echo "   - Type: {$column->Type}\n";
        echo "   - Null: {$column->Null}\n";
        echo "   - Default: " . ($column->Default ?? 'NULL') . "\n";
        echo "   - Extra: {$column->Extra}\n";
    }

    // Try to get the enum values from the Type field
    if (isset($column->Type) && strpos($column->Type, 'enum') === 0) {
        echo "\n✅ Detected ENUM column\n";

        // Parse enum values from the type definition
        preg_match_all("/'([^']+)'/", $column->Type, $matches);
        if (!empty($matches[1])) {
            echo "Valid enum values:\n";
            foreach ($matches[1] as $value) {
                echo "   - '{$value}'\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Error getting column info: {$e->getMessage()}\n";
}

echo "\n=== Testing direct update with 'active' status ===\n";

try {
    // Try to update enrollment 1679 directly
    $result = DB::update("UPDATE enrollment_ujian SET status_enrollment = 'active' WHERE id = 1679");
    echo "✅ Direct SQL update successful! Rows affected: {$result}\n";

    // Check the current value
    $current = DB::select("SELECT status_enrollment FROM enrollment_ujian WHERE id = 1679")[0];
    echo "✅ Current status: '{$current->status_enrollment}'\n";

    // Reset to enrolled
    DB::update("UPDATE enrollment_ujian SET status_enrollment = 'enrolled' WHERE id = 1679");
    echo "✅ Reset to 'enrolled'\n";
} catch (\Exception $e) {
    echo "❌ Direct SQL update failed: {$e->getMessage()}\n";
}

echo "\n=== Testing through Eloquent ===\n";

use App\Models\EnrollmentUjian;

try {
    $enrollment = EnrollmentUjian::find(1679);
    $enrollment->status_enrollment = 'active';
    $enrollment->save();

    echo "✅ Eloquent update successful!\n";

    // Reset
    $enrollment->status_enrollment = 'enrolled';
    $enrollment->save();
    echo "✅ Reset to 'enrolled'\n";
} catch (\Exception $e) {
    echo "❌ Eloquent update failed: {$e->getMessage()}\n";
}

echo "\nDebugging complete!\n";
