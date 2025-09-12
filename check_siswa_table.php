<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Siswa;

// Check siswa table structure
echo "=== SISWA TABLE STRUCTURE ===\n";
$columns = DB::select("DESCRIBE siswa");
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}

echo "\n=== SAMPLE SISWA DATA ===\n";
$sample = Siswa::first();
if ($sample) {
    echo "ID: " . $sample->id . "\n";
    echo "Nama: " . $sample->nama . "\n";
    echo "NIS: " . ($sample->nis ?? 'null') . "\n";
    echo "Kelas ID: " . ($sample->kelas_id ?? 'null') . "\n";
} else {
    echo "No siswa data found\n";
}
