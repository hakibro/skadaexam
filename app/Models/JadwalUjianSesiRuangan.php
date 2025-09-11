<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class JadwalUjianSesiRuangan extends Pivot
{
    protected $table = 'jadwal_ujian_sesi_ruangan';

    // Allow mass assignment
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the pengawas (supervisor) associated with this pivot.
     */
    public function pengawas()
    {
        return $this->belongsTo(Guru::class, 'pengawas_id');
    }

    /**
     * Get the jadwal ujian associated with this pivot.
     */
    public function jadwalUjian()
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    /**
     * Get the sesi ruangan associated with this pivot.
     */
    public function sesiRuangan()
    {
        return $this->belongsTo(SesiRuangan::class);
    }
}
