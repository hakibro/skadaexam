<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketUjian extends Model
{
    use HasFactory;

    protected $table = 'paket_ujian';

    protected $fillable = [
        'tahun_ajaran_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function jadwalUjian()
    {
        return $this->hasMany(JadwalUjian::class, 'paket_ujian_id');
    }

    public function bankSoals()
    {
        return $this->hasMany(BankSoal::class, 'paket_ujian_id');
    }

    public function ruangans()
    {
        return $this->hasMany(Ruangan::class, 'paket_ujian_id');
    }

    public function sesiRuangans()
    {
        return $this->hasMany(SesiRuangan::class, 'paket_ujian_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeForTahunAjaran($query, $tahunAjaranId)
    {
        return $tahunAjaranId ? $query->where('tahun_ajaran_id', $tahunAjaranId) : $query;
    }

    public function isReadOnly(): bool
    {
        return $this->status === 'arsip' || $this->tahunAjaran?->isReadOnly();
    }
}
