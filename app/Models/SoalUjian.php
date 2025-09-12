<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalUjian extends Model
{
    use HasFactory;

    protected $table = 'soal';

    protected $fillable = [
        'jadwal_ujian_id',
        'bank_soal_id',
        'soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'bobot',
        'urutan',
        'tingkat_kesulitan',
        'gambar_soal',
        'status'
    ];

    public function jadwalUjian()
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class);
    }

    public function jawabanSiswas()
    {
        return $this->hasMany(JawabanSiswa::class);
    }
}
