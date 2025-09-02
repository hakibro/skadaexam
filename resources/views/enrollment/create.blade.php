@extends('layouts.app')

@section('title', 'Create Enrollment')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Enrollment Siswa - {{ $jadwalUjian->nama }}</h1>
                <a href="{{ route('enrollment.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md">Kembali</a>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-blue-700">Mata Pelajaran: <span
                                class="font-bold">{{ $jadwalUjian->mapel->nama ?? 'Tidak ada' }}</span></p>
                        <p class="text-sm text-blue-700">Status: <span
                                class="font-bold">{{ ucfirst($jadwalUjian->status) }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700">Tanggal: <span
                                class="font-bold">{{ $jadwalUjian->tanggal_mulai->format('d M Y') }} -
                                {{ $jadwalUjian->tanggal_selesai->format('d M Y') }}</span></p>
                        <p class="text-sm text-blue-700">Durasi: <span class="font-bold">{{ $jadwalUjian->durasi }}
                                menit</span></p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('enrollment.store') }}">
                @csrf
                <input type="hidden" name="jadwal_ujian_id" value="{{ $jadwalUjian->id }}">

                <div class="mb-6">
                    <label for="sesi_ujian_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Sesi Ujian</label>
                    <select id="sesi_ujian_id" name="sesi_ujian_id"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Sesi (Opsional) --</option>
                        @foreach ($sesiList as $sesi)
                            <option value="{{ $sesi->id }}">
                                {{ $sesi->nama }} - {{ $sesi->tanggal->format('d M Y') }} ({{ $sesi->waktu_mulai }} -
                                {{ $sesi->waktu_selesai }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Jika tidak dipilih, sistem akan otomatis memilih sesi yang
                        tersedia.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ($kelasList as $kelas)
                            <div class="flex items-center">
                                <input id="kelas-{{ $kelas->id }}" name="kelas_ids[]" type="checkbox"
                                    value="{{ $kelas->id }}"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="kelas-{{ $kelas->id }}" class="ml-2 block text-sm text-gray-900">
                                    {{ $kelas->nama }} ({{ $kelas->siswa_count ?? 0 }} siswa)
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <p class="text-sm text-yellow-700">
                        <span class="font-bold">Penting:</span> Enrollment akan mendaftarkan semua siswa dari kelas yang
                        dipilih ke jadwal ujian ini.
                        Pastikan Anda telah memilih kelas yang benar.
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                        Daftarkan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
