<?php

namespace App\Exports;

use App\Models\EnrollmentUjian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentUjianExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query->with(['siswa.tahunAjaranRecords.kelas', 'jadwalUjian.mapel', 'sesiRuangan.ruangan', 'sesiRuanganSiswa'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'ID Yayasan',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jadwal Ujian',
            'Mata Pelajaran',
            'Tanggal Ujian',
            'Sesi',
            'Ruangan',
            'Status Enrollment',
            'Status Kehadiran',
            'Waktu Daftar',
        ];
    }

    /**
     * @param mixed $enrollment
     *
     * @return array
     */
    public function map($enrollment): array
    {
        $kelasTahun = $enrollment->siswa?->kelasForTahunAjaran($enrollment->jadwalUjian?->tahun_ajaran_id);
        $statusKehadiran = $enrollment->sesiRuanganSiswa?->status_kehadiran ?? '-';

        return [
            $enrollment->id,
            $enrollment->siswa->idyayasan ?? 'N/A',
            $enrollment->siswa->nis ?? 'N/A',
            $enrollment->siswa->nama ?? 'N/A',
            $kelasTahun->nama_kelas ?? ($enrollment->siswa->kelas->nama_kelas ?? 'N/A'),
            $enrollment->jadwalUjian->judul ?? 'N/A',
            $enrollment->jadwalUjian->mapel->nama_mapel ?? 'N/A',
            $enrollment->jadwalUjian->tanggal ? $enrollment->jadwalUjian->tanggal->format('d/m/Y') : 'N/A',
            $enrollment->sesiRuangan->nama_sesi ?? 'N/A',
            $enrollment->sesiRuangan->ruangan->nama_ruangan ?? 'N/A',
            $enrollment->status_enrollment,
            $statusKehadiran,
            $enrollment->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     *
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
