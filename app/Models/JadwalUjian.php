<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalUjian extends Model
{
    use HasFactory;

    protected $table = 'jadwal_ujian';

    protected $fillable = [
        'judul',
        'mapel_id',
        'tanggal',
        'durasi_menit',
        'deskripsi',
        'status',
        'tampilkan_hasil',
        'jumlah_soal',
        'kelas_target',
        'bank_soal_id',
        'created_by',
        'kode_ujian',
        'jenis_ujian',
        'acak_soal',
        'acak_jawaban'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'kelas_target' => 'array',
        'tampilkan_hasil' => 'boolean',
        'durasi_menit' => 'integer',
        'jumlah_soal' => 'integer',
        'acak_soal' => 'boolean',
        'acak_jawaban' => 'boolean',
        'status' => 'string' // Enum: draft, active, completed, cancelled
    ];

    // Relationships
    /**
     * Get the mapel associated with this jadwal ujian.
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Get the bank soal used in this jadwal ujian.
     */
    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class);
    }

    /**
     * Get the user who created this jadwal ujian.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all hasil ujian for this jadwal ujian.
     */
    public function hasilUjian()
    {
        return $this->hasMany(HasilUjian::class);
    }

    /**
     * Get all sesi ruangan for this jadwal ujian.
     */
    public function sesiRuangan()
    {
        return $this->hasMany(SesiRuangan::class);
    }

    /**
     * Get all enrollment ujian for this jadwal ujian.
     */
    public function enrollmentUjian()
    {
        return $this->hasMany(EnrollmentUjian::class);
    }

    /**
     * Get the berita acara for this jadwal ujian.
     */
    public function beritaAcara()
    {
        return $this->hasMany(BeritaAcaraUjian::class, 'sesi_ruangan_id');
    }

    /**
     * Get all kelas that are targeted by this jadwal.
     */
    public function kelasTarget()
    {
        return Kelas::whereIn('id', $this->kelas_target ?? [])->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', Carbon::today())
            ->where('status', 'active')
            ->orderBy('tanggal');
    }

    // Methods
    /**
     * Get a formatted display of the schedule.
     */
    public function getFullScheduleAttribute()
    {
        return $this->tanggal->format('d M Y') . ' • ' .
            $this->tanggal->format('H:i') . ' (' .
            $this->durasi_menit . ' menit)';
    }

    /**
     * Check if the exam schedule is active.
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the exam schedule is currently ongoing.
     * 
     * @return bool
     */
    public function isSedangBerlangsung()
    {
        $now = Carbon::now();
        $endTime = (clone $this->tanggal)->addMinutes($this->durasi_menit);

        return $now->gte($this->tanggal) &&
            $now->lte($endTime) &&
            $this->isActive();
    }

    /**
     * Check if the exam can be started.
     * 
     * @return bool
     */
    public function canStart()
    {
        $now = Carbon::now();
        return $now->gte($this->tanggal) && $this->isActive();
    }

    /**
     * Get all siswa eligible for this jadwal based on kelas_target
     */
    public function getSiswaEligible()
    {
        return Siswa::whereIn('kelas_id', $this->kelas_target ?? [])
            ->where(function ($query) {
                $query->where('status_pembayaran', 'Lunas')
                    ->orWhere('rekomendasi', 'ya');
            })
            ->get();
    }

    /**
     * Get exam progress statistics
     */
    public function getProgressStats()
    {
        $totalSesi = $this->sesiRuangan()->count();
        $selesaiSesi = $this->sesiRuangan()->where('status', 'selesai')->count();
        $sedangBerjalanSesi = $this->sesiRuangan()->where('status', 'sedang_berjalan')->count();
        $belumMulaiSesi = $this->sesiRuangan()->where('status', 'belum_mulai')->count();

        return [
            'total_sesi' => $totalSesi,
            'selesai' => $selesaiSesi,
            'sedang_berjalan' => $sedangBerjalanSesi,
            'belum_mulai' => $belumMulaiSesi,
            'persentase_selesai' => $totalSesi > 0 ? round(($selesaiSesi / $totalSesi) * 100, 2) : 0
        ];
    }

    /**
     * Get total participants across all sessions
     */
    public function getTotalParticipants()
    {
        return SesiRuanganSiswa::whereHas('sesiRuangan', function ($query) {
            $query->where('jadwal_ujian_id', $this->id);
        })->count();
    }

    /**
     * Get participation statistics
     */
    public function getParticipationStats()
    {
        $totalParticipants = $this->getTotalParticipants();
        $hadirCount = SesiRuanganSiswa::whereHas('sesiRuangan', function ($query) {
            $query->where('jadwal_ujian_id', $this->id);
        })->where('status', 'hadir')->count();

        $tidakHadirCount = SesiRuanganSiswa::whereHas('sesiRuangan', function ($query) {
            $query->where('jadwal_ujian_id', $this->id);
        })->where('status', 'tidak_hadir')->count();

        return [
            'total_participants' => $totalParticipants,
            'hadir' => $hadirCount,
            'tidak_hadir' => $tidakHadirCount,
            'persentase_kehadiran' => $totalParticipants > 0 ? round(($hadirCount / $totalParticipants) * 100, 2) : 0
        ];
    }

    /**
     * Get status badge information
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => ['text' => 'Draft', 'class' => 'bg-gray-100 text-gray-800'],
            'active' => ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800'],
            'completed' => ['text' => 'Selesai', 'class' => 'bg-blue-100 text-blue-800'],
            'cancelled' => ['text' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-800'],
        ];

        return $badges[$this->status] ?? ['text' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get time remaining until exam starts
     */
    public function getTimeUntilStart()
    {
        if ($this->tanggal->isPast()) {
            return null;
        }

        $diff = now()->diff($this->tanggal);

        if ($diff->days > 0) {
            return $diff->days . ' hari';
        } elseif ($diff->h > 0) {
            return $diff->h . ' jam ' . $diff->i . ' menit';
        } else {
            return $diff->i . ' menit';
        }
    }

    /**
     * Get current exam status for coordinator dashboard
     */
    public function getCurrentExamStatus()
    {
        $now = now();
        $endTime = (clone $this->tanggal)->addMinutes($this->durasi_menit);

        if ($now->lt($this->tanggal)) {
            return 'belum_mulai';
        } elseif ($now->between($this->tanggal, $endTime)) {
            return 'sedang_berjalan';
        } else {
            return 'selesai';
        }
    }

    /**
     * Check if exam needs supervisor assignment
     */
    public function needsSupervisorAssignment()
    {
        $unassignedSessions = $this->sesiRuangan()
            ->whereNull('guru_id')
            ->where('status', 'belum_mulai')
            ->count();

        return $unassignedSessions > 0;
    }

    /**
     * Get the start time of the exam
     * This is a computed property since there's no waktu_mulai column in the table
     */
    public function getWaktuMulaiAttribute()
    {
        return $this->tanggal;
    }

    /**
     * Get the end time of the exam
     * This is computed by adding the duration to the start time
     */
    public function getWaktuSelesaiAttribute()
    {
        if (!$this->tanggal) {
            return null;
        }
        
        return (clone $this->tanggal)->addMinutes($this->durasi_menit ?? 0);
    }
}
