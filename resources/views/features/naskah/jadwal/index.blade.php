@extends('layouts.admin')

@section('title', 'Jadwal Ujian')
@section('page-title', 'Jadwal Ujian')
@section('page-description', 'Kelola jadwal ujian dan sesi ujian')

@section('content')
    <div class="space-y-4">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 p-3 border-b bg-gray-50">
                <h3 class="text-base font-semibold text-gray-800">Daftar Jadwal Ujian</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Bulk Actions Dropdown -->
                    <div class="relative" id="bulk-action-dropdown" style="display: none;">
                        <button type="button"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition"
                            onclick="toggleBulkDropdown()">
                            <i class="fa-solid fa-tasks mr-1"></i> Aksi
                            <i class="fa-solid fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div id="bulk-dropdown-menu"
                            class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10 text-sm">
                            <div class="py-1">
                                <button type="button" onclick="bulkStatusChange('draft')"
                                    class="block w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-pencil mr-2 w-4"></i> Set Draft
                                </button>
                                <button type="button" onclick="bulkStatusChange('aktif')"
                                    class="block w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-check-circle mr-2 w-4"></i> Set Aktif
                                </button>
                                <button type="button" onclick="bulkStatusChange('nonaktif')"
                                    class="block w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-pause-circle mr-2 w-4"></i> Set Non-Aktif
                                </button>
                                <button type="button" onclick="bulkStatusChange('selesai')"
                                    class="block w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-flag-checkered mr-2 w-4"></i> Set Selesai
                                </button>
                                <hr class="my-1">
                                <button type="button" onclick="openSusulanWizard()"
                                    class="block w-full text-left px-3 py-1.5 text-purple-600 hover:bg-purple-50">
                                    <i class="fa-solid fa-clock-rotate-left mr-2 w-4"></i> Buat Ujian Susulan
                                </button>
                                <button type="button" onclick="openBulkAssignSesiModal()"
                                    class="block w-full text-left px-3 py-1.5 text-emerald-600 hover:bg-emerald-50">
                                    <i class="fa-solid fa-layer-group mr-2 w-4"></i> Assign Sesi + Enroll
                                </button>
                                <hr class="my-1">
                                <button type="button" onclick="bulkDelete()"
                                    class="block w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-trash mr-2 w-4"></i> Hapus
                                </button>
                                <button type="button" onclick="bulkForceDelete()"
                                    class="block w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-skull-crossbones mr-2 w-4"></i> Hapus Paksa
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <a href="{{ route('naskah.jadwal.create') }}"
                        class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah
                    </a>
                </div>
            </div>

            <!-- Filter Form - Compact -->
            <div class="p-3 bg-white border-b">
                <form action="{{ route('naskah.jadwal.index') }}" method="get" class="jadwal-filter-form"
                    data-auto-submit>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 lg:grid-cols-8 gap-2">
                        <div class="col-span-1">
                            <select name="tahun_ajaran_id"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahunAjarans as $tahunAjaran)
                                    <option value="{{ $tahunAjaran->id }}"
                                        {{ (string) $tahunAjaranId === (string) $tahunAjaran->id ? 'selected' : '' }}>
                                        {{ $tahunAjaran->nama }}{{ $tahunAjaran->is_active ? ' *' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <select name="paket_ujian_id"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="__all" {{ $showAllPaket ? 'selected' : '' }}>Semua Paket</option>
                                @foreach ($paketUjians as $paket)
                                    <option value="{{ $paket->id }}"
                                        {{ (string) $paketUjianId === (string) $paket->id ? 'selected' : '' }}>
                                        {{ $paket->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari judul/kode...">
                        </div>
                        <div class="col-span-1">
                            <select name="status"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif
                                </option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <select name="mapel_id"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Dari tgl">
                        </div>
                        <div class="col-span-1">
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Sampai tgl">
                        </div>
                        <div class="col-span-1">
                            <select name="per_page"
                                class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="150" {{ request('per_page') == 150 ? 'selected' : '' }}>150</option>
                            </select>
                        </div>
                        <div class="col-span-1 flex gap-1">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center px-2 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition">
                                <i class="fa-solid fa-search"></i>
                            </button>
                            <a href="{{ route('naskah.jadwal.index') }}"
                                class="w-full inline-flex justify-center items-center px-2 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs rounded-md transition">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Compact -->
            <div class="overflow-x-auto">
                @if (count($jadwalUjians) > 0)
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left">
                                    <input type="checkbox" id="select-all"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        onchange="toggleAllCheckboxes(this)">
                                </th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Nama Ujian</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase hidden md:table-cell">
                                    Mapel</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Jadwal</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500 uppercase hidden sm:table-cell">
                                    Sesi</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($jadwalUjians as $jadwal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <input type="checkbox" name="jadwal_ids[]" value="{{ $jadwal->id }}"
                                            class="jadwal-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            onchange="updateBulkActionVisibility()">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap font-mono">{{ $jadwal->kode_ujian }}</td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900">{{ $jadwal->judul }}</div>
                                        <div class="text-gray-500">{{ $jadwal->paketUjian->nama ?? 'Belum ada paket' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap hidden md:table-cell">
                                        {{ $jadwal->mapel->nama_mapel ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div>{{ $jadwal->tanggal->format('d/m/Y') }}</div>
                                        <div class="text-gray-500">{{ $jadwal->durasi_menit }} menit</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @php
                                            $badge =
                                                [
                                                    'draft' => 'bg-gray-100 text-gray-700',
                                                    'aktif' => 'bg-green-100 text-green-700',
                                                    'nonaktif' => 'bg-yellow-100 text-yellow-700',
                                                    'selesai' => 'bg-blue-100 text-blue-700',
                                                    'dibatalkan' => 'bg-red-100 text-red-700',
                                                ][$jadwal->status] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span
                                            class="px-2 py-0.5 inline-flex items-center rounded-full {{ $badge }}">
                                            <i class="fa-solid fa-circle mr-1 text-[6px]"></i>
                                            {{ ucfirst($jadwal->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center hidden sm:table-cell">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md">
                                            {{ $jadwal->sesi_ruangans_count ?? $jadwal->sesiRuangans->count() }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-1">
                                            <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                                                class="p-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-600 hover:text-white transition"
                                                title="Detail">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </a>
                                            <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}?assign_sesi=1"
                                                class="p-1 bg-emerald-50 text-emerald-600 rounded hover:bg-emerald-600 hover:text-white transition"
                                                title="Assign Sesi">
                                                <i class="fa-solid fa-layer-group text-xs"></i>
                                            </a>
                                            <a href="{{ route('naskah.jadwal.edit', $jadwal->id) }}"
                                                class="p-1 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-500 hover:text-white transition"
                                                title="Edit">
                                                <i class="fa-solid fa-edit text-xs"></i>
                                            </a>
                                            <form action="{{ route('naskah.jadwal.destroy', $jadwal->id) }}"
                                                method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1 bg-red-50 text-red-600 rounded hover:bg-red-600 hover:text-white transition"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-3 border-t flex justify-between items-center text-sm">
                        <div class="text-gray-600">
                            Menampilkan {{ $jadwalUjians->firstItem() }} - {{ $jadwalUjians->lastItem() }} dari
                            {{ $jadwalUjians->total() }}
                        </div>
                        {{ $jadwalUjians->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fa-solid fa-calendar-xmark text-gray-300 text-4xl mb-2"></i>
                        <p class="text-gray-500">Belum ada jadwal ujian</p>
                        <a href="{{ route('naskah.jadwal.create') }}"
                            class="inline-flex items-center px-3 py-1.5 mt-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition">
                            <i class="fa-solid fa-plus mr-1"></i> Tambah Jadwal
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bulk Action Form (hidden) -->
        <form id="bulk-action-form" action="{{ route('naskah.jadwal.bulk-action') }}" method="POST"
            style="display: none;">
            @csrf
            <input type="hidden" name="action" id="bulk-action-type">
            <input type="hidden" name="new_status" id="bulk-new-status">
        </form>

        <div id="bulkAssignSesiModal"
            class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Assign Sesi dan Enroll Siswa Massal</h3>
                        <p class="text-sm text-gray-500">Sesi sumber yang dipilih akan diterapkan ke semua jadwal
                            tercentang.</p>
                    </div>
                    <button type="button" onclick="closeBulkAssignSesiModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                    </button>
                    <button id="bulkAssignSubmitButton" type="button" onclick="bulkAssignSesiAndEnroll()"
                        @disabled(($sourceSesiOptions ?? collect())->isEmpty())
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-md">
                        Assign Sesi dan Enroll
                    </button>
                    <button type="button" onclick="closeBulkAssignSesiModal()"
                        class="text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        Sistem akan membuat sesi duplikat sesuai tanggal masing-masing jadwal, lalu enroll siswa dari
                        peserta sesi sumber.
                    </div>
                    <div id="bulkAssignProgress" class="hidden rounded-md border border-blue-200 bg-blue-50 p-3">
                        <div class="flex items-center justify-between gap-3 text-sm font-medium text-blue-900">
                            <span id="bulkAssignProgressText">Menunggu proses...</span>
                            <span id="bulkAssignProgressPercent">0%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-blue-100">
                            <div id="bulkAssignProgressBar" class="h-2 rounded-full bg-blue-600 transition-all"
                                style="width: 0%"></div>
                        </div>
                        <div id="bulkAssignLog" class="mt-3 max-h-40 overflow-y-auto space-y-1 text-xs text-gray-700">
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button id="bulkAssignRetryButton" type="button" onclick="retryBulkAssignFromFailedBatch()"
                                class="hidden px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded text-xs">
                                Coba Lagi dari batch gagal
                            </button>
                            <button id="bulkAssignRefreshButton" type="button" onclick="window.location.reload()"
                                class="hidden px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs">
                                Refresh Data
                            </button>
                        </div>
                    </div>

                    @if (($sourceSesiOptions ?? collect())->count() > 0)
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm text-gray-600">
                                <span id="bulkAssignSelectedCount">0</span> jadwal dipilih
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                <input type="checkbox" id="bulkAssignSelectAllSesi"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                Pilih semua sesi
                            </label>
                        </div>

                        <div class="space-y-4">
                            @foreach ($sourceSesiOptions->groupBy(fn($sesi) => $sesi->ruangan->nama_ruangan ?? 'Ruangan tidak tersedia') as $ruanganNama => $sesiGroup)
                                @php $groupKey = 'bulk-sesi-group-' . \Illuminate\Support\Str::slug($ruanganNama); @endphp
                                <div class="border border-emerald-200 rounded-md overflow-hidden">
                                    <div
                                        class="flex items-center justify-between gap-3 px-4 py-3 bg-emerald-100/70 border-b border-emerald-200">
                                        <div>
                                            <div class="text-sm font-semibold text-emerald-900">{{ $ruanganNama }}</div>
                                            <div class="text-xs text-emerald-700">{{ $sesiGroup->count() }} sesi sumber
                                            </div>
                                        </div>
                                        <label
                                            class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-800">
                                            <input type="checkbox"
                                                class="bulk-assign-sesi-group rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                data-group="{{ $groupKey }}">
                                            Pilih grup
                                        </label>
                                    </div>
                                    <div class="divide-y divide-emerald-100">
                                        @foreach ($sesiGroup as $sesiOption)
                                            <label
                                                class="flex items-start gap-3 p-3 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer">
                                                <input type="checkbox" value="{{ $sesiOption->id }}"
                                                    class="bulk-assign-sesi-checkbox mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                    data-group="{{ $groupKey }}">
                                                <span class="flex-1">
                                                    <span
                                                        class="flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-900">
                                                        <span>{{ $sesiOption->nama_sesi }}</span>
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                            <i class="fa-solid fa-layer-group mr-1"></i>Sesi Sumber
                                                        </span>
                                                    </span>
                                                    <span class="block text-xs text-gray-500">
                                                        {{ $sesiOption->kode_sesi }} |
                                                        {{ \Carbon\Carbon::parse($sesiOption->waktu_mulai)->format('H:i') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($sesiOption->waktu_selesai)->format('H:i') }}
                                                        |
                                                        {{ $sesiOption->sesi_ruangan_siswa_count }} siswa
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            Belum ada sesi ruangan sumber pada filter tahun ajaran/paket ini.
                        </div>
                    @endif


                </div>

            </div>
        </div>
    </div>

    {{-- Susulan Wizard Modal --}}
    @include('features.naskah.jadwal.partials.susulan-wizard-modal')

@endsection

@section('scripts')
    <script>
        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.jadwal-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateBulkActionVisibility();
        }

        function updateBulkActionVisibility() {
            const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
            const bulkActionDiv = document.getElementById('bulk-action-dropdown');

            if (checkedBoxes.length > 0) {
                bulkActionDiv.style.display = 'block';
            } else {
                bulkActionDiv.style.display = 'none';
                document.getElementById('bulk-dropdown-menu').classList.add('hidden');
            }
        }

        function toggleBulkDropdown() {
            const menu = document.getElementById('bulk-dropdown-menu');
            menu.classList.toggle('hidden');
        }

        function clearBulkGeneratedInputs() {
            document.querySelectorAll('#bulk-action-form .bulk-generated-input').forEach(input => input.remove());
            document.getElementById('bulk-new-status').value = '';
        }

        function appendBulkInput(form, name, value) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = name;
            hiddenInput.value = value;
            hiddenInput.classList.add('bulk-generated-input');
            form.appendChild(hiddenInput);
        }

        const bulkAssignChunkUrl = @json(route('naskah.jadwal.bulk-assign-sesi-enroll.chunk'));
        const bulkAssignCsrfToken = @json(csrf_token());
        const bulkAssignBatchSize = 2;
        let bulkAssignState = null;

        function bulkStatusChange(newStatus) {
            const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih minimal satu jadwal ujian');
                return;
            }

            const statusText = {
                'draft': 'Draft',
                'aktif': 'Aktif',
                'nonaktif': 'Non-Aktif',
                'selesai': 'Selesai'
            };

            if (confirm(
                    `Apakah Anda yakin ingin mengubah status ${checkedBoxes.length} jadwal ujian menjadi ${statusText[newStatus]}?`
                )) {
                const form = document.getElementById('bulk-action-form');
                clearBulkGeneratedInputs();
                document.getElementById('bulk-action-type').value = 'status_change';
                document.getElementById('bulk-new-status').value = newStatus;

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    appendBulkInput(form, 'jadwal_ids[]', checkbox.value);
                });

                form.submit();
            }
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih minimal satu jadwal ujian');
                return;
            }

            if (confirm(
                    `Apakah Anda yakin ingin menghapus ${checkedBoxes.length} jadwal ujian? Aksi ini tidak dapat dibatalkan.`
                )) {
                const form = document.getElementById('bulk-action-form');
                clearBulkGeneratedInputs();
                document.getElementById('bulk-action-type').value = 'delete';

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    appendBulkInput(form, 'jadwal_ids[]', checkbox.value);
                });

                form.submit();
            }
        }

        function bulkForceDelete() {
            const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih minimal satu jadwal ujian');
                return;
            }

            if (confirm(
                    `Apakah Anda yakin ingin menghapus paksa ${checkedBoxes.length} jadwal ujian beserta hasil ujiannya? Aksi ini tidak dapat dibatalkan.`
                )) {
                const form = document.getElementById('bulk-action-form');
                clearBulkGeneratedInputs();
                document.getElementById('bulk-action-type').value = 'force_delete';

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    appendBulkInput(form, 'jadwal_ids[]', checkbox.value);
                });

                form.submit();
            }
        }

        function openBulkAssignSesiModal() {
            const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih minimal satu jadwal ujian');
                return;
            }

            const selectedCount = document.getElementById('bulkAssignSelectedCount');
            if (selectedCount) {
                selectedCount.textContent = checkedBoxes.length;
            }
            resetBulkAssignProgress();
            document.getElementById('bulkAssignSesiModal').classList.remove('hidden');
            document.getElementById('bulkAssignSesiModal').classList.add('flex');
            document.getElementById('bulk-dropdown-menu').classList.add('hidden');
        }

        function closeBulkAssignSesiModal() {
            if (bulkAssignState?.isProcessing) {
                alert('Proses bulk assign masih berjalan. Tunggu sampai batch selesai atau gagal.');
                return;
            }

            document.getElementById('bulkAssignSesiModal').classList.add('hidden');
            document.getElementById('bulkAssignSesiModal').classList.remove('flex');
        }

        function chunkArray(items, size) {
            const chunks = [];
            for (let index = 0; index < items.length; index += size) {
                chunks.push(items.slice(index, index + size));
            }
            return chunks;
        }

        function resetBulkAssignProgress() {
            if (bulkAssignState?.isProcessing) {
                return;
            }

            bulkAssignState = null;
            document.getElementById('bulkAssignProgress')?.classList.add('hidden');
            document.getElementById('bulkAssignRetryButton')?.classList.add('hidden');
            document.getElementById('bulkAssignRefreshButton')?.classList.add('hidden');
            document.getElementById('bulkAssignLog').innerHTML = '';
            setBulkAssignProgress(0, 1, 'Menunggu proses...');
        }

        function setBulkAssignProcessing(isProcessing) {
            bulkAssignState = bulkAssignState || {};
            bulkAssignState.isProcessing = isProcessing;

            document.getElementById('bulkAssignSubmitButton').disabled = isProcessing;
            document.querySelectorAll(
                '.bulk-assign-sesi-checkbox, .bulk-assign-sesi-group, #bulkAssignSelectAllSesi, .jadwal-checkbox'
            ).forEach((checkbox) => {
                checkbox.disabled = isProcessing;
            });
        }

        function setBulkAssignProgress(processed, total, text = null) {
            const percent = total > 0 ? Math.round((processed / total) * 100) : 0;
            document.getElementById('bulkAssignProgressBar').style.width = `${percent}%`;
            document.getElementById('bulkAssignProgressPercent').textContent = `${percent}%`;
            if (text) {
                document.getElementById('bulkAssignProgressText').textContent = text;
            }
        }

        function appendBulkAssignLog(message, type = 'info') {
            const log = document.getElementById('bulkAssignLog');
            const item = document.createElement('div');
            const colorClass = {
                success: 'text-green-700',
                warning: 'text-amber-700',
                error: 'text-red-700',
                info: 'text-gray-700',
            } [type] || 'text-gray-700';

            item.className = colorClass;
            item.textContent = message;
            log.appendChild(item);
            log.scrollTop = log.scrollHeight;
        }

        function collectBulkAssignTotals(data) {
            const totals = bulkAssignState.totals;
            totals.processed += data.processed || 0;
            totals.attached += data.attached || 0;
            totals.enrolled += data.enrolled || 0;
            totals.updated += data.updated || 0;
            totals.skipped += data.skipped || 0;
            totals.warnings += (data.warnings || []).length;

            (data.warnings || []).forEach((warning) => appendBulkAssignLog(warning, 'warning'));
        }

        async function runBulkAssignChunks(startIndex = 0) {
            document.getElementById('bulkAssignRetryButton')?.classList.add('hidden');
            document.getElementById('bulkAssignRefreshButton')?.classList.add('hidden');
            setBulkAssignProcessing(true);

            const chunks = bulkAssignState.chunks;
            const totalJadwal = bulkAssignState.jadwalIds.length;
            let processedJadwal = startIndex * bulkAssignBatchSize;

            for (let index = startIndex; index < chunks.length; index++) {
                const chunk = chunks[index];
                bulkAssignState.currentIndex = index;
                processedJadwal = Math.min(index * bulkAssignBatchSize, totalJadwal);
                setBulkAssignProgress(
                    processedJadwal,
                    totalJadwal,
                    `Memproses ${processedJadwal}/${totalJadwal} jadwal (batch ${index + 1}/${chunks.length})`
                );

                try {
                    const response = await fetch(bulkAssignChunkUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': bulkAssignCsrfToken,
                        },
                        body: JSON.stringify({
                            jadwal_ids: chunk,
                            sesi_ids: bulkAssignState.sesiIds,
                            chunk_index: index,
                            total_chunks: chunks.length,
                        }),
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        const errors = data.errors?.length ? data.errors : ['Batch gagal diproses.'];
                        errors.forEach((error) => appendBulkAssignLog(error, 'error'));
                        document.getElementById('bulkAssignRetryButton')?.classList.remove('hidden');
                        setBulkAssignProgress(
                            processedJadwal,
                            totalJadwal,
                            `Berhenti di batch ${index + 1}/${chunks.length}`
                        );
                        setBulkAssignProcessing(false);
                        return;
                    }

                    collectBulkAssignTotals(data);
                    processedJadwal = Math.min(processedJadwal + chunk.length, totalJadwal);
                    appendBulkAssignLog(
                        `Batch ${index + 1}/${chunks.length}: ${data.processed || 0} jadwal diproses, ${data.attached || 0} sesi, ${data.enrolled || 0} enroll.`,
                        'success'
                    );
                    setBulkAssignProgress(
                        processedJadwal,
                        totalJadwal,
                        `Memproses ${processedJadwal}/${totalJadwal} jadwal`
                    );
                } catch (error) {
                    appendBulkAssignLog(`Batch ${index + 1} gagal: ${error.message}`, 'error');
                    document.getElementById('bulkAssignRetryButton')?.classList.remove('hidden');
                    setBulkAssignProgress(
                        processedJadwal,
                        totalJadwal,
                        `Berhenti di batch ${index + 1}/${chunks.length}`
                    );
                    setBulkAssignProcessing(false);
                    return;
                }
            }

            const totals = bulkAssignState.totals;
            setBulkAssignProgress(totalJadwal, totalJadwal, `Selesai memproses ${totalJadwal} jadwal`);
            appendBulkAssignLog(
                `Selesai: ${totals.attached} sesi ditambahkan, ${totals.enrolled} siswa di-enroll, ${totals.updated} enrollment diperbarui, ${totals.skipped} siswa dilewati.`,
                'success'
            );
            if (totals.warnings > 0) {
                appendBulkAssignLog(`${totals.warnings} warning tercatat selama proses.`, 'warning');
            }
            document.getElementById('bulkAssignRefreshButton')?.classList.remove('hidden');
            setBulkAssignProcessing(false);
        }

        function retryBulkAssignFromFailedBatch() {
            if (!bulkAssignState || bulkAssignState.currentIndex === undefined) {
                return;
            }

            runBulkAssignChunks(bulkAssignState.currentIndex);
        }

        async function bulkAssignSesiAndEnroll() {
            const checkedJadwal = Array.from(document.querySelectorAll('.jadwal-checkbox:checked'));
            const checkedSesi = Array.from(document.querySelectorAll('.bulk-assign-sesi-checkbox:checked'));

            if (checkedJadwal.length === 0) {
                alert('Pilih minimal satu jadwal ujian');
                return;
            }

            if (checkedSesi.length === 0) {
                alert('Pilih minimal satu sesi sumber');
                return;
            }

            if (!confirm(
                    `Assign ${checkedSesi.length} sesi sumber ke ${checkedJadwal.length} jadwal dan enroll siswa secara massal?`
                )) {
                return;
            }

            const jadwalIds = checkedJadwal.map((checkbox) => checkbox.value);
            const sesiIds = checkedSesi.map((checkbox) => checkbox.value);

            bulkAssignState = {
                jadwalIds,
                sesiIds,
                chunks: chunkArray(jadwalIds, bulkAssignBatchSize),
                currentIndex: 0,
                isProcessing: false,
                totals: {
                    processed: 0,
                    attached: 0,
                    enrolled: 0,
                    updated: 0,
                    skipped: 0,
                    warnings: 0,
                },
            };

            document.getElementById('bulkAssignProgress')?.classList.remove('hidden');
            document.getElementById('bulkAssignLog').innerHTML = '';
            appendBulkAssignLog(
                `Mulai memproses ${jadwalIds.length} jadwal dalam ${bulkAssignState.chunks.length} batch.`,
                'info'
            );
            await runBulkAssignChunks(0);
        }

        document.getElementById('bulkAssignSelectAllSesi')?.addEventListener('change', function() {
            document.querySelectorAll('.bulk-assign-sesi-checkbox, .bulk-assign-sesi-group').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });

        document.querySelectorAll('.bulk-assign-sesi-group').forEach((groupCheckbox) => {
            groupCheckbox.addEventListener('change', function() {
                document.querySelectorAll(`.bulk-assign-sesi-checkbox[data-group="${this.dataset.group}"]`)
                    .forEach((checkbox) => checkbox.checked = this.checked);
            });
        });

        document.getElementById('bulkAssignSesiModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeBulkAssignSesiModal();
            }
        });


        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('bulk-action-dropdown');
            const menu = document.getElementById('bulk-dropdown-menu');

            if (!dropdown.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
@endsection

@push('flash-action')
    @if (session('delete_failed'))
        <form action="{{ route('naskah.jadwal.force-destroy', session('delete_failed')) }}" method="POST"
            class="mt-2 inline-block"
            onsubmit="return confirm('Yakin ingin menghapus paksa jadwal ini beserta hasil ujiannya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                <i class="fa-solid fa-skull-crossbones mr-1"></i> Hapus Paksa
            </button>
        </form>
    @endif
@endpush
