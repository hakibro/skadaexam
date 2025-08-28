<!-- filepath: resources\views\features\naskah\dashboard.blade.php -->
@extends('layouts.dashboard')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-4 text-orange-700">Dashboard Naskah Management</h1>
        <p class="text-gray-600 mb-8">Kelola soal, ujian, dan naskah ujian online.</p>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                <div class="flex items-center gap-4">
                    <div class="bg-orange-100 text-orange-600 p-3 rounded-full">
                        <i class="fa-solid fa-file-lines text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Total Soal</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center gap-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full">
                        <i class="fa-solid fa-clipboard-list text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">0</div>
                        <div class="text-gray-600 font-medium">Total Ujian</div>
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
                        <div class="text-gray-600 font-medium">Ujian Aktif</div>
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
                        <div class="text-gray-600 font-medium">Ujian Selesai</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coming Soon Notice -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div
                class="bg-orange-100 text-orange-600 p-4 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <i class="fa-solid fa-tools text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Fitur Dalam Pengembangan</h2>
            <p class="text-gray-600 text-lg">Modul Naskah Management sedang dalam tahap pengembangan dan akan segera
                tersedia.</p>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <i class="fa-solid fa-file-lines text-2xl text-orange-600 mb-2"></i>
                    <h3 class="font-semibold">Kelola Soal</h3>
                    <p class="text-sm text-gray-600">Bank soal ujian</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <i class="fa-solid fa-clipboard-list text-2xl text-red-600 mb-2"></i>
                    <h3 class="font-semibold">Buat Ujian</h3>
                    <p class="text-sm text-gray-600">Setup ujian online</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <i class="fa-solid fa-calendar text-2xl text-blue-600 mb-2"></i>
                    <h3 class="font-semibold">Jadwal Ujian</h3>
                    <p class="text-sm text-gray-600">Atur jadwal ujian</p>
                </div>
            </div>
        </div>
    </div>
@endsection
