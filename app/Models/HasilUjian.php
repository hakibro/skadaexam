<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $table = 'hasil_ujian';

    protected $fillable = [
        'enrollment_ujian_id',
        'siswa_id',
        'jadwal_ujian_id',
        'sesi_ruangan_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'jumlah_soal',
        'jumlah_dijawab',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_tidak_dijawab',
        'skor',
        'nilai',
        'lulus',
        'is_final',
        'status',
        'jawaban',
        'hasil_detail'
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_menit' => 'integer',
        'jumlah_soal' => 'integer',
        'jumlah_dijawab' => 'integer',
        'jumlah_benar' => 'integer',
        'jumlah_salah' => 'integer',
        'jumlah_tidak_dijawab' => 'integer',
        'skor' => 'integer',
        'nilai' => 'float',
        'lulus' => 'boolean',
        'is_final' => 'boolean',
        'jawaban' => 'array',
        'hasil_detail' => 'array',
        'status' => 'string'
    ];

    /**
     * Get the exam schedule associated with this result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwalUjian()
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    /**
     * Get the session room associated with this result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sesiRuangan()
    {
        return $this->belongsTo(SesiRuangan::class, 'sesi_ruangan_id');
    }

    /**
     * Get the student associated with this result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the enrollment record associated with this result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enrollment()
    {
        return $this->belongsTo(EnrollmentUjian::class, 'enrollment_ujian_id');
    }

    /**
     * Get the session room student record associated with this result through enrollment.
     * This relationship is complex due to the database structure changes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sesiRuanganSiswa()
    {
        return $this->hasOne(SesiRuanganSiswa::class, 'siswa_id', 'siswa_id')
            ->where('sesi_ruangan_id', $this->sesi_ruangan_id);
    }

    /**
     * Calculate the letter grade based on the score.
     *
     * @return string
     */
    public function calculateGrade()
    {
        if (!$this->skor || !$this->jumlah_soal) return 'N/A';

        $nilai = ($this->skor / $this->jumlah_soal) * 100;

        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B+';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 70) return 'C+';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 55) return 'D';
        return 'E';
    }

    /**
     * Get the exam duration in seconds.
     *
     * @return int
     */
    public function getDurationInSeconds()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) return 0;
        return $this->waktu_selesai->diffInSeconds($this->waktu_mulai);
    }

    /**
     * Get the formatted exam duration.
     *
     * @return string
     */
    public function getDurationFormatted()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) return '-';

        $seconds = $this->getDurationInSeconds();
        $hours = intval($seconds / 3600);
        $minutes = intval(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get the percentage of correct answers.
     *
     * @return float
     */
    public function getPersentaseBenar()
    {
        if ($this->jumlah_soal == 0) return 0;
        return round(($this->jumlah_benar / $this->jumlah_soal) * 100, 2);
    }

    /**
     * Finalize the exam result.
     *
     * @return bool
     */
    public function finalize()
    {
        if ($this->is_final) {
            return false; // Already finalized
        }

        // Make sure all fields are properly calculated
        $this->calculateResults();

        $this->is_final = true;
        $this->waktu_selesai = now();
        $this->save();

        return true;
    }

    /**
     * Calculate the exam results.
     *
     * @return bool|void
     */
    public function calculateResults()
    {
        if (!$this->jawaban) {
            $this->jumlah_benar = 0;
            $this->jumlah_salah = 0;
            $this->jumlah_tidak_dijawab = $this->jumlah_soal;
            $this->jumlah_dijawab = 0;
            $this->skor = 0;
            $this->nilai = 0;
            $this->lulus = false;
            return;
        }

        // Assume we have a structure like: 
        // [
        //   {"soal_id": 1, "jawaban": "A", "is_correct": true},
        //   {"soal_id": 2, "jawaban": null, "is_correct": null},
        //   {"soal_id": 3, "jawaban": "B", "is_correct": false}
        // ]

        $answered = collect($this->jawaban)->filter(function ($jawaban) {
            return !is_null($jawaban['jawaban'] ?? null);
        });

        $correct = $answered->filter(function ($jawaban) {
            return ($jawaban['is_correct'] ?? false) === true;
        });

        $this->jumlah_benar = $correct->count();
        $this->jumlah_dijawab = $answered->count();
        $this->jumlah_salah = $this->jumlah_dijawab - $this->jumlah_benar;
        $this->jumlah_tidak_dijawab = $this->jumlah_soal - $this->jumlah_dijawab;
        $this->skor = $this->jumlah_benar;

        // Calculate nilai (percentage)
        if ($this->jumlah_soal > 0) {
            $this->nilai = ($this->jumlah_benar / $this->jumlah_soal) * 100;
        }

        // Check if passed
        $this->lulus = $this->isLulus();

        return $this->save();
    }

    /**
     * Check if the student passed the exam.
     * 
     * @param int $minimumScore Optional minimum score to pass (default: get from jadwal ujian)
     * @return bool
     */
    public function isLulus($minimumScore = null)
    {
        if (!$this->is_final) {
            return false;
        }

        if ($minimumScore === null && $this->jadwalUjian) {
            // Try to get KKM from jadwal ujian settings
            $settings = $this->jadwalUjian->pengaturan ?? [];
            $minimumScore = $settings['kkm'] ?? 75; // Default KKM is 75
        }

        // Calculate percentage score
        $percentage = $this->getPersentaseBenar();

        return $percentage >= $minimumScore;
    }

    /**
     * Scope to get only finalized results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinalized($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope to filter results by a specific student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $siswaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Get the student's answers for this exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jawabanSiswas()
    {
        return $this->hasMany(JawabanSiswa::class);
    }
}
