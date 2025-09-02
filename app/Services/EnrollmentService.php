<?php

namespace App\Services;

use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Siswa;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class EnrollmentService
{
    /**
     * Auto-enroll students to an exam session based on their class/group
     *
     * @param JadwalUjian $jadwalUjian
     * @param array $kelasIds
     * @param SesiRuangan|null $sesiRuangan
     * @return array
     */
    public function enrollStudentsByKelas(JadwalUjian $jadwalUjian, array $kelasIds, ?SesiRuangan $sesiRuangan = null)
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'already_enrolled' => 0,
            'errors' => []
        ];

        if (!$jadwalUjian->isOpen()) {
            $result['errors'][] = 'Jadwal ujian tidak dalam status open';
            return $result;
        }

        // If no specific session is provided, find one via berita acara
        if (!$sesiRuangan) {
            $sesiRuangan = SesiRuangan::whereHas('beritaAcaraUjian', function ($query) use ($jadwalUjian) {
                $query->where('jadwal_ujian_id', $jadwalUjian->id);
            })
                ->where('tanggal', '>=', Carbon::today())
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->first();

            if (!$sesiRuangan) {
                $result['errors'][] = 'Tidak ada sesi ruangan yang tersedia';
                return $result;
            }
        }

        // Get eligible students from the selected classes
        $siswaList = Siswa::whereIn('kelas_id', $kelasIds)
            ->where('status', 'active')
            ->get();

        if ($siswaList->isEmpty()) {
            $result['errors'][] = 'Tidak ada siswa yang ditemukan di kelas yang dipilih';
            return $result;
        }

        // Begin transaction to ensure data integrity
        DB::beginTransaction();
        try {
            foreach ($siswaList as $siswa) {
                // Check if student is already enrolled in this exam
                $existingEnrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
                    ->where('jadwal_ujian_id', $jadwalUjian->id)
                    ->first();

                if ($existingEnrollment) {
                    $result['already_enrolled']++;
                    continue;
                }

                // Check if room has capacity
                if ($sesiRuangan->remainingCapacity() <= 0) {
                    $result['failed']++;
                    $result['errors'][] = "Ruangan {$sesiRuangan->ruangan->nama} penuh untuk siswa {$siswa->nama} ({$siswa->nis})";
                    continue;
                }

                // Create enrollment
                $enrollment = new EnrollmentUjian([
                    'siswa_id' => $siswa->id,
                    'jadwal_ujian_id' => $jadwalUjian->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'status_enrollment' => 'enrolled',
                    'status_kehadiran' => 'belum_hadir',
                    'token_login' => $this->generateUniqueToken(),
                    'token_dibuat_pada' => now(),
                ]);

                $enrollment->save();

                // Create SesiRuanganSiswa record
                SesiRuanganSiswa::create([
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'siswa_id' => $siswa->id,
                    'status' => 'belum_hadir'
                ]);

                $result['success']++;
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in auto enrollment: ' . $e->getMessage());
            $result['errors'][] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Check if a session room has available capacity
     *
     * @param SesiRuangan $sesiRuangan
     * @return bool
     */
    protected function hasAvailableCapacity(SesiRuangan $sesiRuangan)
    {
        return $sesiRuangan->remainingCapacity() > 0;
    }

    /**
     * Enroll a student in an exam session
     * 
     * @param int $siswaId ID siswa
     * @param int $sesiRuanganId ID sesi ruangan
     * @param int $jadwalUjianId ID jadwal ujian
     * @param string|null $catatan Catatan opsional
     * @return EnrollmentUjian
     */
    public function enrollStudent(int $siswaId, int $sesiRuanganId, int $jadwalUjianId, ?string $catatan = null): EnrollmentUjian
    {
        // Check if student already enrolled for this exam
        $existingEnrollment = EnrollmentUjian::where('siswa_id', $siswaId)
            ->where('jadwal_ujian_id', $jadwalUjianId)
            ->first();

        if ($existingEnrollment) {
            throw new \Exception("Siswa sudah terdaftar di jadwal ujian ini");
        }

        // Check if sesi ruangan has capacity
        $sesiRuangan = SesiRuangan::findOrFail($sesiRuanganId);

        if ($sesiRuangan->remainingCapacity() <= 0) {
            throw new \Exception("Kapasitas ruangan sudah penuh");
        }

        // Create enrollment
        $enrollment = EnrollmentUjian::create([
            'siswa_id' => $siswaId,
            'sesi_ruangan_id' => $sesiRuanganId,
            'jadwal_ujian_id' => $jadwalUjianId,
            'status_enrollment' => 'enrolled',
            'status_kehadiran' => 'belum_hadir',
            'token_login' => $this->generateUniqueToken(),
            'token_dibuat_pada' => now(),
            'catatan' => $catatan,
        ]);

        // Create SesiRuanganSiswa record
        SesiRuanganSiswa::create([
            'sesi_ruangan_id' => $sesiRuanganId,
            'siswa_id' => $siswaId,
            'status' => 'belum_hadir'
        ]);

        return $enrollment;
    }

    /**
     * Bulk enroll students by class
     * 
     * @param int $sesiRuanganId ID sesi ruangan
     * @param int $jadwalUjianId ID jadwal ujian
     * @param array $kelasIds Array berisi ID kelas
     * @return int Jumlah siswa yang berhasil didaftarkan
     */
    public function bulkEnrollByClass(int $sesiRuanganId, int $jadwalUjianId, array $kelasIds): int
    {
        // Get students who aren't already enrolled
        $enrolledStudentIds = EnrollmentUjian::where('jadwal_ujian_id', $jadwalUjianId)
            ->pluck('siswa_id');

        $eligibleStudents = Siswa::whereIn('kelas_id', $kelasIds)
            ->whereNotIn('id', $enrolledStudentIds)
            ->get();

        $sesiRuangan = SesiRuangan::findOrFail($sesiRuanganId);
        $remainingCapacity = $sesiRuangan->remainingCapacity();

        if ($remainingCapacity < count($eligibleStudents)) {
            throw new \Exception("Kapasitas ruangan tidak cukup untuk {$eligibleStudents->count()} siswa. Tersisa {$remainingCapacity} kursi.");
        }

        $count = 0;
        DB::beginTransaction();

        try {
            foreach ($eligibleStudents as $student) {
                // Create enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $student->id,
                    'sesi_ruangan_id' => $sesiRuanganId,
                    'jadwal_ujian_id' => $jadwalUjianId,
                    'status_enrollment' => 'enrolled',
                    'status_kehadiran' => 'belum_hadir',
                    'token_login' => $this->generateUniqueToken(),
                    'token_dibuat_pada' => now(),
                ]);

                // Create SesiRuanganSiswa record
                SesiRuanganSiswa::create([
                    'sesi_ruangan_id' => $sesiRuanganId,
                    'siswa_id' => $student->id,
                    'status' => 'belum_hadir'
                ]);

                $count++;
            }

            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate token for student to start exam
     *
     * @param EnrollmentUjian $enrollment
     * @return string|false
     */
    public function generateToken(EnrollmentUjian $enrollment)
    {
        // Check if enrollment is valid
        if ($enrollment->status_enrollment !== 'enrolled') {
            return false;
        }

        // Check if session is active
        if (!$enrollment->sesiRuangan || $enrollment->sesiRuangan->status !== 'berlangsung') {
            return false;
        }

        // Generate token
        $token = $this->generateUniqueToken();
        $enrollment->update([
            'token_login' => $token,
            'token_dibuat_pada' => now(),
            'token_digunakan_pada' => null
        ]);

        return $token;
    }

    /**
     * Generate a new token for an enrollment
     */
    public function regenerateToken(EnrollmentUjian $enrollment): EnrollmentUjian
    {
        $enrollment->update([
            'token_login' => $this->generateUniqueToken(),
            'token_dibuat_pada' => now(),
            'token_digunakan_pada' => null
        ]);

        return $enrollment->refresh();
    }

    /**
     * Validate a token for starting an exam
     */
    public function validateToken(string $token): ?EnrollmentUjian
    {
        $enrollment = EnrollmentUjian::where('token_login', $token)
            ->where('status_enrollment', 'enrolled')
            ->first();

        if (!$enrollment) {
            return null;
        }

        // Check if token is expired (used more than 2 hours ago)
        if ($enrollment->token_digunakan_pada && $enrollment->token_digunakan_pada->addHours(2) < now()) {
            return null;
        }

        // Check if exam session is active
        $sesiRuangan = $enrollment->sesiRuangan;
        if (!$sesiRuangan || $sesiRuangan->status !== 'berlangsung') {
            return null;
        }

        return $enrollment;
    }

    /**
     * Update enrollment status
     */
    public function updateStatus(EnrollmentUjian $enrollment, string $status): EnrollmentUjian
    {
        $validStatuses = ['enrolled', 'completed', 'absent', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status tidak valid: {$status}");
        }

        $kehadiranMapping = [
            'completed' => 'hadir',
            'absent' => 'tidak_hadir'
        ];

        $enrollment->update([
            'status_enrollment' => $status,
            'status_kehadiran' => $kehadiranMapping[$status] ?? $enrollment->status_kehadiran
        ]);

        return $enrollment->refresh();
    }

    /**
     * Mark student as present in exam
     */
    public function markAttendance(EnrollmentUjian $enrollment, string $status = 'hadir'): EnrollmentUjian
    {
        $validStatuses = ['belum_hadir', 'hadir', 'tidak_hadir'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status kehadiran tidak valid: {$status}");
        }

        $enrollment->update([
            'status_kehadiran' => $status,
            'status_enrollment' => $status === 'tidak_hadir' ? 'absent' : $enrollment->status_enrollment
        ]);

        return $enrollment->refresh();
    }

    /**
     * Mark token as used when student logs in
     */
    public function useToken(EnrollmentUjian $enrollment): EnrollmentUjian
    {
        $enrollment->update([
            'token_digunakan_pada' => now(),
            'last_login_at' => now(),
            'status_kehadiran' => 'hadir'
        ]);

        return $enrollment->refresh();
    }

    /**
     * Find eligible students for enrollment
     */
    public function findEligibleStudents(int $jadwalUjianId, ?array $kelasIds = null)
    {
        // Get students who aren't already enrolled
        $enrolledStudentIds = EnrollmentUjian::where('jadwal_ujian_id', $jadwalUjianId)
            ->pluck('siswa_id');

        $query = Siswa::whereNotIn('id', $enrolledStudentIds);

        if ($kelasIds) {
            $query->whereIn('kelas_id', $kelasIds);
        }

        return $query->get();
    }

    /**
     * Generate a unique 6-character token for login
     */
    protected function generateUniqueToken(): string
    {
        do {
            $token = strtoupper(Str::random(6));
        } while (EnrollmentUjian::where('token_login', $token)->exists());

        return $token;
    }
}
