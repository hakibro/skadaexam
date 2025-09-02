<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcaraUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaporanFixController extends Controller
{
    /**
     * Show specific berita acara
     */
    public function show(BeritaAcaraUjian $laporan)
    {
        // Determine the associated SesiRuangan
        $sesiRuangan = SesiRuangan::where('pengawas_id', $laporan->pengawas_id)->first();

        if ($sesiRuangan) {
            // Explicitly associate the SesiRuangan with the BeritaAcara
            $laporan->sesi_ruangan_id = $sesiRuangan->id;
            $laporan->save();

            // Now load all the relationships
            $laporan->load([
                'sesiRuangan.ruangan',
                'pengawas'
            ]);
        } else {
            // No matching SesiRuangan found
            Log::error('No SesiRuangan found for BeritaAcaraUjian #' . $laporan->id);
            abort(404, 'Data sesi ruangan tidak ditemukan untuk berita acara ini.');
        }

        return view('features.koordinator.laporan.show', ['beritaAcara' => $laporan]);
    }
}
