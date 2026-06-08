<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankSoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_soal';

    protected $fillable = [
        'tahun_ajaran_id',
        'paket_ujian_id',
        'kode_bank',
        'judul',
        'deskripsi',
        'mapel_id',
        'tingkat',
        'total_soal',
        'status',
        'pengaturan',
        'created_by'
    ];

    protected $casts = [
        'pengaturan' => 'array',
        'total_soal' => 'integer',
        'status' => 'string',
    ];

    public function getJumlahPilihanAttribute(): int
    {
        $value = (int) data_get($this->pengaturan, 'jumlah_pilihan', 5);

        return in_array($value, [2, 3, 4, 5], true) ? $value : 5;
    }

    public function getTipeSoalDefaultAttribute(): string
    {
        return data_get($this->pengaturan, 'tipe_soal_default', 'pilihan_ganda');
    }

    public function setPengaturanValue(string $key, mixed $value): void
    {
        $pengaturan = $this->pengaturan ?? [];
        data_set($pengaturan, $key, $value);
        $this->pengaturan = $pengaturan;
    }

    /**
     * Get the creator (user) who created this question bank.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function paketUjian()
    {
        return $this->belongsTo(PaketUjian::class, 'paket_ujian_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'bank_soal_id');
    }

    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class, 'bank_soal_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function hasilUjian()
    {
        return $this->hasManyThrough(
            HasilUjian::class,
            JadwalUjian::class,
            'bank_soal_id',
            'jadwal_ujian_id',
            'id',
            'id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    public function scopeForTahunAjaran($query, $tahunAjaranId)
    {
        return $tahunAjaranId ? $query->where('tahun_ajaran_id', $tahunAjaranId) : $query;
    }

    public function scopeByPaketUjian($query, $paketUjianId)
    {
        return $paketUjianId ? $query->where('paket_ujian_id', $paketUjianId) : $query;
    }

    public function updateTotalSoal()
    {
        $this->total_soal = $this->soals()->count();
        $this->save();
    }

    public function totalSoal()
    {
        return $this->soals()->count();
    }

    public function getSourceFileUrlAttribute()
    {
        $pengaturan = $this->pengaturan ?? [];
        if (is_array($pengaturan) && isset($pengaturan['source_file'])) {
            return asset('storage/bank-soal/sources/' . $pengaturan['source_file']);
        }
        return null;
    }

    public function getImportLogAttribute()
    {
        $pengaturan = $this->pengaturan ?? [];
        return $pengaturan['import_log'] ?? [];
    }

    public function setImportLogAttribute($value)
    {
        $pengaturan = $this->pengaturan ?? [];
        $pengaturan['import_log'] = $value;
        $this->pengaturan = $pengaturan;
    }

    public function setSourceFileAttribute($value)
    {
        $pengaturan = $this->pengaturan ?? [];
        $pengaturan['source_file'] = $value;
        $this->pengaturan = $pengaturan;
    }
}