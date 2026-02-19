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
        'acak_jawaban',
        'auto_assign_sesi',
        'scheduling_mode',
        'timezone',
        'aktifkan_auto_logout'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'kelas_target' => 'array',
        'tampilkan_hasil' => 'boolean',
        'durasi_menit' => 'integer',
        'jumlah_soal' => 'integer',
        'acak_soal' => 'boolean',
        'acak_jawaban' => 'boolean',
        'auto_assign_sesi' => 'boolean',
        'aktifkan_auto_logout' => 'boolean',
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
     * Get all sesi ruangan for this jadwal ujian (many-to-many).
     */
    public function sesiRuangans()
    {
        return $this->belongsToMany(SesiRuangan::class, 'jadwal_ujian_sesi_ruangan')
            ->withPivot('id','pengawas_id')
            ->using(JadwalUjianSesiRuangan::class)
            ->withTimestamps();
    }

    /**
     * Keep old method for backward compatibility.
     */
    public function sesiRuangan()
    {
        return $this->sesiRuangans();
    }

    /**
     * Get pengawas assignments for this jadwal ujian.
     */
    public function pengawasAssignments()
    {
        return JadwalUjianSesiRuangan::where('jadwal_ujian_id', $this->id)
            ->whereNotNull('pengawas_id')
            ->get();
    }


    public function pelanggaranUjians()
    {
        return $this->hasMany(PelanggaranUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Get all unique pengawas assigned to this jadwal ujian.
     */
    public function pengawas()
    {
        $pengawasIds = JadwalUjianSesiRuangan::where('jadwal_ujian_id', $this->id)
            ->whereNotNull('pengawas_id')
            ->pluck('pengawas_id')
            ->unique();

        return Guru::whereIn('id', $pengawasIds)->get();
    }

    /**
     * Assign a pengawas to a specific sesi ruangan for this jadwal ujian.
     */
    public function assignPengawas($sesiRuanganId, $pengawasId)
    {
        return JadwalUjianSesiRuangan::updateOrCreate(
            [
                'jadwal_ujian_id' => $this->id,
                'sesi_ruangan_id' => $sesiRuanganId
            ],
            ['pengawas_id' => $pengawasId]
        );
    }

    /**
     * Remove a pengawas assignment from a specific sesi ruangan for this jadwal ujian.
     */
    public function removePengawas($sesiRuanganId)
    {
        return JadwalUjianSesiRuangan::where('jadwal_ujian_id', $this->id)
            ->where('sesi_ruangan_id', $sesiRuanganId)
            ->update(['pengawas_id' => null]);
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
     * Check if this jadwal applies to the given jurusan
     * If mapel's jurusan is null, it applies to all jurusan
     */
    public function appliesToJurusan($jurusan)
    {
        if (!$this->mapel) {
            return false;
        }

        // If mapel's jurusan is null, it applies to all jurusan
        if ($this->mapel->jurusan === null) {
            return true;
        }

        // Otherwise, check for exact match or 'UMUM'
        return $this->mapel->jurusan === $jurusan || $this->mapel->jurusan === 'UMUM';
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
        })->where('status_kehadiran', 'hadir')->count();

        $tidakHadirCount = SesiRuanganSiswa::whereHas('sesiRuangan', function ($query) {
            $query->where('jadwal_ujian_id', $this->id);
        })->whereIn('status_kehadiran', ['tidak_hadir', 'sakit', 'izin'])->count();

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
            'aktif' => ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800'],
            'nonaktif' => ['text' => 'Non-Aktif', 'class' => 'bg-yellow-100 text-yellow-800'],
            'selesai' => ['text' => 'Selesai', 'class' => 'bg-blue-100 text-blue-800'],
            // Legacy compatibility
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
     * For flexible scheduling, get the earliest start time from sesi ruangan
     */
    public function getWaktuMulaiAttribute()
    {
        if ($this->scheduling_mode === 'flexible') {
            $earliestSesi = $this->sesiRuangans()
                ->orderBy('waktu_mulai', 'asc')
                ->first();

            if ($earliestSesi) {
                return Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $earliestSesi->waktu_mulai);
            }
        }

        return $this->tanggal;
    }

    /**
     * Get the end time of the exam
     * For flexible scheduling, get the latest end time from sesi ruangan
     */
    public function getWaktuSelesaiAttribute()
    {
        if (!$this->tanggal) {
            return null;
        }

        if ($this->scheduling_mode === 'flexible') {
            $latestSesi = $this->sesiRuangans()
                ->orderBy('waktu_selesai', 'desc')
                ->first();

            if ($latestSesi) {
                return Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $latestSesi->waktu_selesai);
            }
        }

        return (clone $this->tanggal)->addMinutes($this->durasi_menit ?? 0);
    }

    /**
     * Get all possible time slots for this jadwal ujian
     */
    public function getTimeSlots()
    {
        if ($this->scheduling_mode === 'fixed') {
            return [
                [
                    'waktu_mulai' => $this->tanggal,
                    'waktu_selesai' => $this->waktu_selesai,
                    'durasi_menit' => $this->durasi_menit,
                    'source' => 'fixed'
                ]
            ];
        }

        $timeSlots = [];
        foreach ($this->sesiRuangans as $sesi) {
            $timeSlots[] = [
                'sesi_id' => $sesi->id,
                'sesi_nama' => $sesi->nama_sesi,
                'ruangan' => $sesi->ruangan->nama_ruangan ?? 'Unknown',
                'waktu_mulai' => Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $sesi->waktu_mulai),
                'waktu_selesai' => Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $sesi->waktu_selesai),
                'durasi_menit' => $sesi->durasi,
                'source' => 'sesi'
            ];
        }

        return $timeSlots;
    }

    /**
     * Check if this jadwal uses flexible scheduling
     */
    public function isFlexibleScheduling()
    {
        return $this->scheduling_mode === 'flexible';
    }

    /**
     * Get total capacity across all sesi ruangan
     */
    public function getTotalCapacity()
    {
        if ($this->scheduling_mode === 'fixed') {
            return 0; // No specific capacity for fixed scheduling
        }

        return $this->sesiRuangans->sum(function ($sesi) {
            return $sesi->ruangan->kapasitas ?? 0;
        });
    }

    /**
     * Get schedule summary for display
     */
    public function getScheduleSummary()
    {
        if ($this->scheduling_mode === 'fixed') {
            return [
                'mode' => 'fixed',
                'tanggal' => $this->tanggal->format('d M Y'),
                'waktu' => $this->tanggal->format('H:i') . ' - ' . $this->waktu_selesai->format('H:i'),
                'durasi' => $this->durasi_menit . ' menit',
                'sesi_count' => 0
            ];
        }

        $sesiCount = $this->sesiRuangans->count();

        if ($sesiCount === 0) {
            return [
                'mode' => 'flexible',
                'tanggal' => $this->tanggal->format('d M Y'),
                'waktu' => 'Belum ada sesi terkait',
                'durasi' => $this->durasi_menit . ' menit (target)',
                'sesi_count' => 0
            ];
        }

        $earliestStart = $this->sesiRuangans->min('waktu_mulai');
        $latestEnd = $this->sesiRuangans->max('waktu_selesai');

        return [
            'mode' => 'flexible',
            'tanggal' => $this->tanggal->format('d M Y'),
            'waktu' => $earliestStart . ' - ' . $latestEnd,
            'durasi' => $this->durasi_menit . ' menit per sesi',
            'sesi_count' => $sesiCount
        ];
    }

    /**
     * Get questions/soal for this jadwal ujian
     */
    public function soals()
    {
        return $this->hasMany(SoalUjian::class);
    }
}
