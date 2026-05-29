<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Features\Data\SiswaController as FeatureSiswaController;
use App\Services\SikeuApiService;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::query()->with('kelas');

        if ($request->filled('search') || $request->filled('q')) {
            $search = $request->filled('search') ? $request->get('search') : $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        if ($request->filled('idyayasan')) {
            $query->where('idyayasan', 'like', '%' . $request->idyayasan . '%');
        }

        if ($request->filled('tingkat')) {
            $query->whereHas('kelas', fn($q) => $q->where('tingkat', $request->tingkat));
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('kelas')) {
            $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', '%' . $request->kelas . '%'));
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('rekomendasi') || $request->filled('status_rekomendasi')) {
            $query->where('rekomendasi', $request->get('rekomendasi', $request->status_rekomendasi));
        }

        $perPage = min((int) $request->get('per_page', 50), 200);
        $siswas = $query->orderBy('nama')->paginate($perPage)->appends($request->query());

        $siswas->getCollection()->transform(fn($siswa) => $this->formatSiswa($siswa));

        return response()->json([
            'success' => true,
            'filters' => $request->only([
                'search',
                'q',
                'nama',
                'idyayasan',
                'tingkat',
                'kelas',
                'kelas_id',
                'status_pembayaran',
                'rekomendasi',
                'status_rekomendasi',
            ]),
            'meta' => [
                'current_page' => $siswas->currentPage(),
                'per_page' => $siswas->perPage(),
                'total' => $siswas->total(),
                'last_page' => $siswas->lastPage(),
            ],
            'data' => $siswas->items(),
        ]);
    }

    public function show($id)
    {
        $siswa = Siswa::with('kelas')->find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $this->formatSiswa($siswa),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'idyayasan' => 'required|string|max:50|unique:siswa,idyayasan',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:siswa,email',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status_pembayaran' => ['nullable', Rule::in(array_keys(Siswa::getStatusPembayaranOptions()))],
            'rekomendasi' => ['nullable', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'catatan_rekomendasi' => 'nullable|string|max:500',
        ]);

        $siswa = Siswa::create($validated)->load('kelas');

        return response()->json([
            'success' => true,
            'data' => $this->formatSiswa($siswa),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with('kelas')->find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'idyayasan' => ['sometimes', 'string', 'max:50', Rule::unique('siswa', 'idyayasan')->ignore($siswa->id)],
            'nama' => 'sometimes|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('siswa', 'email')->ignore($siswa->id)],
            'kelas_id' => 'nullable|exists:kelas,id',
            'status_pembayaran' => ['sometimes', Rule::in(array_keys(Siswa::getStatusPembayaranOptions()))],
            'rekomendasi' => ['sometimes', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'catatan_rekomendasi' => 'nullable|string|max:500',
        ]);

        $siswa->update($validated);

        return response()->json([
            'success' => true,
            'data' => $this->formatSiswa($siswa->fresh('kelas')),
        ]);
    }

    public function destroy($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }
        $siswa->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus']);
    }

    public function quickSync(Request $request, SikeuApiService $sikeuApiService)
    {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return app(FeatureSiswaController::class, [
            'sikeuApiService' => $sikeuApiService,
        ])->syncFromApi($request);
    }

    public function setRekomendasi(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'rekomendasi' => ['required_without:status_rekomendasi', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'status_rekomendasi' => ['required_without:rekomendasi', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'catatan_rekomendasi' => 'nullable|string|max:500',
        ]);

        $siswa->update([
            'rekomendasi' => $validated['rekomendasi'] ?? $validated['status_rekomendasi'],
            'catatan_rekomendasi' => $validated['catatan_rekomendasi'] ?? $siswa->catatan_rekomendasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status rekomendasi siswa berhasil diperbarui',
            'data' => $this->formatSiswa($siswa->fresh('kelas')),
        ]);
    }

    private function formatSiswa(Siswa $siswa): array
    {
        return [
            'id' => $siswa->id,
            'nis' => $siswa->nis,
            'idyayasan' => $siswa->idyayasan,
            'nama' => $siswa->nama,
            'email' => $siswa->email,
            'kelas' => $siswa->kelas ? [
                'id' => $siswa->kelas->id,
                'nama_kelas' => $siswa->kelas->nama_kelas,
                'tingkat' => $siswa->kelas->tingkat,
                'jurusan' => $siswa->kelas->jurusan,
            ] : null,
            'status_pembayaran' => $siswa->status_pembayaran,
            'rekomendasi' => $siswa->rekomendasi,
            'catatan_rekomendasi' => $siswa->catatan_rekomendasi,
        ];
    }
}
