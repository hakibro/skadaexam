@extends('layouts.app')

@section('title', 'Hasil Ujian')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold mb-2">Hasil Ujian</h1>
                <p class="text-gray-600">{{ $hasilUjian->jadwalUjian->nama }} - {{ $hasilUjian->jadwalUjian->mapel->nama }}
                </p>
            </div>

            <div class="bg-blue-50 rounded-lg p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-gray-600 mb-1">Skor</p>
                        <p class="text-3xl font-bold">{{ $hasilUjian->skor }} / {{ $hasilUjian->jumlah_soal }}</p>
                    </div>

                    <div class="text-center">
                        <p class="text-gray-600 mb-1">Persentase Benar</p>
                        <p class="text-3xl font-bold">{{ $hasilUjian->getPersentaseBenar() }}%</p>
                    </div>

                    <div class="text-center">
                        <p class="text-gray-600 mb-1">Grade</p>
                        <p class="text-3xl font-bold">{{ $hasilUjian->calculateGrade() }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="font-semibold text-lg mb-4">Detail Siswa</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600">Nama</p>
                            <p class="font-semibold">{{ $hasilUjian->siswa->nama }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">NIS</p>
                            <p class="font-semibold">{{ $hasilUjian->siswa->nis }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Kelas</p>
                            <p class="font-semibold">{{ $hasilUjian->siswa->kelas->nama ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Tanggal Ujian</p>
                            <p class="font-semibold">{{ $hasilUjian->waktu_mulai->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="font-semibold text-lg mb-4">Statistik Jawaban</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <p class="text-gray-600">Jawaban Benar</p>
                            <p class="text-xl font-bold text-green-600">{{ $hasilUjian->jumlah_benar }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Jawaban Salah</p>
                            <p class="text-xl font-bold text-red-600">{{ $hasilUjian->jumlah_salah }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Tidak Dijawab</p>
                            <p class="text-xl font-bold">{{ $hasilUjian->jumlah_tidak_dijawab }}</p>
                        </div>
                    </div>

                    <!-- Progress bar for correct answers -->
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-1">
                        <div class="bg-green-500 h-4 rounded-full" style="width: {{ $hasilUjian->getPersentaseBenar() }}%">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Persentase Jawaban Benar</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="font-semibold text-lg mb-4">Waktu Pengerjaan</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-gray-600">Mulai</p>
                            <p class="font-semibold">{{ $hasilUjian->waktu_mulai->format('H:i:s') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Selesai</p>
                            <p class="font-semibold">
                                {{ $hasilUjian->waktu_selesai ? $hasilUjian->waktu_selesai->format('H:i:s') : '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Durasi</p>
                            <p class="font-semibold">{{ $hasilUjian->getDurationFormatted() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('siswa.dashboard') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
