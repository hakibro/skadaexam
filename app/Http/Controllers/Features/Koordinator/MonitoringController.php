<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\JadwalUjian;
use App\Models\JadwalUjianSesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Ruangan;
use App\Models\Guru;
use App\Models\PelanggaranUjian;  // Include PelanggaranUjian model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Display monitoring dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedStatus = $request->get('status', 'all');
        $selectedRuangan = $request->get('ruangan_id', 'all');

        // Build query for sessions
        $query = SesiRuangan::with(['ruangan', 'sesiRuanganSiswa.siswa', 'jadwalUjians'])
            ->whereHas('jadwalUjians', function ($q) use ($selectedDate, $selectedStatus) {
                $q->whereDate('tanggal', $selectedDate);
                if ($selectedStatus != 'all') {
                    $q->where('status', $selectedStatus);
                }
            });

        if ($selectedRuangan != 'all') {
            $query->where('ruangan_id', $selectedRuangan);
        }

        $sessions = $query->get();

        // Fetch exam violations (PelanggaranUjian)
        $violationsQuery = PelanggaranUjian::with(['siswa', 'jadwalUjian', 'sesiRuangan'])
            ->whereDate('waktu_pelanggaran', $selectedDate);

        if ($selectedStatus != 'all') {
            $violationsQuery->where('is_finalized', $selectedStatus === 'finalized');
        }

        $violations = $violationsQuery->get();
        $rooms = Ruangan::all();  // Get all rooms

        // Return the view with the necessary data
        return view('features.koordinator.monitoring.index', [
            'sessions' => $sessions,
            'violations' => $violations,  // Pass violations to the view
            'selectedDate' => $selectedDate,
            'selectedStatus' => $selectedStatus,
            'selectedRuangan' => $selectedRuangan,
            'rooms' => $rooms,  // Pass rooms to the view
        ]);
    }
}
