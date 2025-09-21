<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== JADWAL UJIAN & MAPEL DEBUG ===" . PHP_EOL;

// Check jadwal ujian ID 4 (from violations)
$jadwal = \App\Models\JadwalUjian::with('mapel')->find(4);

if ($jadwal) {
    echo "Jadwal Ujian ID 4:" . PHP_EOL;
    echo "- Tanggal: {$jadwal->tanggal}" . PHP_EOL;
    echo "- Durasi: {$jadwal->durasi_menit} menit" . PHP_EOL;
    echo "- Mapel ID: {$jadwal->mapel_id}" . PHP_EOL;

    if ($jadwal->mapel) {
        echo "- Mapel: {$jadwal->mapel->nama_mapel}" . PHP_EOL;
        echo "- Jurusan: " . ($jadwal->mapel->jurusan ?: 'UMUM') . PHP_EOL;
    } else {
        echo "- Mapel: NOT FOUND!" . PHP_EOL;
    }
} else {
    echo "Jadwal Ujian ID 4 not found." . PHP_EOL;
}

echo PHP_EOL;

// Check all mapel
$mapels = \App\Models\Mapel::all();
echo "Available mapels: " . $mapels->count() . PHP_EOL;
foreach ($mapels as $mapel) {
    echo "- ID {$mapel->id}: {$mapel->nama_mapel} (Jurusan: " . ($mapel->jurusan ?: 'UMUM') . ")" . PHP_EOL;
}

echo PHP_EOL;

// Check sesi ruangan ID 2
$sesiRuangan = \App\Models\SesiRuangan::with('ruangan')->find(2);
if ($sesiRuangan) {
    echo "Sesi Ruangan ID 2:" . PHP_EOL;
    echo "- Nama Sesi: " . ($sesiRuangan->nama_sesi ?: 'N/A') . PHP_EOL;
    echo "- Waktu: {$sesiRuangan->waktu_mulai} - {$sesiRuangan->waktu_selesai}" . PHP_EOL;

    if ($sesiRuangan->ruangan) {
        echo "- Ruangan: {$sesiRuangan->ruangan->nama_ruangan}" . PHP_EOL;
    } else {
        echo "- Ruangan: NOT FOUND!" . PHP_EOL;
    }
} else {
    echo "Sesi Ruangan ID 2 not found." . PHP_EOL;
}

echo PHP_EOL;
echo "=== DEBUG COMPLETE ===" . PHP_EOL;
