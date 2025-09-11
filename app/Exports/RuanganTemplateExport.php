<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class RuanganTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Return sample data
        return new Collection([
            [
                'R001',
                'Ruang Kelas 1',
                30,
                'Gedung A Lantai 1',
                'kelas',
                'aktif',
                'Ruang kelas reguler'
            ],
            [
                'R002',
                'Laboratorium Komputer',
                25,
                'Gedung B Lantai 2',
                'laboratorium',
                'aktif',
                'Dilengkapi 25 unit komputer'
            ],
            [
                'R003',
                'Aula Utama',
                100,
                'Gedung C Lantai 1',
                'aula',
                'aktif',
                'Ruang serbaguna'
            ],
            [
                'R004',
                'Perpustakaan',
                40,
                'Gedung A Lantai 2',
                'perpustakaan',
                'aktif',
                'Area belajar perpustakaan'
            ],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'kode_ruangan',
            'nama_ruangan',
            'kapasitas',
            'lokasi',
            'jenis_ruangan',
            'status',
            'keterangan'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Add styles to the worksheet
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFont()->setSize(12);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add notes and instructions
        $lastRow = $sheet->getHighestRow() + 2;

        $sheet->setCellValue('A' . $lastRow, 'Instruksi Pengisian:');
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $lastRow)->getFont()->setSize(12);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '1. kode_ruangan wajib diisi dan harus unik.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '2. nama_ruangan wajib diisi.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '3. kapasitas wajib diisi dengan angka 1-1000.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '4. lokasi adalah opsional.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '5. jenis_ruangan diisi dengan: kelas, laboratorium, aula, perpustakaan, atau ruang_ujian.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '6. status diisi dengan: aktif, perbaikan, atau tidak_aktif.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '7. keterangan adalah opsional.');

        // Make instructions stand out
        $sheet->getStyle('A' . ($lastRow - 6) . ':A' . $lastRow)->getFont()->setSize(11);

        return [
            // Add styles here
        ];
    }
}
