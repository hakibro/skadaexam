#!/usr/bin/env php
<?php

// Simple test script to verify the many-to-many relationship is working
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Many-to-Many Relationship between SesiRuangan and JadwalUjian\n";
echo "==================================================================\n\n";

try {
    // Test getting a SesiRuangan and its JadwalUjians
    $sesi = App\Models\SesiRuangan::with(['jadwalUjians', 'ruangan'])->first();

    if ($sesi) {
        echo "✓ Found SesiRuangan: {$sesi->nama_sesi} (ID: {$sesi->id})\n";
        echo "✓ In Ruangan: {$sesi->ruangan->nama_ruangan}\n";
        echo "✓ Associated JadwalUjians: {$sesi->jadwalUjians->count()}\n\n";

        if ($sesi->jadwalUjians->count() > 0) {
            echo "JadwalUjians for this SesiRuangan:\n";
            foreach ($sesi->jadwalUjians as $jadwal) {
                echo "  - {$jadwal->judul} ({$jadwal->kode_ujian})\n";
            }
            echo "\n";
        }
    } else {
        echo "✗ No SesiRuangan found in database\n";
    }

    // Test getting a JadwalUjian and its SesiRuangans
    $jadwal = App\Models\JadwalUjian::with(['sesiRuangans'])->first();

    if ($jadwal) {
        echo "✓ Found JadwalUjian: {$jadwal->judul} (ID: {$jadwal->id})\n";
        echo "✓ Associated SesiRuangans: {$jadwal->sesiRuangans->count()}\n\n";

        if ($jadwal->sesiRuangans->count() > 0) {
            echo "SesiRuangans for this JadwalUjian:\n";
            foreach ($jadwal->sesiRuangans as $sesi) {
                echo "  - {$sesi->nama_sesi} (Ruangan: {$sesi->ruangan->nama_ruangan})\n";
            }
            echo "\n";
        }
    } else {
        echo "✗ No JadwalUjian found in database\n";
    }

    // Test pivot table exists
    $pivotCount = Illuminate\Support\Facades\DB::table('jadwal_ujian_sesi_ruangan')->count();
    echo "✓ Pivot table 'jadwal_ujian_sesi_ruangan' exists with {$pivotCount} records\n";

    echo "\n✓ Many-to-Many relationship test completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
