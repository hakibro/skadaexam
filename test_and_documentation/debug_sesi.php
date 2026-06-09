<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JadwalUjian;
use App\Models\SesiRuangan;

echo "=== DEBUGGING SESI RUANGAN ===\n";

echo "Total sesi_ruangan: " . SesiRuangan::count() . "\n";

echo "\nExisting sesi_ruangan:\n";
foreach (SesiRuangan::all() as $sesi) {
    echo "- ID: {$sesi->id}, {$sesi->nama_sesi}, Template: {$sesi->template_id}\n";
}

echo "\nJadwal ujian for today:\n";
$jadwalToday = JadwalUjian::whereDate('tanggal', today())->get();
foreach ($jadwalToday as $jadwal) {
    echo "- {$jadwal->kode_ujian} - {$jadwal->judul}\n";
    echo "  Sesi count: " . $jadwal->sesiRuangans()->count() . "\n";
}

echo "\nJadwal ujian for tomorrow:\n";
$jadwalTomorrow = JadwalUjian::whereDate('tanggal', today()->addDay())->get();
foreach ($jadwalTomorrow as $jadwal) {
    echo "- {$jadwal->kode_ujian} - {$jadwal->judul}\n";
    echo "  Sesi count: " . $jadwal->sesiRuangans()->count() . "\n";
}
