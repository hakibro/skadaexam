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
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Role options
    public static function getRoleOptions()
    {
        return [
            'guru' => 'Guru (Default)',
            'data' => 'Data Management',
            'naskah' => 'Naskah Management',
            'pengawas' => 'Pengawas',
            'koordinator' => 'Koordinator',
            'ruangan' => 'Ruangan Management',
        ];
    }

    // Get role label
    public function getRoleLabelAttribute()
    {
        $roles = self::getRoleOptions();
        return $roles[$this->role] ?? $this->role;
    }
}
