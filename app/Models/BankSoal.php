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
        'kode_bank',
        'judul',
        'deskripsi',
        'mapel_id',
        'tingkat',
        'jenis_soal',
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

    /**
     * Get the creator (user) who created this question bank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions in this question bank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function soals()
    {
        return $this->hasMany(Soal::class, 'bank_soal_id');
    }

    /**
     * Get all exam schedules using this question bank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class, 'bank_soal_id');
    }

    /**
     * Get the subject (mapel) associated with this question bank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Get all exam results related to this question bank.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function hasilUjian()
    {
        return $this->hasManyThrough(
            HasilUjian::class,
            JadwalUjian::class,
            'bank_soal_id', // Foreign key on jadwal_ujian table...
            'jadwal_ujian_id', // Foreign key on hasil_ujian table...
            'id', // Local key on bank_soal table...
            'id' // Local key on jadwal_ujian table...
        );
    }

    /**
     * Scope to get only active question banks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope to filter question banks by grade level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tingkat
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    /**
     * Update the total question count for this bank.
     *
     * @return void
     */
    public function updateTotalSoal()
    {
        $this->total_soal = $this->soals()->count();
        $this->save();
    }

    /**
     * Get the total number of questions in this bank.
     *
     * @return int
     */
    public function totalSoal()
    {
        return $this->soals()->count();
    }

    /**
     * Get the URL for the source file.
     *
     * @return string|null
     */
    public function getSourceFileUrlAttribute()
    {
        $pengaturan = $this->pengaturan ?? [];
        if (is_array($pengaturan) && isset($pengaturan['source_file'])) {
            return asset('storage/bank-soal/sources/' . $pengaturan['source_file']);
        }
        return null;
    }

    /**
     * Get the import log information.
     *
     * @return array
     */
    public function getImportLogAttribute()
    {
        $pengaturan = $this->pengaturan ?? [];
        return $pengaturan['import_log'] ?? [];
    }

    /**
     * Set the import log information.
     *
     * @param array $value
     * @return void
     */
    public function setImportLogAttribute($value)
    {
        $pengaturan = $this->pengaturan ?? [];
        $pengaturan['import_log'] = $value;
        $this->pengaturan = $pengaturan;
    }

    /**
     * Set the source file information.
     *
     * @param string $value
     * @return void
     */
    public function setSourceFileAttribute($value)
    {
        $pengaturan = $this->pengaturan ?? [];
        $pengaturan['source_file'] = $value;
        $this->pengaturan = $pengaturan;
    }
}
