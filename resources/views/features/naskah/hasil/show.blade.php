@extends('layouts.admin')

@section('title', 'Detail Hasil Ujian')
@section('page-title', 'Detail Hasil Ujian')
@section('page-description', ($hasil->siswa->nama ?? 'Siswa') . ' - ' . ($hasil->jadwalUjian->judul ?? 'Jadwal Ujian'))

@section('content')
    @php
        $nilai = (float) ($hasil->nilai ?? 0);
        $kkm = data_get($hasil, 'jadwalUjian.pengaturan.kkm', $mapel->kkm ?? 75);
        $isSelesai = $hasil->status === 'selesai';
        $statusClass = $hasil->lulus ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('naskah.hasil.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('naskah.hasil.print', $hasil) }}" target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-2"></i> Cetak
                </a>
                <a href="{{ route('naskah.hasil.jawaban', $hasil) }}"
                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fa-solid fa-list-check mr-2"></i> Detail Jawaban
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Nilai</p>
                <div class="mt-2 flex items-end justify-between">
                    <p class="text-4xl font-bold {{ $hasil->lulus ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($nilai, 2) }}
                    </p>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isSelesai ? $statusClass : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $isSelesai ? ($hasil->lulus ? 'Lulus' : 'Tidak Lulus') : ucfirst(str_replace('_', ' ', $hasil->status)) }}
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-500">KKM {{ number_format((float) $kkm, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Jawaban</p>
                <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                    <div>
                        <p class="text-2xl font-semibold text-green-600">{{ $hasil->jumlah_benar ?? $jawabanStats['benar'] }}</p>
                        <p class="text-xs text-gray-500">Benar</p>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-yellow-600">{{ $jawabanStats['parsial'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Parsial</p>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-red-600">{{ $hasil->jumlah_salah ?? $jawabanStats['salah'] }}</p>
                        <p class="text-xs text-gray-500">Salah</p>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-gray-600">{{ $hasil->jumlah_tidak_dijawab ?? $jawabanStats['kosong'] }}</p>
                        <p class="text-xs text-gray-500">Kosong</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Peringkat Jadwal</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                    {{ $peerStats['rank'] ? '#' . $peerStats['rank'] : '-' }}
                    <span class="text-base font-medium text-gray-500">/ {{ $peerStats['total'] }}</span>
                </p>
                <p class="mt-2 text-xs text-gray-500">Rata-rata jadwal {{ number_format($peerStats['avg_jadwal'], 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Durasi</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $hasil->durasi_menit ? $hasil->durasi_menit . ' mnt' : $hasil->getDurationFormatted() }}</p>
                <p class="mt-2 text-xs text-gray-500">Rata-rata kelas {{ number_format($peerStats['avg_kelas'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Ujian</h3>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        @foreach ([
                            'Nama Siswa' => $hasil->siswa->nama ?? '-',
                            'NIS' => $hasil->siswa->nis ?? '-',
                            'Kelas' => $hasil->siswa->kelas->nama_kelas ?? '-',
                            'Mata Pelajaran' => $mapel->nama_mapel ?? '-',
                            'Jadwal' => $hasil->jadwalUjian->judul ?? '-',
                            'Tanggal' => $hasil->jadwalUjian->tanggal ?? '-',
                            'Bank Soal' => $hasil->jadwalUjian->bankSoal->judul ?? '-',
                            'Sesi / Ruangan' => trim(($hasil->sesiRuangan->nama_sesi ?? '-') . ' / ' . ($hasil->sesiRuangan->ruangan->nama_ruangan ?? '-')),
                        ] as $label => $value)
                            <div class="px-5 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Ringkasan Jawaban</h3>
                        <a href="{{ route('naskah.hasil.jawaban', $hasil) }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Soal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jawaban</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse(collect($answerRows)->take(10) as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['nomor'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($row['text'], 110) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['jawaban'] ?: '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $row['status'] === 'benar' ? 'bg-green-100 text-green-700' : ($row['status'] === 'parsial' ? 'bg-yellow-100 text-yellow-700' : ($row['status'] === 'salah' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                                {{ ucfirst($row['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Detail jawaban belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-lg font-medium text-gray-900">Timeline</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($timeline as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ $item['label'] }}</span>
                                <span class="font-medium text-gray-900">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-lg font-medium text-gray-900">Kategori Perlu Perhatian</h3>
                    <div class="mt-4 space-y-4">
                        @forelse($weakCategories as $kategori => $data)
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $kategori }}</span>
                                    <span class="text-gray-500">{{ $data['persentase'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded bg-gray-100">
                                    <div class="h-2 rounded bg-orange-500" style="width: {{ $data['persentase'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada analisis kategori.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Hasil Lain Siswa</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($otherResults as $other)
                            <a href="{{ route('naskah.hasil.show', $other) }}" class="block px-5 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $other->jadwalUjian->mapel->nama_mapel ?? $other->jadwalUjian->judul ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $other->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">{{ number_format((float) $other->nilai, 2) }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-center text-gray-500">Belum ada hasil lain.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
