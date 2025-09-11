@extends('layouts.admin')

@section('title', 'Berita Acara Ujian')
@section('page-title', 'Berita Acara Ujian')
@section('page-description', 'Informasi dan laporan pelaksanaan ujian')

@section('content')
    <div>
        <div class="mb-6">
            <a href="{{ route('pengawas.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-700">
                        <i class="fa-solid fa-clipboard-list mr-2"></i>
                        Berita Acara Ujian
                    </h2>
                    <p class="text-gray-600 mt-1">Informasi dan laporan pelaksanaan ujian</p>
                </div>

                @if (!$beritaAcara || !$beritaAcara->is_final)
                    <div>
                        @if ($beritaAcara)
                            <a href="{{ route('pengawas.berita-acara.edit', $sesiRuangan->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition">
                                <i class="fa-solid fa-edit mr-2"></i>
                                Edit Berita Acara
                            </a>
                        @else
                            <a href="{{ route('pengawas.berita-acara.create', $sesiRuangan->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Buat Berita Acara
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Informasi Ujian</h3>
                    <table class="w-full">
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Mata Pelajaran</td>
                            <td class="py-1 font-bold">
                                @php
                                    $jadwalUjian = $sesiRuangan->jadwalUjians->first();
                                    $mapel = $jadwalUjian
                                        ? ($jadwalUjian->mapel
                                            ? $jadwalUjian->mapel->nama
                                            : 'Tidak ada mapel')
                                        : 'Tidak ada jadwal';
                                @endphp
                                {{ $mapel }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Ruangan</td>
                            <td class="py-1 font-bold">
                                {{ $sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Sesi</td>
                            <td class="py-1 font-bold">{{ $sesiRuangan->nama_sesi }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Waktu</td>
                            <td class="py-1 font-bold">{{ $sesiRuangan->waktu_mulai }} - {{ $sesiRuangan->waktu_selesai }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Status Sesi</td>
                            <td class="py-1">
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesiRuangan->status_badge_class }}">
                                    {{ $sesiRuangan->status_label['text'] }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Informasi Kehadiran</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-blue-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->count() }}
                            </div>
                            <div class="text-sm text-blue-600">Total Siswa</div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->where('status', 'hadir')->count() }}
                            </div>
                            <div class="text-sm text-green-600">Siswa Hadir</div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-red-700">
                                {{ $sesiRuangan->sesiRuanganSiswa->whereIn('status', ['tidak_hadir', 'sakit', 'izin'])->count() }}
                            </div>
                            <div class="text-sm text-red-600">Tidak Hadir</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($beritaAcara)
                <!-- Berita Acara Status -->
                <div class="mt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Status Berita Acara</h3>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-700">Status Verifikasi:</span>
                            <span
                                class="ml-2 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $beritaAcara->verification_badge_class }}">
                                {{ $beritaAcara->verification_status_text }}
                            </span>
                        </div>

                        <div>
                            <span class="font-medium text-gray-700">Status Pelaksanaan:</span>
                            <span
                                class="ml-2 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $beritaAcara->status_badge_class }}">
                                {{ $beritaAcara->status_text }}
                            </span>
                        </div>

                        <div>
                            @if ($beritaAcara->is_final)
                                <span class="font-medium text-gray-700">Difinalisasi pada:</span>
                                <span
                                    class="ml-2 text-gray-800">{{ $beritaAcara->waktu_finalisasi->format('d M Y H:i') }}</span>
                            @else
                                <form action="{{ route('pengawas.berita-acara.finalize', $sesiRuangan->id) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Apakah Anda yakin ingin memfinalisasi berita acara ini? Berita acara yang sudah difinalisasi tidak dapat diubah lagi.')"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                        <i class="fa-solid fa-check-double mr-2"></i>
                                        Finalisasi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Berita Acara Details -->
                <div class="mt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Rincian Berita Acara</h3>

                    <div class="border rounded-lg overflow-hidden">
                        <!-- Tab headers -->
                        <div class="flex border-b">
                            <button
                                class="tab-btn active px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-500"
                                data-tab="pembukaan">Pembukaan</button>
                            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                                data-tab="pelaksanaan">Pelaksanaan</button>
                            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                                data-tab="penutupan">Penutupan</button>
                            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                                data-tab="statistik">Statistik</button>
                        </div>

                        <!-- Tab contents -->
                        <div class="p-4">
                            <!-- Pembukaan -->
                            <div class="tab-content active" id="pembukaan-content">
                                <div class="prose max-w-none">
                                    <h4 class="text-lg font-medium text-gray-800 mb-2">Catatan Pembukaan</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[100px]">
                                        {!! nl2br(e($beritaAcara->catatan_pembukaan ?: 'Tidak ada catatan pembukaan')) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Pelaksanaan -->
                            <div class="tab-content hidden" id="pelaksanaan-content">
                                <div class="prose max-w-none">
                                    <h4 class="text-lg font-medium text-gray-800 mb-2">Catatan Pelaksanaan</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[100px]">
                                        {!! nl2br(e($beritaAcara->catatan_pelaksanaan ?: 'Tidak ada catatan pelaksanaan')) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Penutupan -->
                            <div class="tab-content hidden" id="penutupan-content">
                                <div class="prose max-w-none">
                                    <h4 class="text-lg font-medium text-gray-800 mb-2">Catatan Penutupan</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[100px]">
                                        {!! nl2br(e($beritaAcara->catatan_penutupan ?: 'Tidak ada catatan penutupan')) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Statistik -->
                            <div class="tab-content hidden" id="statistik-content">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="text-sm text-blue-600 mb-1">Terdaftar</div>
                                        <div class="text-2xl font-bold text-blue-700">
                                            {{ $beritaAcara->jumlah_peserta_terdaftar }}</div>
                                    </div>

                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div class="text-sm text-green-600 mb-1">Hadir</div>
                                        <div class="text-2xl font-bold text-green-700">
                                            {{ $beritaAcara->jumlah_peserta_hadir }}</div>
                                    </div>

                                    <div class="bg-red-50 p-4 rounded-lg">
                                        <div class="text-sm text-red-600 mb-1">Tidak Hadir</div>
                                        <div class="text-2xl font-bold text-red-700">
                                            {{ $beritaAcara->jumlah_peserta_tidak_hadir }}</div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-800 mb-2">Persentase Kehadiran</h5>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-green-600 h-4 rounded-full"
                                            style="width: {{ $beritaAcara->attendance_percentage }}%"></div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600 text-right">
                                        {{ $beritaAcara->attendance_percentage }}%
                                        ({{ $beritaAcara->jumlah_peserta_hadir }}/{{ $beritaAcara->jumlah_peserta_terdaftar }})
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Berita acara untuk ujian ini belum dibuat.
                                <a href="{{ route('pengawas.berita-acara.create', $sesiRuangan->id) }}"
                                    class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                    Buat berita acara sekarang
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Daftar Siswa -->
            <div class="mt-8">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Daftar Kehadiran Siswa</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIS
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sesiRuangan->sesiRuanganSiswa as $index => $sesiSiswa)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sesiSiswa->siswa->nis ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sesiSiswa->siswa->nama ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sesiSiswa->siswa->kelas->nama_kelas ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match ($sesiSiswa->status) {
                                                'hadir' => 'bg-green-100 text-green-800',
                                                'tidak_hadir' => 'bg-red-100 text-red-800',
                                                'sakit' => 'bg-yellow-100 text-yellow-800',
                                                'izin' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };

                                            $statusText = match ($sesiSiswa->status) {
                                                'hadir' => 'Hadir',
                                                'tidak_hadir' => 'Tidak Hadir',
                                                'sakit' => 'Sakit',
                                                'izin' => 'Izin',
                                                default => 'Tidak Diketahui',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        Tidak ada siswa yang terdaftar dalam sesi ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.getAttribute('data-tab');

                    // Deactivate all tabs
                    tabBtns.forEach(b => b.classList.remove('active', 'text-blue-600', 'border-b-2',
                        'border-blue-500'));
                    tabBtns.forEach(b => b.classList.add('text-gray-500'));
                    tabContents.forEach(c => c.classList.add('hidden'));

                    // Activate selected tab
                    btn.classList.add('active', 'text-blue-600', 'border-b-2', 'border-blue-500');
                    btn.classList.remove('text-gray-500');
                    document.getElementById(`${tabId}-content`).classList.remove('hidden');
                });
            });
        });
    </script>
@endsection
