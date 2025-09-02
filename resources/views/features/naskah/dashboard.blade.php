<!-- filepath: c:\laragon\www\skadaexam\resources\views\features\naskah\dashboard.blade.php -->
@extends('layouts.admin')

@section('title', 'Naskah Management Dashboard')
@section('page-title', 'Naskah Management')
@section('page-description', 'Kelola soal, bank soal, dan naskah ujian')

@section('content')
    <div class="space-y-6">
        @if (isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $error }}</span>
            </div>
        @else
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Bank Soal -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fa-solid fa-book text-white"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Bank Soal</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $bankSoalCount }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="{{ route('naskah.banksoal.index') }}"
                                class="font-medium text-blue-600 hover:text-blue-500">
                                Lihat semua <span class="sr-only">Bank Soal</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Soal -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <i class="fa-solid fa-question text-white"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Soal</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $soalCount }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="{{ route('naskah.soal.index') }}"
                                class="font-medium text-blue-600 hover:text-blue-500">
                                Lihat semua <span class="sr-only">Soal</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Ujian -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <i class="fa-solid fa-calendar-days text-white"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Jadwal Ujian</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $jadwalUjianCount }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="{{ route('naskah.jadwalujian.index') }}"
                                class="font-medium text-blue-600 hover:text-blue-500">
                                Lihat semua <span class="sr-only">Jadwal Ujian</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hasil Ujian -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <i class="fa-solid fa-chart-column text-white"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Hasil Ujian</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $hasilUjianCount }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="{{ route('naskah.hasilujian.index') }}"
                                class="font-medium text-blue-600 hover:text-blue-500">
                                Lihat semua <span class="sr-only">Hasil Ujian</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pass Rate -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <i class="fa-solid fa-percentage text-white"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Tingkat Kelulusan</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $passRate }}%</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="{{ route('naskah.hasilujian.index') }}"
                                class="font-medium text-blue-600 hover:text-blue-500">
                                Lihat statistik <span class="sr-only">Hasil Ujian</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Jadwal Ujian Mendatang</h3>
                    <p class="mt-1 text-sm text-gray-500">Ujian yang akan dilaksanakan dalam waktu dekat</p>
                </div>
                <div class="border-t border-gray-200">
                    @if (count($upcomingExams) > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach ($upcomingExams as $exam)
                                <li>
                                    <a href="{{ route('naskah.jadwalujian.show', $exam) }}" class="block hover:bg-gray-50">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-blue-600 truncate">
                                                    {{ $exam->nama_ujian }}
                                                </p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <p
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $exam->jenis_ujian }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex justify-between">
                                                <div class="sm:flex">
                                                    <p class="flex items-center text-sm text-gray-500">
                                                        <i class="fa-solid fa-book flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                        {{ $exam->mapel->nama_mapel ?? 'Tidak ada mapel' }}
                                                    </p>
                                                    <p class="mt-2 sm:mt-0 sm:ml-6 flex items-center text-sm text-gray-500">
                                                        <i
                                                            class="fa-solid fa-calendar flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                        {{ $exam->tanggal_ujian->format('d M Y') }}
                                                    </p>
                                                </div>
                                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                    <i class="fa-solid fa-clock flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                    {{ $exam->waktu_mulai }} - {{ $exam->waktu_selesai }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-5 sm:px-6 text-center">
                            <p class="text-gray-500">Tidak ada jadwal ujian mendatang</p>
                        </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="{{ route('naskah.jadwalujian.index') }}"
                            class="font-medium text-blue-600 hover:text-blue-500">
                            Lihat semua jadwal ujian
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (!isset($error))
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Implementation goes here
                // This will be added once the dashboard is working
            });
        </script>
    @endif
@endsection
