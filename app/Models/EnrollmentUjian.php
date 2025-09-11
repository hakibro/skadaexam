<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class EnrollmentUjian extends Model
{
    use HasFactory;

    protected $table = 'enrollment_ujian';

    protected $fillable = [
        'sesi_ruangan_id',
        'jadwal_ujian_id',
        'siswa_id',
        'status_enrollment',
        'token_login',
        'token_dibuat_pada',
        'token_digunakan_pada',
        'waktu_mulai_ujian',
        'waktu_selesai_ujian',
        'last_login_at',
        'last_logout_at',
        'catatan'
    ];

    protected $casts = [
        'token_dibuat_pada' => 'datetime',
        'token_digunakan_pada' => 'datetime',
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
     * Generate a login token for this enrollment.
     *
     * @return string
     */
    public function generateToken()
    {
        $token = strtoupper(Str::random(6));

        while (self::where('token_login', $token)->exists()) {
            $token = strtoupper(Str::random(6));
        }

        $this->token_login = $token;
        $this->token_dibuat_pada = now();
        $this->token_digunakan_pada = null; // Reset if previously used
        $this->save();

        return $this->token_login;
    }

    /**
     * Validate a token - returns true if valid, false if invalid or already used.
     *
     * @param string $token
     * @return bool
     */
    public function validateToken($token)
    {
        // If token doesn't match
        if ($this->token_login !== $token) {
            return false;
        }

        // If token is already used and more than 2 hours old
        if ($this->token_digunakan_pada && $this->token_digunakan_pada->addHours(2) < now()) {
            return false;
        }

        return true;
    }

    /**
     * Mark token as used and update attendance status.
     *
     * @return void
     */
    public function useToken()
    {
        $this->token_digunakan_pada = now();
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
                $sesiRuanganSiswa->status = 'hadir';
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
     * Check if token is expired (created more than 2 hours ago and used).
     *
     * @return bool
     */
    public function isTokenExpired()
    {
        if (!$this->token_login || !$this->token_dibuat_pada) {
            return true;
        }

        if ($this->token_digunakan_pada && $this->token_digunakan_pada->addHours(2) < now()) {
            return true;
        }

        return false;
    }

    /**
     * Get token value for this enrollment
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token_login;
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
                $sesiRuanganSiswa->status = 'logout';
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
