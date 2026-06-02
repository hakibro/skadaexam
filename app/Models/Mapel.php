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
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'tingkat',
        'jurusan',
        'status'
    ];

    protected $casts = [];

    // Relationships
    /**
     * Get all bank soal associated with this mapel.
     */
    public function bankSoals()
    {
        return $this->hasMany(BankSoal::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Get all soal associated with this mapel (through bank soal).
     */
    public function soals()
    {
        return $this->hasManyThrough(Soal::class, BankSoal::class);
    }

    /**
     * Get all jadwal ujian for this mapel.
     */
    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class);
    }

    public function pelanggaranUjians()
    {
        return $this->hasManyThrough(
            PelanggaranUjian::class,
            JadwalUjian::class,
            'mapel_id',       // foreign key di JadwalUjian
            'jadwal_ujian_id', // foreign key di PelanggaranUjian
            'id',             // local key di Mapel
            'id'              // local key di JadwalUjian
        );
    }

    /**
     * Get all siswa enrolled in this mapel.
     */
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'mapel_siswa')
            ->withPivot('status_enrollment', 'tanggal_daftar', 'enrolled_by')
            ->withTimestamps();
    }

    /**
     * Get all enrolled students.
     */
    public function enrolledStudents()
    {
        return $this->siswa();
    }

    // Scopes
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

        while (static::query()
            ->when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->where('kode_mapel', $kode)
            ->exists()) {
            $kode = $base . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $kode;
    }

    // Accessors & Mutators
    public function getDefaultIconAttribute()
    {
        return asset('images/default-mapel-cover.png');
    }
}
