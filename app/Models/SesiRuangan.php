<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\UjianService;
use App\Models\EnrollmentUjian;
use App\Models\HasilUjian;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SesiRuangan extends Model
{
    use HasFactory;

    // The table doesn't have timestamps columns
    public $timestamps = false;

    protected $table = 'sesi_ruangan';

    protected $fillable = [
        'kode_sesi',
        'nama_sesi',
        'waktu_mulai',
        'waktu_selesai',
        'token_ujian',
        'token_expired_at',
        'status',
        'pengaturan',
        'ruangan_id'
    ];

    protected $casts = [
        'token_expired_at' => 'datetime',
        'pengaturan' => 'array',
        'status' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sesi) {
            if (empty($sesi->kode_sesi)) {
                // Get the ruangan code
                $ruangan = Ruangan::find($sesi->ruangan_id);
                $kodeRuangan = $ruangan ? $ruangan->kode_ruangan : 'R';

                // Generate a unique session code
                $uniqueCode = strtoupper(Str::random(6));

                // Format: kode_ruangan-kode_sesi
                $sesi->kode_sesi = $kodeRuangan . '-' . $uniqueCode;
            }
        });
    }

    // Relationships
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * @deprecated This method is kept for backward compatibility.
     * Use getPengawasForJadwal() instead with a specific jadwal_ujian_id.
     */
    public function pengawas()
    {
        // Return a hasOne relationship with Guru model
        // This will always be empty now that the column is removed
        // But at least it returns a relationship instance instead of null
        return $this->belongsTo(Guru::class, 'koordinator_id')->whereNull('koordinator_id');
    }

    /**
     * @deprecated This method is kept for backward compatibility.
     * Use getPengawasForJadwal() instead with a specific jadwal_ujian_id.
     */
    public function guru()
    {
        // Return a hasOne relationship with Guru model
        // This will always be empty now that the column is removed
        // But at least it returns a relationship instance instead of null
        return $this->belongsTo(Guru::class, 'koordinator_id')->whereNull('koordinator_id');
    }

    public function koordinator()
    {
        return $this->belongsTo(Guru::class, 'koordinator_id');
    }

    // Now we have a many-to-many relationship with jadwal_ujian
    public function jadwalUjians()
    {
        return $this->belongsToMany(JadwalUjian::class, 'jadwal_ujian_sesi_ruangan')
            ->withPivot('pengawas_id')
            ->using(JadwalUjianSesiRuangan::class)
            ->withTimestamps();
    }

    // Keep the old method for backward compatibility
    public function jadwalUjian()
    {
        // Return a HasMany relation for backward compatibility
        return $this->belongsToMany(JadwalUjian::class, 'jadwal_ujian_sesi_ruangan')
            ->withPivot('pengawas_id')
            ->using(JadwalUjianSesiRuangan::class)
            ->withTimestamps();
    }

    /**
     * Get pengawas for a specific jadwal
     * 
     * @param int $jadwalUjianId The jadwal ujian ID
     * @return Guru|null The assigned pengawas or null if not assigned
     */
    public function getPengawasForJadwal($jadwalUjianId)
    {
        $pivot = JadwalUjianSesiRuangan::where('jadwal_ujian_id', $jadwalUjianId)
            ->where('sesi_ruangan_id', $this->id)
            ->first();

        if ($pivot && $pivot->pengawas_id) {
            return Guru::find($pivot->pengawas_id);
        }

        return null;
    }

    /**
     * Get the first assigned pengawas for backward compatibility with views
     * 
     * @return object|null Object with nama property or null
     */
    public function getFirstPengawasAttribute()
    {
        $assignedPengawas = $this->getAllAssignedPengawas();
        if (!empty($assignedPengawas)) {
            return (object)['nama' => array_values($assignedPengawas)[0]];
        }
        return null;
    }

    /**
     * Get all unique pengawas assigned to this session
     * 
     * @return array Array of pengawas names indexed by ID
     */
    public function getAllAssignedPengawas()
    {
        $pengawasList = [];
        foreach ($this->jadwalUjians as $jadwal) {
            $pengawas = $this->getPengawasForJadwal($jadwal->id);
            if ($pengawas) {
                $pengawasList[$pengawas->id] = $pengawas->nama;
            }
        }
        return $pengawasList;
    }

    /**
     * Get pengawas names as comma-separated string
     * 
     * @return string
     */
    public function getPengawasNamesAttribute()
    {
        $names = array_values($this->getAllAssignedPengawas());
        return empty($names) ? 'Belum ditentukan' : implode(', ', $names);
    }

    public function sesiRuanganSiswa()
    {
        return $this->hasMany(SesiRuanganSiswa::class);
    }

    // Many-to-Many relationship with Siswa through SesiRuanganSiswa
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'sesi_ruangan_siswa')
            ->withPivot(['status_kehadiran', 'keterangan'])
            ->withTimestamps();
    }

    // With the new migration, there's a direct link from berita_acara_ujian to sesi_ruangan
    public function beritaAcaraUjian()
    {
        return $this->hasOne(BeritaAcaraUjian::class, 'sesi_ruangan_id');
    }

    // Legacy alias for berita acara
    public function beritaAcara()
    {
        return $this->beritaAcaraUjian();
    }

    public function siswaHadir()
    {
        return $this->sesiRuanganSiswa()->where('status_kehadiran', 'hadir');
    }

    public function siswaTidakHadir()
    {
        return $this->sesiRuanganSiswa()->where('status_kehadiran', 'tidak_hadir');
    }

    public function siswaLogout()
    {
        return $this->sesiRuanganSiswa()->where('status_kehadiran', 'logout');
    }

    // Status helpers
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'belum_mulai' => [
                'text' => 'Belum Mulai',
                'class' => 'bg-gray-100 text-gray-800'
            ],
            'berlangsung' => [
                'text' => 'Berlangsung',
                'class' => 'bg-green-100 text-green-800'
            ],
            'selesai' => [
                'text' => 'Selesai',
                'class' => 'bg-blue-100 text-blue-800'
            ],
            'dibatalkan' => [
                'text' => 'Dibatalkan',
                'class' => 'bg-red-100 text-red-800'
            ]
        ];

        return $statuses[$this->status] ?? $statuses['belum_mulai'];
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status_label['class'];
    }

    public function getStatusBorderClassAttribute()
    {
        $classes = [
            'belum_mulai' => 'border-gray-500',
            'berlangsung' => 'border-green-500',
            'selesai' => 'border-blue-500',
            'dibatalkan' => 'border-red-500'
        ];

        return $classes[$this->status] ?? $classes['belum_mulai'];
    }

    public function getDurasiAttribute()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return 0;
        }

        $start = Carbon::parse($this->waktu_mulai);
        $end = Carbon::parse($this->waktu_selesai);

        return $end->diffInMinutes($start);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->status !== 'berlangsung') {
            return $this->status === 'selesai' ? 100 : 0;
        }

        // Get the date from the first jadwal ujian attached to this session
        $jadwalUjian = $this->jadwalUjians()->first();
        if (!$jadwalUjian) {
            return 0; // Can't calculate without a date
        }

        $tanggalStr = $jadwalUjian->tanggal->format('Y-m-d');
        $now = now();
        $start = Carbon::parse($tanggalStr . ' ' . $this->waktu_mulai);
        $end = Carbon::parse($tanggalStr . ' ' . $this->waktu_selesai);

        if ($now->lt($start)) {
            return 0;
        }

        if ($now->gt($end)) {
            return 100;
        }

        $totalMinutes = $end->diffInMinutes($start);
        $elapsedMinutes = $now->diffInMinutes($start);

        return round(($elapsedMinutes / $totalMinutes) * 100);
    }

    public function getElapsedTimeAttribute()
    {
        if ($this->status !== 'berlangsung') {
            return '00:00';
        }

        // Get the date from the first jadwal ujian attached to this session
        $jadwalUjian = $this->jadwalUjians()->first();
        if (!$jadwalUjian) {
            return '00:00'; // Can't calculate without a date
        }

        $tanggalStr = $jadwalUjian->tanggal->format('Y-m-d');
        $now = now();
        $start = Carbon::parse($tanggalStr . ' ' . $this->waktu_mulai);

        if ($now->lt($start)) {
            return '00:00';
        }

        $elapsed = $now->diffInMinutes($start);
        $hours = intval($elapsed / 60);
        $minutes = $elapsed % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getRemainingTimeAttribute()
    {
        if ($this->status !== 'berlangsung') {
            return '00:00';
        }

        // Get the date from the first jadwal ujian attached to this session
        $jadwalUjian = $this->jadwalUjians()->first();
        if (!$jadwalUjian) {
            return '00:00'; // Can't calculate without a date
        }

        $tanggalStr = $jadwalUjian->tanggal->format('Y-m-d');
        $now = now();
        $end = Carbon::parse($tanggalStr . ' ' . $this->waktu_selesai);

        if ($now->gt($end)) {
            return '00:00';
        }

        $remaining = $end->diffInMinutes($now);
        $hours = intval($remaining / 60);
        $minutes = $remaining % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    // Token generation
    public function generateToken()
    {
        $this->token_ujian = strtoupper(Str::random(6));
        $this->token_expired_at = now()->addHours(4);
        $this->save();

        return $this->token_ujian;
    }

    /**
     * Calculate remaining capacity for this session
     * 
     * @return int The number of remaining slots available
     */
    public function remainingCapacity()
    {
        $totalCapacity = $this->ruangan ? $this->ruangan->kapasitas : 0;
        $assignedCount = $this->sesiRuanganSiswa()->count();

        return max(0, $totalCapacity - $assignedCount);
    }

    // Auto start session based on time
    public function checkAutoStart()
    {
        // Get the date from the first jadwal ujian attached to this session
        $jadwalUjian = $this->jadwalUjians()->first();
        if (!$jadwalUjian) {
            return $this; // Can't check without a date
        }

        $tanggalStr = $jadwalUjian->tanggal->toDateString();

        if (
            $this->status === 'belum_mulai' &&
            $tanggalStr == now()->toDateString() &&
            now()->format('H:i:s') >= $this->waktu_mulai
        ) {
            $this->status = 'berlangsung';
            $this->save();
        }

        return $this;
    }

    // Auto end session
    public function checkAutoEnd()
    {
        // Get the date from the first jadwal ujian attached to this session
        $jadwalUjian = $this->jadwalUjians()->first();
        if (!$jadwalUjian) {
            return $this; // Can't check without a date
        }

        $tanggalStr = $jadwalUjian->tanggal->toDateString();

        if (
            $this->status === 'berlangsung' &&
            $tanggalStr == now()->toDateString() &&
            now()->format('H:i:s') >= $this->waktu_selesai
        ) {
            Log::info('set selesai sesi ruangan itu.');
            $this->status = 'selesai';
            $this->save();

            // Auto finalize semua peserta
            $enrollments = EnrollmentUjian::where('sesi_ruangan_id', $this->id)
                ->where('status_enrollment', '=', 'active')
                ->get();

            $ujianService = app(UjianService::class);

            foreach ($enrollments as $enrollment) {
                $hasil = HasilUjian::where('enrollment_ujian_id', $enrollment->id)
                    ->where('is_final', false)
                    ->first();

                if ($hasil) {
                    Log::info('autosubmit untuk siswa.', ['ID Siswa' => $enrollment->siswa->id]);
                    $ujianService->autoSubmitHasilUjian($hasil);
                }

                $enrollment->status_enrollment = 'completed';
                $enrollment->waktu_selesai_ujian = now();
                $enrollment->save();
            }
            SesiRuanganSiswa::where('sesi_ruangan_id', $this->id)
                ->update(['keterangan' => 'force_logout']);
        }

        return $this;
    }

    public function getTampilkanTombolSubmitAttribute()
    {
        return $this->pengaturan['tampilkan_tombol_submit'] ?? false;
    }

    public function setTampilkanTombolSubmitAttribute($value)
    {
        $pengaturan = $this->pengaturan ?? [];
        $pengaturan['tampilkan_tombol_submit'] = (bool) $value;
        $this->pengaturan = $pengaturan;
    }
}
