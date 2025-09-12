<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EnrollmentUjian extends Model
{
    use HasFactory;

    protected $table = 'enrollment_ujian';

    protected $fillable = [
        'sesi_ruangan_id',
        'jadwal_ujian_id',
        'siswa_id',
        'status_enrollment',
        'waktu_mulai_ujian',
        'waktu_selesai_ujian',
        'last_login_at',
        'last_logout_at',
        'catatan'
    ];

    protected $casts = [
        'waktu_mulai_ujian' => 'datetime',
        'waktu_selesai_ujian' => 'datetime',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'status_enrollment' => 'string', // enum('enrolled','completed','cancelled')
    ];

    /**
     * Get the session room for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sesiRuangan(): BelongsTo
    {
        return $this->belongsTo(SesiRuangan::class, 'sesi_ruangan_id');
    }

    /**
     * Get the student for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the exam result for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasilUjian(): HasOne
    {
        return $this->hasOne(HasilUjian::class, 'enrollment_ujian_id');
    }

    /**
     * Get the exam schedule for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    /**
     * Get the attendance record for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sesiRuanganSiswa(): HasOne
    {
        return $this->hasOne(SesiRuanganSiswa::class, 'sesi_ruangan_id', 'sesi_ruangan_id')
            ->where('siswa_id', $this->siswa_id);
    }

    /**
     * Get the attendance status from sesi_ruangan_siswa table.
     *
     * @return string|null
     */
    public function getStatusKehadiranAttribute()
    {
        return $this->sesiRuanganSiswa?->status_kehadiran ?? 'belum_hadir';
    }

    /**
     * Mark student as started and update attendance status.
     *
     * @return void
     */
    public function startExam()
    {
        $this->waktu_mulai_ujian = now();
        $this->last_login_at = now();
        $this->status_enrollment = 'active';
        $this->save();

        // Update the related sesi_ruangan_siswa record
        if ($this->sesi_ruangan_id) {
            $sesiRuanganSiswa = SesiRuanganSiswa::where('sesi_ruangan_id', $this->sesi_ruangan_id)
                ->where('siswa_id', $this->siswa_id)
                ->first();

            if ($sesiRuanganSiswa) {
                $sesiRuanganSiswa->status_kehadiran = 'hadir';
                $sesiRuanganSiswa->save();
            }
        }
    }

    /**
     * Check if enrollment is active and ready for testing.
     *
     * @return bool
     */
    public function isActive()
    {
        // Check if session room is active
        if (!$this->sesiRuangan || !$this->sesiRuangan->isActive()) {
            return false;
        }

        // Check if student is eligible
        if (!$this->siswa) {
            return false;
        }

        // Check enrollment status
        return $this->status_enrollment === 'enrolled' || $this->status_enrollment === 'active';
    }

    /**
     * Check if this student has completed the exam.
     *
     * @return bool
     */
    public function hasCompletedExam()
    {
        return $this->status_enrollment === 'completed' && $this->hasilUjian && $this->waktu_selesai_ujian;
    }

    /**
     * Set this enrollment as complete.
     *
     * @return void
     */
    public function completeExam()
    {
        $this->waktu_selesai_ujian = now();
        $this->last_logout_at = now();
        $this->status_enrollment = 'completed';
        $this->save();
    }

    /**
     * Log student logout.
     *
     * @return void
     */
    public function logLogout()
    {
        $this->last_logout_at = now();
        $this->save();

        // Update the related sesi_ruangan_siswa record if needed
        if ($this->status_enrollment === 'completed' && $this->sesi_ruangan_id) {
            $sesiRuanganSiswa = SesiRuanganSiswa::where('sesi_ruangan_id', $this->sesi_ruangan_id)
                ->where('siswa_id', $this->siswa_id)
                ->first();

            if ($sesiRuanganSiswa) {
                $sesiRuanganSiswa->status_kehadiran = 'hadir'; // Completed means attended
                $sesiRuanganSiswa->save();
            }
        }
    }

    /**
     * Scope to filter enrollments by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_enrollment', $status);
    }
}
