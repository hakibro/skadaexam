<?php

require 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SesiRuangan;
use App\Models\JadwalUjianSesiRuangan;

echo "=== Debugging SesiRuangan 113 Pengawas Assignment ===\n";

$sesiRuangan = SesiRuangan::with([
    'jadwalUjians',
    'jadwalUjians.mapel',
    'ruangan'
])->find(113);

if ($sesiRuangan) {
    echo "Sesi: {$sesiRuangan->nama_sesi}\n";
    echo "Ruangan: " . ($sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'N/A') . "\n";

    echo "\n=== All JadwalUjians in this sesi ===\n";
    foreach ($sesiRuangan->jadwalUjians as $jadwal) {
        echo "- Jadwal ID: {$jadwal->id}\n";
        echo "  Mapel: " . ($jadwal->mapel ? $jadwal->mapel->nama_mapel : 'NULL') . "\n";
        echo "  Tanggal: {$jadwal->tanggal}\n";
    }

    echo "\n=== Pengawas assignments from pivot table ===\n";
    $pivots = JadwalUjianSesiRuangan::where('sesi_ruangan_id', 113)
        ->with(['jadwalUjian.mapel', 'pengawas'])
        ->get();

    foreach ($pivots as $pivot) {
        echo "- Jadwal: {$pivot->jadwal_ujian_id}\n";
        echo "  Mapel: " . ($pivot->jadwalUjian && $pivot->jadwalUjian->mapel ? $pivot->jadwalUjian->mapel->nama_mapel : 'NULL') . "\n";
        echo "  Pengawas ID: {$pivot->pengawas_id}\n";
        echo "  Pengawas: " . ($pivot->pengawas ? $pivot->pengawas->nama : 'NULL') . "\n";
    }
} else {
    echo "SesiRuangan 113 not found\n";
}

echo "\n=== Debug complete ===\n";
