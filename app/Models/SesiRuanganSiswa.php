<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiRuanganSiswa extends Model
{
    use HasFactory;

    protected $table = 'sesi_ruangan_siswa';

    protected $fillable = [
        'sesi_ruangan_id',
        'siswa_id',
        'status_kehadiran',
        'token',
        'token_expired_at',
        'keterangan'
    ];

    protected $casts = [
        'token_expired_at' => 'datetime',
        'status_kehadiran' => 'string'
    ];

    /**
     * Backward compatibility accessor for status
     */
    public function getStatusAttribute()
    {
        return $this->status_kehadiran;
    }

    /**
     * Get the sesi ruangan that this entry belongs to.
     */
    public function sesiRuangan()
    {
        return $this->belongsTo(SesiRuangan::class, 'sesi_ruangan_id');
    }

    /**
     * Get the siswa associated with this entry.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Scope to get only present students.
     */
    public function scopeHadir($query)
    {
        return $query->where('status_kehadiran', 'hadir');
    }

    /**
     * Scope to get only absent students.
     */
    public function scopeTidakHadir($query)
    {
        return $query->where('status_kehadiran', 'tidak_hadir');
    }

    /**
     * Scope to get students who logged out (deprecated functionality)
     */
    public function scopeLogout($query)
    {
        // Logout functionality no longer exists, return empty query
        return $query->whereRaw('0 = 1');
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status_kehadiran) {
            'hadir' => 'bg-green-100 text-green-800',
            'tidak_hadir' => 'bg-red-100 text-red-800',
            'sakit' => 'bg-yellow-100 text-yellow-800',
            'izin' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status dot class for display
     */
    public function getStatusDotClassAttribute()
    {
        return match ($this->status_kehadiran) {
            'hadir' => 'bg-green-400',
            'tidak_hadir' => 'bg-red-400',
            'sakit' => 'bg-yellow-400',
            'izin' => 'bg-blue-400',
            default => 'bg-gray-400'
        };
    }

    /**
     * Get status border class for display
     */
    public function getStatusBorderClassAttribute()
    {
        return match ($this->status_kehadiran) {
            'hadir' => 'border-green-200 bg-green-50',
            'tidak_hadir' => 'border-red-200 bg-red-50',
            'sakit' => 'border-yellow-200 bg-yellow-50',
            'izin' => 'border-blue-200 bg-blue-50',
            default => 'border-gray-200 bg-gray-50'
        };
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status_kehadiran) {
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'sakit' => 'Sakit',
            'izin' => 'Izin',
            default => 'Unknown'
        };
    }

    /**
     * Mark student as present
     */
    public function markPresent()
    {
        $this->update([
            'status_kehadiran' => 'hadir'
        ]);
    }

    /**
     * Mark student as logged out (deprecated - functionality removed)
     */
    public function markLoggedOut()
    {
        // Logout functionality removed, mark as tidak_hadir instead
        $this->update([
            'status_kehadiran' => 'tidak_hadir'
        ]);
    }

    /**
     * Mark student as not present
     */
    public function markNotPresent()
    {
        $this->update([
            'status_kehadiran' => 'tidak_hadir'
        ]);
    }
}
