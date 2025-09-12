<?php

echo 'Investigating if jadwal filtering should be applied...' . PHP_EOL;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SesiRuangan;
use App\Models\JadwalUjian;
use Carbon\Carbon;

$sesiRuangan = SesiRuangan::with(['ruangan', 'jadwalUjians', 'jadwalUjians.mapel'])
    ->find(113);

$today = Carbon::today();
echo 'Today: ' . $today->toDateString() . PHP_EOL;
echo 'Current time: ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;

echo PHP_EOL . 'Jadwal Analysis by Date:' . PHP_EOL;
foreach ($sesiRuangan->jadwalUjians as $index => $jadwal) {
    $jadwalDate = Carbon::parse($jadwal->tanggal);
    $isToday = $jadwalDate->isSameDay($today);
    $isFuture = $jadwalDate->isFuture();
    $isPast = $jadwalDate->isPast();

    echo '  Jadwal ' . ($index + 1) . ' (' . $jadwal->mapel->nama_mapel . '):' . PHP_EOL;
    echo '    Date: ' . $jadwal->tanggal . ' (' . ($isToday ? 'TODAY' : ($isFuture ? 'FUTURE' : 'PAST')) . ')' . PHP_EOL;
    echo '    Status: ' . ($jadwal->status ?? 'no status') . PHP_EOL;
    echo PHP_EOL;
}

// Check if there's active filtering that should be applied
echo 'Possible filters to consider:' . PHP_EOL;
echo '  Today only: ' . $sesiRuangan->jadwalUjians->filter(function ($j) use ($today) {
    return Carbon::parse($j->tanggal)->isSameDay($today);
})->count() . ' jadwal' . PHP_EOL;

echo '  Active status: ' . $sesiRuangan->jadwalUjians->filter(function ($j) {
    return $j->status === 'active' || $j->status === 'aktif';
})->count() . ' jadwal' . PHP_EOL;

echo '  Current/Future only: ' . $sesiRuangan->jadwalUjians->filter(function ($j) use ($today) {
    return !Carbon::parse($j->tanggal)->isPast() || Carbon::parse($j->tanggal)->isSameDay($today);
})->count() . ' jadwal' . PHP_EOL;

// Check what should be the "relevant" jadwal for token generation
echo PHP_EOL . 'Token Generation Context:' . PHP_EOL;
echo 'For token generation, we probably want to show:' . PHP_EOL;
echo '  - Current day exams only? Or' . PHP_EOL;
echo '  - Active status exams only? Or' . PHP_EOL;
echo '  - Specific session time range?' . PHP_EOL;
