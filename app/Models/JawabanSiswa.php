<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'hasil_ujian_id',
        'soal_ujian_id',
        'jawaban',
        'is_flagged',
        'waktu_jawab'
    ];

    protected $dates = [
        'waktu_jawab'
    ];

    public function hasilUjian()
    {
        return $this->belongsTo(HasilUjian::class);
    }

    public function soalUjian()
    {
        return $this->belongsTo(SoalUjian::class);
    }
}
