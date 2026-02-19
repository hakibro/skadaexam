@extends('layouts.admin')

@section('title', 'Manajemen Penugasan Pengawas')
@section('page-title', 'Manajemen Penugasan Pengawas')
@section('page-description', 'Kelola penugasan pengawas ujian berdasarkan jadwal dan sesi')

@section('content')
    <div class="space-y-6">
        <!-- Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-lg bg-blue-50 text-blue-600 mr-4">
                    <i class="fa-solid fa-calendar-days text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Sesi</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_sesi'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-lg bg-green-50 text-green-600 mr-4">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Teralokasi</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['assigned'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-lg bg-amber-50 text-amber-600 mr-4">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Belum Dialokasi</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['unassigned'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Jml Pengawas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_pengawas'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-600 rounded-xl shadow-xl p-4 sticky top-4 z-20 border border-slate-800 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                <div class="flex flex-wrap items-center gap-6">
                    <form id="filterForm" action="{{ route('koordinator.pengawas-assignment.index') }}" method="GET"
                        class="flex items-center gap-3">
                        <div class="relative">
                            <label for="tanggal" class="sr-only">Tanggal</label>
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <i class="fa-solid fa-calendar-day text-sm"></i>
                            </div>
                            <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}"
                                class="pl-10 pr-3 py-2 bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full transition duration-150">
                        </div>
                    </form>

                    @if ($jadwalUjians->count() > 0)
                        <div class="flex flex-col border-l-0 lg:border-l lg:pl-6 border-slate-700">
                            <span class="text-[10px] font-bold text-white uppercase tracking-widest mb-1">Jadwal
                                Ujian</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($jadwalUjians as $jadwal)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-blue-600 border">
                                        {{ $jadwal->mapel->nama_mapel }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <div id="selectedSessionsCount" class="hidden md:block text-sm font-medium text-slate-200 italic">
                        0 sesi terpilih
                    </div>

                    @if ($sesiRuangans->count() > 0)
                        <button id="bulkAssignBtn"
                            class="w-full lg:w-auto inline-flex justify-center items-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-blue-600 bg-white hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 transition-all duration-200">
                            <i class="fa-solid fa-user-plus mr-2"></i>
                            Tugaskan Terpilih
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Session List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-start items-center mb-4 gap-8">

            </div>

            @if ($sesiRuangans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($sesiRuangans->groupBy(fn($s) => $s->ruangan->nama_ruangan ?? 'Unknown') as $ruanganNama => $sesiList)
                        <div class="bg-white shadow-md rounded-xl border flex flex-col">
                            {{-- Header Card --}}
                            <div class="bg-gray-100 flex items-center gap-3 py-3 px-4 rounded-t-xl border-b">
                                <input type="checkbox"
                                    class="select-all-ruangan rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                                    data-ruangan="{{ Str::slug($ruanganNama, '-') }}">
                                <h2 class="text-lg font-semibold text-gray-700">
                                    {{ $ruanganNama }}
                                </h2>
                            </div>

                            {{-- Daftar Sesi --}}
                            <div class="divide-y divide-gray-100 flex-grow">
                                @foreach ($sesiList->groupBy('nama_sesi') as $namaSesi => $grupSesi)
                                    @php
                                        $firstSesi = $grupSesi->first();
                                        $groupSlug = Str::slug($namaSesi, '-');
                                        $ruanganSlug = Str::slug($ruanganNama, '-');

                                        // Cek apakah semua item di grup sudah punya pengawas (untuk status visual)
                                        $allAssigned = $grupSesi->every(fn($i) => !is_null($i->pengawas_for_jadwal));
                                    @endphp

                                    <div
                                        class="p-4 hover:bg-gray-50 transition {{ $allAssigned ? 'border-l-4 border-green-400' : 'border-l-4 border-red-400' }}">
                                        <div class="flex items-center justify-between">
                                            {{-- Kiri: Info --}}
                                            <div class="flex items-start space-x-3">
                                                <input type="checkbox"
                                                    class="session-checkbox group-checkbox mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                                                    data-group="{{ $groupSlug }}"
                                                    data-ruangan-slug="{{ $ruanganSlug }}"
                                                    data-sesi-id="{{ $firstSesi->id }}"
                                                    data-jadwal-id="{{ $firstSesi->jadwal_ujian_id }}"
                                                    data-nama-sesi="{{ $namaSesi }}"
                                                    data-waktu="{{ substr($firstSesi->waktu_mulai, 0, 5) }} - {{ substr($firstSesi->waktu_selesai, 0, 5) }}"
                                                    data-ruangan-nama="{{ $ruanganNama }}">

                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-bold text-gray-900">
                                                            {{ $namaSesi }}
                                                        </span>
                                                        <span
                                                            class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            {{ $firstSesi->status }}
                                                        </span>
                                                    </div>

                                                    <p class="text-sm text-gray-600 mt-0.5">
                                                        <i class="fa-regular fa-clock mr-1"></i>
                                                        {{ substr($firstSesi->waktu_mulai, 0, 5) }} -
                                                        {{ substr($firstSesi->waktu_selesai, 0, 5) }}
                                                    </p>

                                                    {{-- Daftar Mapel & Pengawas dalam grup ini --}}
                                                    <div class="mt-2 space-y-1">
                                                        {{-- @foreach ($grupSesi as $item) --}}
                                                        <div
                                                            class="flex items-center justify-between text-xs p-1.5 rounded {{ $firstSesi->pengawas_for_jadwal ? 'bg-green-50' : 'bg-red-50' }}">

                                                            <span
                                                                class="{{ $firstSesi->pengawas_for_jadwal ? 'text-green-700 font-semibold' : 'text-red-600 italic' }}">
                                                                {{ $firstSesi->pengawas_for_jadwal->nama ?? 'Belum ada' }}
                                                            </span>
                                                        </div>
                                                        {{-- @endforeach --}}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Kanan: Aksi --}}
                                            <div class="flex flex-col space-y-2 ml-2">
                                                <button type="button"
                                                    class="assign-group-pengawas px-3 py-2 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700 shadow-sm whitespace-nowrap"
                                                    data-sesi-id="{{ $firstSesi->id }}"
                                                    data-jadwal-id="{{ $firstSesi->jadwal_ujian_id }}"
                                                    data-nama-sesi="{{ $namaSesi }}"
                                                    data-ruangan-nama="{{ $ruanganNama }}"
                                                    data-waktu="{{ substr($firstSesi->waktu_mulai, 0, 5) }} - {{ substr($firstSesi->waktu_selesai, 0, 5) }}">
                                                    <i class="fa-solid fa-edit mr-1"></i> Atur
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                Tidak ada jadwal atau sesi ruangan pada tanggal
                                <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assign Modal -->
    <div id="assignModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modalBackdrop"></div>

            <div class="relative bg-white rounded-lg w-full max-w-lg mx-4 shadow-xl transform transition-all">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Tugaskan Pengawas</h3>
                    <p class="text-sm text-gray-500 mt-1" id="assignModalDetail"></p>
                    {{-- Hidden inputs to store action data --}}
                    <input type="hidden" id="action_sesi_id">
                    <input type="hidden" id="action_jadwal_id">
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label for="pengawas_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                            Pengawas</label>
                        <select id="pengawas_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Pilih Pengawas</option>
                            @foreach ($availablePengawas as $pengawas)
                                <option value="{{ $pengawas->id }}">{{ $pengawas->nama }}
                                    ({{ $pengawas->nip ?? 'No NIP' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="availabilityInfo" class="hidden mb-4 p-3 bg-yellow-50 text-yellow-700 rounded-md text-sm">
                        <p><i class="fa-solid fa-exclamation-circle mr-2"></i> <span id="availabilityMessage"></span></p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="button" id="cancelAssign"
                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 mr-2">
                        Batal
                    </button>
                    <button type="button" id="confirmAssign"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Tugaskan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div id="bulkAssignModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="bulkModalBackdrop"></div>

            <div class="relative bg-white rounded-lg w-full max-w-lg mx-4 shadow-xl transform transition-all">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Penugasan Masal</h3>
                    <p class="text-sm text-gray-500 mt-1" id="bulkModalDetail">Pilih pengawas untuk sesi yang dipilih</p>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <p id="selectedSessionsCount" class="text-sm font-medium text-gray-700 mb-2">Tidak ada sesi yang
                            dipilih</p>
                    </div>

                    <div class="mb-4">
                        <label for="bulk_pengawas_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                            Pengawas</label>
                        <select id="bulk_pengawas_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Pilih Pengawas</option>
                            @foreach ($availablePengawas as $pengawas)
                                <option value="{{ $pengawas->id }}">{{ $pengawas->nama }}
                                    ({{ $pengawas->nip ?? 'No NIP' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="button" id="cancelBulkAssign"
                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 mr-2">
                        Batal
                    </button>
                    <button type="button" id="confirmBulkAssign"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Tugaskan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Elements ---
            const assignModal = document.getElementById('assignModal');
            const bulkAssignModal = document.getElementById('bulkAssignModal');
            const filterForm = document.getElementById('filterForm');

            // Single Assign Vars
            let currentSesiId = null;
            let currentJadwalId = null;

            // --- Auto Submit Filter ---
            if (filterForm) {
                filterForm.querySelectorAll('input').forEach(input => {
                    input.addEventListener('change', () => filterForm.submit());
                });
            }

            // --- Checkbox Logic ---

            // Select All per Ruangan
            document.querySelectorAll('.select-all-ruangan').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const slug = this.dataset.ruangan;
                    document.querySelectorAll(`.session-checkbox[data-ruangan-slug="${slug}"]`)
                        .forEach(cb => {
                            cb.checked = this.checked;
                        });
                    updateBulkCount();
                });
            });

            // Group Checkbox
            document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Visual feedback (optional)
                    this.closest('.p-4').classList.toggle('bg-blue-50', this.checked);
                    updateBulkCount();
                });
            });

            function updateBulkCount() {
                const count = document.querySelectorAll('.session-checkbox:checked').length;
                document.getElementById('selectedSessionsCount').textContent =
                    count > 0 ? `${count} sesi dipilih` : 'Tidak ada sesi yang dipilih';
            }

            // --- Modal Logic ---

            // Toggle Helper
            const toggleModal = (modal, show) => modal.classList.toggle('hidden', !show);

            // Open Single Assign Modal
            document.querySelectorAll('.assign-group-pengawas').forEach(button => {
                button.addEventListener('click', function() {
                    currentSesiId = this.dataset.sesiId;
                    currentJadwalId = this.dataset.jadwalId;

                    document.getElementById('assignModalDetail').textContent =
                        `${this.dataset.namaSesi} - ${this.dataset.ruanganNama} (${this.dataset.waktu})`;

                    document.getElementById('action_sesi_id').value = currentSesiId;
                    document.getElementById('action_jadwal_id').value = currentJadwalId;

                    document.getElementById('pengawas_id').value = ''; // Reset select
                    document.getElementById('availabilityInfo').classList.add('hidden');

                    toggleModal(assignModal, true);

                    // Optional: Check availability
                    checkPengawasAvailability(currentJadwalId, currentSesiId);
                });
            });

            // Confirm Single Assign
            document.getElementById('confirmAssign').addEventListener('click', function() {
                const pengawasId = document.getElementById('pengawas_id').value;
                if (!pengawasId) return alert('Pilih pengawas terlebih dahulu.');

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';

                fetch('{{ route('koordinator.pengawas-assignment.assign') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            jadwal_ujian_id: currentJadwalId,
                            sesi_ruangan_id: currentSesiId,
                            pengawas_id: pengawasId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                            btn.disabled = false;
                            btn.innerHTML = 'Tugaskan';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan.');
                        btn.disabled = false;
                        btn.innerHTML = 'Tugaskan';
                    });
            });

            // --- Bulk Assign Logic ---

            const bulkAssignBtn = document.getElementById('bulkAssignBtn');
            if (bulkAssignBtn) {
                bulkAssignBtn.addEventListener('click', () => {
                    const checked = document.querySelectorAll('.session-checkbox:checked');
                    if (checked.length === 0) return alert('Pilih minimal satu sesi.');
                    toggleModal(bulkAssignModal, true);
                    updateBulkCount(); // Update text in modal
                });
            }

            document.getElementById('confirmBulkAssign').addEventListener('click', function() {
                const pengawasId = document.getElementById('bulk_pengawas_id').value;
                if (!pengawasId) return alert('Pilih pengawas terlebih dahulu.');

                // Gather unique sessions (sesi_id) and one representative jadwal_id for each
                // Note: Backend logic handles the "all jadwal on date" propagation.
                // We just need to send the list of selected sessions with one valid jadwal context.

                const selections = [];
                document.querySelectorAll('.session-checkbox:checked').forEach(cb => {
                    selections.push({
                        sesi_id: cb.dataset.sesiId,
                        jadwal_id: cb.dataset.jadwalId
                    });
                });

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';

                // We use the bulk-assign route. 
                // Note: We send 'selections' array. Controller needs to handle this structure.
                // Or we can simplify: just send array of sesi_ids. 
                // The controller 'bulkAssign' expects 'session_ids' and 'jadwal_ujian_id'.
                // But we have multiple jadwals potentially.
                // Let's use the loop approach in JS calling single assign, OR update controller.
                // SIMPLIFICATION: Since logic is "assign per date", we can just call single assign for each unique Sesi.

                // Let's use the bulk endpoint but send array of objects
                fetch('{{ route('koordinator.pengawas-assignment.bulk-assign') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selections: selections,
                            pengawas_id: pengawasId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                            btn.disabled = false;
                            btn.innerHTML = 'Tugaskan';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan.');
                        btn.disabled = false;
                        btn.innerHTML = 'Tugaskan';
                    });
            });

            // Availability Check
            function checkPengawasAvailability(jadwalId, sesiId) {
                fetch(
                        `{{ route('koordinator.pengawas-assignment.availability') }}?jadwal_ujian_id=${jadwalId}&sesi_ruangan_id=${sesiId}`
                    )
                    .then(res => res.json())
                    .then(data => {
                        if (data.unavailable && data.unavailable.length > 0) {
                            document.getElementById('availabilityInfo').classList.remove('hidden');
                            document.getElementById('availabilityMessage').textContent =
                                `${data.unavailable.length} pengawas tidak tersedia (bentrok).`;
                        }
                    });
            }

            // Close Modals
            document.querySelectorAll('#cancelAssign, #modalBackdrop, #cancelBulkAssign, #bulkModalBackdrop')
                .forEach(el => {
                    el.addEventListener('click', () => {
                        toggleModal(assignModal, false);
                        toggleModal(bulkAssignModal, false);
                    });
                });
        });
    </script>
@endsection
