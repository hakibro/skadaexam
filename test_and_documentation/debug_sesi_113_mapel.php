<?php

echo 'Investigating SesiRuangan 113 mapel count issue...' . PHP_EOL;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SesiRuangan;

$sesiRuangan = SesiRuangan::with(['ruangan', 'jadwalUjians', 'jadwalUjians.mapel'])
    ->find(113);

if (!$sesiRuangan) {
    echo 'SesiRuangan 113 not found' . PHP_EOL;
    exit;
}

echo 'SesiRuangan 113 Details:' . PHP_EOL;
echo '  ID: ' . $sesiRuangan->id . PHP_EOL;
echo '  Nama Sesi: ' . $sesiRuangan->nama_sesi . PHP_EOL;
echo '  Ruangan: ' . ($sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'No ruangan') . PHP_EOL;
echo '  Total jadwalUjians: ' . $sesiRuangan->jadwalUjians->count() . PHP_EOL;

echo PHP_EOL . 'JadwalUjians Details:' . PHP_EOL;
foreach ($sesiRuangan->jadwalUjians as $index => $jadwal) {
    echo '  Jadwal ' . ($index + 1) . ':' . PHP_EOL;
    echo '    ID: ' . $jadwal->id . PHP_EOL;
    echo '    Mapel ID: ' . ($jadwal->mapel_id ?? 'NULL') . PHP_EOL;
    echo '    Mapel: ' . ($jadwal->mapel ? $jadwal->mapel->nama_mapel : 'NULL/Missing') . PHP_EOL;
    echo '    Tanggal: ' . $jadwal->tanggal . PHP_EOL;
    echo '    Waktu: ' . $jadwal->waktu_mulai . ' - ' . $jadwal->waktu_selesai . PHP_EOL;
}

// Check unique mapel
$mapelNames = $sesiRuangan->jadwalUjians
    ->filter(function ($jadwal) {
        return $jadwal->mapel !== null;
    })
    ->map(function ($jadwal) {
        return $jadwal->mapel->nama_mapel;
    })
    ->unique();

echo PHP_EOL . 'Mapel Analysis:' . PHP_EOL;
echo '  Total jadwal ujian: ' . $sesiRuangan->jadwalUjians->count() . PHP_EOL;
echo '  Jadwal with mapel: ' . $sesiRuangan->jadwalUjians->filter(fn($j) => $j->mapel !== null)->count() . PHP_EOL;
echo '  Unique mapel count: ' . $mapelNames->count() . PHP_EOL;
echo '  Unique mapel names: ' . $mapelNames->implode(', ') . PHP_EOL;

// Check what the view will show
if ($sesiRuangan->jadwalUjians->count() > 1) {
    echo PHP_EOL . 'View Logic Analysis (multiple jadwal):' . PHP_EOL;
    echo '  Display: "' . $mapelNames->implode(' + ') . '"' . PHP_EOL;
    if ($mapelNames->count() != $sesiRuangan->jadwalUjians->count()) {
        echo '  Note: "(' . $mapelNames->count() . ' dari ' . $sesiRuangan->jadwalUjians->count() . ' mapel)"' . PHP_EOL;
    } else {
        echo '  Note: "(' . $mapelNames->count() . ' mapel)"' . PHP_EOL;
    }
}

echo PHP_EOL . 'Expected vs Actual:' . PHP_EOL;
echo '  Expected unique mapel: 2' . PHP_EOL;
echo '  Actual unique mapel: ' . $mapelNames->count() . PHP_EOL;
echo '  Total jadwal count: ' . $sesiRuangan->jadwalUjians->count() . PHP_EOL;
