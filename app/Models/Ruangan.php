<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'lokasi',
        'kapasitas',
        'fasilitas',
        'jenis_ruangan',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'fasilitas' => 'array',
        'kapasitas' => 'integer',
        'status' => 'string',
        'jenis_ruangan' => 'string',
        'nama_ruangan' => 'string',
        'kode_ruangan' => 'string',
        'lokasi' => 'string',
    ];

    /**
     * Get all session rooms associated with this room.
     */
    public function sesiRuangan()
    {
        return $this->hasMany(SesiRuangan::class, 'ruangan_id');
    }

    /**
     * Get current active sessions
     */
    public function sesiAktif()
    {
        return $this->sesiRuangan()->whereIn('status', ['belum_mulai', 'berlangsung']);
    }

    /**
     * Get sessions for today
     */
    public function sesiHariIni()
    {
        return $this->sesiRuangan()->where('tanggal', today());
    }

    /**
     * Get all exam reports from this room.
     */
    public function beritaAcara()
    {
        return $this->hasManyThrough(
            BeritaAcaraUjian::class,
            SesiRuangan::class,
            'ruangan_id',
            'sesi_ruangan_id',
            'id',
            'id'
        );
    }

    /**
     * Scope to get only active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get the display name of the room.
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->nama_ruangan} ({$this->kode_ruangan})";
    }

    /**
     * Check if this room is available.
     */
    public function isAvailable()
    {
        return $this->status === 'aktif';
    }

    /**
     * Get sessions count for this room
     */
    public function getSesiCountAttribute()
    {
        return $this->sesiRuangan()->count();
    }

    /**
     * Get active sessions count
     */
    public function getActiveSesiCountAttribute()
    {
        return $this->sesiRuangan()->whereIn('status', ['belum_mulai', 'berlangsung'])->count();
    }

    /**
     * Check if room is available for a given time period.
     */
    public function isAvailableFor($date, $startTime, $endTime)
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $conflictingSessions = $this->sesiRuangan()
            ->where('tanggal', $date)
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('waktu_mulai', '>=', $startTime)
                        ->where('waktu_mulai', '<', $endTime);
                })
                    ->orWhere(function ($subQ) use ($startTime, $endTime) {
                        $subQ->where('waktu_selesai', '>', $startTime)
                            ->where('waktu_selesai', '<=', $endTime);
                    })
                    ->orWhere(function ($subQ) use ($startTime, $endTime) {
                        $subQ->where('waktu_mulai', '<=', $startTime)
                            ->where('waktu_selesai', '>=', $endTime);
                    });
            })
            ->count();

        return $conflictingSessions === 0;
    }

    /**
     * Get available facilities as formatted string
     */
    public function getFormattedFasilitasAttribute()
    {
        if (!is_array($this->fasilitas) || empty($this->fasilitas)) {
            return 'Tidak ada fasilitas';
        }

        $facilityLabels = [
            'wifi' => 'WiFi',
            'ac' => 'AC',
            'proyektor' => 'Proyektor',
            'komputer' => 'Komputer',
            'papan_tulis' => 'Papan Tulis',
            'cctv' => 'CCTV',
            'printer' => 'Printer',
            'speaker' => 'Speaker'
        ];

        $formatted = [];
        foreach ($this->fasilitas as $facility) {
            $formatted[] = $facilityLabels[$facility] ?? ucfirst($facility);
        }

        return implode(', ', $formatted);
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'aktif' => ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800'],
            'perbaikan' => ['text' => 'Perbaikan', 'class' => 'bg-yellow-100 text-yellow-800'],
            'tidak_aktif' => ['text' => 'Tidak Aktif', 'class' => 'bg-red-100 text-red-800'],
        ];

        return $labels[$this->status] ?? ['text' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status_label['class'];
    }

    /**
     * Get current session status
     */
    public function getCurrentSessionStatus()
    {
        $now = now();
        $currentSession = $this->sesiRuangan()
            ->where('tanggal', $now->toDateString())
            ->where('waktu_mulai', '<=', $now->format('H:i:s'))
            ->where('waktu_selesai', '>=', $now->format('H:i:s'))
            ->first();

        return $currentSession ? $currentSession->status : 'kosong';
    }

    /**
     * Get room utilization percentage
     */
    public function getUtilizationPercentage($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $totalSessions = $this->sesiRuangan()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->count();

        $workingDays = $startDate->diffInWeekdays($endDate);
        $maxPossibleSessions = $workingDays * 8; // Assume 8 sessions per day max

        return $maxPossibleSessions > 0 ? round(($totalSessions / $maxPossibleSessions) * 100) : 0;
    }

    /**
     * Get next scheduled session
     */
    public function getNextSession()
    {
        return $this->sesiRuangan()
            ->where('status', 'belum_mulai')
            ->where(function ($query) {
                $query->where('tanggal', '>', today())
                    ->orWhere(function ($q) {
                        $q->where('tanggal', today())
                            ->where('waktu_mulai', '>', now()->format('H:i:s'));
                    });
            })
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->first();
    }

    /**
     * Check if room needs maintenance based on usage
     */
    public function needsMaintenance()
    {
        $utilizationRate = $this->getUtilizationPercentage();
        return $utilizationRate > 80 && $this->status === 'aktif';
    }
}
