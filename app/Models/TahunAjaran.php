<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'kode',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function paketUjian()
    {
        return $this->hasMany(PaketUjian::class, 'tahun_ajaran_id');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_ajaran_id');
    }

    public function jadwalUjian()
    {
        return $this->hasMany(JadwalUjian::class, 'tahun_ajaran_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'aktif');
    }

    public function isReadOnly(): bool
    {
        return $this->status === 'arsip';
    }

    public static function active(): ?self
    {
        return static::query()->active()->first();
    }
}
