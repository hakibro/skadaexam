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
                        <div class="text-3xl font-bold text-gray-800">{{ count($assignments) }}</div>
                        <div class="text-gray-600 font-medium">Ujian Hari Ini</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        @php
                            $totalSiswa = 0;
                            foreach ($assignments as $assignment) {
                                $totalSiswa += $assignment->sesiRuanganSiswa->count();
                            }
                        @endphp
                        <div class="text-3xl font-bold text-gray-800">{{ $totalSiswa }}</div>
                        <div class="text-gray-600 font-medium">Total Siswa</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                        <i class="fa-solid fa-calendar-check text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ count($upcomingAssignments) }}</div>
                        <div class="text-gray-600 font-medium">Jadwal Mendatang</div>
                        <div class="text-xs text-yellow-600 mt-1">Setelah hari ini</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                        <i class="fa-solid fa-clipboard-check text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ count($pastAssignments) }}</div>
                        <div class="text-gray-600 font-medium">Ujian Selesai</div>
                        <div class="text-xs text-purple-600 mt-1">Sebelum hari ini</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Assignments -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-calendar-day text-green-600 mr-2"></i>
                Jadwal Pengawasan Hari Ini
            </h3>

            @if (count($assignments) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruangan</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah Siswa</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($assignments as $assignment)
                                @php
                                    $jadwalUjians = $assignment->jadwalUjians;
                                    $mapelNames = [];
                                    $kodeJadwals = [];

                                    foreach ($jadwalUjians as $jadwal) {
                                        if ($jadwal->mapel) {
                                            $mapelNames[] = $jadwal->mapel->nama_mapel;
                                        } else {
                                            $mapelNames[] = 'Tidak ada mapel';
                                        }
                                        $kodeJadwals[] = $jadwal->kode_jadwal ?? '-';
                                    }

                                    $mapelDisplay =
                                        count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                                    $kodeDisplay = count($kodeJadwals) > 0 ? implode(', ', $kodeJadwals) : '-';
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $mapelDisplay }}</div>
                                        <div class="text-sm text-gray-500">Kode: {{ $kodeDisplay }}</div>
                                        @if (count($jadwalUjians) > 1)
                                            <div class="text-xs text-blue-600 mt-1">{{ count($jadwalUjians) }} Ujian</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ $assignment->ruangan ? $assignment->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $assignment->nama_sesi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->waktu_mulai }} -
                                            {{ $assignment->waktu_selesai }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $assignment->sesiRuanganSiswa->count() }} Siswa
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assignment->status_badge_class }}">
                                            {{ $assignment->status_label['text'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('pengawas.generate-token', $assignment->id) }}"
                                                class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded-md">
                                                <i class="fa-solid fa-key mr-1"></i> Token
                                            </a>
                                            <a href="{{ route('pengawas.assignment', $assignment->id) }}"
                                                class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded-md">
                                                <i class="fa-solid fa-users mr-1"></i> Absen
                                            </a>
                                            <a href="{{ route('pengawas.berita-acara.show', $assignment->id) }}"
                                                class="text-white bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded-md">
                                                <i class="fa-solid fa-clipboard mr-1"></i> Berita Acara
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="inline-block p-4 rounded-full bg-yellow-100 mb-4">
                        <i class="fa-solid fa-calendar-times text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Tidak Ada Jadwal Pengawasan Hari Ini</h3>
                    <p class="text-gray-500 mt-1">Anda tidak memiliki tugas pengawasan untuk hari ini.</p>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-key text-green-600 mr-2"></i>
                    Generate Token Ujian
                </h3>
                <p class="text-gray-600 mb-4">Generate token untuk siswa login ke sistem ujian.</p>

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <label for="sesi_select" class="block text-sm font-medium text-gray-700 mb-2">Pilih Sesi
                        Ruangan:</label>
                    <select id="sesi_select" class="block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">-- Pilih Sesi --</option>
                        @foreach ($assignments as $assignment)
                            @php
                                $jadwalUjians = $assignment->jadwalUjians;
                                $mapelNames = [];

                                foreach ($jadwalUjians as $jadwal) {
                                    if ($jadwal->mapel) {
                                        $mapelNames[] = $jadwal->mapel->nama_mapel;
                                    } else {
                                        $mapelNames[] = 'Tidak ada mapel';
                                    }
                                }

                                $mapelDisplay =
                                    count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                            @endphp
                            <option value="{{ $assignment->id }}">
                                {{ $mapelDisplay }} -
                                {{ $assignment->ruangan ? $assignment->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                                ({{ $assignment->waktu_mulai }} - {{ $assignment->waktu_selesai }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <a href="#" id="generate_token_btn"
                    class="inline-block text-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-key mr-2"></i>
                    Generate Token
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fa-solid fa-file-alt text-blue-600 mr-2"></i>
                    Berita Acara Ujian
                </h3>
                <p class="text-gray-600 mb-4">Buat dan kelola berita acara hasil pengawasan ujian.</p>

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <label for="berita_acara_select" class="block text-sm font-medium text-gray-700 mb-2">Pilih Sesi
                        Ruangan:</label>
                    <select id="berita_acara_select" class="block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">-- Pilih Sesi --</option>
                        @foreach ($assignments as $assignment)
                            @php
                                $jadwalUjians = $assignment->jadwalUjians;
                                $mapelNames = [];

                                foreach ($jadwalUjians as $jadwal) {
                                    if ($jadwal->mapel) {
                                        $mapelNames[] = $jadwal->mapel->nama_mapel;
                                    } else {
                                        $mapelNames[] = 'Tidak ada mapel';
                                    }
                                }

                                $mapelDisplay =
                                    count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                            @endphp
                            <option value="{{ $assignment->id }}">
                                {{ $mapelDisplay }} -
                                {{ $assignment->ruangan ? $assignment->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                                ({{ $assignment->waktu_mulai }} - {{ $assignment->waktu_selesai }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <a href="#" id="berita_acara_btn"
                    class="inline-block text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-clipboard mr-2"></i>
                    Buat Berita Acara
                </a>
            </div>
        </div>

        <!-- Upcoming Assignments -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-calendar-alt text-yellow-600 mr-2"></i>
                Jadwal Pengawasan Mendatang
            </h3>

            <div class="mb-2 bg-yellow-50 text-yellow-700 p-3 rounded border border-yellow-200">
                <i class="fa-solid fa-info-circle mr-2"></i>
                Menampilkan jadwal pengawasan untuk tanggal setelah hari ini
            </div>

            @if (count($upcomingAssignments) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruangan</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($upcomingAssignments as $assignment)
                                @php
                                    $jadwalUjians = $assignment->jadwalUjians;
                                    $mapelNames = [];
                                    $kodeJadwals = [];
                                    $tanggal = '-';

                                    foreach ($jadwalUjians as $jadwal) {
                                        if ($jadwal->mapel) {
                                            $mapelNames[] = $jadwal->mapel->nama_mapel;
                                        } else {
                                            $mapelNames[] = 'Tidak ada mapel';
                                        }
                                        $kodeJadwals[] = $jadwal->kode_jadwal ?? '-';

                                        // Use the first available date for display
                                        if ($tanggal === '-' && $jadwal->tanggal) {
                                            $tanggal = $jadwal->tanggal->format('d M Y');
                                        }
                                    }

                                    $mapelDisplay =
                                        count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                                    $kodeDisplay = count($kodeJadwals) > 0 ? implode(', ', $kodeJadwals) : '-';
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $tanggal }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $mapelDisplay }}</div>
                                        <div class="text-sm text-gray-500">Kode: {{ $kodeDisplay }}</div>
                                        @if (count($jadwalUjians) > 1)
                                            <div class="text-xs text-blue-600 mt-1">{{ count($jadwalUjians) }} Ujian</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ $assignment->ruangan ? $assignment->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $assignment->nama_sesi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->waktu_mulai }} -
                                            {{ $assignment->waktu_selesai }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="inline-block p-4 rounded-full bg-yellow-100 mb-4">
                        <i class="fa-solid fa-calendar-times text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Tidak Ada Jadwal Pengawasan Mendatang</h3>
                    <p class="text-gray-500 mt-1">Anda tidak memiliki tugas pengawasan untuk hari-hari mendatang.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateTokenBtn = document.getElementById('generate_token_btn');
            const sesiSelect = document.getElementById('sesi_select');
            const beritaAcaraBtn = document.getElementById('berita_acara_btn');
            const beritaAcaraSelect = document.getElementById('berita_acara_select');

            // Handle token generation button
            generateTokenBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const sesiId = sesiSelect.value;
                if (!sesiId) {
                    alert('Silahkan pilih sesi ruangan terlebih dahulu');
                    return;
                }

                window.location.href = `/features/pengawas/generate-token/${sesiId}`;
            });

            // Handle berita acara button
            beritaAcaraBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const sesiId = beritaAcaraSelect.value;
                if (!sesiId) {
                    alert('Silahkan pilih sesi ruangan terlebih dahulu');
                    return;
                }

                window.location.href = `/features/pengawas/berita-acara/${sesiId}`;
            });

            // Initially disable buttons if no selection
            function updateButtons() {
                generateTokenBtn.disabled = !sesiSelect.value;
                beritaAcaraBtn.disabled = !beritaAcaraSelect.value;

                // Update href directly
                if (sesiSelect.value) {
                    generateTokenBtn.href = `/features/pengawas/generate-token/${sesiSelect.value}`;
                } else {
                    generateTokenBtn.href = '#';
                }

                if (beritaAcaraSelect.value) {
                    beritaAcaraBtn.href = `/features/pengawas/berita-acara/${beritaAcaraSelect.value}`;
                } else {
                    beritaAcaraBtn.href = '#';
                }
            }

            sesiSelect.addEventListener('change', updateButtons);
            beritaAcaraSelect.addEventListener('change', updateButtons);

            // Initial update
            updateButtons();
        });
    </script>
@endsection
