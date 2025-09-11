<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ComprehensiveRuanganTemplateExport implements FromCollection, WithHeadings, WithStyles
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
                'aktif',
                'S001',
                'Ujian Matematika',
                '08:00',
                '10:00',
                'belum_mulai',
                'S12345',
                'John Doe'
            ],
            [
                'R001',
                'Ruang Kelas 1',
                30,
                'Gedung A Lantai 1',
                'aktif',
                'S001',
                'Ujian Matematika',
                '08:00',
                '10:00',
                'belum_mulai',
                'S12346',
                'Jane Smith'
            ],
            [
                'R002',
                'Laboratorium Komputer',
                25,
                'Gedung B Lantai 2',
                'aktif',
                'S002',
                'Ujian Bahasa Inggris',
                '10:30',
                '12:30',
                'belum_mulai',
                'S12345',
                'John Doe'
            ],
            [
                'R002',
                'Laboratorium Komputer',
                25,
                'Gedung B Lantai 2',
                'aktif',
                'S002',
                'Ujian Bahasa Inggris',
                '10:30',
                '12:30',
                'belum_mulai',
                'S12347',
                'Sarah Johnson'
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
            'kapasitas_ruangan',
            'lokasi_ruangan',
            'status_ruangan',
            'kode_sesi',
            'nama_sesi',
            'waktu_mulai_sesi',
            'waktu_selesai_sesi',
            'status_sesi',
            'idyayasan',
            'nama_siswa'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Add styles to the worksheet
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFont()->setSize(12);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');

        // Auto size columns
        foreach (range('A', 'L') as $col) {
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
        $sheet->setCellValue('A' . $lastRow, '2. status_ruangan diisi dengan: aktif, perbaikan, atau tidak_aktif.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '3. kode_sesi wajib diisi jika ingin membuat sesi ruangan.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '4. status_sesi diisi dengan: belum_mulai, berlangsung, selesai, atau dibatalkan.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '5. idyayasan wajib diisi jika ingin menambahkan siswa ke sesi.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '6. Format waktu: HH:MM (contoh: 08:30).');
        $lastRow++;

        $sheet->setCellValue('A' . ($lastRow + 1), 'Contoh di atas menunjukkan:');
        $sheet->getStyle('A' . ($lastRow + 1))->getFont()->setBold(true);
        $lastRow++;
        $sheet->setCellValue('A' . ($lastRow + 1), '- Ruangan R001 dengan 2 siswa (S12345 dan S12346) pada sesi S001.');
        $lastRow++;
        $sheet->setCellValue('A' . ($lastRow + 1), '- Ruangan R002 dengan 2 siswa (S12345 dan S12347) pada sesi S002.');

        // Make instructions stand out
        $sheet->getStyle('A' . ($lastRow - 8) . ':A' . ($lastRow + 2))->getFont()->setSize(11);

        return [
            // Add styles here
        ];
    }
}
