<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Siswa extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $table = 'siswa';
    protected $guard_name = 'siswa';

    protected $fillable = [
        'nis',
        'idyayasan',
        'nama',
        'email',
        'password',
        'kelas_id',
        'status_pembayaran',
        'rekomendasi',
        'catatan_rekomendasi',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'status_pembayaran' => 'string', // Enum: Lunas, Belum Lunas
        'rekomendasi' => 'string', // Enum: ya, tidak
    ];

    // Override guard name untuk Spatie Permission
    public function getDefaultGuardName(): string
    {
        return 'siswa';
    }

    // Auto-generate email saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($siswa) {
            // Only auto-generate email if not provided
            if (empty($siswa->email) && !empty($siswa->idyayasan)) {
                $siswa->email = $siswa->idyayasan . '@smkdata.sch.id';
            }

            // Only set default password if not provided and not already hashed
            if (empty($siswa->password)) {
                $siswa->password = 'password'; // Will be hashed by cast
            }

            // Set default rekomendasi if not provided
            if (empty($siswa->rekomendasi)) {
                $siswa->rekomendasi = 'tidak';
            }
        });
    }

    // Status pembayaran options
    public static function getStatusPembayaranOptions()
    {
        return [
            'Belum Lunas' => 'Belum Lunas',
            'Lunas' => 'Lunas'
        ];
    }

    // Rekomendasi options
    public static function getRekomendasiOptions()
    {
        return [
            'tidak' => 'Tidak',
            'ya' => 'Ya'
        ];
    }

    // Helper methods
    public function canTakeExam()
    {
        return $this->status_pembayaran === 'Lunas';
    }

    public function isRecommended()
    {
        return $this->rekomendasi === 'ya';
    }

    public function isActive()
    {
        return $this->hasRole('siswa', 'siswa');
    }

    // Get kelas options for forms
    public static function getKelasOptions()
    {
        return [
            'X IPA 1',
            'X IPA 2',
            'X IPA 3',
            'X IPS 1',
            'X IPS 2',
            'X IPS 3',
            'XI IPA 1',
            'XI IPA 2',
            'XI IPA 3',
            'XI IPS 1',
            'XI IPS 2',
            'XI IPS 3',
            'XII IPA 1',
            'XII IPA 2',
            'XII IPA 3',
            'XII IPS 1',
            'XII IPS 2',
            'XII IPS 3',
        ];
    }

    // Relationships
    /**
     * Get the kelas that this siswa belongs to.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }

    /**
     * Get all sesi ruangan that this siswa is enrolled in.
     */
    public function sesiRuangan()
    {
        return $this->belongsToMany(SesiRuangan::class, 'sesi_ruangan_siswa', 'siswa_id', 'sesi_ruangan_id')
            ->withPivot('status', 'waktu_masuk', 'waktu_keluar', 'last_activity', 'durasi_aktif', 'catatan')
            ->withTimestamps();
    }

    /**
     * Get all sesi ruangan siswa records for this siswa.
     */
    public function sesiRuanganSiswa()
    {
        return $this->hasMany(SesiRuanganSiswa::class);
    }

    /**
     * Get all enrollment ujian records for this siswa.
     */
    public function enrollmentUjian()
    {
        return $this->hasMany(EnrollmentUjian::class);
    }

    /**
     * Get all hasil ujian records for this siswa.
     */
    public function hasilUjian()
    {
        return $this->hasMany(HasilUjian::class);
    }

    // Scope for filtering
    /**
     * Scope a query to only include active students.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAktif($query)
    {
        // Consider a student active if they have the 'siswa' role
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'siswa');
        });
    }

    /**
     * Scope a query to filter by kelas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $kelas_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKelas($query, $kelas_id)
    {
        return $query->where('kelas_id', $kelas_id);
    }

    /**
     * Scope a query to filter by payment status.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pembayaran', $status);
    }

    /**
     * Scope a query to filter by recommendation status.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $rekomendasi
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRekomendasi($query, $rekomendasi)
    {
        return $query->where('rekomendasi', $rekomendasi);
    }

    /**
     * Scope a query to only include students who have paid in full.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLunas($query)
    {
        return $query->where('status_pembayaran', 'Lunas');
    }

    /**
     * Scope a query to only include students who haven't paid in full.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('status_pembayaran', 'Belum Lunas');
    }

    /**
     * Scope a query to only include recommended students.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecommended($query)
    {
        return $query->where('rekomendasi', 'ya');
    }

    /**
     * Scope a query to only include not recommended students.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotRecommended($query)
    {
        return $query->where('rekomendasi', 'tidak');
    }

    /**
     * Get current exam session for this student
     */
    public function getCurrentSession()
    {
        $now = now();
        return $this->sesiRuanganSiswa()
            ->whereHas('sesiRuangan', function ($query) use ($now) {
                $query->where('tanggal', $now->toDateString())
                    ->where('waktu_mulai', '<=', $now->format('H:i:s'))
                    ->where('waktu_selesai', '>=', $now->format('H:i:s'));
            })
            ->first();
    }

    /**
     * Get exam history for this student
     */
    public function getExamHistory()
    {
        return $this->sesiRuanganSiswa()
            ->whereHas('sesiRuangan', function ($query) {
                $query->where('status', 'selesai');
            })
            ->with(['sesiRuangan.jadwalUjian.mapel', 'sesiRuangan.ruangan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get upcoming exams for this student
     */
    public function getUpcomingExams()
    {
        return $this->sesiRuanganSiswa()
            ->whereHas('sesiRuangan', function ($query) {
                $query->where('status', 'belum_mulai')
                    ->where(function ($q) {
                        $q->where('tanggal', '>', today())
                            ->orWhere(function ($subQ) {
                                $subQ->where('tanggal', today())
                                    ->where('waktu_mulai', '>', now()->format('H:i:s'));
                            });
                    });
            })
            ->with(['sesiRuangan.jadwalUjian.mapel', 'sesiRuangan.ruangan'])
            ->get();
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats()
    {
        $total = $this->sesiRuanganSiswa()->count();
        $hadir = $this->sesiRuanganSiswa()->where('status_kehadiran', 'hadir')->count();
        $tidak_hadir = $this->sesiRuanganSiswa()->where('status_kehadiran', 'tidak_hadir')->count();

        return [
            'total' => $total,
            'hadir' => $hadir,
            'tidak_hadir' => $tidak_hadir,
            'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get status badge for payment
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'Lunas' => ['text' => 'Lunas', 'class' => 'bg-green-100 text-green-800'],
            'Belum Lunas' => ['text' => 'Belum Lunas', 'class' => 'bg-red-100 text-red-800'],
        ];

        return $badges[$this->status_pembayaran] ?? ['text' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get recommendation badge
     */
    public function getRekomendasiBadgeAttribute()
    {
        $badges = [
            'ya' => ['text' => 'Ya', 'class' => 'bg-blue-100 text-blue-800'],
            'tidak' => ['text' => 'Tidak', 'class' => 'bg-gray-100 text-gray-800'],
        ];

        return $badges[$this->rekomendasi] ?? ['text' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Check if student is currently in exam session
     */
    public function isInExam()
    {
        return $this->getCurrentSession() !== null;
    }

    /**
     * Get student full info for coordinator dashboard
     */
    public function getFullInfoAttribute()
    {
        return [
            'nis' => $this->nis,
            'nama' => $this->nama,
            'kelas' => $this->kelas->nama ?? 'N/A',
            'status_pembayaran' => $this->status_pembayaran,
            'rekomendasi' => $this->rekomendasi,
            'attendance_stats' => $this->getAttendanceStats(),
            'current_session' => $this->getCurrentSession(),
        ];
    }

    // Static method to generate email
    public static function generateEmail($idyayasan)
    {
        return $idyayasan . '@smkdata.sch.id';
    }
    public function getRedirectRoute()
    {
        return 'siswa.dashboard';
    }
}
