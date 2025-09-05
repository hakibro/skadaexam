use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Services\SesiAssignmentService;

echo "=== Testing New Jadwal Ujian Scheduling ===\n\n";

// Check migration results
echo "1. Migration Results:\n";
echo "Total jadwal ujian: " . JadwalUjian::count() . "\n";
echo "Flexible scheduling: " . JadwalUjian::where('scheduling_mode', 'flexible')->count() . "\n";
echo "Auto assign enabled: " . JadwalUjian::where('auto_assign_sesi', true)->count() . "\n\n";

// Test service
echo "2. Testing SesiAssignmentService:\n";
$service = new SesiAssignmentService();
$sampleJadwal = JadwalUjian::where('scheduling_mode', 'flexible')->first();

if ($sampleJadwal) {
echo "Sample jadwal: {$sampleJadwal->judul}\n";
echo "Date: " . $sampleJadwal->tanggal->format('Y-m-d') . "\n";

$assigned = $service->autoAssignSesiByDate($sampleJadwal);
echo "Auto assigned: {$assigned} sesi\n";

$scheduleInfo = $service->getConsolidatedSchedule($sampleJadwal);
echo "Total sessions: {$scheduleInfo['total_sessions']}\n";
echo "Total capacity: {$scheduleInfo['total_capacity']}\n";
} else {
echo "No flexible jadwal found\n";
}

echo "\n=== Test Complete ===\n";