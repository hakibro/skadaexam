<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SesiRuangan extends Model
{
    use HasFactory;

    // The table doesn't have timestamps columns
    public $timestamps = false;

    protected $table = 'sesi_ruangan';

    protected $fillable = [
        'kode_sesi',
        'nama_sesi',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'token_ujian',
        'token_expired_at',
        'status',
        'pengaturan',
        'ruangan_id',
        'pengawas_id',
        'template_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'token_expired_at' => 'datetime',
        'pengaturan' => 'array',
        'status' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sesi) {
            if (empty($sesi->kode_sesi)) {
                $sesi->kode_sesi = 'SESI-' . strtoupper(Str::random(6));
            }
        });
    }

    // Relationships
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function pengawas()
    {
        return $this->belongsTo(Guru::class, 'pengawas_id');
    }

    public function template()
    {
        return $this->belongsTo(SesiTemplate::class, 'template_id');
    }

    public function koordinator()
    {
        return $this->belongsTo(Guru::class, 'koordinator_id');
    }

    // According to the database schema, jadwal_ujian_id doesn't exist in sesi_ruangan table
    // But we'll leave it for compatibility
    public function jadwalUjian()
    {
        return $this->hasOneThrough(
            JadwalUjian::class,
            BeritaAcaraUjian::class,
            'sesi_ruangan_id', // Foreign key on berita_acara_ujian table
            'id', // Foreign key on jadwal_ujian table
            'id', // Local key on sesi_ruangan table
            'jadwal_ujian_id' // Local key on berita_acara_ujian table
        );
    }

    public function sesiRuanganSiswa()
    {
        return $this->hasMany(SesiRuanganSiswa::class);
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
        return $this->sesiRuanganSiswa()->where('status', 'hadir');
    }

    public function siswaTidakHadir()
    {
        return $this->sesiRuanganSiswa()->where('status', 'tidak_hadir');
    }

    public function siswaLogout()
    {
        return $this->sesiRuanganSiswa()->where('status', 'logout');
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

        $now = now();
        $tanggalStr = $this->tanggal instanceof Carbon ? $this->tanggal->format('Y-m-d') : $this->tanggal;
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

        $now = now();
        $tanggalStr = $this->tanggal instanceof Carbon ? $this->tanggal->format('Y-m-d') : $this->tanggal;
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

        $now = now();
        $tanggalStr = $this->tanggal instanceof Carbon ? $this->tanggal->format('Y-m-d') : $this->tanggal;
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
        $tanggalStr = $this->tanggal instanceof Carbon ? $this->tanggal->toDateString() : $this->tanggal;

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
        $tanggalStr = $this->tanggal instanceof Carbon ? $this->tanggal->toDateString() : $this->tanggal;

        if (
            $this->status === 'berlangsung' &&
            $tanggalStr == now()->toDateString() &&
            now()->format('H:i:s') >= $this->waktu_selesai
        ) {
            $this->status = 'selesai';
            $this->save();
        }

        return $this;
    }
}
