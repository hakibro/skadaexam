<?php

namespace App\Exports;

use App\Models\HasilUjian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class HasilUjianExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'ID',
            'Nama Siswa',
            'ID Yayasan',
            'Kelas',
            'Mata Pelajaran',
            'Nama Ujian',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi (menit)',
            'Jumlah Soal',
            'Benar',
            'Salah',
            'Tidak Dijawab',
            'Nilai',
            'Status',
            'Lulus',
            'Ruangan',
            'Sesi'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($hasil): array
    {
        return [
            $hasil->id,
            $hasil->siswa->nama ?? 'N/A',
            $hasil->siswa->idyayasan ?? 'N/A',
            $hasil->siswa->kelas->nama_kelas ?? 'N/A',
            $hasil->jadwalUjian->mapel->nama_mapel ?? 'N/A',
            $hasil->jadwalUjian->judul ?? 'N/A',
            $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d/m/Y H:i') : 'N/A',
            $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d/m/Y H:i') : 'N/A',
            $hasil->durasi_menit ?? 'N/A',
            $hasil->jumlah_soal,
            $hasil->jumlah_benar,
            $hasil->jumlah_salah,
            $hasil->jumlah_tidak_dijawab,
            number_format($hasil->nilai, 2),
            $hasil->status,
            $hasil->lulus ? 'Ya' : 'Tidak',
            $hasil->sesiRuangan->ruangan->nama_ruangan ?? 'N/A',
            $hasil->sesiRuangan->nama_sesi ?? 'N/A',
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
