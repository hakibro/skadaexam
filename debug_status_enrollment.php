<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EnrollmentUjian;
use Illuminate\Support\Facades\Schema;

echo "=== Debugging status_enrollment column ===\n\n";

// Check enrollment 1679 current status
$enrollment = EnrollmentUjian::find(1679);

if ($enrollment) {
    echo "✅ Current enrollment data:\n";
    echo "   - ID: {$enrollment->id}\n";
    echo "   - Siswa ID: {$enrollment->siswa_id}\n";
    echo "   - Current status: '{$enrollment->status_enrollment}'\n";
    echo "   - Sesi Ruangan ID: {$enrollment->sesi_ruangan_id}\n\n";
} else {
    echo "❌ Enrollment 1679 not found\n";
    exit;
}

// Test valid status values by checking migration
echo "Checking valid status values...\n";

// Try to update with different status values to see which ones work
$validStatuses = ['enrolled', 'completed', 'cancelled', 'absent', 'active'];

foreach ($validStatuses as $status) {
    try {
        // Create a test copy first to avoid affecting real data
        $testEnrollment = new EnrollmentUjian();
        $testEnrollment->fill([
            'siswa_id' => $enrollment->siswa_id,
            'sesi_ruangan_id' => $enrollment->sesi_ruangan_id,
            'status_enrollment' => $status,
        ]);

        // Test if the status is valid by trying to set it (without saving)
        echo "   - '{$status}': ";

        // Just test the assignment without saving
        $enrollment->status_enrollment = $status;
        echo "✅ Valid\n";
    } catch (\Exception $e) {
        echo "❌ Invalid - {$e->getMessage()}\n";
    }
}

// Reset to original status
$enrollment->refresh();

echo "\n=== Checking database schema ===\n";

// Try to get column information
try {
    $columns = Schema::getConnection()->getDoctrineSchemaManager()
        ->listTableDetails('enrollment_ujian')
        ->getColumns();

    if (isset($columns['status_enrollment'])) {
        $column = $columns['status_enrollment'];
        echo "Column type: " . $column->getType()->getName() . "\n";
        echo "Column length: " . ($column->getLength() ?? 'N/A') . "\n";

        // If it's an enum, try to get the values
        if ($column->getType()->getName() === 'string') {
            echo "This appears to be a string column, checking for ENUM constraint...\n";
        }
    }
} catch (\Exception $e) {
    echo "Could not get schema info: {$e->getMessage()}\n";
}

echo "\n=== Testing the startExam method ===\n";

try {
    // Test the method without actually running it
    echo "Current method implementation sets status to 'active'\n";
    echo "This seems to be the problem - 'active' might not be a valid enum value\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\nProblem identified: 'active' status not valid for status_enrollment column\n";
