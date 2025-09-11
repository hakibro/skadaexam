<?php

namespace App\Observers;

use App\Models\SesiRuanganSiswa;
use App\Models\JadwalUjian;
use App\Models\EnrollmentUjian;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;

class SesiRuanganSiswaObserver
{
    /**
     * Handle the SesiRuanganSiswa "created" event.
     */
    public function created(SesiRuanganSiswa $sesiRuanganSiswa): void
    {
        try {
            $sesiRuanganId = $sesiRuanganSiswa->sesi_ruangan_id;
            $siswaId = $sesiRuanganSiswa->siswa_id;

            // Get the session
            $sesiRuangan = $sesiRuanganSiswa->sesiRuangan;
            if (!$sesiRuangan) {
                return;
            }

            // Get student
            $siswa = $sesiRuanganSiswa->siswa;
            if (!$siswa || !$siswa->kelas) {
                return;
            }

            $kelasJurusan = $siswa->kelas->jurusan;
            $kelasId = $siswa->kelas_id;

            // Find matching jadwal ujian based on jurusan compatibility
            $matchingJadwals = JadwalUjian::whereHas('mapel', function ($query) use ($kelasJurusan) {
                $query->where('jurusan', $kelasJurusan)
                    ->orWhere('jurusan', 'UMUM')
                    ->orWhereNull('jurusan'); // If jurusan is null, it applies to all
            })
                ->where('status', 'aktif')
                ->whereJsonContains('kelas_target', $kelasId)
                ->get();

            if ($matchingJadwals->isEmpty()) {
                return;
            }

            foreach ($matchingJadwals as $jadwal) {
                // Check if the jadwal is attached to this sesi
                if (!$sesiRuangan->jadwalUjians()->where('jadwal_ujian_id', $jadwal->id)->exists()) {
                    $sesiRuangan->jadwalUjians()->attach($jadwal->id);
                    Log::info("Observer: Attached jadwal ujian {$jadwal->id} to sesi ruangan {$sesiRuangan->id}");
                }

                // Check if enrollment exists
                $existingEnrollment = EnrollmentUjian::where('jadwal_ujian_id', $jadwal->id)
                    ->where('sesi_ruangan_id', $sesiRuanganId)
                    ->where('siswa_id', $siswaId)
                    ->first();

                if (!$existingEnrollment) {
                    // Create new enrollment
                    $enrollment = new EnrollmentUjian([
                        'sesi_ruangan_id' => $sesiRuanganId,
                        'jadwal_ujian_id' => $jadwal->id,
                        'siswa_id' => $siswaId,
                        'status_enrollment' => 'enrolled',
                        'catatan' => 'Auto-enrolled by system'
                    ]);
                    $enrollment->save();

                    Log::info("Observer: Auto-enrolled student {$siswaId} in jadwal {$jadwal->id} for sesi {$sesiRuanganId}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in SesiRuanganSiswaObserver@created: ' . $e->getMessage());
        }
    }

    /**
     * Handle the SesiRuanganSiswa "deleted" event.
     */
    public function deleted(SesiRuanganSiswa $sesiRuanganSiswa): void
    {
        try {
            $sesiRuanganId = $sesiRuanganSiswa->sesi_ruangan_id;
            $siswaId = $sesiRuanganSiswa->siswa_id;

            // Delete enrollments that aren't completed and don't have exam results
            $enrollments = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuanganId)
                ->where('siswa_id', $siswaId)
                ->where(function ($query) {
                    $query->where('status_enrollment', '!=', 'completed')
                        ->orWhereNull('status_enrollment');
                })
                ->whereDoesntHave('hasilUjian')
                ->get();

            foreach ($enrollments as $enrollment) {
                $enrollment->delete();
                Log::info("Observer: Deleted enrollment ID {$enrollment->id} for student {$siswaId} in sesi {$sesiRuanganId}");
            }
        } catch (\Exception $e) {
            Log::error('Error in SesiRuanganSiswaObserver@deleted: ' . $e->getMessage());
        }
    }
}
