<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcaraUjian extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_ujian';

    protected $fillable = [
        'sesi_ruangan_id',
        'pengawas_id',
        'catatan_pembukaan',
        'catatan_pelaksanaan',
        'catatan_penutupan',
        'jumlah_peserta_terdaftar',
        'jumlah_peserta_hadir',
        'jumlah_peserta_tidak_hadir',
        'status_pelaksanaan',
        'is_final',
        'waktu_finalisasi'
    ];

    protected $casts = [
        'waktu_finalisasi' => 'datetime',
        'is_final' => 'boolean',
        'jumlah_peserta_terdaftar' => 'integer',
        'jumlah_peserta_hadir' => 'integer',
        'jumlah_peserta_tidak_hadir' => 'integer',
        'status_pelaksanaan' => 'string'
    ];

    /**
     * Get the sesi ruangan that this berita acara belongs to.
     */
    public function sesiRuangan()
    {
        return $this->belongsTo(SesiRuangan::class, 'sesi_ruangan_id');
    }

    /**
     * Get the pengawas (guru) who created this berita acara.
     */
    public function pengawas()
    {
        return $this->belongsTo(Guru::class, 'pengawas_id');
    }

    /**
     * Scope to get only finalized berita acara.
     */
    public function scopeFinalized($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope to get only draft berita acara.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_final', false);
    }

    /**
     * Scope to get by status pelaksanaan.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pelaksanaan', $status);
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        if ($this->is_final) {
            return match ($this->status_pelaksanaan) {
                'selesai_normal' => 'bg-green-100 text-green-800',
                'selesai_terganggu' => 'bg-yellow-100 text-yellow-800',
                'dibatalkan' => 'bg-red-100 text-red-800',
                default => 'bg-blue-100 text-blue-800'
            };
        }
        return 'bg-gray-100 text-gray-800'; // Draft
    }

    /**
     * Get verification badge class for display (mapped from finalization status)
     */
    public function getVerificationBadgeClassAttribute()
    {
        return $this->is_final
            ? 'bg-green-100 text-green-800'
            : 'bg-yellow-100 text-yellow-800';
    }

    /**
     * Get verification status text (mapped from finalization status)
     */
    public function getVerificationStatusTextAttribute()
    {
        return $this->is_final ? 'Diverifikasi' : 'Menunggu Verifikasi';
    }

    /**
     * Get status verifikasi attribute (mapped from is_final)
     */
    public function getStatusVerifikasiAttribute()
    {
        return $this->is_final ? 'verified' : 'pending';
    }

    /**
     * Get koordinator relationship (for compatibility)
     */
    public function koordinator()
    {
        // Return null since we don't track who verified, just use the pengawas
        return $this->pengawas();
    }

    /**
     * Get jumlah_hadir attribute (mapped from jumlah_peserta_hadir)
     */
    public function getJumlahHadirAttribute()
    {
        return $this->jumlah_peserta_hadir;
    }

    /**
     * Get jumlah_tidak_hadir attribute (mapped from jumlah_peserta_tidak_hadir)
     */
    public function getJumlahTidakHadirAttribute()
    {
        return $this->jumlah_peserta_tidak_hadir;
    }

    /**
     * Get jumlah_logout attribute (calculated from session data)
     * Note: logout functionality has been removed from current design
     */
    public function getJumlahLogoutAttribute()
    {
        // Logout functionality is no longer tracked in the current system
        return 0;
    }

    /**
     * Calculate attendance statistics from related SesiRuangan
     */
    public function calculateAttendance()
    {
        if (!$this->sesi_ruangan_id) {
            return false;
        }

        $siswaStats = \App\Models\SesiRuanganSiswa::where('sesi_ruangan_id', $this->sesi_ruangan_id)
            ->selectRaw('status_kehadiran, COUNT(*) as count')
            ->groupBy('status_kehadiran')
            ->pluck('count', 'status_kehadiran')
            ->toArray();

        $this->jumlah_peserta_terdaftar = array_sum($siswaStats);
        $this->jumlah_peserta_hadir = $siswaStats['hadir'] ?? 0;
        $this->jumlah_peserta_tidak_hadir = ($siswaStats['tidak_hadir'] ?? 0) + ($siswaStats['sakit'] ?? 0) + ($siswaStats['izin'] ?? 0);

        return $this->save();
    }
    /**
     * Get daftar_tidak_hadir attribute
     */
    public function getDaftarTidakHadirAttribute()
    {
        if (!$this->sesi_ruangan_id) {
            return null;
        }

        $siswaNotPresent = \App\Models\SesiRuanganSiswa::where('sesi_ruangan_id', $this->sesi_ruangan_id)
            ->whereIn('status_kehadiran', ['tidak_hadir', 'sakit', 'izin'])
            ->with('siswa')
            ->get()
            ->pluck('siswa.nama')
            ->filter()
            ->join(', ');

        return $siswaNotPresent ?: null;
    }

    /**
     * Get waktu_mulai_aktual attribute (placeholder)
     */
    public function getWaktuMulaiAktualAttribute()
    {
        // This would typically come from the session start time
        return $this->created_at;
    }

    /**
     * Get waktu_selesai_aktual attribute (placeholder)
     */
    public function getWaktuSelesaiAktualAttribute()
    {
        // This would typically come from when the session actually ended
        return $this->is_final ? $this->waktu_finalisasi : null;
    }

    /**
     * Get kendala_teknis attribute (from catatan_pelaksanaan)
     */
    public function getKendalaTeknisAttribute()
    {
        // Filter technical issues from general notes
        return strpos($this->catatan_pelaksanaan, 'kendala') !== false ? $this->catatan_pelaksanaan : null;
    }

    /**
     * Get kejadian_khusus attribute (from catatan_pelaksanaan)
     */
    public function getKejadianKhususAttribute()
    {
        return $this->catatan_pelaksanaan;
    }

    /**
     * Get catatan_khusus attribute (from catatan_penutupan)
     */
    public function getCatatanKhususAttribute()
    {
        return $this->catatan_penutupan;
    }

    /**
     * Get saran_perbaikan attribute (placeholder)
     */
    public function getSaranPerbaikanAttribute()
    {
        // This could be derived from notes or be a separate field
        return null;
    }

    /**
     * Get tanggal_verifikasi attribute (from waktu_finalisasi)
     */
    public function getTanggalVerifikasiAttribute()
    {
        return $this->waktu_finalisasi;
    }

    /**
     * Get catatan_koordinator attribute (placeholder)
     */
    public function getCatatanKoordinatorAttribute()
    {
        // Extract coordinator notes from catatan_pembukaan if exists
        if (strpos($this->catatan_pembukaan, 'Catatan Koordinator:') !== false) {
            return substr($this->catatan_pembukaan, strpos($this->catatan_pembukaan, 'Catatan Koordinator:') + 20);
        }
        return null;
    }

    /**
     * Get status text for display
     */
    public function getStatusTextAttribute()
    {
        if (!$this->is_final) {
            return 'Draft';
        }

        return match ($this->status_pelaksanaan) {
            'selesai_normal' => 'Selesai Normal',
            'selesai_terganggu' => 'Selesai Terganggu',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status_pelaksanaan))
        };
    }

    /**
     * Check if this berita acara can be edited
     */
    public function canEdit()
    {
        return !$this->is_final;
    }

    /**
     * Check if this berita acara can be finalized
     */
    public function canFinalize()
    {
        return !$this->is_final && !empty($this->status_pelaksanaan);
    }

    /**
     * Finalize this berita acara
     */
    public function finalize()
    {
        $this->update([
            'is_final' => true,
            'waktu_finalisasi' => now()
        ]);
    }

    /**
     * Get attendance percentage
     */
    public function getAttendancePercentageAttribute()
    {
        if ($this->jumlah_peserta_terdaftar <= 0) {
            return 0;
        }

        return round(($this->jumlah_peserta_hadir / $this->jumlah_peserta_terdaftar) * 100, 2);
    }

    /**
     * Get formatted attendance info
     */
    public function getAttendanceInfoAttribute()
    {
        return "{$this->jumlah_peserta_hadir}/{$this->jumlah_peserta_terdaftar} ({$this->attendance_percentage}%)";
    }
}
