<!-- filepath: resources\views\features\koordinator\dashboard.blade.php -->
@extends('layouts.dashboard')

@section('content')
<div>
    <h1 class="text-3xl font-bold mb-4 text-purple-700">Dashboard Koordinator</h1>
    <p class="text-gray-600 mb-8">Koordinasi dan penugasan guru dalam sistem ujian.</p>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center gap-4">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                    <i class="fa-solid fa-user-tie text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-800">0</div>
                    <div class="text-gray-600 font-medium">Guru Aktif</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center gap-4">
                <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                    <i class="fa-solid fa-tasks text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-800">0</div>
                    <div class="text-gray-600 font-medium">Penugasan</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center gap-4">
                <div class="bg-green-100 text-green-600 p-3 rounded-full">
                    <i class="fa-solid fa-calendar-check text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-800">0</div>
                    <div class="text-gray-600 font-medium">Jadwal Aktif</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center gap-4">
                <div class="bg-orange-100 text-orange-600 p-3 rounded-full">
                    <i class="fa-solid fa-bell text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-800">0</div>
                    <div class="text-gray-600 font-medium">Notifikasi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-user-plus text-purple-600 mr-2"></i>
                Penugasan Guru
            </h3>
            <p class="text-gray-600 mb-4">Atur penugasan guru untuk berbagai role ujian.</p>
            <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
                <i class="fa-solid fa-plus mr-2"></i>
                Tambah Penugasan
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-calendar-alt text-blue-600 mr-2"></i>
                Jadwal Koordinasi
            </h3>
            <p class="text-gray-600 mb-4">Kelola jadwal rapat dan koordinasi.</p>
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                <i class="fa-solid fa-calendar-plus mr-2"></i>
                Buat Jadwal
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-chart-bar text-green-600 mr-2"></i>
                Laporan Koordinasi
            </h3>
            <p class="text-gray-600 mb-4">Buat laporan aktivitas koordinasi.</p>
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                <i class="fa-solid fa-file-export mr-2"></i>
                Generate Laporan
            </button>
        </div>
    </div>

    <!-- Coming Soon Notice -->
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="bg-purple-100 text-purple-600 p-4 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
            <i class="fa-solid fa-tools text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Fitur Koordinasi Dalam Pengembangan</h2>
        <p class="text-gray-600 text-lg">Sistem koordinasi guru sedang dalam tahap pengembangan.</p>
    </div>
</div>
@endsection