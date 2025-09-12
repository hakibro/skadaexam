@extends('layouts.admin')

@section('title', 'Manajemen Laporan')
@section('page-title', 'Manajemen Berita Acara')
@section('page-description', 'Kelola verifikasi dan persetujuan berita acara ujian')

@section('content')
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-clock text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['pending'] }}</div>
                        <div class="text-gray-600 font-medium">Menunggu Verifikasi</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-check-circle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['verified'] }}</div>
                        <div class="text-gray-600 font-medium">Sudah Diverifikasi</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-times-circle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['rejected'] }}</div>
                        <div class="text-gray-600 font-medium">Ditolak</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-file-alt text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                        <div class="text-gray-600 font-medium">Total Laporan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <!-- Show current filters if any are applied -->
            @if (request()->hasAny(['tanggal', 'status', 'pengawas', 'per_page']))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fa-solid fa-filter text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                        <div class="ml-2 text-sm text-blue-700">
                            @if (request('tanggal'))
                                <span class="bg-blue-100 px-2 py-1 rounded mr-2">Tanggal: {{ request('tanggal') }}</span>
                            @endif
                            @if (request('status'))
                                <span class="bg-blue-100 px-2 py-1 rounded mr-2">Status:
                                    {{ ucfirst(request('status')) }}</span>
                            @endif
                            @if (request('pengawas'))
                                @php
                                    $selectedPengawas = $pengawasList->find(request('pengawas'));
                                @endphp
                                <span class="bg-blue-100 px-2 py-1 rounded mr-2">Pengawas:
                                    {{ $selectedPengawas?->nama ?? 'ID ' . request('pengawas') }}</span>
                            @endif
                            @if (request('per_page') && request('per_page') != 15)
                                <span class="bg-blue-100 px-2 py-1 rounded mr-2">Per halaman:
                                    {{ request('per_page') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end"
                action="{{ route('koordinator.laporan.index') }}" id="filter-form">
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-calendar mr-1 text-gray-400"></i>
                        Tanggal
                    </label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-info-circle mr-1 text-gray-400"></i>
                        Status Verifikasi
                    </label>
                    <select id="status" name="status"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Diverifikasi
                        </option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="pengawas" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-user-tie mr-1 text-gray-400"></i>
                        Pengawas
                    </label>
                    <select id="pengawas" name="pengawas"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Semua Pengawas</option>
                        @foreach ($pengawasList as $pengawas)
                            <option value="{{ $pengawas->id }}"
                                {{ request('pengawas') == $pengawas->id ? 'selected' : '' }}>
                                {{ $pengawas->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-list-ol mr-1 text-gray-400"></i>
                        Per Halaman
                    </label>
                    <select id="per_page" name="per_page"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                <div class="flex space-x-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-search mr-1"></i>
                        Filter
                    </button>
                    <a href="{{ route('koordinator.laporan.index') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                        <i class="fa-solid fa-times mr-1"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div id="bulk-actions" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle text-yellow-600 mr-2"></i>
                    <span class="text-sm font-medium text-yellow-800">
                        <span id="selected-count">0</span> laporan dipilih
                    </span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkVerify()"
                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        <i class="fa-solid fa-check mr-1"></i>Verifikasi Bulk
                    </button>
                    <button onclick="bulkReject()" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                        <i class="fa-solid fa-times mr-1"></i>Tolak Bulk
                    </button>
                    <button onclick="clearSelection()"
                        class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                        <i class="fa-solid fa-times mr-1"></i>Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Berita Acara Ujian</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola verifikasi dan persetujuan berita acara dari pengawas ujian
                </p>
            </div>

            @if ($beritaAcaras->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi & Ruangan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pengawas
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Verifikasi
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Laporan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($beritaAcaras as $beritaAcara)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="berita-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                            value="{{ $beritaAcara->id }}"
                                            {{ $beritaAcara->status_verifikasi !== 'pending' ? 'disabled' : '' }}>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $beritaAcara->sesiRuangan->nama_sesi ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">
                                            <i class="fa-solid fa-door-open mr-1"></i>
                                            {{ $beritaAcara->sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $beritaAcara->sesiRuangan->kode_sesi ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <i class="fa-solid fa-user text-purple-600 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $beritaAcara->pengawas->nama }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $beritaAcara->pengawas->nip ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if ($beritaAcara->sesiRuangan && $beritaAcara->sesiRuangan->jadwalUjians->count() > 0)
                                                @php
                                                    // Use the BeritaAcaraUjian creation date to filter relevant jadwals
                                                    $beritaAcaraDate = \Carbon\Carbon::parse(
                                                        $beritaAcara->created_at,
                                                    )->toDateString();

                                                    // Find jadwals that match the berita acara date or are close to it
                                                    $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians->filter(
                                                        function ($jadwal) use ($beritaAcaraDate) {
                                                            $jadwalDate = \Carbon\Carbon::parse(
                                                                $jadwal->tanggal,
                                                            )->toDateString();
                                                            // Include jadwals from the same date or within 1 day
                                                            return abs(
                                                                \Carbon\Carbon::parse($jadwalDate)->diffInDays(
                                                                    \Carbon\Carbon::parse($beritaAcaraDate),
                                                                ),
                                                            ) <= 1;
                                                        },
                                                    );

                                                    // If no close jadwals found, use all jadwals
                                                    if ($relevantJadwals->isEmpty()) {
                                                        $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians;
                                                    }

                                                    // Get the first relevant jadwal date for display
                                                    $displayDate =
                                                        $relevantJadwals->first()->tanggal ??
                                                        $beritaAcara->created_at->format('Y-m-d');
                                                @endphp
                                                {{ \Carbon\Carbon::parse($displayDate)->format('d M Y') }}
                                            @else
                                                {{ $beritaAcara->created_at->format('d M Y') }}
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @if ($beritaAcara->sesiRuangan && $beritaAcara->sesiRuangan->jadwalUjians->count() > 0)
                                                @php
                                                    // Use the same filtering logic as above
                                                    $beritaAcaraDate = \Carbon\Carbon::parse(
                                                        $beritaAcara->created_at,
                                                    )->toDateString();

                                                    $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians->filter(
                                                        function ($jadwal) use ($beritaAcaraDate) {
                                                            $jadwalDate = \Carbon\Carbon::parse(
                                                                $jadwal->tanggal,
                                                            )->toDateString();
                                                            return abs(
                                                                \Carbon\Carbon::parse($jadwalDate)->diffInDays(
                                                                    \Carbon\Carbon::parse($beritaAcaraDate),
                                                                ),
                                                            ) <= 1;
                                                        },
                                                    );

                                                    if ($relevantJadwals->isEmpty()) {
                                                        $relevantJadwals = $beritaAcara->sesiRuangan->jadwalUjians;
                                                    }

                                                    $mapelNames = $relevantJadwals
                                                        ->filter(function ($jadwal) {
                                                            return $jadwal->mapel !== null;
                                                        })
                                                        ->map(function ($jadwal) {
                                                            return $jadwal->mapel->nama_mapel;
                                                        })
                                                        ->unique();
                                                @endphp

                                                @if ($mapelNames->count() > 0)
                                                    {{ $mapelNames->implode(' + ') }}
                                                    @if ($mapelNames->count() > 1)
                                                        <span class="text-xs text-gray-400">({{ $mapelNames->count() }}
                                                            mapel)</span>
                                                    @endif
                                                @else
                                                    <span class="text-red-500">Mapel tidak tersedia</span>
                                                @endif
                                            @else
                                                <span class="text-red-500">Tidak ada jadwal</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $beritaAcara->sesiRuangan->waktu_mulai ?? 'N/A' }} -
                                            {{ $beritaAcara->sesiRuangan->waktu_selesai ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $beritaAcara->verification_badge_class }}">
                                            {{ $beritaAcara->verification_status_text }}
                                        </span>
                                        @if ($beritaAcara->status_verifikasi === 'verified')
                                            <div class="text-xs text-gray-500 mt-1">
                                                Oleh: {{ $beritaAcara->koordinator->nama ?? 'N/A' }}
                                            </div>
                                        @elseif($beritaAcara->status_verifikasi === 'rejected')
                                            <div class="text-xs text-red-600 mt-1">
                                                Ditolak: {{ $beritaAcara->tanggal_verifikasi?->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $beritaAcara->created_at->format('d M Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $beritaAcara->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                        <a href="{{ route('koordinator.laporan.show', $beritaAcara->id) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @if ($beritaAcara->status_verifikasi === 'pending')
                                            <button onclick="showVerificationModal({{ $beritaAcara->id }}, 'verify')"
                                                class="text-green-600 hover:text-green-900" title="Verifikasi">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button onclick="showVerificationModal({{ $beritaAcara->id }}, 'reject')"
                                                class="text-red-600 hover:text-red-900" title="Tolak">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('koordinator.laporan.download', $beritaAcara->id) }}"
                                            class="text-gray-600 hover:text-gray-900" title="Download PDF">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $beritaAcaras->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fa-solid fa-file-times text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Berita Acara</h3>
                    <p class="text-gray-500">Tidak ditemukan berita acara dengan filter yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Verification Modal -->
    <div id="verification-modal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="verification-title">Verifikasi Berita Acara</h3>
                    <button onclick="closeVerificationModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form id="verification-form">
                    <input type="hidden" id="verification-berita-id" name="berita_acara_id">
                    <input type="hidden" id="verification-action" name="action">

                    <div class="mb-4">
                        <label for="verification-notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="notes-label">Catatan Verifikasi</span>
                        </label>
                        <textarea id="verification-notes" name="catatan" rows="4"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Tambahkan catatan atau komentar..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeVerificationModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Batal
                        </button>
                        <button type="submit" id="verification-submit-btn"
                            class="px-4 py-2 text-sm font-medium text-white rounded-md">
                            <i class="fa-solid fa-check mr-1"></i>
                            Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add debugging for form submission
            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    console.log('Filter form submitted');

                    // Log current form data
                    const formData = new FormData(filterForm);
                    console.log('Form data:', Object.fromEntries(formData));

                    // Allow normal form submission to continue
                });
            }

            // Bulk selection functionality
            const selectAllCheckbox = document.getElementById('select-all');
            const beritaCheckboxes = document.querySelectorAll('.berita-checkbox:not([disabled])');
            const bulkActionsDiv = document.getElementById('bulk-actions');
            const selectedCountSpan = document.getElementById('selected-count');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    beritaCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkActions();
                });
            }

            beritaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkActions);
            });

            function updateBulkActions() {
                const selectedCount = document.querySelectorAll('.berita-checkbox:checked').length;

                if (selectedCountSpan) {
                    selectedCountSpan.textContent = selectedCount;
                }

                if (bulkActionsDiv) {
                    if (selectedCount > 0) {
                        bulkActionsDiv.classList.remove('hidden');
                    } else {
                        bulkActionsDiv.classList.add('hidden');
                    }
                }
            }

            // Verification form submission
            const verificationForm = document.getElementById('verification-form');
            if (verificationForm) {
                verificationForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitVerification();
                });
            }
        });

        // Modal functions
        function showVerificationModal(beritaId, action) {
            document.getElementById('verification-berita-id').value = beritaId;
            document.getElementById('verification-action').value = action;

            const title = document.getElementById('verification-title');
            const notesLabel = document.getElementById('notes-label');
            const submitBtn = document.getElementById('verification-submit-btn');

            if (action === 'verify') {
                title.textContent = 'Verifikasi Berita Acara';
                notesLabel.textContent = 'Catatan Verifikasi';
                submitBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Verifikasi';
                submitBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700';
            } else {
                title.textContent = 'Tolak Berita Acara';
                notesLabel.textContent = 'Alasan Penolakan';
                submitBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i>Tolak';
                submitBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700';
            }

            document.getElementById('verification-modal').classList.remove('hidden');
        }

        function closeVerificationModal() {
            document.getElementById('verification-modal').classList.add('hidden');
            document.getElementById('verification-form').reset();
        }

        // Submit verification
        function submitVerification() {
            const formData = new FormData(document.getElementById('verification-form'));

            fetch('{{ route('koordinator.laporan.verify') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        closeVerificationModal();
                        location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('Error: ' + error.message, 'error');
                });
        }

        // Bulk operations
        function bulkVerify() {
            const selectedIds = Array.from(document.querySelectorAll('.berita-checkbox:checked')).map(cb => cb.value);

            if (selectedIds.length === 0) {
                showToast('Pilih minimal satu laporan', 'error');
                return;
            }

            if (confirm(`Verifikasi ${selectedIds.length} laporan terpilih?`)) {
                fetch('{{ route('koordinator.laporan.bulk-verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            berita_acara_ids: selectedIds,
                            action: 'verify'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            location.reload();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error: ' + error.message, 'error');
                    });
            }
        }

        function bulkReject() {
            const selectedIds = Array.from(document.querySelectorAll('.berita-checkbox:checked')).map(cb => cb.value);

            if (selectedIds.length === 0) {
                showToast('Pilih minimal satu laporan', 'error');
                return;
            }

            const reason = prompt('Masukkan alasan penolakan:');
            if (reason === null) return;

            if (confirm(`Tolak ${selectedIds.length} laporan terpilih?`)) {
                fetch('{{ route('koordinator.laporan.bulk-verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            berita_acara_ids: selectedIds,
                            action: 'reject',
                            catatan: reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            location.reload();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error: ' + error.message, 'error');
                    });
            }
        }

        // Clear selection
        function clearSelection() {
            document.querySelectorAll('.berita-checkbox, #select-all').forEach(cb => cb.checked = false);
            document.getElementById('bulk-actions').classList.add('hidden');
        }

        // Toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
@endsection
