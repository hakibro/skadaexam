use App\Models\SesiRuangan;
use App\Models\JadwalUjian;
use App\Services\SesiAssignmentService;

$today = '2025-09-05';

echo "=== Checking Sesi Ruangan for Today ({$today}) ===\n\n";

// Check sesi ruangan with today's date
$todaySesi = SesiRuangan::whereDate('tanggal', $today)->get();
echo "Sesi ruangan for today: " . $todaySesi->count() . "\n";

foreach ($todaySesi as $sesi) {
echo "- {$sesi->nama_sesi} | Time: {$sesi->waktu_mulai}-{$sesi->waktu_selesai} | Room: " . ($sesi->ruangan->nama_ruangan ?? 'No room') . "\n";
}
echo "\n";

// Check jadwal ujian with today's date
$todayJadwal = JadwalUjian::whereDate('tanggal', $today)->get();
echo "Jadwal ujian for today: " . $todayJadwal->count() . "\n";

foreach ($todayJadwal as $jadwal) {
echo "- {$jadwal->judul} | Mode: {$jadwal->scheduling_mode} | Auto: " . ($jadwal->auto_assign_sesi ? 'Yes' : 'No') . "\n";
}
echo "\n";

// If no sesi today, create some test data
if ($todaySesi->count() === 0) {
echo "Creating test sesi ruangan for today...\n";

// First, check if we have ruangan
$ruangan = \App\Models\Ruangan::first();
if ($ruangan) {
$testSesi = SesiRuangan::create([
'kode_sesi' => 'TEST-' . strtoupper(Str::random(6)),
'nama_sesi' => 'Test Sesi ' . date('H:i'),
'tanggal' => $today,
'waktu_mulai' => '08:00:00',
'waktu_selesai' => '10:00:00',
'status' => 'belum_mulai',
'ruangan_id' => $ruangan->id,
]);

echo "Created test sesi: {$testSesi->nama_sesi}\n";
} else {
echo "No ruangan available to create test sesi\n";
}
}

echo "\n=== Testing Assignment Service ===\n";

// Test with a jadwal that has today's date
$testJadwal = JadwalUjian::whereDate('tanggal', $today)->first();
if ($testJadwal) {
echo "Testing with: {$testJadwal->judul}\n";

$service = new SesiAssignmentService();
$assigned = $service->autoAssignSesiByDate($testJadwal);
echo "Assigned: {$assigned} sesi\n";

$scheduleInfo = $service->getConsolidatedSchedule($testJadwal);
echo "Schedule info: " . ($scheduleInfo['has_schedule'] ? 'Yes' : 'No') . "\n";
echo "Total sessions: {$scheduleInfo['total_sessions']}\n";

if ($scheduleInfo['has_schedule']) {
echo "Time slots:\n";
foreach ($scheduleInfo['time_slots'] as $slot) {
echo " - {$slot['sesi_nama']}: {$slot['waktu_mulai']}-{$slot['waktu_selesai']}\n";
}
}
}

echo "\n=== Test Complete ===\n";