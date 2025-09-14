<?php

namespace App\Exports;

use App\Models\HasilUjian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class HasilUjianPdfExport implements FromView
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $hasilUjians = $this->query->with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas'])->get();

        return view('exports.hasil-ujian-pdf', [
            'hasilUjians' => $hasilUjians,
        ]);
    }
}
