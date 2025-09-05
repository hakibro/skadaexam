#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing JadwalUjian with ID 2\n";
echo "============================\n";

try {
    $jadwal = App\Models\JadwalUjian::with(['sesiRuangans', 'mapel'])->find(2);

    if ($jadwal) {
        echo "✓ Found Jadwal: {$jadwal->judul}\n";
        echo "✓ Mapel: " . ($jadwal->mapel->nama_mapel ?? 'N/A') . "\n";
        echo "✓ Status: {$jadwal->status}\n";
        echo "✓ Sesi Ruangans Count: {$jadwal->sesiRuangans->count()}\n";

        if ($jadwal->sesiRuangans->count() > 0) {
            echo "\nSesi Ruangans:\n";
            foreach ($jadwal->sesiRuangans->take(3) as $index => $sesi) {
                echo "  " . ($index + 1) . ". {$sesi->nama_sesi}\n";
                echo "     - Ruangan: " . ($sesi->ruangan->nama_ruangan ?? 'N/A') . "\n";
                echo "     - Tanggal: " . ($sesi->tanggal ? $sesi->tanggal->format('d M Y') : 'N/A') . "\n";
                echo "     - Waktu: " . ($sesi->waktu_mulai ? \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') : 'N/A') . " - " . ($sesi->waktu_selesai ? \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') : 'N/A') . "\n";
            }
            if ($jadwal->sesiRuangans->count() > 3) {
                echo "  ... dan " . ($jadwal->sesiRuangans->count() - 3) . " sesi lainnya\n";
            }
        }

        echo "\n✅ Relationship test passed!\n";
    } else {
        echo "✗ Jadwal with ID 2 not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
