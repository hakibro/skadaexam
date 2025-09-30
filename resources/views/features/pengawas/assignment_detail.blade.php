@extends('layouts.admin')

@section('title', 'Detail Tugas Pengawasan')
@section('page-title', 'Detail Tugas Pengawasan')
@section('page-description', 'Informasi dan Presensi Siswa')

@section('content')
    <div>
        <div class="mb-6">
            <a href="{{ route('pengawas.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-green-700 mb-6">Detail Jadwal Pengawasan</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Bagian Kiri: Detail Jadwal --}}
                <div class="space-y-6">
                    {{-- Detail Utama --}}
                    <div class="bg-gray-50 rounded-xl shadow p-5 space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm font-medium text-gray-600">Mata Pelajaran</p>
                            <p class="text-base font-bold text-right">
                                @if ($sesiRuangan->jadwalUjians->count() > 1)
                                    @php
                                        $mapelNames = $sesiRuangan->jadwalUjians
                                            ->filter(fn($jadwal) => $jadwal->mapel !== null)
                                            ->map(fn($jadwal) => $jadwal->mapel->nama_mapel)
                                            ->unique();
                                    @endphp
                                    @if ($mapelNames->count() > 0)
                                        {{ $mapelNames->implode(' + ') }}
                                        <span class="block text-sm text-gray-500">
                                            ({{ $mapelNames->count() }} dari {{ $sesiRuangan->jadwalUjians->count() }}
                                            mapel)
                                        </span>
                                    @else
                                        <span class="text-red-500">Tidak ada mapel tersedia</span>
                                    @endif
                                @elseif($sesiRuangan->jadwalUjians->count() == 1)
                                    @php $jadwal = $sesiRuangan->jadwalUjians->first(); @endphp
                                    {{ $jadwal->mapel->nama_mapel ?? 'Mapel tidak tersedia' }}
                                @else
                                    <span class="text-red-500">Tidak ada jadwal</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex justify-between">
                            <p class="text-sm font-medium text-gray-600">Tanggal</p>
                            <p class="text-base font-bold">
                                @if ($sesiRuangan->jadwalUjians->count() > 0)
                                    {{ $sesiRuangan->jadwalUjians->first()->tanggal->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <div class="flex justify-between">
                            <p class="text-sm font-medium text-gray-600">Waktu</p>
                            <p class="text-base font-bold">
                                {{ $sesiRuangan->waktu_mulai }} - {{ $sesiRuangan->waktu_selesai }}
                            </p>
                        </div>
                    </div>

                    {{-- Detail Tambahan --}}
                    <div class="bg-gray-50 rounded-xl shadow p-5 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Ruangan</span>
                            <span class="font-bold">{{ $sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Sesi</span>
                            <span class="font-bold">{{ $sesiRuangan->nama_sesi }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Jumlah Siswa</span>
                            <span class="font-bold">{{ $sesiRuangan->siswa->count() }} siswa</span>
                        </div>
                    </div>
                </div>

                {{-- Bagian Kanan: Informasi Kehadiran --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Kehadiran</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-5 rounded-lg text-center shadow">
                            <div class="text-3xl font-bold text-blue-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->count() }}
                            </div>
                            <div class="text-sm text-blue-600">Total Siswa</div>
                        </div>

                        <div class="bg-green-50 p-5 rounded-lg text-center shadow">
                            <div class="text-3xl font-bold text-green-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count() }}
                            </div>
                            <div class="text-sm text-green-600">Siswa Hadir</div>
                        </div>

                        <div class="bg-red-50 p-5 rounded-lg text-center shadow">
                            <div class="text-3xl font-bold text-red-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->whereIn('status_kehadiran', ['tidak_hadir', 'sakit', 'izin'])->count() }}
                            </div>
                            <div class="text-sm text-red-600">Tidak Hadir</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-green-700 mb-4">Daftar Kehadiran Siswa</h2>
                <button onclick="window.location.reload()"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fa-solid fa-refresh mr-2"></i>
                    Refresh
                </button>
            </div>

            <form action="{{ route('pengawas.update-attendance', $sesiRuangan->id) }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left">No.</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">ID YYS</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Nama Siswa</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Kelas</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($sesiRuangan->sesiRuanganSiswa->count() > 0)
                                @foreach ($sesiRuangan->sesiRuanganSiswa as $i => $sesiSiswa)
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">{{ $i + 1 }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            @if ($sesiSiswa->siswa)
                                                {{ $sesiSiswa->siswa->idyayasan ?: 'Tidak ada ID YYS' }}
                                            @else
                                                <span class="text-red-500">Data siswa tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            @if ($sesiSiswa->siswa)
                                                {{ $sesiSiswa->siswa->nama ?: 'Nama tidak tersedia' }} -
                                                <span
                                                    class="text-sm font-bold @if ($sesiSiswa->siswa->status_pembayaran === 'Lunas') text-green-600 @else text-red-600 @endif">{{ $sesiSiswa->siswa->status_pembayaran ?: 'Status pembayaran tidak tersedia' }}</span>
                                            @else
                                                <span class="text-red-500">Data siswa tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            @if ($sesiSiswa->siswa && $sesiSiswa->siswa->kelas)
                                                {{ $sesiSiswa->siswa->kelas->nama }}
                                            @elseif($sesiSiswa->siswa)
                                                <span class="text-gray-500">Tidak ada kelas</span>
                                            @else
                                                <span class="text-red-500">Data siswa tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <div class="flex justify-center space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" disabled
                                                        name="attendance[{{ $sesiSiswa->siswa_id }}]" value="hadir"
                                                        class="form-radio text-green-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" disabled
                                                        name="attendance[{{ $sesiSiswa->siswa_id }}]" value="tidak_hadir"
                                                        class="form-radio text-red-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'tidak_hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Tidak Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" disabled
                                                        name="attendance[{{ $sesiSiswa->siswa_id }}]" value="sakit"
                                                        class="form-radio text-yellow-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'sakit' ? 'checked' : '' }}>
                                                    <span class="ml-2">Sakit</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" disabled
                                                        name="attendance[{{ $sesiSiswa->siswa_id }}]" value="izin"
                                                        class="form-radio text-blue-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'izin' ? 'checked' : '' }}>
                                                    <span class="ml-2">Izin</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">Tidak ada siswa
                                        yang terdaftar</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    {{-- <button type="submit" disabled
                        class="bg-gray-200 text-gray-400 px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Kehadiran
                    </button> --}}
                </div>
            </form>
        </div>
    </div>
@endsection
