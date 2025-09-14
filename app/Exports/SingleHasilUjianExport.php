<?php

namespace App\Exports;

use App\Models\HasilUjian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SingleHasilUjianExport implements FromView
{
    protected $hasil;

    public function __construct(HasilUjian $hasil)
    {
        $this->hasil = $hasil;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $this->hasil->load(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas']);

        return view('exports.hasil-ujian-single', [
            'hasil' => $this->hasil,
        ]);
    }
}
