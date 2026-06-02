<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NaskahComprehensiveTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection(): Collection
    {
        return collect([
            ['Matematika', 'X', 'RPL', 'Bank Matematika X RPL', 'uas', 'Matematika', '2026-06-10', 45, 'X RPL 1,X RPL 2'],
            ['Bahasa Indonesia', 'XI', 'UMUM', 'Bank Bahasa Indonesia XI', '', 'Bahasa Indonesia', '2026-06-11', 45, ''],
        ]);
    }

    public function headings(): array
    {
        return [
            'nama_mapel',
            'tingkat',
            'jurusan',
            'judul_bank_soal',
            'jenis_soal',
            'judul_ujian',
            'tanggal',
            'durasi_menit',
            'kelas_target',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD9EAD3');

        $instructionRow = $sheet->getHighestRow() + 2;
        $sheet->setCellValue('A' . $instructionRow, 'Instruksi:');
        $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);

        $instructions = [
            'nama_mapel dan tingkat wajib diisi.',
            'tingkat: X, XI, atau XII.',
            'jenis_soal boleh kosong; sistem akan mengikuti paket ujian aktif jika memungkinkan.',
            'tanggal bisa berupa date cell Excel, YYYY-MM-DD, atau DD/MM/YYYY.',
            'kelas_target boleh kosong. Jika diisi, pisahkan nama kelas dengan koma.',
            'Import ini membuat/memperbarui mata pelajaran, bank soal, dan jadwal ujian. Soal tetap diimport dari detail bank soal.',
        ];

        foreach ($instructions as $offset => $instruction) {
            $sheet->setCellValue('A' . ($instructionRow + $offset + 1), ($offset + 1) . '. ' . $instruction);
        }

        return [];
    }
}
