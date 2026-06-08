<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SoalRichTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nomor_soal',
            'tipe_soal',
            'pertanyaan',
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'kunci_jawaban',
            'interactive_left',
            'interactive_right',
            'interactive_items',
            'interactive_zones',
            'audio_path',
            'pembahasan',
            'kategori',
            'bobot',
        ];
    }

    public function array(): array
    {
        return [
            [1, 'pilihan_ganda', 'Ibukota Indonesia adalah ...', 'Jakarta', 'Bandung', 'Surabaya', 'Medan', '', 'A', '', '', '', '', '', 'Jakarta adalah ibukota Indonesia.', 'umum', 1],
            [2, 'pilihan_kompleks', 'Pilih bilangan genap.', '1', '2', '3', '4', '5', 'B,D', '', '', '', '', '', '', 'umum', 1],
            [3, 'benar_salah', 'Matahari terbit dari timur.', 'Benar', 'Salah', '', '', '', 'A', '', '', '', '', '', '', 'umum', 1],
            [4, 'isian_singkat', 'Proses pembuatan makanan pada tumbuhan disebut ...', '', '', '', '', '', 'fotosintesis|fotosintesa', '', '', '', '', '', '', 'umum', 1],
            [5, 'teks_rumpang', 'Tumbuhan membuat makanan melalui [[fotosintesis]].', '', '', '', '', '', '', '', '', '', '', '', '', 'umum', 1],
            [6, 'menjodohkan', 'Jodohkan negara dan ibukotanya.', '', '', '', '', '', '', 'Indonesia|Jepang', 'Jakarta|Tokyo', '', '', '', '', 'umum', 1],
            [7, 'mengurutkan', 'Urutkan proses berikut.', '', '', '', '', '', '', '', '', 'Perencanaan|Pelaksanaan|Evaluasi', '', '', '', 'umum', 1],
            [8, 'drag_drop', 'Tempatkan item ke area yang tepat.', '', '', '', '', '', '', '', '', 'Air|Api', 'Cair|Panas', '', '', 'umum', 1],
            [9, 'listening', 'Dengarkan audio lalu pilih jawaban.', 'A', 'B', 'C', 'D', '', 'A', '', '', '', '', 'contoh.mp3', '', 'umum', 1],
        ];
    }
}
