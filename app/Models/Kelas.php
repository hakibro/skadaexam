<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'deskripsi'
    ];

    /**
     * Get the siswa for the kelas.
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id', 'id');
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
        return ['IPA', 'IPS', 'MIPA', 'BAHASA', 'AGAMA', 'UMUM'];
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

    /**
     * Get the number of students in this class.
     */
    public function getStudentCountAttribute()
    {
        return $this->siswa()->count();
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
