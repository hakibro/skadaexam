<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SesiTemplate extends Model
{
    use HasFactory;

    protected $table = 'sesi_templates';

    protected $fillable = [
        'kode_sesi',
        'nama_sesi',
        'deskripsi',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'pengaturan',
        'is_active',
        'keterangan',
        'created_by'
    ];

    protected $casts = [
        'pengaturan' => 'array',
        'status' => 'string',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sesi) {
            if (empty($sesi->kode_sesi)) {
                $sesi->kode_sesi = 'TEMP-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Get all sessions using this template
     */
    public function sesiRuangan()
    {
        return $this->hasMany(SesiRuangan::class, 'template_id');
    }

    /**
     * Apply this template to all rooms
     * 
     * @param string|array|null $ruanganIds Specific room IDs to apply to, null for all
     * @param string|null $date Date to apply sessions to, null for today
     * @return int Number of sessions created/updated
     */
    public function applyToRuangan($ruanganIds = null, $date = null)
    {
        $date = $date ?? now()->toDateString();

        $query = Ruangan::query();
        if ($ruanganIds) {
            $query->whereIn('id', (array) $ruanganIds);
        } else {
            $query->where('status', 'aktif');
        }

        $rooms = $query->get();
        $count = 0;

        foreach ($rooms as $room) {
            // Check if session already exists
            $existingSession = SesiRuangan::where('ruangan_id', $room->id)
                ->where('tanggal', $date)
                ->where('template_id', $this->id)
                ->first();

            if ($existingSession) {
                // Update existing session
                $existingSession->update([
                    'nama_sesi' => $this->nama_sesi,
                    'waktu_mulai' => $this->waktu_mulai,
                    'waktu_selesai' => $this->waktu_selesai,
                    'status' => $this->status,
                    'pengaturan' => $this->pengaturan
                ]);
                $count++;
            } else {
                // Create new session
                SesiRuangan::create([
                    'ruangan_id' => $room->id,
                    'template_id' => $this->id,
                    'tanggal' => $date,
                    'kode_sesi' => 'SESI-' . strtoupper(Str::random(6)),
                    'nama_sesi' => $this->nama_sesi,
                    'waktu_mulai' => $this->waktu_mulai,
                    'waktu_selesai' => $this->waktu_selesai,
                    'status' => $this->status,
                    'pengaturan' => $this->pengaturan
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Update all sessions based on this template
     * 
     * @return int Number of sessions updated
     */
    public function updateAllSessions()
    {
        return SesiRuangan::where('template_id', $this->id)
            ->where('status', '!=', 'selesai')  // Don't update completed sessions
            ->where('status', '!=', 'dibatalkan')  // Don't update canceled sessions
            ->update([
                'nama_sesi' => $this->nama_sesi,
                'waktu_mulai' => $this->waktu_mulai,
                'waktu_selesai' => $this->waktu_selesai,
                'status' => $this->status,
                'pengaturan' => $this->pengaturan
            ]);
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'belum_mulai' => [
                'text' => 'Belum Mulai',
                'class' => 'bg-gray-100 text-gray-800'
            ],
            'berlangsung' => [
                'text' => 'Berlangsung',
                'class' => 'bg-green-100 text-green-800'
            ],
            'selesai' => [
                'text' => 'Selesai',
                'class' => 'bg-blue-100 text-blue-800'
            ],
            'dibatalkan' => [
                'text' => 'Dibatalkan',
                'class' => 'bg-red-100 text-red-800'
            ]
        ];

        return $statuses[$this->status] ?? $statuses['belum_mulai'];
    }

    /**
     * Get active status label
     */
    public function getActiveStatusLabelAttribute()
    {
        return $this->is_active
            ? ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800']
            : ['text' => 'Non-Aktif', 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get the time range formatted for display
     */
    public function getTimeRangeAttribute()
    {
        return \Carbon\Carbon::parse($this->waktu_mulai)->format('H:i') . ' - ' .
            \Carbon\Carbon::parse($this->waktu_selesai)->format('H:i');
    }

    /**
     * Get duration in minutes
     */
    public function getDurationAttribute()
    {
        $start = \Carbon\Carbon::parse($this->waktu_mulai);
        $end = \Carbon\Carbon::parse($this->waktu_selesai);
        return $end->diffInMinutes($start);
    }
}
