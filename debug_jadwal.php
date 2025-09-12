<?php

require 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JadwalUjian;

echo "=== Checking JadwalUjian records for SesiRuangan 111 ===\n";

$jadwalIds = [37, 38, 40]; // From previous debug output

foreach ($jadwalIds as $id) {
    echo "\n=== JadwalUjian ID: {$id} ===\n";
    $jadwal = JadwalUjian::with('mapel')->find($id);

    if ($jadwal) {
        echo "Judul: " . ($jadwal->judul ?? 'NULL') . "\n";
        echo "Mapel ID: " . ($jadwal->mapel_id ?? 'NULL') . "\n";
        echo "Mapel loaded: " . ($jadwal->mapel ? 'YES' : 'NO') . "\n";
        if ($jadwal->mapel) {
            echo "Mapel nama: " . $jadwal->mapel->nama_mapel . "\n";
        }
        echo "Tanggal: {$jadwal->tanggal}\n";
    } else {
        echo "NOT FOUND\n";
    }
}

echo "\n=== Complete ===\n";
