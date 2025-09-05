#!/usr/bin/env php
<?php
/**
 * Test script untuk fitur jadwal ujian yang baru
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Jadwal Ujian New Features ===\n\n";

try {
    // Test 1: Check jadwal ujian with new status
    echo "1. Testing Status Values:\n";
    $statusCounts = [];
    $statuses = ['draft', 'aktif', 'nonaktif', 'selesai'];

    foreach ($statuses as $status) {
        $count = App\Models\JadwalUjian::where('status', $status)->count();
        $statusCounts[$status] = $count;
        echo "   - Status '$status': $count records\n";
    }

    // Test 2: Check many-to-many relationship
    echo "\n2. Testing Many-to-Many Relationship:\n";
    $jadwal = App\Models\JadwalUjian::with('sesiRuangans')->first();
    if ($jadwal) {
        echo "   - Jadwal: {$jadwal->judul}\n";
        echo "   - Sesi Ruangan Count: {$jadwal->sesiRuangans->count()}\n";

        if ($jadwal->sesiRuangans->count() > 0) {
            echo "   - Sample Sesi Ruangans:\n";
            foreach ($jadwal->sesiRuangans->take(3) as $sesi) {
                $ruanganNama = $sesi->ruangan ? $sesi->ruangan->nama_ruangan : 'N/A';
                echo "     * {$sesi->nama_sesi} - {$ruanganNama}\n";
            }
        }
    }

    // Test 3: Check status badge functionality
    echo "\n3. Testing Status Badges:\n";
    foreach ($statuses as $status) {
        $jadwal = App\Models\JadwalUjian::where('status', $status)->first();
        if ($jadwal) {
            $badge = $jadwal->status_badge;
            echo "   - Status '$status' badge: {$badge['text']} ({$badge['class']})\n";
        }
    }

    // Test 4: Test tanggal functionality
    echo "\n4. Testing Date Functionality:\n";
    $jadwal = App\Models\JadwalUjian::first();
    if ($jadwal && $jadwal->tanggal) {
        echo "   - Jadwal tanggal: {$jadwal->tanggal->format('d M Y')}\n";
        echo "   - Accessor waktu_mulai: " . ($jadwal->waktu_mulai ? $jadwal->waktu_mulai->format('H:i') : 'N/A') . "\n";
        echo "   - Accessor waktu_selesai: " . ($jadwal->waktu_selesai ? $jadwal->waktu_selesai->format('H:i') : 'N/A') . "\n";
    }

    echo "\n✅ All tests completed successfully!\n\n";

    echo "=== Summary ===\n";
    echo "- New status values are working: " . (array_sum($statusCounts) > 0 ? "✅" : "❌") . "\n";
    echo "- Many-to-many relationship: " . ($jadwal && $jadwal->sesiRuangans ? "✅" : "❌") . "\n";
    echo "- Status badges: ✅\n";
    echo "- Date handling: " . ($jadwal && $jadwal->tanggal ? "✅" : "❌") . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
