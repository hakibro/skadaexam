<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaTahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'siswa_tahun_ajaran';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'kelas_id',
        'status_siswa',
        'status_pembayaran',
        'rekomendasi',
        'catatan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
