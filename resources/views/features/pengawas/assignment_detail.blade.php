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
                            <td class="py-2 font-bold">{{ $sesiRuangan->jadwalUjian->mapel->nama }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Tanggal</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->jadwalUjian->tanggal->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Waktu</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->jadwalUjian->waktu_mulai }} -
                                {{ $sesiRuangan->jadwalUjian->waktu_selesai }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table class="w-full">
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Ruangan</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->ruangan->nama }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Jumlah Siswa</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->siswa->count() }} siswa</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-600">Pengawas</td>
                            <td class="py-2 font-bold">{{ $sesiRuangan->guru->nama_lengkap }}</td>
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
                                <th class="border border-gray-300 px-4 py-2 text-left">NIS</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Nama Siswa</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Kelas</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($sesiRuangan->siswa->count() > 0)
                                @foreach ($sesiRuangan->siswa as $i => $siswa)
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">{{ $i + 1 }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $siswa->nis }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $siswa->nama_lengkap }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ $siswa->kelas ? $siswa->kelas->nama : 'Tidak ada kelas' }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <div class="flex justify-center space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $siswa->id }}]"
                                                        value="hadir" class="form-radio text-green-600"
                                                        {{ $siswa->pivot->status_kehadiran === 'hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $siswa->id }}]"
                                                        value="tidak_hadir" class="form-radio text-red-600"
                                                        {{ $siswa->pivot->status_kehadiran === 'tidak_hadir' ? 'checked' : '' }}>
                                                    <span class="ml-2">Tidak Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $siswa->id }}]"
                                                        value="sakit" class="form-radio text-yellow-600"
                                                        {{ $siswa->pivot->status_kehadiran === 'sakit' ? 'checked' : '' }}>
                                                    <span class="ml-2">Sakit</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="attendance[{{ $siswa->id }}]"
                                                        value="izin" class="form-radio text-blue-600"
                                                        {{ $siswa->pivot->status_kehadiran === 'izin' ? 'checked' : '' }}>
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
