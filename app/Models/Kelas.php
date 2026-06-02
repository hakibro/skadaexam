<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'tahun_ajaran_id',
        'nama_kelas',
        'tingkat',
        'jurusan',
        'deskripsi'
    ];

    /**
     * Relasi ke data siswa per tahun ajaran.
     *
     * Tabel siswa_tahun_ajaran menyimpan:
     * - siswa_id
     * - tahun_ajaran_id
     * - kelas_id
     * - status_siswa
     * - status_pembayaran
     * - rekomendasi
     */
    public function siswaTahunAjaran(): HasMany
    {
        return $this->hasMany(SiswaTahunAjaran::class, 'kelas_id', 'id');
    }

    /**
     * Get the siswa for the kelas through siswa_tahun_ajaran.
     */
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'siswa_tahun_ajaran',
            'kelas_id',
            'siswa_id'
        )
            ->withPivot([
                'tahun_ajaran_id',
                'status_siswa',
                'status_pembayaran',
                'rekomendasi',
                'catatan',
            ]);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Get all jadwal ujian targeting this kelas.
     */
    public function jadwalUjian()
    {
        return JadwalUjian::whereJsonContains('kelas_target', $this->id);
    }

    /**
     * Get all tingkat options.
     */
    public static function getTingkatOptions(): array
    {
        return ['X', 'XI', 'XII'];
    }

    /**
     * Get all jurusan options.
     */
    public static function getJurusanOptions(): array
    {
        return self::select('jurusan')
            ->distinct()
            ->pluck('jurusan')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Scope to filter by tingkat.
     */
    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    /**
     * Scope to filter by jurusan.
     */
    public function scopeByJurusan($query, $jurusan)
    {
        return $query->where('jurusan', $jurusan);
    }

    public function scopeForTahunAjaran($query, $tahunAjaranId)
    {
        return $tahunAjaranId
            ? $query->where('tahun_ajaran_id', $tahunAjaranId)
            : $query;
    }

    /**
     * Get the number of active students in this class.
     */
    public function getStudentCountAttribute()
    {
        return $this->siswaTahunAjaran()
            ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
            ->where('status_siswa', 'aktif')
            ->count();
    }

    /**
     * Accessor for 'nama' attribute to provide compatibility.
     * Maps to nama_kelas for backward compatibility.
     */
    public function getNamaAttribute()
    {
        return $this->nama_kelas;
    }

    /**
     * Format the class name for display.
     */
    public function getFormattedNameAttribute()
    {
        $parts = [];

        if ($this->tingkat) {
            $parts[] = $this->tingkat;
        }

        if ($this->jurusan) {
            $parts[] = $this->jurusan;
        }

        // Extract the numeric part if available
        $numericPart = preg_replace('/.*?(\d+)$/i', '$1', $this->nama_kelas);

        if ($numericPart && is_numeric($numericPart)) {
            $parts[] = $numericPart;
        }

        return !empty($parts) ? implode(' ', $parts) : $this->nama_kelas;
    }
}