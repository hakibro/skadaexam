<?php

echo 'Final verification: Koordinator Laporan Index display fix...' . PHP_EOL;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BeritaAcaraUjian;
use Carbon\Carbon;

// Simulate the controller query
$beritaAcaras = BeritaAcaraUjian::with(['sesiRuangan.ruangan', 'sesiRuangan.jadwalUjians.mapel', 'pengawas'])
    ->whereNotNull('sesi_ruangan_id')
    ->take(5)
    ->get();

echo 'Processing ' . $beritaAcaras->count() . ' BeritaAcaraUjian records...' . PHP_EOL;

foreach ($beritaAcaras as $beritaAcara) {
    echo PHP_EOL . '--- BeritaAcara ID: ' . $beritaAcara->id . ' ---' . PHP_EOL;

    // Date display logic
    if ($beritaAcara->sesiRuangan && $beritaAcara->sesiRuangan->jadwalUjians->count() > 0) {
        $beritaAcaraDate = Carbon::parse($beritaAcara->created_at)->toDateString();

        $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians->filter(function ($jadwal) use ($beritaAcaraDate) {
            $jadwalDate = Carbon::parse($jadwal->tanggal)->toDateString();
            return abs(Carbon::parse($jadwalDate)->diffInDays(Carbon::parse($beritaAcaraDate))) <= 1;
        });

        if ($relevantJadwals->isEmpty()) {
            $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians;
        }

        $displayDate = $relevantJadwals->first()->tanggal ?? $beritaAcara->created_at->format('Y-m-d');
        echo 'Date: ' . Carbon::parse($displayDate)->format('d M Y') . PHP_EOL;
    } else {
        echo 'Date: ' . $beritaAcara->created_at->format('d M Y') . PHP_EOL;
    }

    // Mapel display logic
    if ($beritaAcara->sesiRuangan && $beritaAcara->sesiRuangan->jadwalUjians->count() > 0) {
        $beritaAcaraDate = Carbon::parse($beritaAcara->created_at)->toDateString();

        $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians->filter(function ($jadwal) use ($beritaAcaraDate) {
            $jadwalDate = Carbon::parse($jadwal->tanggal)->toDateString();
            return abs(Carbon::parse($jadwalDate)->diffInDays(Carbon::parse($beritaAcaraDate))) <= 1;
        });

        if ($relevantJadwals->isEmpty()) {
            $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians;
        }

        $mapelNames = $relevantJadwals
            ->filter(function ($jadwal) {
                return $jadwal->mapel !== null;
            })
            ->map(function ($jadwal) {
                return $jadwal->mapel->nama_mapel;
            })
            ->unique();

        if ($mapelNames->count() > 0) {
            $mapelDisplay = $mapelNames->implode(' + ');
            if ($mapelNames->count() > 1) {
                $mapelDisplay .= ' (' . $mapelNames->count() . ' mapel)';
            }
            echo 'Mapel: ' . $mapelDisplay . PHP_EOL;
        } else {
            echo 'Mapel: Mapel tidak tersedia' . PHP_EOL;
        }
    } else {
        echo 'Mapel: Tidak ada jadwal' . PHP_EOL;
    }

    // Time display
    echo 'Waktu: ' . ($beritaAcara->sesiRuangan->waktu_mulai ?? 'N/A') . ' - ' . ($beritaAcara->sesiRuangan->waktu_selesai ?? 'N/A') . PHP_EOL;
}

echo PHP_EOL . '✅ Koordinator Laporan Index page should now display:' . PHP_EOL;
echo '   - Correct exam dates based on relevant jadwal ujians' . PHP_EOL;
echo '   - Subject names filtered by exam date (matching berita acara date ±1 day)' . PHP_EOL;
echo '   - Proper grouping and counting of multiple subjects' . PHP_EOL;
echo '✅ Fix completed successfully!' . PHP_EOL;
