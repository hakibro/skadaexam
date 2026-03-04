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
                    <button type="button"
                        class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-sm rounded-md transition"
                        data-modal-toggle="susulanModal">
                        <i class="fa-solid fa-clock mr-1"></i> Susulan
                    </button>
                </div>
            </div>

            <!-- Filter Form - Compact -->
            <div class="p-3 bg-white border-b">
                <form action="{{ route('naskah.jadwal.index') }}" method="get" class="jadwal-filter-form">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 lg:grid-cols-8 gap-2">
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
                                <option value="30" {{ request('per_page', 30) == 30 ? 'selected' : '' }}>30</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
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
                                        <div class="text-gray-500">{{ $jadwal->jenis_ujian }}</div>
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
    </div>

    <!-- Modal Ujian Susulan -->
    <div id="susulanModal" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow-lg">
                <div class="flex justify-between items-center p-4 border-b rounded-t">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-clock mr-2"></i>Buat Ujian Susulan
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-toggle="susulanModal">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('naskah.jadwal.susulan.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="jadwal_ids" class="block mb-2 text-sm font-medium text-gray-900">
                                Pilih Jadwal Ujian <span class="text-red-500">*</span>
                            </label>
                            <select id="jadwal_ids" name="jadwal_ids[]" multiple required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @foreach ($allJadwal as $jadwal)
                                    <option value="{{ $jadwal->id }}">{{ $jadwal->judul }} ({{ $jadwal->kode_ujian }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Pilih satu atau lebih jadwal yang akan dibuatkan ujian
                                susulan.</p>
                        </div>
                        <div>
                            <label for="tanggal" class="block mb-2 text-sm font-medium text-gray-900">
                                Tanggal Ujian Susulan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal" id="tanggal" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="waktu_mulai" class="block mb-2 text-sm font-medium text-gray-900">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="waktu_mulai" id="waktu_mulai" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            <div>
                                <label for="waktu_selesai" class="block mb-2 text-sm font-medium text-gray-900">
                                    Waktu Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="waktu_selesai" id="waktu_selesai" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end p-4 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900"
                            data-modal-toggle="susulanModal">
                            <i class="fa-solid fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            class="text-white bg-orange-700 hover:bg-orange-800 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            <i class="fa-solid fa-save mr-2"></i>Buat Ujian Susulan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                document.getElementById('bulk-action-type').value = 'status_change';
                document.getElementById('bulk-new-status').value = newStatus;

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'jadwal_ids[]';
                    hiddenInput.value = checkbox.value;
                    form.appendChild(hiddenInput);
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
                document.getElementById('bulk-action-type').value = 'delete';

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'jadwal_ids[]';
                    hiddenInput.value = checkbox.value;
                    form.appendChild(hiddenInput);
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
                document.getElementById('bulk-action-type').value = 'force_delete';

                // Add selected jadwal IDs to form
                checkedBoxes.forEach(checkbox => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'jadwal_ids[]';
                    hiddenInput.value = checkbox.value;
                    form.appendChild(hiddenInput);
                });

                form.submit();
            }
        }


        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('bulk-action-dropdown');
            const menu = document.getElementById('bulk-dropdown-menu');

            if (!dropdown.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Toggle modal susulan
        const susulanModal = document.getElementById('susulanModal');
        document.querySelectorAll('[data-modal-toggle="susulanModal"]').forEach(btn => {
            btn.addEventListener('click', () => {
                susulanModal.classList.toggle('hidden');
                susulanModal.classList.toggle('flex');
            });
        });
        susulanModal.addEventListener('click', (e) => {
            if (e.target === susulanModal) {
                susulanModal.classList.add('hidden');
                susulanModal.classList.remove('flex');
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


@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit filter form when selecting status, mapel, or date
            const autoSubmitElements = document.querySelectorAll(
                '.jadwal-filter-form select, .jadwal-filter-form input[type="date"]');
            autoSubmitElements.forEach(element => {
                element.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });
        });
    </script>
@endsection
