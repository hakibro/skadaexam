@extends('layouts.admin')

@section('title', 'Pengawas Dashboard')
@section('page-title', 'Pengawas Dashboard')
@section('page-description', 'Buat Token, Monitor Ujian, dan Laporan')

@section('content')
    <div>

        <!-- Modal -->
        <div id="tataTertibModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 h-5/6 p-4 relative">
                <!-- Tombol close -->
                <button onclick="document.getElementById('tataTertibModal').classList.add('hidden')"
                    class="absolute top-2 right-2 text-red-600 font-bold text-xl">
                    ✕
                </button>

                <h2 class="text-lg font-bold mb-4">Tata Tertib Ujian</h2>

                <!-- PDF embed -->
                <iframe src="{{ route('pengawas.tata-tertib') }}" class="w-full h-[85%] border rounded"></iframe>

            </div>
        </div>
        <!-- Statistics Cards -->
        <div class="flex flex-col md:flex-row gap-6 mb-8 justify-center">
            <!-- Card Tata Tertib -->
            <div onclick="document.getElementById('tataTertibModal').classList.remove('hidden')"
                class="cursor-pointer bg-white rounded-lg shadow-lg p-4 border-t-4 border-red-500 hover:shadow-xl transition flex flex-col items-center text-center">
                <div class="bg-red-100 text-red-600 p-3 rounded-full mb-2">
                    <i class="fa-solid fa-file-pdf text-xl"></i>
                </div>
                <div class="text-sm font-semibold text-red-700">Tata Tertib Ujian</div>
                <div class="text-gray-600 text-xs">Lihat tata tertib ujian terbaru</div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Card Ujian Hari Ini -->
                <div
                    class="bg-white rounded-lg shadow-lg p-4 border-t-4 border-green-500 flex flex-col items-center text-center">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full mb-2">
                        <i class="fa-solid fa-eye text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ count($assignments) }}</div>
                    <div class="text-gray-600 text-xs font-medium">Ujian Hari Ini</div>
                </div>
                <!-- Card Total Siswa -->
                <div
                    class="bg-white rounded-lg shadow-lg p-4 border-t-4 border-blue-500 flex flex-col items-center text-center">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full mb-2">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    @php
                        $totalSiswa = 0;
                        foreach ($assignments as $assignment) {
                            $totalSiswa += $assignment->sesiRuanganSiswa->count();
                        }
                    @endphp
                    <div class="text-2xl font-bold text-gray-800">{{ $totalSiswa }}</div>
                    <div class="text-gray-600 text-xs font-medium">Total Siswa</div>
                </div>
                <!-- Card Jadwal Mendatang -->
                <div
                    class="bg-white rounded-lg shadow-lg p-4 border-t-4 border-yellow-500 flex flex-col items-center text-center">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full mb-2">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ count($upcomingAssignments) }}</div>
                    <div class="text-gray-600 text-xs font-medium">Jadwal Mendatang</div>
                    <div class="text-xs text-yellow-600">Setelah hari ini</div>
                </div>
                <!-- Card Ujian Selesai -->
                <div
                    class="bg-white rounded-lg shadow-lg p-4 border-t-4 border-purple-500 flex flex-col items-center text-center">
                    <div class="bg-purple-100 text-purple-600 p-3 rounded-full mb-2">
                        <i class="fa-solid fa-clipboard-check text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ count($pastAssignments) }}</div>
                    <div class="text-gray-600 text-xs font-medium">Ujian Selesai</div>
                    <div class="text-xs text-purple-600">Sebelum hari ini</div>
                </div>
            </div>
        </div>



        <!-- Today's Assignments -->
        @php
            // Urutkan dulu assignments berdasarkan ruangan + waktu_mulai
            $sortedAssignments = $assignments->sortBy(function ($assignment) {
                $ruanganId = $assignment->ruangan->id ?? 0;
                $waktuMulai = $assignment->waktu_mulai ?? '00:00:00';
                return sprintf('%03d_%s', $ruanganId, $waktuMulai);
            });

            // Group assignments berdasarkan ruangan_id
            $groupedByRuangan = $sortedAssignments->groupBy(function ($assignment) {
                return $assignment->ruangan->id ?? 0;
            });
        @endphp

        @if (count($groupedByRuangan) > 0)
            @foreach ($groupedByRuangan as $ruanganId => $assignmentsInRuangan)
                @php
                    $ruanganName = $assignmentsInRuangan->first()->ruangan->nama_ruangan ?? 'Tidak ada ruangan';
                @endphp
                <!-- Card per Ruangan -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h4 class="font-bold text-lg text-gray-800 mb-4">
                        <i class="fa-solid fa-door-open text-green-600 mr-2"></i>
                        Ruangan: {{ $ruanganName }}
                    </h4>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @foreach ($assignmentsInRuangan as $assignment)
                            @php
                                $jadwalUjians = $assignment->jadwalUjians->sortBy('kode_jadwal');
                                $mapelNames = [];
                                foreach ($jadwalUjians as $jadwal) {
                                    $mapelNames[] = $jadwal->mapel->nama_mapel ?? 'Tidak ada mapel';
                                }
                                $mapelDisplay =
                                    count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                            @endphp

                            <!-- Sesi Assignment -->
                            <div
                                class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row justify-between items-center md:items-center {{ $assignment->status_badge_class }} gap-4">

                                <!-- Info Sesi -->
                                <div class="flex-1 flex flex-col gap-2 text-center md:text-left">
                                    <!-- Nama Sesi & Status -->
                                    <div class="font-semibold text-gray-900 text-lg md:text-xl flex items-center gap-2">
                                        {{ $assignment->nama_sesi }}
                                        <span
                                            class="inline-flex items-center justify-center text-xs md:text-sm px-2 py-1 font-semibold rounded-full
            {{ $assignment->status_label['text'] === 'Berlangsung' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                            {{ $assignment->status_label['text'] }}
                                        </span>

                                    </div>
                                    <!-- Waktu Sesi -->
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-clock text-blue-500"></i>
                                        <span>{{ $assignment->waktu_mulai }} - {{ $assignment->waktu_selesai }}</span>
                                    </div>

                                    <!-- Info Grid -->
                                    <div class="flex gap-2 text-sm md:text-base text-gray-600">
                                        <!-- Jumlah Siswa -->
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-user-group text-green-500"></i>
                                            <span>{{ $assignment->sesiRuanganSiswa->count() }} Siswa</span>
                                        </div>

                                        <!-- Mapel -->
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-book text-yellow-500"></i>
                                            <span>{{ $mapelDisplay }}</span>
                                        </div>
                                    </div>
                                </div>




                                <!-- Tombol Actions -->
                                <div class="flex-1 flex flex-wrap gap-2 w-full md:w-auto mt-4 md:mt-0">
                                    @hasanyrole('admin|koordinator')
                                        <form action="{{ route('pengawas.toggle-submit', $assignment->id) }}" method="POST"
                                            class="inline-block flex-1 whitespace-nowrap">
                                            @csrf
                                            <button type="submit" name="tampilkan"
                                                value="{{ $assignment->tampilkan_tombol_submit ? 0 : 1 }}"
                                                class="flex items-center justify-center px-3 py-2 rounded-lg shadow-sm border transition
                        {{ $assignment->tampilkan_tombol_submit
                            ? 'bg-green-100 border-green-300 hover:bg-green-200'
                            : 'bg-gray-100 border-gray-300 hover:bg-gray-200' }}">
                                                <i
                                                    class="fas {{ $assignment->tampilkan_tombol_submit ? 'fa-toggle-on text-green-600' : 'fa-toggle-off text-gray-500' }} text-2xl"></i>
                                                <span
                                                    class="ml-2 font-semibold {{ $assignment->tampilkan_tombol_submit ? 'text-green-700' : 'text-gray-700' }}">
                                                    {{ $assignment->tampilkan_tombol_submit ? 'Tombol Submit Aktif' : 'Tombol Submit Nonaktif' }}
                                                </span>
                                            </button>
                                        </form>
                                    @endhasanyrole

                                    <a href="{{ route('pengawas.generate-token', $assignment->id) }}"
                                        class="flex-1 min-w-[120px] text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-center whitespace-nowrap">
                                        <i class="fa-solid fa-key mr-2"></i> Token
                                    </a>
                                    <a href="{{ route('pengawas.assignment', $assignment->id) }}"
                                        class="flex-1 min-w-[120px] text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-md text-center whitespace-nowrap">
                                        <i class="fa-solid fa-users mr-2"></i> Absen
                                    </a>
                                    <a href="{{ route('pengawas.manage-enrollment', $assignment->id) }}"
                                        class="flex-1 min-w-[120px] text-white bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded-md text-center whitespace-nowrap">
                                        <i class="fa-solid fa-user-cog mr-2"></i> Enrollment
                                    </a>
                                    <a href="{{ route('pengawas.berita-acara.show', $assignment->id) }}"
                                        class="flex-1 min-w-[120px] text-white bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-md text-center whitespace-nowrap">
                                        <i class="fa-solid fa-clipboard mr-2"></i> Berita Acara
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                <div class="inline-block p-4 rounded-full bg-yellow-100 mb-4">
                    <i class="fa-solid fa-calendar-times text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700">Tidak Ada Jadwal Pengawasan Hari Ini</h3>
                <p class="text-gray-500 mt-1">Anda tidak memiliki tugas pengawasan untuk hari ini.</p>
            </div>
        @endif




        <!-- Live Monitoring -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4 flex-wrap">
                <!-- Heading & Counter -->
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-shield-alt text-red-600"></i>
                        Pelanggaran Ujian
                    </h3>
                    <div class="px-3 py-1 text-sm font-bold rounded-full bg-red-100 text-red-700">
                        <span id="violation-counter">0</span> Pelanggaran
                    </div>
                </div>

                <!-- Dropdown & Refresh Button -->
                <div class="flex flex-wrap items-center gap-3">
                    @role('admin|koordinator')
                        <select id="monitoring_select" class="p-2 border border-gray-300 rounded-md text-sm">
                            <option value="all">Semua Ruangan Hari Ini</option>
                            @foreach ($assignments as $assignment)
                                @php
                                    $mapelDisplay = implode(
                                        ' + ',
                                        $assignment->jadwalUjians
                                            ->map(fn($jadwal) => $jadwal->mapel?->nama_mapel ?? 'Tidak ada mapel')
                                            ->toArray(),
                                    );
                                    $ruanganDisplay = $assignment->ruangan->nama_ruangan ?? 'Tidak ada ruangan';
                                @endphp
                                <option value="{{ $assignment->id }}">
                                    {{ $ruanganDisplay }}: {{ $mapelDisplay }}
                                </option>
                            @endforeach
                        </select>
                    @endrole

                    <button id="refresh-violations"
                        class="bg-blue-100 text-blue-700 p-2 rounded-md hover:bg-blue-200 flex items-center gap-1">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>



            <div id="violations-container" class="flex flex-col space-y-4">
                <div id="no-violations" class="text-center text-gray-500 p-6">
                    <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                    <p class="text-lg font-medium">Tidak ada pelanggaran yang terdeteksi</p>
                    <p class="text-sm mt-1">Semua siswa mengikuti ujian dengan tertib</p>
                </div>
                <!-- JS akan menambahkan card pelanggaran di sini -->
            </div>



            <div class="mt-4 text-gray-600 text-sm">
                <i class="fas fa-info-circle mr-1"></i>
                Sistem akan memperbarui data secara otomatis setiap 30 detik. Anda juga dapat menekan tombol refresh untuk
                memperbarui manual.
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

    <!-- Violation Action Modal -->
    <div id="violation-action-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-lg w-full border-2 border-orange-500">
                <div class="">


                    <!-- Student Info -->
                    <div id="violation-student-info" class="bg-gray-50 rounded-lg p-4 flex flex-col">
                        <div class="grid grid-cols-2 gap-1 text-sm">
                            <span id="modal-subject-name" class="font-semibold ml-1">-</span>
                            <span id="modal-student-name" class="font-semibold ml-1">-</span>
                            <span id="modal-violation-time" class="font-semibold ml-1">-</span>
                            <span id="modal-violation-type" class="font-semibold ml-1 text-red-600">-</span>
                        </div>
                        <div id="modal-violation-description"
                            class="bg-red-50 border border-red-200 rounded-lg p-2 mt-2 text-sm text-red-800">
                            -
                        </div>
                    </div>
                </div>



                <!-- Action Selection -->
                <div class="px-6 py-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tindakan:</label>
                    <div class="grid grid-cols-1">
                        <label
                            class="flex items-center p-2 cursor-pointer hover:bg-gray-50 has-[:checked]:border-gray-500 has-[:checked]:bg-gray-50">
                            <input type="radio" name="violation-action" value="dismiss" class="mr-3 text-gray-600">
                            <div>
                                <div class="font-medium text-gray-700">Abaikan Pelanggaran</div>
                                <div class="text-sm text-gray-500">Siswa dapat melanjutkan ujian tanpa konsekuensi
                                </div>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-2 cursor-pointer hover:bg-gray-50 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                            <input type="radio" name="violation-action" value="warning" class="mr-3 text-yellow-600">
                            <div>
                                <div class="font-medium text-yellow-700">Berikan Peringatan</div>
                                <div class="text-sm text-gray-500">Siswa dapat melanjutkan ujian dengan catatan
                                    peringatan</div>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-2 cursor-pointer hover:bg-gray-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="radio" name="violation-action" value="suspend" class="mr-3 text-orange-600">
                            <div>
                                <div class="font-medium text-orange-700">Hentikan Sementara</div>
                                <div class="text-sm text-gray-500">Batalkan enrollment siswa dari ujian saat ini</div>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-2 cursor-pointer hover:bg-gray-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                            <input type="radio" name="violation-action" value="remove" class="mr-3 text-red-600">
                            <div>
                                <div class="font-medium text-red-700">Keluarkan dari Ujian</div>
                                <div class="text-sm text-gray-500">Hapus enrollment siswa dan keluarkan dari ujian
                                </div>
                            </div>
                        </label>
                    </div>
                </div> <!-- Notes -->
                <div class="mb-2 px-6 py-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan (Opsional):</label>
                    <textarea id="violation-notes" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan catatan tambahan mengenai pelanggaran dan tindakan yang diambil..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 px-6 py-2">
                    <button id="close-violation-modal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                    <button id="process-violation-btn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-check mr-2"></i>
                        Proses Tindakan
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        function openSettingModal(id) {
            document.getElementById('settingModal-' + id).classList.remove('hidden');
        }

        function closeSettingModal(id) {
            document.getElementById('settingModal-' + id).classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const generateTokenBtn = document.getElementById('generate_token_btn');
            const sesiSelect = document.getElementById('sesi_select');
            const beritaAcaraBtn = document.getElementById('berita_acara_btn');
            const beritaAcaraSelect = document.getElementById('berita_acara_select');
            const monitoringSelect = document.getElementById('monitoring_select');
            const refreshViolationsBtn = document.getElementById('refresh-violations');
            const violationsBody = document.getElementById('violations-body');
            const violationCounter = document.getElementById('violation-counter');
            const dismissBtn = document.getElementById('dismiss-violation-btn');
            const processBtn = document.getElementById('process-violation-btn');
            const closeModalBtn = document.getElementById('close-violation-modal');
            const modal = document.getElementById('violation-action-modal');
            // Load violations initially
            loadViolations();

            // Set interval to refresh violations every 30 seconds
            const violationsRefreshInterval = setInterval(loadViolations, 30000);



            // Handle refresh violations button
            refreshViolationsBtn.addEventListener('click', function() {
                loadViolations();
            });
            // Close modal handlers
            const closeModal = () => {
                modal.classList.add('hidden');
            };

            // Handle monitoring select change
            if (monitoringSelect) {
                monitoringSelect.addEventListener('change', loadViolations);
            }

            // Tambahkan di awal script (sekali saja)
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }
            if (dismissBtn) {
                dismissBtn.addEventListener('click', handleDismissViolation);
            }
            if (processBtn) {
                processBtn.addEventListener('click', handleProcessViolation);
            }



            // Handler dismiss
            function handleDismissViolation() {
                const violationId = this.dataset.violationId;
                const notes = document.getElementById('violation-notes').value;
                processViolation(violationId, 'dismiss', null, notes);
                closeModal();
            }

            // Handler process
            function handleProcessViolation() {
                const violationId = this.dataset.violationId;
                const selectedAction = document.querySelector('input[name="violation-action"]:checked');
                const notes = document.getElementById('violation-notes').value;

                if (!selectedAction) {
                    showToast('Pilih tindakan terlebih dahulu', 'warning');
                    return;
                }

                // Show confirmation based on action severity
                let confirmMessage = '';
                switch (selectedAction.value) {
                    case 'dismiss':
                        confirmMessage =
                            'Abaikan pelanggaran ini dan lanjutkan ujian tanpa konsekuensi?';
                        break;
                    case 'warning':
                        confirmMessage = 'Berikan peringatan kepada siswa dan lanjutkan ujian?';
                        break;
                    case 'suspend':
                        confirmMessage = 'Hentikan sementara siswa dari ujian saat ini?';
                        break;
                    case 'remove':
                        confirmMessage =
                            'KELUARKAN siswa dari ujian dan hapus enrollment? Tindakan ini tidak dapat dibatalkan!';
                        break;
                }
                console.log('Confirm message:', confirmMessage, violationId, selectedAction.value, notes);

                if (confirm(confirmMessage)) {
                    processViolation(violationId, selectedAction.value, null, notes);
                    closeModal();
                }
            }

            function loadViolations() {
                const sesiId = monitoringSelect ? monitoringSelect.value : '{{ $currentAssignment->id ?? '' }}';
                console.log('Loading violations for session:', sesiId);

                const container = document.getElementById('violations-container');
                container.innerHTML = `
        <div class="flex flex-col items-center justify-center p-6">
            <i class="fas fa-spinner fa-spin text-blue-500 text-4xl mb-3"></i>
            <p class="text-lg font-medium">Memuat data pelanggaran...</p>
        </div>
    `;

                const url =
                    `{{ url('/features/pengawas/get-violations') }}${sesiId !== 'all' ? '/' + sesiId : ''}`;
                console.log('Fetching violations from:', url);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Response data:', data);
                        container.innerHTML = '';

                        if (data.success && data.violations.length > 0) {
                            violationCounter.textContent = data.violations.length;

                            data.violations.forEach(violation => {
                                const card = document.createElement('div');
                                card.className = `bg-white rounded-lg shadow p-4 flex flex-col md:flex-row md:items-center md:justify-between mb-4
                    ${violation.is_dismissed ? 'opacity-50' : ''}`;

                                card.innerHTML = `
<div class="flex flex-1 flex-col md:flex-row md:items-center md:justify-start gap-2 p-3 bg-white rounded-lg shadow-sm">
    <div class="flex items-center gap-3 md:gap-4">
        <!-- Badges -->
        <div class="flex flex-col items-start gap-1 mb-2 md:mb-0 whitespace-nowrap">
            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                ${formatViolationType(violation.jenis_pelanggaran)}
            </span>
            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                (${timeSince(violation.waktu_pelanggaran)})
            </span>
        </div>
        <!-- Siswa & Waktu -->
        <div class="text-sm text-gray-900 font-semibold md:flex-1">
            <div class="">${violation.siswa.nama} </div>
            <div>${violation.siswa.idyayasan || '-'}</div>
        </div>
    </div>

    <!-- Deskripsi, Mapel & Ruangan -->
    <div class="flex mt-2 md:mt-0 md:flex md:items-center md:justify-between text-xs text-gray-500 gap-2">
        <div>
            <div class="">Waktu: ${formatDate(violation.waktu_pelanggaran)}</div>
            <div>${violation.deskripsi || '-'}</div>
        </div>
        <div>
            <div>Mapel: ${(violation.jadwal_ujian && violation.jadwal_ujian.mapel) ? violation.jadwal_ujian.mapel.nama_mapel : 'Tidak ada mapel'}</div>
            <div>Ruangan: ${(violation.sesi_ruangan && violation.sesi_ruangan.ruangan) ? violation.sesi_ruangan.ruangan.nama_ruangan : 'Tidak ada ruangan'}, Sesi: ${violation.sesi_ruangan ? violation.sesi_ruangan.nama_sesi : '-'}</div>
        </div>
    </div>
</div>

<!-- Status & Aksi -->
<div class="mt-2 md:mt-0 md:ml-4 flex flex-row md:flex-col items-start md:items-end gap-1 justify-between">
        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full 
            ${violation.is_dismissed ? 'bg-gray-100 text-gray-800' : (violation.is_finalized ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')}">
            ${violation.is_dismissed 
                ? 'Diabaikan' 
                : (violation.is_finalized 
                    ? 'Diproses: ' + (violation.tindakan || 'Tidak ada tindakan') 
                    : 'Belum Diproses')}
        </span>

        <div>
            ${!violation.is_dismissed && !violation.is_finalized ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-violation-id="${violation.id}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-student-name="${violation.siswa ? violation.siswa.nama : 'Tidak diketahui'}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-violation-type="${formatViolationType(violation.jenis_pelanggaran)}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-violation-time="${formatDate(violation.waktu_pelanggaran)}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-subject-name="${(violation.jadwal_ujian && violation.jadwal_ujian.mapel) ? violation.jadwal_ujian.mapel.nama_mapel : 'Tidak diketahui'}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                data-violation-description="${violation.deskripsi || 'Tidak ada deskripsi'}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="process-violation bg-blue-600 text-white hover:bg-blue-800 px-2 py-1 rounded text-sm flex items-center gap-1">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-check-circle"></i> Proses
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="text-gray-400 text-xs flex items-center gap-1">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-check"></i> Sudah ditangani
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `}
        </div>
    </div>
</div>

                `;
                                container.appendChild(card);
                            });

                            setupViolationActionButtons(); // attach event listeners
                        } else {
                            container.innerHTML = `
                <div class="flex flex-col items-center justify-center p-6">
                    <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                    <p class="text-lg font-medium">Tidak ada pelanggaran yang terdeteksi</p>
                    <p class="text-sm mt-1">Semua siswa mengikuti ujian dengan tertib</p>
                </div>
            `;
                            violationCounter.textContent = '0';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching violations:', error);
                        container.innerHTML = `
            <div class="flex flex-col items-center justify-center p-6 text-red-500">
                <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                <p class="text-lg font-medium">Gagal memuat data pelanggaran</p>
                <p class="text-sm mt-1">Silakan coba memuat ulang data</p>
            </div>
        `;
                    });
            }


            // Function to setup violation action buttons
            function setupViolationActionButtons() {
                // Dismiss violation buttons
                document.querySelectorAll('.dismiss-violation').forEach(button => {
                    button.addEventListener('click', function() {
                        const violationId = this.getAttribute('data-violation-id');
                        const studentName = this.getAttribute('data-student-name');
                        const violationType = this.getAttribute('data-violation-type');
                        const violationTime = this.getAttribute('data-violation-time');
                        const subjectName = this.getAttribute('data-subject-name');
                        const violationDescription = this.getAttribute(
                            'data-violation-description');

                        showViolationActionModal(
                            violationId,
                            studentName,
                            violationType,
                            violationTime,
                            subjectName,
                            violationDescription,
                            'dismiss'
                        );
                    });
                });

                // Process violation buttons
                document.querySelectorAll('.process-violation').forEach(button => {
                    button.addEventListener('click', function() {
                        const violationId = this.getAttribute('data-violation-id');
                        const studentName = this.getAttribute('data-student-name');
                        const violationType = this.getAttribute('data-violation-type');
                        const violationTime = this.getAttribute('data-violation-time');
                        const subjectName = this.getAttribute('data-subject-name');
                        const violationDescription = this.getAttribute(
                            'data-violation-description');

                        showViolationActionModal(
                            violationId,
                            studentName,
                            violationType,
                            violationTime,
                            subjectName,
                            violationDescription,
                            'process'
                        );
                    });
                });
            }

            // Function to process violation (dismiss or finalize)
            function processViolation(violationId, action, tindakan = null, catatan = null) {
                const payload = {
                    action: action
                };

                if (tindakan) {
                    payload.tindakan = tindakan;
                }

                if (catatan) {
                    payload.catatan_pengawas = catatan;
                }

                fetch(`/features/pengawas/process-violation/${violationId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Pelanggaran berhasil diproses', 'success');
                            loadViolations(); // Reload the violations
                        } else {
                            showToast('Gagal memproses pelanggaran', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error processing violation:', error);
                        showToast('Terjadi kesalahan saat memproses pelanggaran', 'error');
                    });
            }



            // Helper function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            // Helper function to format time since
            function timeSince(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);

                if (seconds < 60) {
                    return `${seconds} detik yang lalu`;
                }

                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) {
                    return `${minutes} menit yang lalu`;
                }

                const hours = Math.floor(minutes / 60);
                if (hours < 24) {
                    return `${hours} jam yang lalu`;
                }

                const days = Math.floor(hours / 24);
                return `${days} hari yang lalu`;
            }

            // Helper function to format violation type
            function formatViolationType(type) {
                switch (type) {
                    case 'tab_switching':
                        return 'Perpindahan Tab';
                    case 'refresh':
                        return 'Refresh Halaman';
                    default:
                        return type.charAt(0).toUpperCase() + type.slice(1).replace(/_/g, ' ');
                }
            }

            // Handle auto-logout toggle buttons
            const toggleButtons = document.querySelectorAll('.toggle-auto-logout');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const jadwalId = this.getAttribute('data-jadwal-id');
                    const currentState = this.getAttribute('data-state');
                    const mapelName = this.getAttribute('data-mapel');
                    const isCurrentlyActive = currentState === 'active';
                    const confirmMessage = isCurrentlyActive ?
                        `Nonaktifkan auto-logout untuk ${mapelName}?\n\nSiswa akan dapat berpindah tab tanpa logout otomatis.` :
                        `Aktifkan auto-logout untuk ${mapelName}?\n\nSiswa akan dilogout otomatis jika berpindah tab.`;

                    if (confirm(confirmMessage)) {
                        // Send AJAX request to toggle the feature
                        fetch(`{{ url('/features/pengawas/toggle-auto-logout') }}/${jadwalId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({})
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Update button appearance based on new state
                                    if (data.aktifkan_auto_logout) {
                                        // Active state
                                        button.className =
                                            'toggle-auto-logout inline-flex items-center justify-center rounded-md py-1 px-2 text-xs font-medium w-auto bg-red-500 hover:bg-red-600 text-white';
                                        button.innerHTML =
                                            '<i class="fa-solid fa-lock mr-1"></i> Auto-Logout: Aktif';
                                        button.setAttribute('data-state', 'active');
                                    } else {
                                        // Inactive state
                                        button.className =
                                            'toggle-auto-logout inline-flex items-center justify-center rounded-md py-1 px-2 text-xs font-medium w-auto bg-green-500 hover:bg-green-600 text-white';
                                        button.innerHTML =
                                            '<i class="fa-solid fa-unlock mr-1"></i> Auto-Logout: Nonaktif';
                                        button.setAttribute('data-state', 'inactive');
                                    }

                                    // Show success message
                                    alert(data.message);
                                } else {
                                    alert('Gagal mengubah pengaturan auto-logout');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat mengubah pengaturan auto-logout');
                            });
                    }
                });
            });

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

            // Function to show violation action modal
            function showViolationActionModal(violationId, studentName, violationType, violationTime, subjectName,
                violationDescription, defaultAction = 'process') {
                // Populate modal with violation data
                document.getElementById('modal-student-name').textContent = studentName;
                document.getElementById('modal-violation-time').textContent = violationTime;
                document.getElementById('modal-subject-name').textContent = subjectName;
                document.getElementById('modal-violation-type').textContent = violationType;
                document.getElementById('modal-violation-description').textContent = violationDescription;

                // Clear previous selections
                document.querySelectorAll('input[name="violation-action"]').forEach(radio => {
                    radio.checked = false;
                });

                // Set default action if specified
                if (defaultAction === 'dismiss') {
                    // For dismiss, pre-select dismiss as default
                    document.querySelector('input[name="violation-action"][value="dismiss"]').checked = true;
                }

                // Clear notes
                document.getElementById('violation-notes').value = '';

                // Show modal
                modal.classList.remove('hidden');

                // Focus on first radio button
                const firstRadio = document.querySelector('input[name="violation-action"]');
                if (firstRadio) firstRadio.focus();

                // Setup keyboard navigation
                const handleKeyDown = (e) => {
                    if (e.key === 'Escape') {
                        closeModal();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const processBtn = document.getElementById('process-violation-btn');
                        processBtn.click();
                    }
                };
                // Simpan ID pelanggaran ke tombol submit
                processBtn.dataset.violationId = violationId;

                // Setup modal event listeners
                closeModalBtn.dataset.violationId = violationId;;
                dismissBtn.dataset.violationId = violationId;;
                processBtn.dataset.violationId = violationId;;

                document.addEventListener('keydown', handleKeyDown);


                // Close modal when clicking outside
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeModal();
                    }
                });

            }

            // Function to show toast notification
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className =
                    `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full`;

                switch (type) {
                    case 'success':
                        toast.classList.add('bg-green-500');
                        break;
                    case 'error':
                        toast.classList.add('bg-red-500');
                        break;
                    case 'warning':
                        toast.classList.add('bg-yellow-500');
                        break;
                    default:
                        toast.classList.add('bg-blue-500');
                }

                toast.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;

                document.body.appendChild(toast);

                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);

                // Auto remove after 4 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 4000);
            }
        });
    </script>
@endsection
