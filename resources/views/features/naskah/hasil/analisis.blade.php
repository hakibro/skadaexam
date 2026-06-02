@extends('layouts.admin')

@section('title', 'Analisis Lanjutan Hasil Ujian')
@section('page-title', 'Analisis Lanjutan')
@section('page-description', 'Ringkasan performa, sebaran nilai, dan analisis butir soal')

@section('content')
    @php
        $filterParams = request()->only(['tahun_ajaran_id', 'paket_ujian_id', 'jadwal_id', 'kelas_id', 'tingkat', 'jurusan']);
        $rangeTotal = max((int) $totalHasil, 1);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <a href="{{ route('naskah.hasil.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('naskah.hasil.export', array_merge($filterParams, ['format' => 'xlsx'])) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-file-excel mr-2"></i> Export XLSX
                </a>
                <a href="{{ route('naskah.hasil.export', array_merge($filterParams, ['format' => 'csv'])) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-file-csv mr-2"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <form method="GET" action="{{ route('naskah.hasil.analisis') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
                <div>
                    <label for="tahun_ajaran_id" class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach ($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" @selected((string) $tahunAjaranId === (string) $tahun->id)>
                                {{ $tahun->nama }}{{ $tahun->is_active ? ' - Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="paket_ujian_id" class="block text-sm font-medium text-gray-700">Paket</label>
                    <select id="paket_ujian_id" name="paket_ujian_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Semua paket</option>
                        @foreach ($paketUjians as $paket)
                            <option value="{{ $paket->id }}" @selected(request('paket_ujian_id') == $paket->id)>
                                {{ $paket->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jadwal_id" class="block text-sm font-medium text-gray-700">Jadwal</label>
                    <select id="jadwal_id" name="jadwal_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Semua jadwal</option>
                        @foreach ($jadwalUjians as $jadwal)
                            <option value="{{ $jadwal->id }}" @selected(request('jadwal_id') == $jadwal->id)>
                                {{ $jadwal->judul }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="kelas_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected(request('kelas_id') == $kelas->id)>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat</label>
                    <select id="tingkat" name="tingkat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Semua tingkat</option>
                        @foreach ($tingkatList as $tingkat)
                            <option value="{{ $tingkat }}" @selected(request('tingkat') == $tingkat)>{{ $tingkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jurusan" class="block text-sm font-medium text-gray-700">Jurusan</label>
                    <select id="jurusan" name="jurusan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Semua jurusan</option>
                        @foreach ($jurusanList as $jurusan)
                            <option value="{{ $jurusan }}" @selected(request('jurusan') == $jurusan)>{{ $jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex w-full justify-center items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fa-solid fa-filter mr-2"></i> Terapkan
                    </button>
                    <a href="{{ route('naskah.hasil.analisis') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Total Hasil Selesai</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalHasil) }}</p>
                <p class="mt-1 text-xs text-gray-500">Data sesuai filter aktif</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Rata-rata Nilai</p>
                <p class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($avgNilai, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">Median {{ number_format($medianNilai, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Kelulusan</p>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($passRate, 1) }}%</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($passCount) }} siswa lulus</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Rentang Nilai</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($minNilai, 2) }} - {{ number_format($maxNilai, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">Durasi rata-rata {{ number_format($avgDurasi, 1) }} menit</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-lg font-medium text-gray-900">Distribusi Nilai</h3>
                <div class="mt-4 space-y-4">
                    @foreach ($scoreRanges as $range => $count)
                        @php $percent = round(($count / $rangeTotal) * 100, 1); @endphp
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">{{ $range }}</span>
                                <span class="text-gray-500">{{ $count }} hasil</span>
                            </div>
                            <div class="mt-2 h-2 rounded bg-gray-100">
                                <div class="h-2 rounded bg-blue-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-lg font-medium text-gray-900">Kategori Terlemah</h3>
                <div class="mt-4 space-y-4">
                    @forelse ($categoryAnalysis as $category)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">{{ $category['category'] }}</span>
                                <span class="text-gray-500">{{ $category['accuracy'] }}%</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $category['questions'] }} soal</p>
                            <div class="mt-2 h-2 rounded bg-gray-100">
                                <div class="h-2 rounded bg-orange-500" style="width: {{ $category['accuracy'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada data kategori.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-lg font-medium text-gray-900">Perbandingan Jurusan</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($jurusanComparison as $jurusan)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $jurusan['jurusan'] }}</p>
                                <p class="text-xs text-gray-500">{{ $jurusan['jumlah'] }} hasil</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ number_format($jurusan['rata_rata'], 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada data jurusan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Performa Kelas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lulus</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($kelasPerfomance as $kelas)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $kelas['kelas'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $kelas['jumlah'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($kelas['rata_rata'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $kelas['lulus'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Performa Jadwal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jadwal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lulus</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($jadwalComparison as $jadwal)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($jadwal['jadwal'], 45) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $jadwal['jumlah'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($jadwal['rata_rata'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $jadwal['lulus'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Analisis Butir Soal</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Soal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Benar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parsial</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kosong</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akurasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($questionAnalysis as $question)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $question['nomor'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($question['text'], 90) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $question['category'] }}</td>
                                <td class="px-4 py-3 text-sm text-green-700">{{ $question['correct'] }}</td>
                                <td class="px-4 py-3 text-sm text-yellow-700">{{ $question['partial'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-sm text-red-700">{{ $question['incorrect'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $question['blank'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $question['accuracy'] }}%</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $question['difficulty_label'] === 'Sulit' ? 'bg-red-100 text-red-700' : ($question['difficulty_label'] === 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $question['difficulty_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada detail jawaban untuk dianalisis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Siswa Terbaik</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($topStudents as $hasil)
                        <a href="{{ route('naskah.hasil.show', $hasil) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $hasil->siswa->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $hasil->siswa->kelas->nama_kelas ?? '-' }}</p>
                            </div>
                            <p class="text-sm font-semibold text-green-700">{{ number_format($hasil->nilai, 2) }}</p>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-center text-gray-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Perlu Perhatian</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($bottomStudents as $hasil)
                        <a href="{{ route('naskah.hasil.show', $hasil) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $hasil->siswa->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $hasil->siswa->kelas->nama_kelas ?? '-' }}</p>
                            </div>
                            <p class="text-sm font-semibold text-red-700">{{ number_format($hasil->nilai, 2) }}</p>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-center text-gray-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
