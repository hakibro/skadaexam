@extends('layouts.app')

@section('title', 'Mulai Ujian')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">{{ $jadwalUjian->nama }}</h1>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <p class="text-blue-700">Sesi: {{ $sesiUjian->nama }} ({{ $sesiUjian->tanggal->format('d M Y') }})</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-md">
                    <h2 class="font-semibold text-lg mb-2">Informasi Ujian</h2>
                    <ul class="space-y-2">
                        <li><span class="font-medium">Mata Pelajaran:</span> {{ $jadwalUjian->mapel->nama }}</li>
                        <li><span class="font-medium">Durasi:</span> {{ $jadwalUjian->durasi }} menit</li>
                        <li><span class="font-medium">Jumlah Soal:</span> {{ $hasilUjian->jumlah_soal }}</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-4 rounded-md">
                    <h2 class="font-semibold text-lg mb-2">Data Siswa</h2>
                    <ul class="space-y-2">
                        <li><span class="font-medium">Nama:</span> {{ auth('siswa')->user()->nama }}</li>
                        <li><span class="font-medium">NIS:</span> {{ auth('siswa')->user()->nis }}</li>
                        <li><span class="font-medium">Kelas:</span> {{ auth('siswa')->user()->kelas->nama ?? '-' }}</li>
                    </ul>
                </div>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                <h3 class="text-yellow-700 font-medium mb-2">Peraturan Ujian:</h3>
                <ol class="list-decimal ml-5 text-yellow-700 space-y-1">
                    <li>Waktu ujian akan dimulai segera setelah Anda menekan tombol "Mulai Ujian".</li>
                    <li>Jangan menutup browser atau meninggalkan halaman ujian.</li>
                    <li>Pastikan koneksi internet Anda stabil selama ujian berlangsung.</li>
                    <li>Jawaban akan otomatis tersimpan saat Anda beralih ke soal lain.</li>
                    <li>Jika waktu habis, ujian akan otomatis dikumpulkan.</li>
                </ol>
            </div>

            <div class="flex justify-center">
                <a href="{{ route('ujian.soal') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-md transition">
                    Mulai Ujian Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection
