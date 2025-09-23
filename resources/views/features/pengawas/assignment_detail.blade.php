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
            <h2 class="text-2xl font-bold text-green-700 mb-4">Detail Jadwal Pengawasan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <table class="w-full">
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Mata Pelajaran</td>
                            <td class="py-2 font-bold">
                                @if ($sesiRuangan->jadwalUjians->count() > 1)
                                    @php
                                        $mapelNames = $sesiRuangan->jadwalUjians
                                            ->filter(function ($jadwal) {
                                                return $jadwal->mapel !== null;
                                            })
                                            ->map(function ($jadwal) {
                                                return $jadwal->mapel->nama_mapel;
                                            })
                                            ->unique();
                                    @endphp
                                    @if ($mapelNames->count() > 0)
                                        {{ $mapelNames->implode(' + ') }}
                                        @if ($mapelNames->count() != $sesiRuangan->jadwalUjians->count())
                                            <span class="text-sm text-gray-500">({{ $mapelNames->count() }} dari
                                                {{ $sesiRuangan->jadwalUjians->count() }} mapel)</span>
                                        @else
                                            <span class="text-sm text-gray-500">({{ $mapelNames->count() }} mapel)</span>
                                        @endif
                                    @else
                                        <span class="text-red-500">Tidak ada mapel tersedia</span>
                                        <span class="text-sm text-gray-500">({{ $sesiRuangan->jadwalUjians->count() }}
                                            jadwal)</span>
                                    @endif
                                @elseif($sesiRuangan->jadwalUjians->count() == 1)
                                    @php
                                        $jadwal = $sesiRuangan->jadwalUjians->first();
                                    @endphp
                                    @if ($jadwal->mapel)
                                        {{ $jadwal->mapel->nama_mapel }}
                                    @else
                                        <span class="text-red-500">Mapel tidak tersedia</span>
                                        <span class="text-sm text-gray-500">(ID: {{ $jadwal->id }}, Mapel ID:
                                            {{ $jadwal->mapel_id ?? 'NULL' }})</span>
                                    @endif
                                @else
                                    <span class="text-red-500">Tidak ada jadwal</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Tanggal</td>
                            <td class="py-2 font-bold">
                                @if ($sesiRuangan->jadwalUjians->count() > 0)
                                    {{ $sesiRuangan->jadwalUjians->first()->tanggal->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Waktu</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->waktu_mulai }} - {{ $sesiRuangan->waktu_selesai }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table class="w-full">
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Ruangan</td>
                            <td class="py-2 font-bold">
                                {{ $sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Sesi</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->nama_sesi }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Jumlah Siswa</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->siswa->count() }} siswa</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-green-700 mb-4">Daftar Kehadiran Siswa</h2>

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
                                                    <input type="radio" name="attendance[{{ $sesiSiswa->siswa_id }}]"
                                                        value="hadir" class="form-radio text-green-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $sesiSiswa->siswa_id }}]"
                                                        value="tidak_hadir" class="form-radio text-red-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'tidak_hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Tidak Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $sesiSiswa->siswa_id }}]"
                                                        value="sakit" class="form-radio text-yellow-600"
                                                        {{ $sesiSiswa->status_kehadiran === 'sakit' ? 'checked' : '' }}>
                                                    <span class="ml-2">Sakit</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $sesiSiswa->siswa_id }}]"
                                                        value="izin" class="form-radio text-blue-600"
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
                    <button type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Kehadiran
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
