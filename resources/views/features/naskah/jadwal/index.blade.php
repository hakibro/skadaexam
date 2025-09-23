@extends('layouts.admin')

@section('title', 'Jadwal Ujian')
@section('page-title', 'Jadwal Ujian')
@section('page-description', 'Kelola jadwal ujian dan sesi ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Daftar Jadwal Ujian</h3>
                <div class="flex space-x-2">
                    <!-- Bulk Actions -->
                    <div class="relative" id="bulk-action-dropdown" style="display: none;">
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150"
                            onclick="toggleBulkDropdown()">
                            <i class="fa-solid fa-tasks mr-2"></i> Aksi Terpilih
                            <i class="fa-solid fa-chevron-down ml-2"></i>
                        </button>
                        <div id="bulk-dropdown-menu"
                            class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-300 rounded-md shadow-lg z-10">
                            <div class="py-1">
                                <button type="button" onclick="bulkStatusChange('draft')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-pencil mr-2"></i> Set Draft
                                </button>
                                <button type="button" onclick="bulkStatusChange('aktif')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-check-circle mr-2"></i> Set Aktif
                                </button>
                                <button type="button" onclick="bulkStatusChange('nonaktif')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-pause-circle mr-2"></i> Set Non-Aktif
                                </button>
                                <button type="button" onclick="bulkStatusChange('selesai')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-flag-checkered mr-2"></i> Set Selesai
                                </button>
                                <hr class="my-1">
                                <button type="button" onclick="bulkDelete()"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-trash mr-2"></i> Hapus Terpilih
                                </button>
                                <button type="button" onclick="bulkForceDelete()"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-skull-crossbones mr-2"></i> Hapus Paksa Terpilih
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('naskah.jadwal.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Jadwal
                        </a>
                        {{-- <a href="{{ route('naskah.jadwal.batch-update-kelas-target') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-sync mr-2"></i> Update Kelas Target
                        </a> --}}
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50">
                <form action="{{ route('naskah.jadwal.index') }}" method="get" class="jadwal-filter-form">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-input w-full md:w-64 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari judul atau kode ujian...">
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif
                                </option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                            <select name="mapel_id"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="form-input w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="form-input w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="w-full md:w-auto flex items-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                                <i class="fa-solid fa-search mr-2"></i> Filter
                            </button>
                            <a href="{{ route('naskah.jadwal.index') }}"
                                class="inline-flex items-center px-4 py-2 ml-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition duration-150">
                                <i class="fa-solid fa-times mr-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if (count($jadwalUjians) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left">
                                    <input type="checkbox" id="select-all"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        onchange="toggleAllCheckboxes(this)">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Ujian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mapel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($jadwalUjians as $jadwal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="jadwal_ids[]" value="{{ $jadwal->id }}"
                                            class="jadwal-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            onchange="updateBulkActionVisibility()">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm">{{ $jadwal->kode_ujian }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $jadwal->judul }}</div>
                                        <div class="text-sm text-gray-500">{{ $jadwal->jenis_ujian }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div><i class="fa-solid fa-calendar-day mr-1"></i>
                                            {{ $jadwal->tanggal->format('d M Y') }}</div>
                                        <div class="text-sm text-gray-500"><i class="fa-solid fa-clock mr-1"></i>
                                            ({{ $jadwal->durasi_menit }} menit)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($jadwal->status)
                                            @case('draft')
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <i class="fa-solid fa-pencil mr-1"></i> Draft
                                                </span>
                                            @break

                                            @case('aktif')
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fa-solid fa-check-circle mr-1"></i> Aktif
                                                </span>
                                            @break

                                            @case('nonaktif')
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fa-solid fa-pause-circle mr-1"></i> Non-Aktif
                                                </span>
                                            @break

                                            @case('selesai')
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fa-solid fa-flag-checkered mr-1"></i> Selesai
                                                </span>
                                            @break

                                            @case('dibatalkan')
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fa-solid fa-ban mr-1"></i> Dibatalkan
                                                </span>
                                            @break

                                            @default
                                                <span
                                                    class="px-3 py-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ ucfirst($jadwal->status) }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md">
                                            {{ $jadwal->sesi_ruangans_count ?? $jadwal->sesiRuangans->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex flex-col justify-center items-start space-y-1">
                                            <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700">
                                                <i class="fa-solid fa-eye mr-1"></i> Detail
                                            </a>
                                            <a href="{{ route('naskah.jadwal.edit', $jadwal->id) }}"
                                                class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-xs rounded-md hover:bg-yellow-700">
                                                <i class="fa-solid fa-edit mr-1"></i> Edit
                                            </a>
                                            <form action="{{ route('naskah.jadwal.destroy', $jadwal->id) }}"
                                                method="POST" class="inline-block"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ujian ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs rounded-md hover:bg-red-700">
                                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4 border-t">
                        {{ $jadwalUjians->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="fa-solid fa-calendar-xmark text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">Belum ada jadwal ujian</p>
                        <p class="text-gray-400 mb-4">Tambahkan jadwal ujian baru untuk memulai</p>
                        <a href="{{ route('naskah.jadwal.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Jadwal Ujian
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
