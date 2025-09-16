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
        // Return sample data with different scenarios
        return new Collection([
            // Scenario 1: Complete data - create room, session, and assign students
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
                '255092',
                'ACHMAD AGUNG HERMANSYAH'
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
                '220119',
                'AHMAD NUR FATHONI'
            ],
            // Scenario 2: Only room data - create room without session
            [
                'R002',
                'Laboratorium Komputer',
                25,
                'Gedung B Lantai 2',
                'aktif',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            // Scenario 3: Assign students to existing session (empty room data)
            [
                '',
                '',
                '',
                '',
                '',
                'S001',
                '',
                '',
                '',
                '',
                '220696',
                'ANGGA SAPUTRA'
            ],
            [
                '',
                '',
                '',
                '',
                '',
                'S001',
                '',
                '',
                '',
                '',
                '220361',
                'AHMAD IBRAHIM MAOUVIQ'
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
            'status',
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
        $sheet->setCellValue('A' . $lastRow, '1. kode_ruangan: Wajib diisi untuk membuat/update ruangan. Kosongkan jika hanya assign siswa.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '2. status_ruangan: aktif, perbaikan, atau tidak_aktif.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '3. kode_sesi: Wajib diisi untuk membuat sesi atau assign siswa ke sesi.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '4. status_sesi: belum_mulai, berlangsung, selesai, atau dibatalkan.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '5. idyayasan: ID siswa (bisa berupa angka atau string). Wajib untuk assignment siswa.');
        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, '6. Format waktu: HH:MM (contoh: 08:30).');
        $lastRow++;

        $sheet->setCellValue('A' . ($lastRow + 1), 'Contoh skenario di atas:');
        $sheet->getStyle('A' . ($lastRow + 1))->getFont()->setBold(true);
        $lastRow++;
        $sheet->setCellValue('A' . ($lastRow + 1), '- Baris 1-2: Buat ruangan R001 dengan sesi S001 dan 2 siswa.');
        $lastRow++;
        $sheet->setCellValue('A' . ($lastRow + 1), '- Baris 3: Buat hanya ruangan R002 tanpa sesi.');
        $lastRow++;
        $sheet->setCellValue('A' . ($lastRow + 1), '- Baris 4-5: Assign 2 siswa lagi ke sesi S001 yang sudah ada.');

        // Make instructions stand out
        $sheet->getStyle('A' . ($lastRow - 8) . ':A' . ($lastRow + 2))->getFont()->setSize(11);

        return [
            // Add styles here
        ];
    }
}
