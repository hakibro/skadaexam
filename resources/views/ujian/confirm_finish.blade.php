@extends('layouts.app')

@section('title', 'Konfirmasi Selesai Ujian')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold mb-4">Konfirmasi Selesai Ujian</h1>
                <p class="text-gray-600">Pastikan Anda telah menjawab semua soal sebelum mengakhiri ujian</p>
            </div>

            <div class="bg-blue-50 rounded-lg p-6 mb-8">
                <h2 class="font-semibold text-lg mb-4">Ringkasan Jawaban</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Jumlah Soal</p>
                        <p class="text-xl font-bold">{{ $totalSoal }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Sudah Dijawab</p>
                        <p class="text-xl font-bold text-green-600">{{ $terjawab }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Belum Dijawab</p>
                        <p class="text-xl font-bold {{ $belumTerjawab > 0 ? 'text-red-600' : 'text-gray-600' }}">
                            {{ $belumTerjawab }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Persentase Jawaban</p>
                        <p class="text-xl font-bold">{{ round(($terjawab / $totalSoal) * 100) }}%</p>
                    </div>
                </div>
            </div>

            @if ($belumTerjawab > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <p class="text-yellow-700">
                        <span class="font-bold">Perhatian!</span> Masih ada {{ $belumTerjawab }} soal yang belum dijawab.
                        Apakah Anda yakin ingin mengakhiri ujian?
                    </p>
                </div>
            @endif

            <div class="flex justify-between">
                <a href="{{ route('ujian.soal') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-md transition">
                    Kembali ke Ujian
                </a>

                <form action="{{ route('ujian.finish') }}" method="GET">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-md transition">
                        Selesai & Kumpulkan
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
