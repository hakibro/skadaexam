<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\SesiUjian;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JadwalUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JadwalUjian::with(['mapel', 'bankSoal', 'creator']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by mapel
        if ($request->has('mapel_id') && $request->mapel_id != '') {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('tanggal_ujian', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('tanggal_ujian', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'like', "%{$search}%")
                    ->orWhere('kode_ujian', 'like', "%{$search}%");
            });
        }

        $jadwalUjians = $query->orderBy('tanggal_ujian', 'desc')->paginate(10);
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();

        return view('features.naskah.jadwal.index', compact('jadwalUjians', 'mapels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        // $bankSoals = BankSoal::orderBy('judul', 'asc')->get();
        $bankSoals = BankSoal::withCount('soals')->orderBy('judul', 'asc')->get();

        return view('features.naskah.jadwal.create', compact('mapels', 'bankSoals'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'durasi_menit' => 'required|integer|min:1',
            'jumlah_soal' => 'required|integer|min:1',
            'jenis_ujian' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        // Generate unique exam code
        $kodeUjian = 'U' . date('Ymd') . strtoupper(Str::random(5));

        // Create new jadwal ujian
        $jadwalUjian = JadwalUjian::create([
            'kode_ujian' => $kodeUjian,
            'nama_ujian' => $request->nama_ujian,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $request->bank_soal_id,
            'jenis_ujian' => $request->jenis_ujian,
            'tanggal_ujian' => $request->tanggal_ujian,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'durasi_menit' => $request->durasi_menit,
            'jumlah_soal' => $request->jumlah_soal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwalUjian->id)
            ->with('success', 'Jadwal ujian berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalUjian $jadwal)
    {
        $jadwal->load(['mapel', 'bankSoal', 'creator', 'sesiUjians']);

        return view('features.naskah.jadwal.show', compact('jadwal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalUjian $jadwal)
    {
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $bankSoals = BankSoal::orderBy('judul', 'asc')->get();

        return view('features.naskah.jadwal.edit', compact('jadwal', 'mapels', 'bankSoals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalUjian $jadwal)
    {
        $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'durasi_menit' => 'required|integer|min:1',
            'jumlah_soal' => 'required|integer|min:1',
            'jenis_ujian' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $jadwal->update([
            'nama_ujian' => $request->nama_ujian,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $request->bank_soal_id,
            'jenis_ujian' => $request->jenis_ujian,
            'tanggal_ujian' => $request->tanggal_ujian,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'durasi_menit' => $request->durasi_menit,
            'jumlah_soal' => $request->jumlah_soal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', 'Jadwal ujian berhasil diperbarui');
    }

    /**
     * Update status of the resource.
     */
    public function updateStatus(Request $request, JadwalUjian $jadwal)
    {
        $request->validate([
            'status' => 'required|in:draft,aktif,selesai,dibatalkan',
        ]);

        $jadwal->update([
            'status' => $request->status,
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', 'Status jadwal ujian berhasil diperbarui');
    }

    /**
     * Attach an existing sesi to this jadwal
     */
    public function attachSesi(Request $request, JadwalUjian $jadwal)
    {
        $request->validate([
            'sesi_id' => 'required|exists:sesi_ujian,id',
        ]);

        $sesi = SesiUjian::findOrFail($request->sesi_id);

        // Update the sesi with the new jadwal
        $sesi->update([
            'jadwal_ujian_id' => $jadwal->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil ditambahkan ke jadwal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalUjian $jadwal)
    {
        // Check if there are any results associated with this exam
        if ($jadwal->hasilUjians()->count() > 0) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Jadwal ujian tidak dapat dihapus karena sudah memiliki hasil ujian');
        }

        $jadwal->delete();

        return redirect()->route('naskah.jadwal.index')
            ->with('success', 'Jadwal ujian berhasil dihapus');
    }
}
