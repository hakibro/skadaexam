<?php

namespace App\Exports;

use App\Models\HasilUjian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilUjianSimpleExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
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
        return $this->query->with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No.',
            'Nama Siswa',
            'ID Yayasan',
            'Kelas',
            'Mata Pelajaran',
            'Ujian',
            'Nilai',
            'Status',
            'Waktu Selesai',
            'Jumlah Soal',
            'Benar',
            'Salah'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $status = $row->status === 'selesai'
            ? ($row->lulus ? 'Lulus' : 'Tidak Lulus')
            : ucfirst(str_replace('_', ' ', $row->status));

        return [
            $row->id,
            $row->siswa->nama ?? 'N/A',
            $row->siswa->idyayasan ?? 'N/A',
            $row->siswa->kelas->nama_kelas ?? 'N/A',
            $row->jadwalUjian->mapel->nama_mapel ?? 'N/A',
            $row->jadwalUjian->judul ?? 'N/A',
            $row->status === 'selesai' ? number_format($row->nilai, 2) : '-',
            $status,
            $row->waktu_selesai ? $row->waktu_selesai->format('d/m/Y H:i') : '-',
            $row->jumlah_soal ?? 0,
            $row->jumlah_benar ?? 0,
            $row->jumlah_salah ?? 0,
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header row) as bold text with a thicker bottom border
            1 => [
                'font' => ['bold' => true],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'F2F2F2',
                    ],
                ],
            ],
        ];
    }
}
