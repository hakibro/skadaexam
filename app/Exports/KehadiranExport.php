<?php

namespace App\Exports;

use App\Models\SesiRuanganSiswa;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping
};

class KehadiranExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;
    protected $data;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $query = SesiRuanganSiswa::query()
            ->with([
                'siswa.kelas',
                'sesiRuangan.ruangan',
                'sesiRuangan.jadwalUjian',
            ]);

        // ================= FILTER (SAMA DENGAN INDEX) =================

        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('sesiRuangan.jadwalUjian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay(),
                ]);
            });
        }

        if ($request->filled('ruangan_id')) {
            $query->whereHas('sesiRuangan', function ($q) use ($request) {
                $q->where('ruangan_id', $request->ruangan_id);
            });
        }

        if ($request->filled('tingkat')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('tingkat', $request->tingkat);
            });
        }

        if ($request->filled('jurusan')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }

        $this->data = $query->latest('id')->get();
    }

    /**
     * Data collection
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * Header Excel
     */
    public function headings(): array
    {
        return [
            'ID Yayasan',
            'Nama Siswa',
            'Kelas',
            'Status Kehadiran',
            'Tanggal Ujian',
            'Ruangan',
            'Sesi',
        ];
    }

    /**
     * Mapping per baris
     */
    public function map($row): array
    {
        $jadwal = $row->sesiRuangan->jadwalUjian->first();

        return [
            $row->siswa->idyayasan ?? '-', // ← pastikan field ini ada
            $row->siswa->nama ?? '-',
            $row->siswa->kelas->formatted_name ?? '-',
            ucfirst(str_replace('_', ' ', $row->status_kehadiran)),
            $jadwal?->tanggal?->format('d-m-Y') ?? '-',
            $row->sesiRuangan->ruangan->nama_ruangan ?? '-',
            $row->sesiRuangan->nama_sesi ?? '-',
        ];
    }
}
