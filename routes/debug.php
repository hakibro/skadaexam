<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Debug Routes (only available in debug mode)
if (config('app.debug')) {
    Route::get('/debug-auth', function () {
        if (!auth()->check()) {
            return ['error' => 'Not authenticated'];
        }

        $user = auth()->user();
        return [
            'user' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'No role',
            'guard' => 'web',
            'middleware_working' => true
        ];
    });

    Route::get('/test-soal-image', function () {
        $imageService = app(\App\Services\SoalImageService::class);
        $types = ['pertanyaan', 'pilihan', 'pembahasan'];
        $results = [];

        foreach ($types as $type) {
            $filename = $imageService->createTestImage($type);
            $results[$type] = [
                'filename' => $filename,
                'url' => $filename ? Storage::url('soal/' . $type . '/' . $filename) : null,
                'full_path' => $filename ? storage_path('app/public/soal/' . $type . '/' . $filename) : null,
                'exists' => $filename ? file_exists(storage_path('app/public/soal/' . $type . '/' . $filename)) : false
            ];
        }

        return view('debug.test-images', ['results' => $results]);
    });

    Route::get('/force-logout', function () {
        Auth::logout();
        session()->flush();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/')->with('message', 'Logged out successfully!');
    });

    // Test filter functionality
    Route::get('/test-filter', function () {
        $siswa = \App\Models\Siswa::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('idyayasan', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(request('kelas'), function ($q, $kelas) {
                $q->where('kelas', $kelas);
            })
            ->when(request('status_pembayaran'), function ($q, $status) {
                $q->where('status_pembayaran', $status);
            })
            ->when(request('rekomendasi'), function ($q, $rekomendasi) {
                $q->where('rekomendasi', $rekomendasi);
            })
            ->paginate(10);

        return response()->json([
            'total' => $siswa->total(),
            'current_page' => $siswa->currentPage(),
            'per_page' => $siswa->perPage(),
            'filters_applied' => request()->only(['search', 'kelas', 'status_pembayaran', 'rekomendasi']),
            'sample_data' => $siswa->items(),
        ]);
    })->middleware('auth:web');
}
