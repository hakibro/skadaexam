<?php

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GuruExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Return the query for the export
     */
    public function query()
    {
        return Guru::with('user.roles');
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Email',
            'Role',
        ];
    }

    /**
     * Define how each row should be mapped
     */
    public function map($guru): array
    {
        $role = $guru->user ? $guru->user->roles()->first()?->name ?? 'guru' : 'guru';

        return [
            $guru->nama,
            $guru->nip ?? '',
            $guru->email,
            $role,
        ];
    }
}
