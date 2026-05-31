<?php

namespace App\Services;

use App\Models\PaketUjian;
use App\Models\TahunAjaran;

class TahunAjaranService
{
    public function active(): ?TahunAjaran
    {
        return TahunAjaran::active();
    }

    public function activeId(): ?int
    {
        return $this->active()?->id;
    }

    public function ensureActive(): TahunAjaran
    {
        $tahunAjaran = $this->active();

        if (!$tahunAjaran) {
            throw new \RuntimeException('Belum ada tahun ajaran aktif. Buat dan aktifkan tahun ajaran terlebih dahulu.');
        }

        return $tahunAjaran;
    }

    public function defaultPaketFor(TahunAjaran $tahunAjaran): PaketUjian
    {
        return PaketUjian::firstOrCreate(
            [
                'tahun_ajaran_id' => $tahunAjaran->id,
                'nama' => 'Paket Ujian Utama',
            ],
            [
                'tanggal_mulai' => $tahunAjaran->tanggal_mulai,
                'tanggal_selesai' => $tahunAjaran->tanggal_selesai,
                'status' => 'aktif',
                'keterangan' => 'Paket default untuk jadwal ujian pada tahun ajaran ini.',
            ]
        );
    }
}
