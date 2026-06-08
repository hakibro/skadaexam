<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mapel';

    protected $fillable = [
        'tahun_ajaran_id',
        'paket_ujian_id',
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'tingkat',
        'jurusan',
        'status'
    ];

    protected $casts = [];

    public function bankSoals()
    {
        return $this->hasMany(BankSoal::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function paketUjian()
    {
        return $this->belongsTo(PaketUjian::class, 'paket_ujian_id');
    }

    public function soals()
    {
        return $this->hasManyThrough(Soal::class, BankSoal::class);
    }

    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class);
    }

    public function pelanggaranUjians()
    {
        return $this->hasManyThrough(
            PelanggaranUjian::class,
            JadwalUjian::class,
            'mapel_id',
            'jadwal_ujian_id',
            'id',
            'id'
        );
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'mapel_siswa')
            ->withPivot('status_enrollment', 'tanggal_daftar', 'enrolled_by')
            ->withTimestamps();
    }

    public function enrolledStudents()
    {
        return $this->siswa();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    public function scopeForTahunAjaran($query, $tahunAjaranId)
    {
        return $tahunAjaranId ? $query->where('tahun_ajaran_id', $tahunAjaranId) : $query;
    }

    public function scopeByPaketUjian($query, $paketUjianId)
    {
        return $paketUjianId ? $query->where('paket_ujian_id', $paketUjianId) : $query;
    }

    public static function generateKode(string $namaMapel, ?string $tingkat = null, ?int $tahunAjaranId = null): string
    {
        $words = preg_split('/\s+/', strtoupper(preg_replace('/[^A-Za-z0-9\s]/', ' ', $namaMapel)));
        $prefix = collect($words)
            ->filter()
            ->take(3)
            ->map(fn($word) => substr($word, 0, 3))
            ->implode('');

        $prefix = substr($prefix ?: 'MAPEL', 0, 10);
        $tingkatPart = $tingkat ? '-' . strtoupper((string) $tingkat) : '';
        $base = $prefix . $tingkatPart;
        $kode = $base;
        $counter = 1;

        while (
            static::query()
                ->when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
                ->where('kode_mapel', $kode)
                ->exists()
        ) {
            $kode = $base . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $kode;
    }

    public function getDefaultIconAttribute()
    {
        return asset('images/default-mapel-cover.png');
    }
}