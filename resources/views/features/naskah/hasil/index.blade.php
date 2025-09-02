@extends('layouts.admin')

@section('title', 'Hasil Ujian')
@section('page-title', 'Daftar Hasil Ujian')
@section('page-description', 'Manajemen hasil ujian siswa')

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex flex-wrap justify-between items-center">
            <div class="flex space-x-2 mb-2 sm:mb-0">
                <a href="{{ route('naskah.dashboard') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <!-- Filter Form -->
            <div class="flex-1 sm:flex-none">
                <form action="{{ route('naskah.hasil.index') }}" method="GET" class="flex flex-wrap gap-2 sm:justify-end">
                    <select name="jadwal_id"
                        class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">-- Semua Jadwal --</option>
                        @foreach ($jadwalUjians as $jadwal)
                            <option value="{{ $jadwal->id }}" {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                {{-- {{ $jadwal->judul }} ({{ $jadwal->tanggal_ujian->format('d/m/Y') }}) --}}
                                {{ $jadwal->judul }} ({{ optional($jadwal->tanggal_ujian)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>

                    <select name="kelas_id"
                        class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fa-solid fa-filter mr-2"></i> Filter
                    </button>

                    @if (request('jadwal_id') || request('kelas_id'))
                        <a href="{{ route('naskah.hasil.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fa-solid fa-times mr-2"></i> Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Status Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Hasil Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Hasil Ujian</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalHasil }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">{{ $completedHasil }} Selesai,
                        {{ $totalHasil - $completedHasil }} Belum Selesai</dd>
                </div>
            </div>

            <!-- Average Score Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Rata-rata Nilai</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $averageScore }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">Dari {{ $completedHasil }} hasil yang selesai</dd>
                </div>
            </div>

            <!-- Pass Rate Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Tingkat Kelulusan</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $passRate }}%</dd>
                    <dd class="mt-1 text-sm text-gray-500">{{ $passedCount }} dari {{ $completedHasil }} lulus KKM</dd>
                </div>
            </div>

            <!-- Latest Result Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Hasil Ujian Terbaru</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ $latestHasil ? $latestHasil->created_at->format('d/m') : '-' }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">
                        {{ $latestHasil ? $latestHasil->created_at->format('H:i') . ' WIB' : 'Tidak ada data' }}</dd>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Daftar Hasil Ujian
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Total {{ $hasilUjians->total() }} hasil ujian
                    {{ request('jadwal_id') ? 'pada jadwal yang dipilih' : '' }}
                    {{ request('kelas_id') ? 'untuk kelas yang dipilih' : '' }}
                </p>
            </div>

            @if ($hasilUjians->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nilai
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($hasilUjians as $hasil)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $hasil->siswa->nama ?? 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $hasil->siswa->nis ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $hasil->jadwalUjian->judul ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $hasil->jadwalUjian ? $hasil->jadwalUjian->tanggal_ujian->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $hasil->jadwalUjian->sesiUjian->bankSoal->mapel->nama_mapel ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $hasil->siswa->kelas->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($hasil->status === 'selesai')
                                            <div
                                                class="text-sm font-bold {{ $hasil->nilai_akhir >= ($hasil->jadwalUjian->sesiUjian->bankSoal->mapel->kkm ?? 75) ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($hasil->nilai_akhir, 2) }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500">-</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($hasil->status === 'selesai')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Sedang Berlangsung
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-sm text-gray-900">{{ $hasil->created_at->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @if ($hasil->waktu_selesai)
                                                Selesai: {{ $hasil->waktu_selesai->format('H:i') }}
                                            @else
                                                Belum selesai
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('naskah.hasil.show', $hasil->id) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $hasilUjians->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fa-solid fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 text-lg">Tidak ada hasil ujian yang tersedia</p>
                    <p class="text-gray-400 text-sm mt-1">
                        @if (request('jadwal_id') || request('kelas_id'))
                            Coba ubah filter atau <a href="{{ route('naskah.hasil.index') }}"
                                class="text-blue-500 hover:underline">reset filter</a>
                        @else
                            Hasil ujian akan ditampilkan saat siswa mengikuti ujian
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Export Options -->
        @if ($hasilUjians->count() > 0)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Ekspor Hasil Ujian
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ekspor data hasil ujian dalam format yang Anda butuhkan
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6 flex flex-wrap gap-3">
                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <i class="fa-solid fa-file-excel mr-2"></i> Export Excel
                    </a>
                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fa-solid fa-file-csv mr-2"></i> Export CSV
                    </a>
                    <a href="{{ route('naskah.hasil.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                        <i class="fa-solid fa-file-pdf mr-2"></i> Export PDF
                    </a>
                </div>
            </div>
        @endif

        <!-- Analytics Section -->
        @if ($hasilUjians->count() > 0)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Analisis Hasil Ujian
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ringkasan dan analisis hasil ujian
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center">
                        <p class="text-gray-500 mb-3">
                            Lihat analisis lengkap dari hasil ujian untuk memahami performa siswa secara detail
                        </p>
                        <a href="{{ route('naskah.hasil.analisis', request()->all()) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fa-solid fa-chart-line mr-2"></i> Lihat Analisis Lengkap
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
