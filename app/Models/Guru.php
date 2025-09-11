<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nama',
        'nip',
        'email',
        'user_id',
        'password',
    ];

    // Reference to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships
    /**
     * Get all berita acara ujian where this guru is the pengawas.
     */
    public function beritaAcaraUjians()
    {
        return $this->hasMany(BeritaAcaraUjian::class, 'pengawas_id');
    }

    /**
     * Get all berita acara ujian that this guru has verified (as koordinator).
     */
    public function beritaAcaraVerified()
    {
        return $this->hasMany(BeritaAcaraUjian::class, 'koordinator_id');
    }

    /**
     * Get all sesi ruangan where this guru is the pengawas.
     */
    public function sesiRuanganPengawas()
    {
        return $this->hasMany(SesiRuangan::class, 'pengawas_id');
    }

    /**
     * Get all sesi ruangan where this guru is the koordinator.
     */
    public function sesiRuanganKoordinator()
    {
        return $this->hasMany(SesiRuangan::class, 'koordinator_id');
    }

    /**
     * Legacy alias for backward compatibility
     */
    public function sesiRuanganDiawasi()
    {
        return $this->sesiRuanganPengawas();
    }

    /**
     * Check if guru is available for assignment at a given time
     */
    public function isAvailableAt($tanggal, $waktuMulai, $waktuSelesai)
    {
        return !$this->sesiRuanganPengawas()
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                    ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                    ->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                        $q->where('waktu_mulai', '<=', $waktuMulai)
                            ->where('waktu_selesai', '>=', $waktuSelesai);
                    });
            })
            ->exists();
    }

    /**
     * Get schedule for a specific date
     */
    public function getScheduleForDate($tanggal)
    {
        return $this->sesiRuanganPengawas()
            ->with(['ruangan', 'jadwalUjian.mapel', 'jadwalUjian.kelas'])
            ->where('tanggal', $tanggal)
            ->orderBy('waktu_mulai')
            ->get();
    }

    /**
     * Get current active session (if any)
     */
    public function getCurrentActiveSession()
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return $this->sesiRuanganPengawas()
            ->where('tanggal', $today)
            ->where('waktu_mulai', '<=', $currentTime)
            ->where('waktu_selesai', '>=', $currentTime)
            ->where('status', 'berlangsung')
            ->first();
    }

    /**
     * Get the user's roles through the user relationship
     */
    public function roles()
    {
        return $this->user ? $this->user->roles : collect();
    }

    /**
     * Get current role name for display
     */
    public function getCurrentRoleName()
    {
        return $this->user ? $this->user->getCurrentRoleName() : 'No Role';
    }

    /**
     * Get the redirect route based on the user's role
     */
    public function getRedirectRoute()
    {
        return $this->user ? $this->user->getRedirectRoute() : 'home';
    }

    /**
     * Get role label for display
     */
    public function getRoleLabelAttribute()
    {
        $roles = self::getRoleOptions();
        $currentRole = $this->getCurrentRoleName();
        return $roles[$currentRole] ?? $currentRole;
    }

    /**
     * Query scopes for filtering by roles
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('user', function ($q) use ($roleName) {
            $q->whereHas('roles', function ($q2) use ($roleName) {
                $q2->where('name', $roleName);
            });
        });
    }

    public function scopeDataManagers($query)
    {
        return $this->scopeWithRole($query, 'data');
    }

    public function scopeNaskahManagers($query)
    {
        return $this->scopeWithRole($query, 'naskah');
    }

    public function scopeRuanganManagers($query)
    {
        return $this->scopeWithRole($query, 'ruangan');
    }

    public function scopeSupervisors($query)
    {
        return $this->scopeWithRole($query, 'pengawas');
    }

    public function scopeCoordinators($query)
    {
        return $this->scopeWithRole($query, 'koordinator');
    }

    public function scopeRegularGurus($query)
    {
        return $this->scopeWithRole($query, 'guru');
    }

    /**
     * Forward role checks to the user model
     */
    public function hasRole($role)
    {
        return $this->user && $this->user->hasRole($role);
    }

    /**
     * Role checking convenience methods that forward to the user
     */
    public function canManageData()
    {
        return $this->user && $this->user->canManageData();
    }

    public function canManageNaskah()
    {
        return $this->user && $this->user->canManageNaskah();
    }

    public function canManageRuangan()
    {
        return $this->user && $this->user->canManageRuangan();
    }

    public function canSupervise()
    {
        return $this->user && $this->user->canSupervise();
    }

    public function canCoordinate()
    {
        return $this->user && $this->user->canCoordinate();
    }

    public function isDefaultGuru()
    {
        return $this->user && $this->user->isGuru();
    }

    /**
     * Available roles for guru
     * @return array Role options with descriptions
     */
    public static function getRoleOptions()
    {
        return [
            'data' => 'Data Management',
            'naskah' => 'Naskah Management',
            'ruangan' => 'Ruangan Management',
            'pengawas' => 'Pengawas',
            'koordinator' => 'Koordinator',
            'guru' => 'Guru (Default)'
        ];
    }
}
