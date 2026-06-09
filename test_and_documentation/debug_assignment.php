<?php

require 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SesiRuangan;

echo "=== Debugging SesiRuangan 111 Data Structure ===\n";

$sesiRuangan = SesiRuangan::with([
    'jadwalUjians',
    'jadwalUjians.mapel',
    'ruangan',
    'siswa',
    'sesiRuanganSiswa',
    'sesiRuanganSiswa.siswa',
    'sesiRuanganSiswa.siswa.kelas'
])->find(111);

if ($sesiRuangan) {
    echo "=== Sesi Ruangan Info ===\n";
    echo "ID: {$sesiRuangan->id}\n";
    echo "Nama Sesi: {$sesiRuangan->nama_sesi}\n";
    echo "Ruangan: " . ($sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'N/A') . "\n";

    echo "\n=== Jadwal Ujians ===\n";
    echo "Count: " . $sesiRuangan->jadwalUjians->count() . "\n";
    foreach ($sesiRuangan->jadwalUjians as $jadwal) {
        echo "- Jadwal ID: {$jadwal->id}\n";
        echo "  Mapel: " . ($jadwal->mapel ? $jadwal->mapel->nama : 'N/A') . "\n";
        echo "  Tanggal: {$jadwal->tanggal}\n";
    }

    echo "\n=== Siswa (via direct relationship) ===\n";
    echo "Count: " . $sesiRuangan->siswa->count() . "\n";
    foreach ($sesiRuangan->siswa->take(3) as $siswa) {
        echo "- Siswa ID: {$siswa->id}, NIS: {$siswa->nis}, Nama: {$siswa->nama_lengkap}\n";
    }

    echo "\n=== SesiRuanganSiswa (pivot table records) ===\n";
    echo "Count: " . $sesiRuangan->sesiRuanganSiswa->count() . "\n";
    foreach ($sesiRuangan->sesiRuanganSiswa->take(3) as $sesiSiswa) {
        echo "- SRS ID: {$sesiSiswa->id}, Siswa ID: {$sesiSiswa->siswa_id}\n";
        if ($sesiSiswa->siswa) {
            echo "  Siswa: {$sesiSiswa->siswa->nama_lengkap} ({$sesiSiswa->siswa->nis})\n";
            echo "  Kelas: " . ($sesiSiswa->siswa->kelas ? $sesiSiswa->siswa->kelas->nama : 'N/A') . "\n";
        } else {
            echo "  Siswa: NOT LOADED\n";
        }
        echo "  Status Kehadiran: " . ($sesiSiswa->status_kehadiran ?? 'NULL') . "\n";
    }
} else {
    echo "SesiRuangan with ID 111 not found\n";
}

echo "\n=== Sample Siswa Data Check ===\n";
$sampleSiswa = \App\Models\Siswa::first();
if ($sampleSiswa) {
    echo "Sample Siswa attributes:\n";
    foreach ($sampleSiswa->getAttributes() as $key => $value) {
        echo "  {$key}: " . (is_null($value) ? 'NULL' : "'{$value}'") . "\n";
    }
}

echo "\n=== Sample Mapel Data Check ===\n";
$sampleMapel = \App\Models\Mapel::first();
if ($sampleMapel) {
    echo "Sample Mapel attributes:\n";
    foreach ($sampleMapel->getAttributes() as $key => $value) {
        echo "  {$key}: " . (is_null($value) ? 'NULL' : "'{$value}'") . "\n";
    }
}

echo "\n=== Debug complete ===\n";
