<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    // Default guard untuk Spatie
    protected $guard_name = 'web';

    // Relationship with Guru model
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Override guard name untuk Spatie Permission
    public function getDefaultGuardName(): string
    {
        return 'web';
    }

    // Available roles untuk users (web guard)
    public static function getRoleOptions()
    {
        return [
            'admin' => 'Administrator - Manage System & Guru',
            'koordinator' => 'Koordinator - Manage Exam Sessions & Supervisors'
        ];
    }

    // Role checking methods
    public function isAdmin()
    {
        return $this->hasRole('admin', 'web');
    }

    public function isKoordinator()
    {
        return $this->hasRole('koordinator', 'web');
    }

    // Guru role checking methods
    public function isGuru()
    {
        return $this->hasRole('guru', 'web');
    }

    public function isSiswa()
    {
        return $this->hasRole('siswa', 'siswa');
    }

    public function canManageData()
    {
        return $this->hasRole('data', 'web');
    }

    public function canManageNaskah()
    {
        return $this->hasRole('naskah', 'web');
    }

    public function canManageRuangan()
    {
        return $this->hasRole('ruangan', 'web');
    }

    public function canSupervise()
    {
        return $this->hasRole('pengawas', 'web');
    }

    public function canCoordinate()
    {
        return $this->hasRole('koordinator', 'web');
    }

    // Admin permissions
    public function canManageGuru()
    {
        return $this->isAdmin();
    }

    public function canAccessAdminPanel()
    {
        return $this->isAdmin();
    }

    public function canAccessKoordinatorPanel()
    {
        return $this->isKoordinator() || $this->isAdmin();
    }

    public function canManageExamSessions()
    {
        return $this->canAccessKoordinatorPanel();
    }

    public function canAssignSupervisors()
    {
        return $this->canAccessKoordinatorPanel();
    }

    public function canVerifyReports()
    {
        return $this->canAccessKoordinatorPanel();
    }

    // Relationships - User now creates bank soal and jadwal ujian based on FK constraints
    /**
     * Get all bank soal created by this user.
     */
    public function bankSoals()
    {
        return $this->hasMany(BankSoal::class, 'created_by');
    }

    /**
     * Get all jadwal ujian created by this user.
     */
    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class, 'created_by');
    }

    /**
     * Check if user has any active sessions
     */
    public function hasActiveSessions()
    {
        // Implementation could check for active login sessions
        return false;
    }

    /**
     * Get the redirect route based on the user's role
     * @return string The route name to redirect to
     */
    public function getRedirectRoute()
    {
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        } elseif ($this->canManageData()) {
            return 'data.dashboard';
        } elseif ($this->canManageRuangan()) {
            return 'ruangan.dashboard';
        } elseif ($this->canSupervise()) {
            return 'pengawas.dashboard';
        } elseif ($this->canCoordinate()) {
            return 'koordinator.dashboard';
        } elseif ($this->canManageNaskah()) {
            return 'naskah.dashboard';
        } elseif ($this->isGuru()) {
            return 'guru.dashboard';
        }
        // Default route
        return 'home';
    }

    /**
     * Get current role name for display
     */
    public function getCurrentRoleName()
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'No Role';
    }
}
