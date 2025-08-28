<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

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

    // Helper methods untuk roles
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isGuru()
    {
        return $this->hasRole('guru');
    }

    public function isSiswa()
    {
        return $this->hasRole('siswa');
    }

    public function getAvailableFeatures()
    {
        if ($this->isAdmin()) {
            return ['admin', 'data', 'naskah', 'pengawas', 'koordinator', 'ruangan'];
        }

        $features = [];
        $roleNames = $this->roles->pluck('name')->toArray();

        foreach ($roleNames as $role) {
            if (!in_array($role, ['guru', 'siswa'])) {
                $features[] = $role;
            }
        }

        return $features;
    }

    // Relasi ke guru dan siswa
    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }
}

// Menetapkan peran admin kepada pengguna dengan email admin@test.com
$user = User::where('email', 'admin@test.com')->first();
if ($user) {
    $user->assignRole('admin');
}
