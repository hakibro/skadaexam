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

    // Accessors & Mutators
    public function getDefaultIconAttribute()
    {
        return asset('images/default-mapel-cover.png');
    }
}
