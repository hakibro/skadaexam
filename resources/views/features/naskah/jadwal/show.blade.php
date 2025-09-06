@extends('layouts.admin')

@section('title', 'Detail Jadwal Ujian')
@section('page-title', 'Detail Jadwal Ujian')
@section('page-description', 'Informasi jadwal ujian dan sesi ruangan')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detail Jadwal Ujian</h1>


            <div class="space-x-2">
                <a href="{{ route('naskah.jadwal.edit', $jadwal) }}"
                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Jadwal
                </a>
            </div>
        </div>

        <!-- Hidden debug info (can be revealed with ?debug=1 parameter) -->
        @if (request()->has('debug'))
            <div class="bg-yellow-50 border border-yellow-500 text-yellow-800 p-4 mb-6 rounded">
                <p class="font-bold">Debug Information</p>
                <p class="mt-2">Debug ID: {{ $debug_id ?? 'Not set' }}</p>
                <p>Timestamp: {{ $debug_timestamp ?? date('Y-m-d H:i:s') }}</p>
                <p>Route: {{ Route::currentRouteName() }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Column 1: Basic Info & Status -->
            <div class="lg:col-span-2">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="font-medium text-lg">Informasi Jadwal</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Kode Ujian</p>
                                <p class="font-semibold">{{ $jadwal->kode_ujian ?? 'Tidak tersedia' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                @if ($jadwal->status == 'aktif')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                @elseif($jadwal->status == 'draft')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                @elseif($jadwal->status == 'selesai')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                                @elseif($jadwal->status == 'dibatalkan')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $jadwal->status }}</span>
                                @endif
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Judul</p>
                                <p class="font-semibold">{{ $jadwal->judul ?? 'Tidak tersedia' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Jenis Ujian</p>
                                <p class="font-semibold">{{ $jadwal->jenis_ujian ?? 'Tidak tersedia' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Mata Pelajaran</p>
                                <p class="font-semibold">
                                    {{ isset($jadwal->mapel) ? $jadwal->mapel->nama_mapel ?? ($jadwal->mapel->nama ?? 'Tidak tersedia') : 'Tidak tersedia' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Bank Soal</p>
                                <p class="font-semibold">
                                    {{ isset($jadwal->bankSoal) ? $jadwal->bankSoal->judul ?? 'Tidak tersedia' : 'Tidak tersedia' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Tanggal Ujian</p>
                                <p class="font-semibold">
                                    {{ $jadwal->tanggal ? $jadwal->tanggal->format('d F Y') : 'Tidak tersedia' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Waktu</p>
                                <p class="font-semibold">
                                    @if (isset($jadwal->waktu_mulai) && isset($jadwal->waktu_selesai))
                                        {{ $jadwal->waktu_mulai->format('H:i') }} -
                                        {{ $jadwal->waktu_selesai->format('H:i') }}
                                    @else
                                        Tidak tersedia
                                    @endif
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Durasi</p>
                                <p class="font-semibold">{{ $jadwal->durasi_menit ?? '0' }} Menit</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Jumlah Soal</p>
                                <p class="font-semibold">{{ $jadwal->jumlah_soal ?? '0' }} Soal</p>
                            </div>
                        </div>

                        @if ($jadwal->deskripsi)
                            <div class="mt-6">
                                <p class="text-sm text-gray-500">Deskripsi</p>
                                <div class="mt-1 prose max-w-none">
                                    {{ $jadwal->deskripsi }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pengaturan Ujian -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="font-medium text-lg">Pengaturan Ujian</h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                @if ($jadwal->acak_soal)
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-2">Soal diacak</span>
                                @else
                                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="ml-2">Soal tidak diacak</span>
                                @endif
                            </li>

                            <li class="flex items-center">
                                @if ($jadwal->acak_jawaban)
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-2">Pilihan jawaban diacak</span>
                                @else
                                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="ml-2">Pilihan jawaban tidak diacak</span>
                                @endif
                            </li>

                            <li class="flex items-center">
                                @if ($jadwal->tampilkan_hasil)
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-2">Hasil ujian ditampilkan ke siswa</span>
                                @else
                                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="ml-2">Hasil ujian tidak ditampilkan ke siswa</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Column 2: Status & Actions -->
            <div class="lg:col-span-1">
                <!-- Info & Stats Card -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="font-medium text-lg">Informasi Tambahan</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Dibuat Oleh</p>
                                <p class="font-semibold">
                                    {{ isset($jadwal->creator) ? $jadwal->creator->name : 'Tidak tersedia' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Tanggal Dibuat</p>
                                <p class="font-semibold">
                                    {{ $jadwal->created_at ? $jadwal->created_at->format('d F Y, H:i') : 'Tidak tersedia' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Terakhir Diperbarui</p>
                                <p class="font-semibold">
                                    {{ $jadwal->updated_at ? $jadwal->updated_at->format('d F Y, H:i') : 'Tidak tersedia' }}
                                </p>
                            </div>

                            <div class="pt-2">
                                <p class="text-sm text-gray-500">Jumlah Sesi Ruangan</p>
                                <p class="font-semibold text-2xl">
                                    {{ $jadwal->sesiRuangans->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b px-6 py-3 bg-blue-50">
                        <h2 class="font-medium text-lg flex items-center">
                            <i class="fas fa-cog text-blue-600 mr-2"></i> Pengaturan Status
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Current Status Display -->
                        <div class="mb-4 flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-3">Status Saat Ini:</span>
                            @switch($jadwal->status)
                                @case('draft')
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fa-solid fa-pencil mr-1"></i> Draft
                                    </span>
                                @break

                                @case('aktif')
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fa-solid fa-check-circle mr-1"></i> Aktif
                                    </span>
                                @break

                                @case('nonaktif')
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fa-solid fa-pause-circle mr-1"></i> Non-Aktif
                                    </span>
                                @break

                                @case('selesai')
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-flag-checkered mr-1"></i> Selesai
                                    </span>
                                @break

                                @default
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($jadwal->status) }}
                                    </span>
                            @endswitch
                        </div>

                        <!-- Status Update Form -->
                        <form action="{{ route('naskah.jadwal.status', $jadwal) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Ubah Status Menjadi:</label>
                                <select name="status"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="draft" {{ $jadwal->status == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="aktif" {{ $jadwal->status == 'aktif' ? 'selected' : '' }}>Aktif
                                    </option>
                                    <option value="nonaktif" {{ $jadwal->status == 'nonaktif' ? 'selected' : '' }}>
                                        Non-Aktif
                                    </option>
                                    <option value="selesai" {{ $jadwal->status == 'selesai' ? 'selected' : '' }}>
                                        Selesai</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex justify-center items-center">
                                <i class="fas fa-sync-alt mr-2"></i> Perbarui Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sesi Ruangan Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b px-6 py-3 flex justify-between items-center">
                <h2 class="font-medium text-lg">Sesi Ruangan</h2>

                @if (auth()->user()->can('update', $jadwal))
                    <div class="flex space-x-2">
                        <a href="{{ route('naskah.jadwal.edit', $jadwal) }}"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-edit mr-2"></i> Edit Jadwal
                        </a>
                    </div>
                @endif
            </div>
            <div class="p-6">
                @if ($jadwal->sesiRuangans->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ruangan</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kelas</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Peserta</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($jadwal->sesiRuangans as $sesi)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $sesi->ruangan->nama ?? 'Tidak tersedia' }}</div>
                                            <div class="text-sm text-gray-500">Kapasitas:
                                                {{ $sesi->ruangan->kapasitas ?? '0' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($sesi->kelas)
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $sesi->kelas->nama ?? 'Tidak tersedia' }}</div>
                                            @else
                                                <div class="text-sm text-gray-500">Tidak terkait dengan kelas</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : 'Tidak tersedia' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if ($sesi->waktu_mulai && $sesi->waktu_selesai)
                                                    {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                                @else
                                                    Tidak tersedia
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($sesi->status == 'aktif')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                            @elseif($sesi->status == 'draft')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                            @elseif($sesi->status == 'selesai')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $sesi->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-medium">{{ $sesi->sesiRuanganSiswa->count() ?? '0' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('ruangan.sesi.show', [$sesi->ruangan_id, $sesi->id]) }}"
                                                    class="text-blue-600 hover:text-blue-900">Detail</a>
                                                @if (auth()->user()->can('update', $jadwal))
                                                    <button type="button" onclick="detachSesi({{ $sesi->id }})"
                                                        class="text-red-600 hover:text-red-900">
                                                        Lepas
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Belum ada sesi ruangan yang ditambahkan ke jadwal ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer Navigation -->
        <div class="mt-6 flex items-center justify-between">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.jadwal.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
                <a href="{{ route('naskah.dashboard') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
            </div>

            @if (auth()->user()->can('delete', $jadwal) && ($jadwal->status == 'draft' || $jadwal->status == 'dibatalkan'))
                <form action="{{ route('naskah.jadwal.destroy', $jadwal) }}" method="POST" class="inline-block"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ujian ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                        <i class="fas fa-trash mr-2"></i> Hapus Jadwal
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Modal section removed -->

    <!-- Debug Data (Only visible with ?debug=1 parameter) -->
    @if (request()->has('debug'))
        <div class="mt-8 bg-gray-100 p-6 rounded-lg border border-gray-300">
            <h3 class="text-lg font-medium mb-4">Debug Data</h3>
            <div class="overflow-auto max-h-96 text-xs font-mono">
                <pre>{{ print_r($jadwal->toArray(), true) }}</pre>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        // Optional: Add any JavaScript needed for interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Add confirmation for status changes
            const statusForm = document.querySelector(
                'form[action="{{ route('naskah.jadwal.status', $jadwal) }}"]');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    const status = this.querySelector('select[name="status"]').value;
                    if (status === 'selesai' || status === 'dibatalkan') {
                        if (!confirm(
                                `Apakah Anda yakin ingin mengubah status menjadi ${status.toUpperCase()}?`
                            )) {
                            e.preventDefault();
                        }
                    }
                });
            }
        });

        function detachSesi(sesiId) {
            if (confirm('Apakah Anda yakin ingin melepas sesi ini dari jadwal ujian?')) {
                fetch('{{ route('naskah.jadwal.detach-sesi', $jadwal) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            sesi_id: sesiId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Gagal melepas sesi: ' + (data.message || 'Error tidak diketahui'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat melepas sesi');
                    });
            }
        }
    </script>
@endpush
