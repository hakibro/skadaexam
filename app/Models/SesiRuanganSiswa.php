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
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

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
        return $query->where('status', 'hadir');
    }

    /**
     * Scope to get only absent students.
     */
    public function scopeTidakHadir($query)
    {
        return $query->where('status', 'tidak_hadir');
    }

    /**
     * Scope to get logged out students.
     */
    public function scopeLogout($query)
    {
        return $query->where('status', 'logout');
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'hadir' => 'bg-green-100 text-green-800',
            'tidak_hadir' => 'bg-red-100 text-red-800',
            'logout' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status dot class for display
     */
    public function getStatusDotClassAttribute()
    {
        return match ($this->status) {
            'hadir' => 'bg-green-400',
            'tidak_hadir' => 'bg-red-400',
            'logout' => 'bg-yellow-400',
            default => 'bg-gray-400'
        };
    }

    /**
     * Get status border class for display
     */
    public function getStatusBorderClassAttribute()
    {
        return match ($this->status) {
            'hadir' => 'border-green-200 bg-green-50',
            'tidak_hadir' => 'border-red-200 bg-red-50',
            'logout' => 'border-yellow-200 bg-yellow-50',
            default => 'border-gray-200 bg-gray-50'
        };
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'logout' => 'Logout',
            default => 'Unknown'
        };
    }

    /**
     * Mark student as present
     */
    public function markPresent()
    {
        $this->update([
            'status' => 'hadir'
        ]);
    }

    /**
     * Mark student as logged out
     */
    public function markLoggedOut()
    {
        $this->update([
            'status' => 'logout'
        ]);
    }

    /**
     * Mark student as not present
     */
    public function markNotPresent()
    {
        $this->update([
            'status' => 'tidak_hadir'
        ]);
    }
}
