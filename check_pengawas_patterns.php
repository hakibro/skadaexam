<?php

require_once 'vendor/autoload.php';

// Load the Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SesiRuangan;

echo "Checking pengawas assignment patterns\n";
echo "=====================================\n";

// Sample a few sessions to understand pengawas patterns
$sessions = SesiRuangan::with(['jadwalUjians'])->take(5)->get();

foreach ($sessions as $sesi) {
    echo "Sesi: {$sesi->nama_sesi}\n";
    echo "Jadwal count: " . $sesi->jadwalUjians->count() . "\n";

    $pengawasList = [];
    foreach ($sesi->jadwalUjians as $jadwal) {
        $pengawas = $sesi->getPengawasForJadwal($jadwal->id);
        if ($pengawas) {
            $pengawasList[$pengawas->id] = $pengawas->nama;
        }
    }

    echo "Unique pengawas: " . count($pengawasList) . "\n";
    if (!empty($pengawasList)) {
        echo "Pengawas names: " . implode(', ', $pengawasList) . "\n";
    }
    echo "---\n";
}

echo "Done!\n";
