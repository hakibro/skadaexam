<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Features\Data\SiswaController as FeatureSiswaController;
use App\Services\SikeuApiService;
use App\Services\TahunAjaranService;
use App\Models\Siswa;
use App\Models\SiswaTahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id', app(TahunAjaranService::class)->activeId());
        $query = Siswa::query()->with(['kelas', 'tahunAjaranRecords.kelas']);

        if ($tahunAjaranId) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

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
            $query->whereHas('tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereHas('kelas', fn($kelas) => $kelas->where('tingkat', $request->tingkat)));
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('kelas_id', $request->kelas_id)
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('kelas')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereHas('kelas', fn($kelas) => $kelas->where('nama_kelas', 'like', '%' . $request->kelas . '%')));
        }

        if ($request->filled('status_pembayaran')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('status_pembayaran', $request->status_pembayaran)
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('rekomendasi') || $request->filled('status_rekomendasi')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('rekomendasi', $request->get('rekomendasi', $request->status_rekomendasi))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
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
                'tahun_ajaran_id',
            ]),
            'meta' => [
                'current_page' => $siswas->currentPage(),
                'per_page' => $siswas->perPage(),
                'total' => $siswas->total(),
                'last_page' => $siswas->lastPage(),
            ],
            'tahun_ajaran_id' => $tahunAjaranId,
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

        $tahunAjaranId = app(TahunAjaranService::class)->ensureActive()->id;
        $siswa = Siswa::create(collect($validated)->only(['idyayasan', 'nama', 'email'])->all());

        SiswaTahunAjaran::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $tahunAjaranId,
            ],
            [
                'kelas_id' => $validated['kelas_id'] ?? null,
                'status_siswa' => 'aktif',
                'status_pembayaran' => $validated['status_pembayaran'] ?? 'Belum Lunas',
                'rekomendasi' => $validated['rekomendasi'] ?? 'tidak',
                'catatan' => $validated['catatan_rekomendasi'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $this->formatSiswa($siswa->fresh('tahunAjaranRecords.kelas')),
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

        $siswa->update(collect($validated)->only(['idyayasan', 'nama', 'email'])->all());

        $tahunAjaranId = $request->get('tahun_ajaran_id', app(TahunAjaranService::class)->activeId());
        if ($tahunAjaranId && array_intersect(array_keys($validated), ['kelas_id', 'status_pembayaran', 'rekomendasi', 'catatan_rekomendasi'])) {
            $record = $siswa->tahunAjaranRecords()->firstOrNew(['tahun_ajaran_id' => $tahunAjaranId]);
            $record->fill([
                'kelas_id' => $validated['kelas_id'] ?? $record->kelas_id,
                'status_siswa' => $record->status_siswa ?? 'aktif',
                'status_pembayaran' => $validated['status_pembayaran'] ?? $record->status_pembayaran ?? 'Belum Lunas',
                'rekomendasi' => $validated['rekomendasi'] ?? $record->rekomendasi ?? 'tidak',
                'catatan' => $validated['catatan_rekomendasi'] ?? $record->catatan,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatSiswa($siswa->fresh('tahunAjaranRecords.kelas')),
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
        $startedAt = microtime(true);

        try {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            $request->headers->set('Accept', 'application/json');

            $response = app(FeatureSiswaController::class)->syncFromApi($request);
            $payload = method_exists($response, 'getData') ? $response->getData(true) : [];
            $success = (bool) ($payload['success'] ?? false);
            $data = $payload['data'] ?? [];
            $durationMs = round((microtime(true) - $startedAt) * 1000, 2);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => $payload['error'] ?? $payload['message'] ?? 'Quick sync gagal.',
                    'error' => $payload['error'] ?? null,
                    'duration_ms' => $durationMs,
                ], method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 500);
            }

            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => $payload['message'] ?? 'Quick sync selesai.',
                'tahun_ajaran_id' => $request->get('tahun_ajaran_id', app(TahunAjaranService::class)->activeId()),
                'summary' => [
                    'total_api_records' => $data['total_api_records'] ?? 0,
                    'total_db_records' => $data['total_db_records'] ?? 0,
                    'kelas' => [
                        'created' => $data['created_kelas'] ?? 0,
                        'updated' => $data['updated_kelas'] ?? 0,
                    ],
                    'siswa' => [
                        'created' => $data['created_siswa'] ?? 0,
                        'updated' => $data['updated_siswa'] ?? 0,
                        'restored' => $data['restored_siswa'] ?? 0,
                        'deleted' => $data['deleted_siswa'] ?? 0,
                        'skipped' => $data['skipped'] ?? 0,
                    ],
                    'errors_count' => count($data['errors'] ?? []),
                ],
                'data' => $data,
                'duration_ms' => $durationMs,
            ]);
        } catch (\Throwable $e) {
            Log::error('API siswa quick sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Quick sync gagal: ' . $e->getMessage(),
                'exception' => get_class($e),
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ], 500);
        }
    }

    public function setRekomendasi(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'rekomendasi' => ['required_without:status_rekomendasi', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'status_rekomendasi' => ['required_without:rekomendasi', Rule::in(array_keys(Siswa::getRekomendasiOptions()))],
            'catatan_rekomendasi' => 'nullable|string|max:500',
        ]);

        $tahunAjaranId = $request->get('tahun_ajaran_id', app(TahunAjaranService::class)->activeId());
        if ($tahunAjaranId) {
            $record = $siswa->tahunAjaranRecords()
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->firstOrNew(['tahun_ajaran_id' => $tahunAjaranId]);

            $record->fill([
                'kelas_id' => $record->kelas_id ?? $siswa->kelas_id,
                'status_siswa' => $record->status_siswa ?? 'aktif',
                'status_pembayaran' => $record->status_pembayaran ?? $siswa->status_pembayaran,
                'rekomendasi' => $validated['rekomendasi'] ?? $validated['status_rekomendasi'],
                'catatan' => $validated['catatan_rekomendasi'] ?? $record->catatan,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status rekomendasi siswa berhasil diperbarui',
            'data' => $this->formatSiswa($siswa->fresh('tahunAjaranRecords.kelas')),
        ]);
    }

    private function formatSiswa(Siswa $siswa): array
    {
        $tahunAjaranId = request('tahun_ajaran_id', app(TahunAjaranService::class)->activeId());
        $tahunRecord = $tahunAjaranId
            ? ($siswa->relationLoaded('tahunAjaranRecords')
                ? $siswa->tahunAjaranRecords->firstWhere('tahun_ajaran_id', (int) $tahunAjaranId)
                : $siswa->tahunAjaranRecords()->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas')->first())
            : null;
        $kelas = $tahunRecord?->kelas;

        return [
            'id' => $siswa->id,
            'nis' => $siswa->nis,
            'idyayasan' => $siswa->idyayasan,
            'nama' => $siswa->nama,
            'email' => $siswa->email,
            'kelas' => $kelas ? [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'tingkat' => $kelas->tingkat,
                'jurusan' => $kelas->jurusan,
            ] : null,
            'status_pembayaran' => $tahunRecord?->status_pembayaran ?? 'Belum Lunas',
            'rekomendasi' => $tahunRecord?->rekomendasi ?? 'tidak',
            'catatan_rekomendasi' => $tahunRecord?->catatan,
        ];
    }
}
