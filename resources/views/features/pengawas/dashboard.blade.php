@extends('layouts.admin')

@section('title', 'Pengawas Dashboard')
@section('page-title', 'Pengawas Dashboard')
@section('page-description', 'Buat Token, Monitor Ujian, dan Laporan')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-4 text-green-700">Dashboard Pengawas</h1>
        <p class="text-gray-600 mb-8">Monitor dan supervisi jalannya ujian online.</p>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full">
                        <i class="fa-solid fa-eye text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Ujian Diawasi</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Siswa Online</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                        <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Peringatan</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center gap-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full">
                        <i class="fa-solid fa-ban text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Kecurangan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-desktop text-green-600 mr-2"></i>
                    Live Monitoring
                </h3>
                <p class="text-gray-600 mb-4">Monitor siswa yang sedang mengerjakan ujian secara real-time.</p>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                    <i class="fa-solid fa-play mr-2"></i>
                    Mulai Monitoring
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-file-alt text-blue-600 mr-2"></i>
                    Laporan Pengawasan
                </h3>
                <p class="text-gray-600 mb-4">Buat laporan hasil pengawasan ujian.</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    <i class="fa-solid fa-file-download mr-2"></i>
                    Buat Laporan
                </button>
            </div>
        </div>

        <!-- Coming Soon Notice -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div
                class="bg-green-100 text-green-600 p-4 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <i class="fa-solid fa-tools text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Fitur Monitoring Dalam Pengembangan</h2>
            <p class="text-gray-600 text-lg">Sistem monitoring pengawasan ujian sedang dalam tahap pengembangan.</p>
        </div>
    </div>
@endsection
