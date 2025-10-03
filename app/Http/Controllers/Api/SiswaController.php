<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    // GET semua siswa
    public function index()
    {
        $siswas = Siswa::with('kelas')->get()->map(function ($siswa) {
            return [
                'idyayasan' => $siswa->idyayasan,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : null,
                'status_pembayaran' => $siswa->status_pembayaran,
                'rekomendasi' => $siswa->rekomendasi,
            ];
        });

        return response()->json($siswas);
    }

    // GET detail siswa
    public function show($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }
        return response()->json($siswa);
    }

    // POST tambah siswa
    public function store(Request $request)
    {
        $siswa = Siswa::create($request->only(['nim', 'nama', 'email']));
        return response()->json($siswa, 201);
    }

    // PUT update siswa
    public function update(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }
        $siswa->update($request->only(['nim', 'nama', 'email']));
        return response()->json($siswa);
    }

    // DELETE hapus siswa
    public function destroy($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }
        $siswa->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus']);
    }
}
