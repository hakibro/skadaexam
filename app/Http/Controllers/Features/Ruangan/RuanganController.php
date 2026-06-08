<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\PaketUjian;
use App\Models\Ruangan;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\SiswaTahunAjaran;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class RuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahunAjaranService = app(TahunAjaranService::class);
        $activeYear = $tahunAjaranService->active();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear?->id);
        $paketUjianId = null;
        $showAllPaket = $request->get('paket_ujian_id') === '__all';

        if ($request->has('paket_ujian_id')) {
            $paketUjianId = $showAllPaket ? null : $request->get('paket_ujian_id');
        } elseif ($tahunAjaranId) {
            $activePaket = PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)
                ->where('status', 'aktif')
                ->orderByDesc('tanggal_mulai')
                ->orderBy('nama')
                ->first();

            if (!$activePaket && $activeYear && (string) $tahunAjaranId === (string) $activeYear->id) {
                $activePaket = $tahunAjaranService->defaultPaketFor($activeYear);
            }

            $paketUjianId = $activePaket?->id;
        }

        $query = Ruangan::forTahunAjaran($tahunAjaranId)
            ->forPaketUjian($paketUjianId)
            ->with(['paketUjian'])
            ->withCount('sesiRuangan');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kapasitas
        if ($request->filled('kapasitas_min')) {
            $query->where('kapasitas', '>=', $request->kapasitas_min);
        }

        if ($request->filled('kapasitas_max')) {
            $query->where('kapasitas', '<=', $request->kapasitas_max);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_ruangan', 'like', "%{$search}%")
                    ->orWhere('kode_ruangan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        match ($request->input('sort', 'nama_asc')) {
            'nama_desc' => $query->orderByDesc('nama_ruangan'),
            'kapasitas_asc' => $query->orderBy('kapasitas')->orderBy('nama_ruangan'),
            'kapasitas_desc' => $query->orderByDesc('kapasitas')->orderBy('nama_ruangan'),
            'created_at_asc' => $query->orderBy('created_at'),
            'created_at_desc' => $query->orderByDesc('created_at'),
            default => $query->orderBy('nama_ruangan'),
        };

        $ruangans = $query->paginate(15);

        // Calculate statistics for the view
        $statistics = [
            'total' => Ruangan::forTahunAjaran($tahunAjaranId)->forPaketUjian($paketUjianId)->count(),
            'aktif' => Ruangan::forTahunAjaran($tahunAjaranId)->forPaketUjian($paketUjianId)->where('status', 'aktif')->count(),
            'nonaktif' => Ruangan::forTahunAjaran($tahunAjaranId)->forPaketUjian($paketUjianId)->where('status', 'tidak_aktif')->count(),
            'perbaikan' => Ruangan::forTahunAjaran($tahunAjaranId)->forPaketUjian($paketUjianId)->where('status', 'perbaikan')->count(),
        ];
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $paketUjians = PaketUjian::when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('features.ruangan.index', compact('ruangans', 'statistics', 'tahunAjarans', 'tahunAjaranId', 'paketUjians', 'paketUjianId', 'showAllPaket'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            app(TahunAjaranService::class)->ensureActive();
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum membuat ruangan.');
        }

        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $paketUjians = PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();
        $defaultPaketId = app(TahunAjaranService::class)->defaultPaketFor($activeYear)?->id;

        return view('features.ruangan.create', compact('paketUjians', 'defaultPaketId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:191',
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan',
            'kapasitas' => 'required|integer|min:1|max:1000',
            'lokasi' => 'nullable|string|max:191',
            'fasilitas' => 'nullable|array',
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
            'paket_ujian_id' => 'nullable|exists:paket_ujian,id',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $activeYear = app(TahunAjaranService::class)->ensureActive();
            $ruangan = Ruangan::create([
                'tahun_ajaran_id' => $activeYear->id,
                'paket_ujian_id' => $request->paket_ujian_id ?: null,
                'nama_ruangan' => $request->nama_ruangan,
                'kode_ruangan' => $request->kode_ruangan,
                'kapasitas' => $request->kapasitas,
                'lokasi' => $request->lokasi,
                'fasilitas' => $request->fasilitas ?? [],
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return redirect()->route('ruangan.show', $ruangan->id)
                ->with('success', 'Ruangan berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ruangan $ruangan)
    {
        // Load relationships dengan proper counting
        $ruangan->loadCount([
            'sesiRuangan as sesi_ruangan_count',
            'sesiRuangan as active_sessions_count' => function ($query) {
                $query->where('status', 'berlangsung');
            }
        ]);

        // Get active sessions count
        $activeSessions = $ruangan->sesiRuangan()
            ->where('status', 'berlangsung')
            ->orWhere(function ($query) {
                $query->where('status', 'belum_mulai')
                    ->whereHas('jadwalUjians', function ($q) {
                        $q->whereDate('tanggal', now()->toDateString());
                    })
                    ->where('waktu_mulai', '<=', now()->format('H:i:s'))
                    ->where('waktu_selesai', '>=', now()->format('H:i:s'));
            })
            ->count();

        // Get total participants
        $totalParticipants = DB::table('sesi_ruangan_siswa')
            ->join('sesi_ruangan', 'sesi_ruangan_siswa.sesi_ruangan_id', '=', 'sesi_ruangan.id')
            ->where('sesi_ruangan.ruangan_id', $ruangan->id)
            ->count();

        // Count total sessions
        $sesiCount = $ruangan->sesiRuangan()->count();

        // Get recent sessions
        $recentSessions = $ruangan->sesiRuangan()
            ->with(['jadwalUjians', 'jadwalUjians.mapel'])
            ->withCount('sesiRuanganSiswa')
            ->orderBy('waktu_mulai', 'desc')
            ->take(5)
            ->get();

        // Get today's sessions
        $todaySessions = $ruangan->sesiRuangan()
            ->whereHas('jadwalUjians', function ($query) {
                $query->whereDate('tanggal', now()->toDateString());
            })
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Get all sessions for this room
        $sesiRuangan = $ruangan->sesiRuangan()
            ->with(['jadwalUjians', 'jadwalUjians.mapel'])
            ->withCount('sesiRuanganSiswa')
            ->orderBy('id', 'desc')  // Menggunakan 'id' sebagai pengganti 'created_at'
            ->get();

        // Generate calendar data for the current month
        $calendarDays = [];
        $sessionsData = [];

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Include days from previous month to fill the first week
        $startDay = $startOfMonth->copy()->startOfWeek();

        // Include days from next month to fill the last week
        $endDay = $endOfMonth->copy()->endOfWeek();

        // Loop through all days
        for ($day = $startDay; $day->lte($endDay); $day->addDay()) {
            $date = $day->format('Y-m-d');

            // Get sessions for this day
            $daySessions = $ruangan->sesiRuangan()
                ->whereHas('jadwalUjians', function ($query) use ($date) {
                    $query->whereDate('tanggal', $date);
                })
                ->get();

            // Count sessions by status
            $sessionCounts = [
                'belum_mulai' => $daySessions->where('status', 'belum_mulai')->count(),
                'berlangsung' => $daySessions->where('status', 'berlangsung')->count(),
                'selesai' => $daySessions->where('status', 'selesai')->count()
            ];

            // Add to calendar data
            $calendarDays[] = [
                'date' => $date,
                'day' => $day->day,
                'isCurrentMonth' => $day->month === now()->month,
                'isToday' => $day->isToday(),
                'hasSessions' => $daySessions->isNotEmpty(),
                'sessionCounts' => $sessionCounts
            ];

            // Add to sessions data for the modal
            if ($daySessions->isNotEmpty()) {
                $sessionsData[$date] = $daySessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'ruangan_id' => $session->ruangan_id,
                        'nama_sesi' => $session->nama_sesi,
                        'kode_sesi' => $session->kode_sesi,
                        'status' => $session->status,
                        'status_label' => $session->status_label['text'],
                        'waktu_mulai' => \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i'),
                        'waktu_selesai' => \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i'),
                        'siswa_count' => $session->sesiRuanganSiswa()->count(),
                        'kapasitas' => $session->ruangan->kapasitas,
                        'jadwal_count' => $session->jadwalUjians->count(),
                    ];
                })->toArray();
            }
        }

        return view('features.ruangan.show', compact(
            'ruangan',
            'activeSessions',
            'totalParticipants',
            'recentSessions',
            'sesiCount',
            'todaySessions',
            'sesiRuangan',
            'calendarDays',
            'sessionsData'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ruangan $ruangan)
    {
        $paketUjians = PaketUjian::where('tahun_ajaran_id', $ruangan->tahun_ajaran_id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('features.ruangan.edit', compact('ruangan', 'paketUjians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:191',
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan,' . $ruangan->id,
            'kapasitas' => 'required|integer|min:1|max:1000',
            'lokasi' => 'nullable|string|max:191',
            'fasilitas' => 'nullable|array',
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
            'paket_ujian_id' => 'nullable|exists:paket_ujian,id',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $ruangan->update([
                'nama_ruangan' => $request->nama_ruangan,
                'kode_ruangan' => $request->kode_ruangan,
                'paket_ujian_id' => $request->paket_ujian_id ?: null,
                'kapasitas' => $request->kapasitas,
                'lokasi' => $request->lokasi,
                'fasilitas' => $request->fasilitas ?? [],
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            $ruangan->sesiRuangan()
                ->where('sumber', 'sumber')
                ->update(['paket_ujian_id' => $ruangan->paket_ujian_id]);

            return redirect()->route('ruangan.show', $ruangan)
                ->with('success', 'Ruangan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Update status of the resource.
     */
    public function updateStatus(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
        ]);

        try {
            $ruangan->update([
                'status' => $request->status,
            ]);

            return redirect()->back()
                ->with('success', 'Status ruangan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ruangan status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruangan $ruangan)
    {
        try {
            // Check if there are any sesi associated with this room
            if ($ruangan->sesiRuangan()->count() > 0) {
                return redirect()->route('ruangan.index')
                    ->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki sesi ujian');
            }

            $ruangan->delete();

            return redirect()->route('ruangan.index')
                ->with('success', 'Ruangan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the room and all related sessions
     */
    public function forceDelete(Ruangan $ruangan)
    {
        try {
            DB::beginTransaction();

            // Get count of sessions before deletion
            $sesiCount = $ruangan->sesiRuangan()->count();

            // Delete all sessions related to this room first
            $ruangan->sesiRuangan()->each(function ($sesi) {
                // Delete all student enrollments for this session
                $sesi->sesiRuanganSiswa()->delete();
                // Delete the session
                $sesi->delete();
            });

            // Now delete the room
            $ruangan->delete();

            DB::commit();

            return redirect()->route('ruangan.index')
                ->with('success', "Ruangan berhasil dihapus paksa beserta $sesiCount sesi terkait");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus paksa ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Import multiple ruangan
     */
    public function import()
    {
        try {
            app(TahunAjaranService::class)->ensureActive();
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum import ruangan.');
        }

        return view('features.ruangan.import');
    }

    /**
     * Process ruangan import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'ruangan_data' => 'required|array',
            'ruangan_data.*.nama_ruangan' => 'required|string|max:191',
            'ruangan_data.*.kode_ruangan' => 'required|string|max:20|distinct|unique:ruangan,kode_ruangan',
            'ruangan_data.*.kapasitas' => 'required|integer|min:1|max:1000',
            'ruangan_data.*.lokasi' => 'nullable|string|max:191',
            'ruangan_data.*.status' => 'nullable|in:aktif,perbaikan,tidak_aktif',
        ]);

        try {
            DB::beginTransaction();
            $activeYear = app(TahunAjaranService::class)->ensureActive();

            $ruanganData = $request->ruangan_data;
            $imported = 0;

            foreach ($ruanganData as $data) {
                Ruangan::create([
                    'tahun_ajaran_id' => $activeYear->id,
                    'nama_ruangan' => $data['nama_ruangan'],
                    'kode_ruangan' => $data['kode_ruangan'],
                    'kapasitas' => $data['kapasitas'],
                    'lokasi' => $data['lokasi'] ?? null,
                    'fasilitas' => $data['fasilitas'] ?? [],
                    'status' => $data['status'] ?? 'aktif',
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
                $imported++;
            }

            DB::commit();

            return redirect()->route('ruangan.index')
                ->with('success', $imported . ' ruangan berhasil diimpor');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengimpor ruangan: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        // Pastikan $ids selalu array
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        // Buang kemungkinan nilai kosong
        $ids = array_filter($ids);
        $ids = Ruangan::whereIn('id', $ids)
            ->whereDoesntHave('tahunAjaran', fn($query) => $query->where('status', 'arsip'))
            ->pluck('id')
            ->all();

        if (count($ids) === 0) {
            return redirect()->back()->with('error', 'Tidak ada item aktif yang dapat diproses.');
        }

        try {
            switch ($action) {
                case 'hapus':
                    try {
                        Ruangan::whereIn('id', $ids)->delete();
                        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Cek kode error MySQL 1451 = cannot delete or update a parent row
                        if ($e->errorInfo[1] == 1451) {
                            return redirect()->back()->with('error_with_force', [
                                'message' => 'Tidak bisa menghapus ruangan karena masih ada data terkait.',
                                'ids' => implode(',', $ids)
                            ]);
                        }
                        throw $e; // biarkan error lain jalan normal
                    }

                // case 'hapus_paksa':
                //     foreach ($ids as $id) {
                //         Ruangan::where('id', $id)->forceDelete(); // atau delete dengan disable fk
                //     }
                //     return redirect()->back()->with('success', 'Ruangan dan semua data terkait berhasil dihapus paksa.');

                case 'hapus_paksa':
                    foreach ($ids as $id) {
                        // Ambil semua sesi dalam ruangan ini
                        $sesiIds = DB::table('sesi_ruangan')->where('ruangan_id', $id)->pluck('id');

                        foreach ($sesiIds as $sesiId) {
                            // Cari hasil ujian dalam sesi ini
                            $hasilUjianIds = DB::table('hasil_ujian')->where('sesi_ruangan_id', $sesiId)->pluck('id');

                            // Hapus pelanggaran ujian yang terkait hasil ujian
                            DB::table('pelanggaran_ujian')->whereIn('hasil_ujian_id', $hasilUjianIds)->delete();

                            // Hapus hasil ujian
                            DB::table('hasil_ujian')->whereIn('id', $hasilUjianIds)->delete();

                            // Hapus sesi ruangan
                            DB::table('sesi_ruangan')->where('id', $sesiId)->delete();
                        }

                        // Hapus ruangannya
                        DB::table('ruangan')->where('id', $id)->delete();
                    }

                    return redirect()->back()->with('success', 'Ruangan dan semua data terkait berhasil dihapus paksa.');



                case 'aktifkan':
                    Ruangan::whereIn('id', $ids)->update(['status' => 'aktif']);
                    return redirect()->back()->with('success', 'Ruangan berhasil diaktifkan.');

                case 'nonaktifkan':
                    Ruangan::whereIn('id', $ids)->update(['status' => 'tidak_aktif']);
                    return redirect()->back()->with('success', 'Ruangan berhasil dinonaktifkan.');

                case 'perbaikan':
                    Ruangan::whereIn('id', $ids)->update(['status' => 'perbaikan']);
                    return redirect()->back()->with('success', 'Ruangan berhasil ditandai untuk perbaikan.');

                default:
                    return redirect()->back()->with('error', 'Aksi tidak dikenal.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') { // Integrity constraint violation
                return redirect()->back()->with('error', 'Tidak bisa menghapus karena ada data terkait. 
                Klik tombol Hapus Paksa jika ingin menghapus beserta semua data terkait.');
            }
            throw $e; // biar error lain tetap muncul
        }
    }



    /**
     * Bulk delete rooms
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:ruangan,id'
        ]);

        try {
            DB::beginTransaction();

            $roomIds = $request->room_ids;
            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($roomIds as $roomId) {
                $room = Ruangan::find($roomId);
                if ($room) {
                    if ($room->tahunAjaran?->isReadOnly()) {
                        $skippedCount++;
                        continue;
                    }

                    if ($room->sesiRuangan()->count() > 0) {
                        $skippedCount++;
                    } else {
                        $room->delete();
                        $deletedCount++;
                    }
                }
            }

            DB::commit();

            $message = $deletedCount . ' ruangan berhasil dihapus';
            if ($skippedCount > 0) {
                $message .= '. ' . $skippedCount . ' ruangan dilewati karena masih memiliki sesi ujian';
            }

            return redirect()->route('ruangan.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Show comprehensive import page for rooms, sessions and students
     */
    public function importComprehensive()
    {
        try {
            $activeYear = app(TahunAjaranService::class)->ensureActive();
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum import komprehensif.');
        }

        $paketUjians = PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('features.ruangan.import-comprehensive', compact('paketUjians'));
    }

    /**
     * Process comprehensive import for rooms, sessions and students
     */
    public function processComprehensiveImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
            'paket_ujian_id' => 'nullable|exists:paket_ujian,id',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('import_file');
            $activeYear = app(TahunAjaranService::class)->ensureActive();
            $import = new \App\Imports\ComprehensiveRuanganImport($activeYear->id, $request->paket_ujian_id ?: null);
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);

            $results = $import->getImportResults();

            DB::commit();

            return redirect()->route('ruangan.import.comprehensive')
                ->with('success', "Import selesai: {$results['ruangan_created']} ruangan baru, " .
                    "{$results['ruangan_updated']} ruangan diupdate, " .
                    "{$results['sesi_created']} sesi baru, " .
                    "{$results['sesi_updated']} sesi diupdate, " .
                    "{$results['siswa_assigned']} siswa ditambahkan ke sesi");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            $failures = $e->failures();
            $error = 'Error validasi pada baris: ';
            $error .= implode(', ', array_map(function ($failure) {
                return $failure->row() . ' (' . $failure->errors()[0] . ')';
            }, $failures));

            return redirect()->back()
                ->withInput()
                ->with('error', $error);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Comprehensive import failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download comprehensive import template
     */
    public function downloadComprehensiveTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ComprehensiveRuanganTemplateExport(),
            'template_import_ruangan_sesi_siswa.xlsx'
        );
    }

    public function downloadDataSiswa()
    {
        // Ambil data dari API
        $response = Http::get('https://api.daruttaqwa.or.id/sisda/v1/exam/smk/uts1');
        $data = $response->json()['data'] ?? [];

        // Mapping data
        $collection = collect($data)->map(function ($item) {
            return [
                'ID Person' => $item['idperson'] ?? '',
                'Nama' => $item['nama'] ?? '',
                'Kelas' => $item['KelasFormal'] ?? '',
                'AsramaPondok' => $item['AsramaPondok'] ?? '',
                'KelasPondok' => $item['KelasPondok'] ?? '',
                'Kategori' => $item['AsramaPondok'] ? 'Pondok' : 'Non Pondok',
            ];
        });

        // Export ke Excel
        return Excel::download(
            new class ($collection) implements FromCollection, WithHeadings {
            protected $collection;

            public function __construct(Collection $collection)
            {
                $this->collection = $collection;
            }

            public function collection()
            {
                return $this->collection;
            }

            public function headings(): array
            {
                return [
                'ID Person',
                'Nama',
                'Kelas',
                'AsramaPondok',
                'KelasPondok',
                'Kategori'
                ];
            }
            },
            'data_siswa_pondok_nonpondok.xlsx'
        );
    }

    public function examCards(Request $request)
    {
        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear->id);

        $tingkatList = Kelas::forTahunAjaran($tahunAjaranId)
            ->whereNotNull('tingkat')
            ->select('tingkat')
            ->distinct()
            ->orderBy('tingkat')
            ->pluck('tingkat');
        $kelasList = Kelas::forTahunAjaran($tahunAjaranId)->orderBy('nama_kelas')->get();
        $paketUjians = PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();
        $selectedPaketId = $request->get('paket_ujian_id') ?: $paketUjians->firstWhere('status', 'aktif')?->id;

        $studentQuery = Siswa::query()
            ->with(['tahunAjaranRecords' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas')])
            ->when($selectedPaketId, function ($q) use ($tahunAjaranId, $selectedPaketId) {
                $q->whereHas('sesiRuangan', function ($sesi) use ($tahunAjaranId, $selectedPaketId) {
                    $sesi->where('sesi_ruangan.tahun_ajaran_id', $tahunAjaranId)
                        ->where('sesi_ruangan.sumber', 'sumber')
                        ->where('sesi_ruangan.paket_ujian_id', $selectedPaketId);
                });
            }, fn($q) => $q->whereRaw('1 = 0'))
            ->whereHas('tahunAjaranRecords', function ($q) use ($request, $tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);

                if ($request->filled('kelas_id')) {
                    $q->where('kelas_id', $request->kelas_id);
                }

                if ($request->filled('tingkat')) {
                    $q->whereHas('kelas', fn($kelas) => $kelas->where('tingkat', $request->tingkat));
                }
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $studentQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%");
            });
        }

        $students = $studentQuery
            ->orderByRaw(
                '(select kelas.nama_kelas from siswa_tahun_ajaran join kelas on kelas.id = siswa_tahun_ajaran.kelas_id where siswa_tahun_ajaran.siswa_id = siswa.id and siswa_tahun_ajaran.tahun_ajaran_id = ? limit 1) asc',
                [$tahunAjaranId]
            )
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        return view('features.ruangan.kartu-ujian.index', compact(
            'students',
            'tingkatList',
            'kelasList',
            'paketUjians',
            'tahunAjaranId',
            'selectedPaketId'
        ));
    }

    public function printExamCards(Request $request)
    {
        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear->id);
        $paketUjian = $request->filled('paket_ujian_id')
            ? PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)->findOrFail($request->paket_ujian_id)
            : PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)->where('status', 'aktif')->first();
        $mode = $request->get('mode', 'front');

        $records = SiswaTahunAjaran::with(['siswa', 'kelas'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->when($paketUjian, function ($q) use ($tahunAjaranId, $paketUjian) {
                $q->whereHas('siswa.sesiRuangan', function ($sesi) use ($tahunAjaranId, $paketUjian) {
                    $sesi->where('sesi_ruangan.tahun_ajaran_id', $tahunAjaranId)
                        ->where('sesi_ruangan.sumber', 'sumber')
                        ->where('sesi_ruangan.paket_ujian_id', $paketUjian->id);
                });
            }, fn($q) => $q->whereRaw('1 = 0'))
            ->when($request->filled('tingkat'), fn($q) => $q->whereHas('kelas', fn($kelas) => $kelas->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->whereHas('siswa', function ($q) use ($request) {
                if ($request->filled('search')) {
                    $search = $request->search;
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('idyayasan', 'like', "%{$search}%");
                }
            })
            ->get()
            ->sort(function ($a, $b) {
                $kelasCompare = strnatcasecmp($a->kelas?->nama_kelas ?? '', $b->kelas?->nama_kelas ?? '');

                if ($kelasCompare !== 0) {
                    return $kelasCompare;
                }

                return strnatcasecmp($a->siswa?->nama ?? '', $b->siswa?->nama ?? '');
            })
            ->values();

        $jadwals = JadwalUjian::with('mapel')
            ->forTahunAjaran($tahunAjaranId)
            ->when($paketUjian, fn($q) => $q->where('paket_ujian_id', $paketUjian->id))
            ->orderBy('tanggal')
            ->get();

        $sourceSessions = $this->sourceSessionMapForCards($tahunAjaranId, $paketUjian?->id);
        $cards = $records->map(function ($record) use ($jadwals, $sourceSessions) {
            $kelas = $record->kelas;
            $studentJadwals = $jadwals->filter(fn($jadwal) => $this->jadwalMatchesKelas($jadwal, $kelas))
                ->groupBy(fn($jadwal) => $jadwal->tanggal?->format('Y-m-d') ?: '-');

            return [
                'siswa' => $record->siswa,
                'kelas' => $kelas,
                'source_session' => $sourceSessions[$record->siswa_id] ?? null,
                'jadwals' => $studentJadwals,
            ];
        });

        return view('features.ruangan.kartu-ujian.print', [
            'cards' => $cards,
            'settings' => SchoolSetting::allAsArray(),
            'tahunAjaran' => TahunAjaran::find($tahunAjaranId),
            'paketUjian' => $paketUjian,
            'mode' => $mode,
        ]);
    }

    private function sourceSessionMapForCards(int $tahunAjaranId, ?int $paketUjianId = null): array
    {
        $rows = DB::table('sesi_ruangan_siswa')
            ->join('sesi_ruangan', 'sesi_ruangan_siswa.sesi_ruangan_id', '=', 'sesi_ruangan.id')
            ->join('ruangan', 'sesi_ruangan.ruangan_id', '=', 'ruangan.id')
            ->where('sesi_ruangan.tahun_ajaran_id', $tahunAjaranId)
            ->where('sesi_ruangan.sumber', 'sumber')
            ->when($paketUjianId, fn($q) => $q->where('sesi_ruangan.paket_ujian_id', $paketUjianId))
            ->select(
                'sesi_ruangan_siswa.siswa_id',
                'sesi_ruangan.nama_sesi',
                'sesi_ruangan.kode_sesi',
                'sesi_ruangan.waktu_mulai',
                'sesi_ruangan.waktu_selesai',
                'ruangan.nama_ruangan',
                'ruangan.kode_ruangan'
            )
            ->orderBy('ruangan.kode_ruangan')
            ->orderBy('sesi_ruangan.nama_sesi')
            ->get();

        return $rows->keyBy('siswa_id')->all();
    }

    private function jadwalMatchesKelas(JadwalUjian $jadwal, ?Kelas $kelas): bool
    {
        if (!$kelas) {
            return false;
        }

        $targets = collect($jadwal->kelas_target ?? [])->map(fn($id) => (string) $id)->all();
        if (!empty($targets) && !in_array((string) $kelas->id, $targets, true)) {
            return false;
        }

        if ($jadwal->mapel?->tingkat && strtoupper((string) $jadwal->mapel->tingkat) !== strtoupper((string) $kelas->tingkat)) {
            return false;
        }

        $mapelJurusan = strtoupper((string) ($jadwal->mapel?->jurusan ?? ''));
        $kelasJurusan = strtoupper((string) ($kelas->jurusan ?? ''));

        return $mapelJurusan === '' || $mapelJurusan === 'UMUM' || $mapelJurusan === $kelasJurusan;
    }

    /**
     * Download basic ruangan import template
     */
    public function downloadRuanganTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RuanganTemplateExport(),
            'template_import_ruangan.xlsx'
        );
    }
}
