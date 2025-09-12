<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Kelas;

// Check kelas table structure
echo "=== KELAS TABLE STRUCTURE ===\n";
$columns = DB::select("DESCRIBE kelas");
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}

echo "\n=== SAMPLE KELAS DATA ===\n";
$sample = Kelas::limit(5)->get();
foreach ($sample as $kelas) {
    echo "ID: " . $kelas->id . " | ";
    echo "nama_kelas: " . ($kelas->nama_kelas ?? 'null') . " | ";
    echo "tingkat: " . ($kelas->tingkat ?? 'null') . " | ";
    echo "jurusan: " . ($kelas->jurusan ?? 'null') . "\n";
}
