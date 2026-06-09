<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "Testing violation fetching logic..." . PHP_EOL;

// Check if violations exist and which sessions they belong to
$violations = App\Models\PelanggaranUjian::with([
    'siswa',
    'hasilUjian',
    'jadwalUjian.mapel',
    'sesiRuangan.ruangan'
])->orderBy('waktu_pelanggaran', 'desc')->limit(5)->get();

echo "Found " . $violations->count() . " violations:" . PHP_EOL;

foreach ($violations as $violation) {
    echo "- ID: " . $violation->id . PHP_EOL;
    echo "  Type: " . $violation->jenis_pelanggaran . PHP_EOL;
    echo "  Time: " . $violation->waktu_pelanggaran . PHP_EOL;
    echo "  Siswa: " . ($violation->siswa ? $violation->siswa->nama : 'Unknown') . PHP_EOL;
    echo "  Sesi Ruangan ID: " . $violation->sesi_ruangan_id . PHP_EOL;
    echo "  Jadwal Ujian ID: " . $violation->jadwal_ujian_id . PHP_EOL;
    echo PHP_EOL;
}

// Check which sessions exist today
$today = now()->format('Y-m-d');
echo "Sessions today ($today):" . PHP_EOL;

$sessions = App\Models\SesiRuangan::with(['jadwalUjians' => function ($q) use ($today) {
    $q->whereDate('tanggal', $today);
}, 'ruangan'])->whereHas('jadwalUjians', function ($q) use ($today) {
    $q->whereDate('tanggal', $today);
})->get();

foreach ($sessions as $session) {
    echo "- Session ID: " . $session->id . ", Nama: " . $session->nama_sesi . ", Ruangan: " . ($session->ruangan ? $session->ruangan->nama_ruangan : 'Unknown') . PHP_EOL;
}

// Check pivot table assignments
echo PHP_EOL . "Pengawas assignments for today:" . PHP_EOL;
$assignments = App\Models\JadwalUjianSesiRuangan::with(['jadwalUjian', 'sesiRuangan', 'pengawas'])
    ->join('jadwal_ujian', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id', '=', 'jadwal_ujian.id')
    ->whereDate('jadwal_ujian.tanggal', $today)
    ->get();

foreach ($assignments as $assignment) {
    echo "- Pengawas: " . ($assignment->pengawas ? $assignment->pengawas->nama : 'Unknown') . PHP_EOL;
    echo "  Session: " . ($assignment->sesiRuangan ? $assignment->sesiRuangan->nama_sesi : 'Unknown') . PHP_EOL;
    echo "  Jadwal: " . ($assignment->jadwalUjian ? $assignment->jadwalUjian->judul : 'Unknown') . PHP_EOL;
    echo PHP_EOL;
}
