<?php

namespace App\Services;

use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SesiAssignmentService
{
    /**
     * Auto assign sesi ruangan berdasarkan tanggal yang sama dengan smart reuse logic
     */
    public function autoAssignSesiByDate(JadwalUjian $jadwalUjian)
    {
        if (!$jadwalUjian->auto_assign_sesi || $jadwalUjian->scheduling_mode !== 'flexible') {
            return false;
        }

        $targetDate = $jadwalUjian->tanggal->format('Y-m-d');

        // STEP 1: Check for existing sesi_ruangan that are already used by other jadwal on the same date
        $existingSesiForDate = $this->getSesiRuanganForDate($targetDate);

        $assignedCount = 0;
        $reusedCount = 0;
        $createdCount = 0;

        if (!empty($existingSesiForDate)) {
            // STEP 2: Try to reuse existing sesi_ruangan from same date
            foreach ($existingSesiForDate as $sesi) {
                // Check if this sesi is already linked to our jadwal
                if (!$jadwalUjian->sesiRuangans()->where('sesi_ruangan.id', $sesi->id)->exists()) {
                    // Always reuse existing sesi from same date (share them)
                    $jadwalUjian->sesiRuangans()->attach($sesi->id, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $assignedCount++;
                    $reusedCount++;

                    Log::info("Reused existing sesi {$sesi->kode_sesi} for jadwal {$jadwalUjian->kode_ujian} on date {$targetDate}");
                }
            }
        }

        // STEP 3: If no existing sesi found, create new ones by duplicating from any existing sesi
        if ($assignedCount == 0) {
            $createdCount = $this->createNewSesiByDuplication($jadwalUjian, $targetDate);
            $assignedCount += $createdCount;
        }

        // STEP 4: Log summary
        if ($assignedCount > 0) {
            $summary = "Assigned {$assignedCount} sesi to jadwal {$jadwalUjian->kode_ujian}: ";
            if ($reusedCount > 0) $summary .= "{$reusedCount} reused, ";
            if ($createdCount > 0) $summary .= "{$createdCount} created";
            Log::info($summary);
        } else {
            Log::warning("No sesi assigned to jadwal {$jadwalUjian->kode_ujian} on date {$targetDate}");
        }

        return $assignedCount;
    }

    /**
     * Check if sesi ruangan is suitable for jadwal ujian
     */
    private function isSesiSuitableForJadwal(SesiRuangan $sesi, JadwalUjian $jadwalUjian): bool
    {
        // Basic checks
        if ($sesi->status === 'dibatalkan') {
            return false;
        }

        // We no longer strictly require the session duration to match the exam duration
        // The jadwal ujian duration will be used for student work time

        // However, we still check if the session has reasonable duration to accommodate the exam
        $sesiDuration = $sesi->durasi; // in minutes
        $jadwalDuration = $jadwalUjian->durasi_menit;

        // Session should be at least as long as the exam (with some flexibility)
        // We don't check the upper limit since longer sessions can still accommodate the exam
        if ($sesiDuration && $sesiDuration < ($jadwalDuration - 5)) {
            // Session is too short for the exam (allowing 5 min tolerance)
            return false;
        }

        // Check room capacity if needed
        if ($sesi->ruangan && $sesi->ruangan->kapasitas < 10) {
            // Skip rooms that are too small
            return false;
        }

        return true;
    }

    /**
     * Get all available time slots for a jadwal ujian based on assigned sesi
     */
    public function getAvailableTimeSlots(JadwalUjian $jadwalUjian): array
    {
        $timeSlots = [];

        foreach ($jadwalUjian->sesiRuangans as $sesi) {
            $timeSlots[] = [
                'sesi_id' => $sesi->id,
                'sesi_nama' => $sesi->nama_sesi,
                'ruangan' => $sesi->ruangan->nama_ruangan ?? 'Unknown',
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'tanggal' => $jadwalUjian->tanggal->format('Y-m-d'), // Use jadwal's date instead of sesi's date
                'status' => $sesi->status,
                'kapasitas' => $sesi->ruangan->kapasitas ?? 0,
                'terisi' => $sesi->sesiRuanganSiswa()->count(),
                'tersedia' => $sesi->remainingCapacity()
            ];
        }

        // Sort by waktu_mulai
        usort($timeSlots, function ($a, $b) {
            return strcmp($a['waktu_mulai'], $b['waktu_mulai']);
        });

        return $timeSlots;
    }

    /**
     * Get consolidated schedule information for a jadwal ujian
     */
    public function getConsolidatedSchedule(JadwalUjian $jadwalUjian): array
    {
        $timeSlots = $this->getAvailableTimeSlots($jadwalUjian);

        if (empty($timeSlots)) {
            return [
                'has_schedule' => false,
                'message' => 'Belum ada sesi ruangan yang terkait dengan jadwal ini',
                'time_slots' => [],
                'earliest_start' => null,
                'latest_end' => null,
                'total_capacity' => 0
            ];
        }

        $earliestStart = null;
        $latestEnd = null;
        $totalCapacity = 0;

        foreach ($timeSlots as $slot) {
            if (!$earliestStart || $slot['waktu_mulai'] < $earliestStart) {
                $earliestStart = $slot['waktu_mulai'];
            }

            if (!$latestEnd || $slot['waktu_selesai'] > $latestEnd) {
                $latestEnd = $slot['waktu_selesai'];
            }

            $totalCapacity += $slot['kapasitas'];
        }

        return [
            'has_schedule' => true,
            'time_slots' => $timeSlots,
            'earliest_start' => $earliestStart,
            'latest_end' => $latestEnd,
            'total_capacity' => $totalCapacity,
            'total_sessions' => count($timeSlots),
            'date' => $jadwalUjian->tanggal->format('Y-m-d')
        ];
    }

    /**
     * Auto assign sesi untuk semua jadwal ujian yang eligible
     */
    public function autoAssignForAllEligibleJadwal(): int
    {
        $eligibleJadwal = JadwalUjian::where('auto_assign_sesi', true)
            ->where('scheduling_mode', 'flexible')
            ->whereIn('status', ['draft', 'aktif'])
            ->get();

        $totalAssigned = 0;

        foreach ($eligibleJadwal as $jadwal) {
            $assigned = $this->autoAssignSesiByDate($jadwal);
            $totalAssigned += $assigned;
        }

        Log::info("Auto-assigned {$totalAssigned} sesi ruangan connections");

        return $totalAssigned;
    }

    /**
     * Remove expired or unsuitable sesi assignments
     */
    public function cleanupAssignments(JadwalUjian $jadwalUjian): int
    {
        $removedCount = 0;

        foreach ($jadwalUjian->sesiRuangans as $sesi) {
            // We don't need to check tanggal anymore since it's now stored in jadwal_ujian

            // Remove if sesi is cancelled
            if ($sesi->status === 'dibatalkan') {
                $jadwalUjian->sesiRuangans()->detach($sesi->id);
                $removedCount++;

                Log::info("Removed cancelled sesi {$sesi->kode_sesi} from jadwal {$jadwalUjian->kode_ujian}");
            }
        }

        return $removedCount;
    }

    /**
     * Get existing sesi_ruangan that are already used by jadwal_ujian on specific date
     */
    private function getSesiRuanganForDate(string $targetDate): array
    {
        // Find jadwal_ujian on the target date that have sesi_ruangan assigned
        $jadwalOnDate = JadwalUjian::whereDate('tanggal', $targetDate)
            ->whereHas('sesiRuangans')
            ->with('sesiRuangans')
            ->get();

        $sesiRuanganIds = [];
        foreach ($jadwalOnDate as $jadwal) {
            foreach ($jadwal->sesiRuangans as $sesi) {
                $sesiRuanganIds[] = $sesi->id;
            }
        }

        // Return unique sesi_ruangan that are used on this date
        if (empty($sesiRuanganIds)) {
            return [];
        }

        return SesiRuangan::whereIn('id', array_unique($sesiRuanganIds))->get()->all();
    }

    /**
     * Create new sesi_ruangan by duplicating existing sesi structure
     */
    private function createNewSesiByDuplication(JadwalUjian $jadwalUjian, string $targetDate): int
    {
        // Get any existing sesi_ruangan as template for duplication
        $existingSesi = SesiRuangan::whereHas('ruangan', function ($query) {
            $query->where('status', 'aktif');
        })->with('ruangan')->first();

        if (!$existingSesi) {
            // If no existing sesi found, create basic sesi for all active rooms
            return $this->createBasicSesiForAllRooms($jadwalUjian, $targetDate);
        }

        // Get all active rooms for duplication
        $activeRooms = \App\Models\Ruangan::where('status', 'aktif')->get();
        $createdCount = 0;

        foreach ($activeRooms as $room) {
            // Create new sesi by duplicating structure but with new date and room
            $newSesi = SesiRuangan::create([
                'ruangan_id' => $room->id,
                'nama_sesi' => $existingSesi->nama_sesi . ' - ' . $room->nama_ruangan,
                'waktu_mulai' => $existingSesi->waktu_mulai,
                'waktu_selesai' => $existingSesi->waktu_selesai,
                'status' => 'belum_mulai',
                'pengaturan' => $existingSesi->pengaturan,
                'template_id' => null // No template dependency
            ]);

            // Link the new sesi to the jadwal ujian
            $jadwalUjian->sesiRuangans()->attach($newSesi->id, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $createdCount++;

            Log::info("Created new sesi {$newSesi->kode_sesi} for jadwal {$jadwalUjian->kode_ujian} in room {$room->nama_ruangan}");
        }

        return $createdCount;
    }

    /**
     * Create basic sesi for all rooms when no existing sesi found
     */
    private function createBasicSesiForAllRooms(JadwalUjian $jadwalUjian, string $targetDate): int
    {
        $activeRooms = \App\Models\Ruangan::where('status', 'aktif')->get();
        $createdCount = 0;

        foreach ($activeRooms as $room) {
            // Create basic sesi with default timing
            $newSesi = SesiRuangan::create([
                'ruangan_id' => $room->id,
                'nama_sesi' => 'Sesi Ujian - ' . $room->nama_ruangan,
                'waktu_mulai' => $jadwalUjian->waktu_mulai,
                'waktu_selesai' => $jadwalUjian->waktu_selesai,
                'status' => 'belum_mulai',
                'pengaturan' => json_encode([
                    'max_peserta' => $room->kapasitas,
                    'jadwal_ujian_id' => $jadwalUjian->id,
                ]),
                'template_id' => null
            ]);

            // Link the new sesi to the jadwal ujian
            $jadwalUjian->sesiRuangans()->attach($newSesi->id, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $createdCount++;

            Log::info("Created basic sesi {$newSesi->kode_sesi} for jadwal {$jadwalUjian->kode_ujian} in room {$room->nama_ruangan}");
        }

        return $createdCount;
    }
}
