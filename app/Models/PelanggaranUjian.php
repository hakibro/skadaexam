<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranUjian extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_ujian';

    protected $fillable = [
        'siswa_id',
        'hasil_ujian_id',
        'jadwal_ujian_id',
        'sesi_ruangan_id',
        'jenis_pelanggaran',
        'deskripsi',
        'waktu_pelanggaran',
        'is_dismissed', // Apakah pelanggaran ini diabaikan oleh pengawas
        'is_finalized', // Apakah pelanggaran ini sudah diputuskan oleh pengawas
        'tindakan', // Tindakan apa yang diambil pengawas (peringatan, skors, diskualifikasi)
        'catatan_pengawas'
    ];

    protected $casts = [
        'waktu_pelanggaran' => 'datetime',
        'is_dismissed' => 'boolean',
        'is_finalized' => 'boolean',
    ];

    /**
     * Get the student associated with this violation.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the exam result associated with this violation.
     */
    public function hasilUjian()
    {
        return $this->belongsTo(HasilUjian::class);
    }

    /**
     * Get the exam schedule associated with this violation.
     */
    public function jadwalUjian()
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    /**
     * Get the session room associated with this violation.
     */
    public function sesiRuangan()
    {
        return $this->belongsTo(SesiRuangan::class);
    }
}
