<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Guru extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $table = 'guru';

    // IMPORTANT: Define guard untuk Spatie Permission
    protected $guard_name = 'guru';

    protected $fillable = [
        'nama',
        'nip',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    // Override guard name untuk Spatie Permission
    public function getDefaultGuardName(): string
    {
        return 'guru';
    }

    // Available roles untuk guru
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

    // Role checking methods - ONLY Spatie dengan guru guard
    public function canManageData()
    {
        return $this->hasRole('data', 'guru');
    }

    public function canManageNaskah()
    {
        return $this->hasRole('naskah', 'guru');
    }

    public function canManageRuangan()
    {
        return $this->hasRole('ruangan', 'guru');
    }

    public function canSupervise()
    {
        return $this->hasRole('pengawas', 'guru');
    }

    public function canCoordinate()
    {
        return $this->hasRole('koordinator', 'guru');
    }

    public function isDefaultGuru()
    {
        return $this->hasRole('guru', 'guru');
    }

    // Get current role name for display
    public function getCurrentRoleName()
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'No Role';
    }

    public function getRoleLabelAttribute()
    {
        $roles = self::getRoleOptions();
        $currentRole = $this->getCurrentRoleName();
        return $roles[$currentRole] ?? $currentRole;
    }

    // Scopes berdasarkan Spatie roles
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName)->where('guard_name', 'guru');
        });
    }

    public function scopeDataManagers($query)
    {
        return $query->withRole('data');
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
}
