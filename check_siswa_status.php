<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;

echo "Total Siswa: " . Siswa::count() . PHP_EOL;
echo "Siswa active: " . Siswa::where('status', 'active')->count() . PHP_EOL;
echo "Siswa aktif: " . Siswa::where('status', 'aktif')->count() . PHP_EOL;

// Check actual status values
$statuses = Siswa::select('status')->distinct()->pluck('status');
echo "Actual status values: " . $statuses->implode(', ') . PHP_EOL;

// Also check if Siswa table exists and has data
try {
    $sample = Siswa::first();
    if ($sample) {
        echo "Sample siswa: " . $sample->nama . " - status: " . $sample->status . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
