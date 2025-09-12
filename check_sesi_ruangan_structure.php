<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\SesiRuangan;

// Check sesi_ruangan table structure
echo "=== SESI_RUANGAN TABLE STRUCTURE ===\n";
$columns = DB::select("DESCRIBE sesi_ruangan");
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}

echo "\n=== SAMPLE SESI_RUANGAN DATA ===\n";
$sample = SesiRuangan::limit(3)->get();
foreach ($sample as $sesi) {
    echo "ID: " . $sesi->id . " | ";
    echo "nama: " . ($sesi->nama ?? 'null') . " | ";
    echo "nama_sesi: " . ($sesi->nama_sesi ?? 'null') . " | ";
    echo "jadwal_ujian_id: " . ($sesi->jadwal_ujian_id ?? 'null') . " | ";
    echo "status: " . ($sesi->status ?? 'null') . "\n";
}
