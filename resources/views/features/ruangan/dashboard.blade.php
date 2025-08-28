<!-- filepath: resources\views\features\ruangan\dashboard.blade.php -->
@extends('layouts.dashboard')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-4 text-indigo-700">Dashboard Ruangan Management</h1>
        <p class="text-gray-600 mb-8">Kelola ruang ujian dan sesi ujian online.</p>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
                        <i class="fa-solid fa-door-open text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Total Ruangan</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full">
                        <i class="fa-solid fa-check-circle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Ruangan Aktif</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                        <i class="fa-solid fa-clock text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Sesi Berlangsung</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center gap-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Kapasitas Total</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-plus-circle text-indigo-600 mr-2"></i>
                    Tambah Ruangan
                </h3>
                <p class="text-gray-600 mb-4">Buat ruang ujian virtual baru untuk ujian online.</p>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Buat Ruangan
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-cog text-blue-600 mr-2"></i>
                    Kelola Sesi
                </h3>
                <p class="text-gray-600 mb-4">Atur sesi ujian dan jadwal penggunaan ruangan.</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    <i class="fa-solid fa-calendar mr-2"></i>
                    Atur Sesi
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-chart-pie text-green-600 mr-2"></i>
                    Statistik Ruangan
                </h3>
                <p class="text-gray-600 mb-4">Lihat statistik penggunaan ruangan ujian.</p>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                    <i class="fa-solid fa-chart-bar mr-2"></i>
                    Lihat Statistik
                </button>
            </div>
        </div>

        <!-- Coming Soon Notice -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div
                class="bg-indigo-100 text-indigo-600 p-4 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <i class="fa-solid fa-tools text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Fitur Ruangan Dalam Pengembangan</h2>
            <p class="text-gray-600 text-lg">Sistem management ruangan ujian sedang dalam tahap pengembangan.</p>
        </div>
    </div>
@endsection
