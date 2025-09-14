@extends('layouts.admin')

@section('title', 'Hasil Ujian')
@section('page-title', 'Daftar Hasil Ujian')
@section('page-description', 'Manajemen hasil ujian siswa')

@section('content')
    <div class="space-y-6">
        <!-- Header Section with Stats -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Hasil Ujian</h2>
                <p class="mt-1 text-sm text-gray-500">Pantau dan analisis hasil ujian dari semua siswa</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                <!-- Total Results Card -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Total Hasil</h3>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalHasil) }}</p>
                                <p class="ml-2 text-sm text-gray-500">hasil ujian</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $completedHasil }} selesai,
                                {{ $totalHasil - $completedHasil }} dalam proses</p>
                        </div>
                    </div>
                </div>

                <!-- Average Score Card -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Rata-Rata Nilai</h3>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900">{{ $averageScore }}</p>
                                <p class="ml-2 text-sm text-gray-500">dari 100</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Berdasarkan {{ $completedHasil }} ujian yang selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Pass Rate Card -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Tingkat Kelulusan</h3>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900">{{ $passRate }}%</p>
                                <p class="ml-2 text-sm text-gray-500">lulus</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $passedCount }} dari {{ $completedHasil }} lulus KKM
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Latest Result Card -->
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Hasil Terbaru</h3>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $latestHasil ? $latestHasil->created_at->format('d/m') : '-' }}
                                </p>
                                <p class="ml-2 text-sm text-gray-500">
                                    {{ $latestHasil ? $latestHasil->created_at->format('H:i') . ' WIB' : '' }}
                                </p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $latestHasil ? $latestHasil->siswa->nama ?? 'Siswa' : 'Belum ada data' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-800">Filter Hasil Ujian</h3>
                <a href="{{ route('naskah.dashboard') }}" class="text-blue-600 hover:underline text-sm">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="p-6">
                <form action="{{ route('naskah.hasil.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Jadwal Filter -->
                    <div>
                        <label for="jadwal_id" class="block text-sm font-medium text-gray-700 mb-1">Jadwal Ujian</label>
                        <select id="jadwal_id" name="jadwal_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="">Semua Jadwal</option>
                            @foreach ($jadwalUjians as $jadwal)
                                <option value="{{ $jadwal->id }}"
                                    {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                    {{ $jadwal->judul }} ({{ optional($jadwal->tanggal)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelas Filter -->
                    <div>
                        <label for="kelas_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select id="kelas_id" name="kelas_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}"
                                    {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Ujian</label>
                        <select id="status" name="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="">Semua Status</option>
                            <option value="belum_mulai" {{ request('status') == 'belum_mulai' ? 'selected' : '' }}>Belum
                                Mulai</option>
                            <option value="berlangsung" {{ request('status') == 'berlangsung' ? 'selected' : '' }}>Sedang
                                Berlangsung</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai
                            </option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Siswa</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                placeholder="Nama atau NIS siswa...">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-span-1 md:col-span-4 flex space-x-3 mt-2">
                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                            <i class="fa-solid fa-filter mr-2"></i> Terapkan Filter
                        </button>

                        @if (request()->hasAny(['jadwal_id', 'kelas_id', 'status', 'search']))
                            <a href="{{ route('naskah.hasil.index') }}"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 flex items-center">
                                <i class="fa-solid fa-times mr-2"></i> Reset Filter
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Daftar Hasil Ujian</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $hasilUjians->total() }} hasil ditemukan
                        {{ request('search') ? 'untuk pencarian "' . request('search') . '"' : '' }}
                    </p>
                </div>

                @if ($hasilUjians->count() > 0)
                    <div class="hidden md:block">
                        <a href="{{ route('naskah.hasil.analisis', request()->all()) }}"
                            class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-md hover:bg-indigo-100 inline-flex items-center">
                            <i class="fa-solid fa-chart-line mr-2"></i> Analisis Lanjutan
                        </a>
                    </div>
                @endif
            </div>

            @if ($hasilUjians->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Siswa
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas / Sesi
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nilai
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($hasilUjians as $hasil)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $hasil->siswa->nama ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    IDYYS: {{ $hasil->siswa->idyayasan ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $hasil->jadwalUjian->mapel->nama_mapel ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $hasil->jadwalUjian->judul ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $hasil->siswa->kelas->nama_kelas ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $hasil->sesiRuangan ? $hasil->sesiRuangan->nama_sesi : 'Tidak ada sesi' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($hasil->status === 'selesai')
                                            <div class="flex items-center">
                                                <span
                                                    class="text-sm font-semibold {{ $hasil->lulus ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($hasil->nilai, 2) }}
                                                </span>
                                                @if ($hasil->lulus)
                                                    <span
                                                        class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Lulus
                                                    </span>
                                                @else
                                                    <span
                                                        class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Tidak Lulus
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Benar: {{ $hasil->jumlah_benar }}/{{ $hasil->jumlah_soal }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">
                                                Belum ada nilai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($hasil->status === 'selesai')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                        @elseif ($hasil->status === 'berlangsung')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Sedang Ujian
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Belum Mulai
                                            </span>
                                        @endif

                                        @if ($hasil->status === 'berlangsung' && $hasil->waktu_mulai)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Mulai: {{ $hasil->waktu_mulai->format('H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ optional($hasil->created_at)->format('d/m/Y') ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @if ($hasil->waktu_selesai)
                                                Selesai: {{ $hasil->waktu_selesai->format('H:i') }}
                                                <span title="Durasi Ujian">
                                                    ({{ $hasil->durasi_menit ?? '?' }} menit)
                                                </span>
                                            @else
                                                {{ optional($hasil->created_at)->format('H:i') ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('naskah.hasil.show', $hasil->id) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>

                                        @if ($hasil->jadwalUjian)
                                            <a href="{{ route('naskah.hasil.by-jadwal', $hasil->jadwal_ujian_id) }}"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fa-solid fa-list"></i> Satu Jadwal
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $hasilUjians->withQueryString()->links() }}
                </div>
            @else
                <div class="py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-700">Tidak ada hasil ujian</h3>
                    <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                        @if (request()->hasAny(['jadwal_id', 'kelas_id', 'status', 'search']))
                            Tidak ada hasil yang sesuai dengan filter yang dipilih.
                            <a href="{{ route('naskah.hasil.index') }}" class="text-blue-600 hover:underline">Reset
                                filter</a>
                        @else
                            Hasil ujian akan muncul saat siswa mulai mengikuti ujian. Pastikan jadwal ujian telah dibuat dan
                            siswa sudah terdaftar.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Export Options -->
        @if ($hasilUjians->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800">
                        Ekspor Data Hasil Ujian
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ekspor data hasil ujian dalam format yang Anda butuhkan untuk analisis lebih lanjut
                    </p>
                </div>
                <div class="p-6 flex flex-wrap gap-4">
                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Excel (.xlsx)
                    </a>

                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        CSV
                    </a>

                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        PDF
                    </a>

                    @if (request()->hasAny(['jadwal_id', 'kelas_id', 'status', 'search']))
                        <p class="text-xs text-gray-500 mt-2 flex-full">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Data yang diekspor sesuai dengan filter yang dipilih.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Analytics Section -->
        @if ($hasilUjians->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800">
                        Analisis Lanjutan
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Lihat analisis detail untuk hasil ujian
                    </p>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-center md:text-left">
                            <p class="text-gray-600 max-w-xl">
                                Lihat analisis lengkap dari hasil ujian untuk memahami performa siswa secara mendalam,
                                termasuk
                                distribusi nilai, perbandingan antar kelas, tingkat kesulitan soal, dan masih banyak lagi.
                            </p>
                        </div>
                        <a href="{{ route('naskah.hasil.analisis', request()->all()) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            Lihat Analisis Lanjutan
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form when jadwal_id or kelas_id changes
            document.getElementById('jadwal_id').addEventListener('change', function() {
                this.form.submit();
            });

            document.getElementById('kelas_id').addEventListener('change', function() {
                this.form.submit();
            });

            document.getElementById('status').addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
@endpush
