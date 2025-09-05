<?php

namespace App\Services;

use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SesiAssignmentService
{
    /**
     * Auto assign sesi ruangan berdasarkan tanggal yang sama
     */
    public function autoAssignSesiByDate(JadwalUjian $jadwalUjian)
    {
        if (!$jadwalUjian->auto_assign_sesi || $jadwalUjian->scheduling_mode !== 'flexible') {
            return false;
        }

        // Cari sesi ruangan dengan tanggal yang sama
        $availableSesi = SesiRuangan::whereDate('tanggal', $jadwalUjian->tanggal)
            ->whereDoesntHave('jadwalUjians', function ($query) use ($jadwalUjian) {
                $query->where('jadwal_ujian.id', $jadwalUjian->id);
            })
            ->get();

        $assignedCount = 0;

        foreach ($availableSesi as $sesi) {
            // Check if sesi is suitable for this jadwal
            if ($this->isSesiSuitableForJadwal($sesi, $jadwalUjian)) {
                $jadwalUjian->sesiRuangans()->attach($sesi->id, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $assignedCount++;

                Log::info("Auto-assigned sesi {$sesi->kode_sesi} to jadwal {$jadwalUjian->kode_ujian}");
            }
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

        // Check if duration matches (allow some flexibility)
        $sesiDuration = $sesi->durasi; // in minutes
        $jadwalDuration = $jadwalUjian->durasi_menit;

        if ($sesiDuration && abs($sesiDuration - $jadwalDuration) > 15) {
            // Allow max 15 minutes difference
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
                'tanggal' => $sesi->tanggal,
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
            // Remove if tanggal doesn't match
            if ($sesi->tanggal->format('Y-m-d') !== $jadwalUjian->tanggal->format('Y-m-d')) {
                $jadwalUjian->sesiRuangans()->detach($sesi->id);
                $removedCount++;

                Log::info("Removed sesi {$sesi->kode_sesi} from jadwal {$jadwalUjian->kode_ujian} due to date mismatch");
            }

            // Remove if sesi is cancelled
            if ($sesi->status === 'dibatalkan') {
                $jadwalUjian->sesiRuangans()->detach($sesi->id);
                $removedCount++;

                Log::info("Removed cancelled sesi {$sesi->kode_sesi} from jadwal {$jadwalUjian->kode_ujian}");
            }
        }

        return $removedCount;
    }
}
