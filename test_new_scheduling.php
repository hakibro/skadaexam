<?php

/**
 * Test script untuk menguji konsep jadwal ujian berbasis sesi ruangan
 */

require_once 'bootstrap/app.php';

use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Ruangan;
use App\Services\SesiAssignmentService;

echo "=== Testing New Jadwal Ujian Scheduling Concept ===\n\n";

try {
    // Test 1: Check existing jadwal ujian after migration
    echo "1. Checking existing jadwal ujian after migration...\n";

    $jadwalCount = JadwalUjian::count();
    echo "Total jadwal ujian: {$jadwalCount}\n";

    // Check flexible scheduling jadwal
    $flexibleCount = JadwalUjian::where('scheduling_mode', 'flexible')->count();
    echo "Flexible scheduling jadwal: {$flexibleCount}\n";

    // Check auto assign enabled
    $autoAssignCount = JadwalUjian::where('auto_assign_sesi', true)->count();
    echo "Auto assign enabled: {$autoAssignCount}\n\n";

    // Test 2: Test SesiAssignmentService
    echo "2. Testing SesiAssignmentService...\n";

    $sesiService = new SesiAssignmentService();

    // Get a sample flexible jadwal
    $sampleJadwal = JadwalUjian::where('scheduling_mode', 'flexible')->first();

    if ($sampleJadwal) {
        echo "Testing with jadwal: {$sampleJadwal->judul}\n";
        echo "Jadwal date: {$sampleJadwal->tanggal->format('Y-m-d')}\n";

        // Test auto assignment
        $assignedCount = $sesiService->autoAssignSesiByDate($sampleJadwal);
        echo "Auto assigned sesi: {$assignedCount}\n";

        // Test consolidated schedule
        $scheduleInfo = $sesiService->getConsolidatedSchedule($sampleJadwal);
        echo "Schedule info:\n";
        echo "- Has schedule: " . ($scheduleInfo['has_schedule'] ? 'Yes' : 'No') . "\n";
        echo "- Total sessions: {$scheduleInfo['total_sessions']}\n";
        echo "- Total capacity: {$scheduleInfo['total_capacity']}\n";

        if ($scheduleInfo['has_schedule']) {
            echo "- Earliest start: {$scheduleInfo['earliest_start']}\n";
            echo "- Latest end: {$scheduleInfo['latest_end']}\n";

            // Show time slots
            echo "Time slots:\n";
            foreach ($scheduleInfo['time_slots'] as $slot) {
                echo "  * Sesi: {$slot['sesi_nama']} | Ruangan: {$slot['ruangan']} | ";
                echo "Waktu: {$slot['waktu_mulai']}-{$slot['waktu_selesai']} | ";
                echo "Kapasitas: {$slot['tersedia']}/{$slot['kapasitas']}\n";
            }
        }
        echo "\n";

        // Test model methods
        echo "3. Testing model methods...\n";

        $sampleJadwal->refresh();
        echo "Is flexible scheduling: " . ($sampleJadwal->isFlexibleScheduling() ? 'Yes' : 'No') . "\n";
        echo "Total capacity: " . $sampleJadwal->getTotalCapacity() . "\n";

        $scheduleSummary = $sampleJadwal->getScheduleSummary();
        echo "Schedule summary:\n";
        echo "- Mode: {$scheduleSummary['mode']}\n";
        echo "- Tanggal: {$scheduleSummary['tanggal']}\n";
        echo "- Waktu: {$scheduleSummary['waktu']}\n";
        echo "- Durasi: {$scheduleSummary['durasi']}\n";
        echo "- Sesi count: {$scheduleSummary['sesi_count']}\n\n";

        // Test time slots
        echo "4. Testing time slots...\n";
        $timeSlots = $sampleJadwal->getTimeSlots();
        echo "Found " . count($timeSlots) . " time slots:\n";

        foreach ($timeSlots as $i => $slot) {
            echo "Slot " . ($i + 1) . ":\n";
            if (isset($slot['sesi_nama'])) {
                echo "  - Sesi: {$slot['sesi_nama']}\n";
                echo "  - Ruangan: {$slot['ruangan']}\n";
            }
            echo "  - Waktu mulai: {$slot['waktu_mulai']}\n";
            echo "  - Waktu selesai: {$slot['waktu_selesai']}\n";
            echo "  - Durasi: {$slot['durasi_menit']} menit\n";
            echo "  - Source: {$slot['source']}\n";
        }
        echo "\n";
    } else {
        echo "No flexible jadwal found for testing\n\n";
    }

    // Test 3: Check sesi ruangan data
    echo "5. Checking sesi ruangan data...\n";

    $sesiCount = SesiRuangan::count();
    echo "Total sesi ruangan: {$sesiCount}\n";

    // Show some sample sesi with their dates
    $sampleSesi = SesiRuangan::with('ruangan')->take(5)->get();
    echo "Sample sesi ruangan:\n";

    foreach ($sampleSesi as $sesi) {
        echo "- Sesi: {$sesi->nama_sesi} | ";
        echo "Tanggal: {$sesi->tanggal} | ";
        echo "Waktu: {$sesi->waktu_mulai}-{$sesi->waktu_selesai} | ";
        echo "Ruangan: " . ($sesi->ruangan->nama_ruangan ?? 'No room') . " | ";
        echo "Jadwal count: " . $sesi->jadwalUjians()->count() . "\n";
    }
    echo "\n";

    // Test 4: Test command simulation
    echo "6. Testing command simulation...\n";

    $eligibleJadwal = JadwalUjian::where('auto_assign_sesi', true)
        ->where('scheduling_mode', 'flexible')
        ->whereIn('status', ['draft', 'aktif'])
        ->get();

    echo "Found {$eligibleJadwal->count()} eligible jadwal for auto assignment:\n";

    foreach ($eligibleJadwal->take(5) as $jadwal) {
        echo "- {$jadwal->judul} | Date: {$jadwal->tanggal->format('Y-m-d')} | ";
        echo "Current sesi: {$jadwal->sesiRuangans()->count()} | Status: {$jadwal->status}\n";
    }
    echo "\n";

    echo "=== Test Completed Successfully! ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
