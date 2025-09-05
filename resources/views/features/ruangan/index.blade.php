@extends('layouts.admin')

@section('title', 'Daftar Ruangan')
@section('page-title', 'Daftar Ruangan')
@section('page-description', 'Kelola seluruh ruangan ujian')

@section('content')
    <div class="py-4">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form action="{{ route('ruangan.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fa-solid fa-search mr-1 text-gray-400"></i>
                            Cari Ruangan
                        </label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Nama atau kode ruangan"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fa-solid fa-toggle-on mr-1 text-gray-400"></i>
                            Status
                        </label>
                        <select name="status" id="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="perbaikan" {{ request('status') == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                            </option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak
                                Aktif</option>
                        </select>
                    </div>

                    <div>
                        <label for="kapasitas_min" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fa-solid fa-users mr-1 text-gray-400"></i>
                            Kapasitas Min
                        </label>
                        <input type="number" name="kapasitas_min" id="kapasitas_min" value="{{ request('kapasitas_min') }}"
                            placeholder="Min" min="1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="kapasitas_max" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Max
                        </label>
                        <input type="number" name="kapasitas_max" id="kapasitas_max" value="{{ request('kapasitas_max') }}"
                            placeholder="Max" min="1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <div class="flex justify-between">
                    <div class="flex items-center space-x-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fa-solid fa-filter mr-2"></i> Filter
                        </button>
                        <a href="{{ route('ruangan.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fa-solid fa-eraser mr-2"></i> Reset
                        </a>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('ruangan.create') }}"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Ruangan
                        </a>
                        <a href="{{ route('ruangan.import') }}"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fa-solid fa-file-import mr-2"></i> Import
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Action -->
        @if ($ruangans->count() > 0)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <form id="bulk-form" action="{{ route('ruangan.bulk-delete') }}" method="POST">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <button type="button" id="select-all"
                            class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            <i class="fa-regular fa-square-check mr-1"></i> Pilih Semua
                        </button>
                        <button type="submit" id="bulk-delete"
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700" disabled>
                            <i class="fa-solid fa-trash mr-1"></i> Hapus Terpilih
                        </button>
                        <span id="selected-count" class="text-sm text-gray-500">0 ruangan dipilih</span>
                    </div>
                </form>
            </div>
        @endif

        <!-- Ruangan List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Ruangan</h3>
                    <p class="text-sm text-gray-500">
                        Total: {{ $ruangans->total() }} ruangan
                    </p>
                </div>
            </div>

            @if ($ruangans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all-header"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Ruangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Ruangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kapasitas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lokasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ruangans as $ruangan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="room_ids[]" value="{{ $ruangan->id }}"
                                            class="room-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $ruangan->kode_ruangan }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ruangan->nama_ruangan }}</div>
                                        <div class="text-sm text-gray-500">{{ $ruangan->jenis_ruangan }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fa-solid fa-users mr-1 text-gray-400"></i>
                                            {{ $ruangan->kapasitas }} orang
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ruangan->lokasi ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($ruangan->status == 'aktif')
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fa-solid fa-check-circle mr-1"></i>Aktif
                                            </span>
                                        @elseif($ruangan->status == 'perbaikan')
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fa-solid fa-tools mr-1"></i>Perbaikan
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fa-solid fa-times-circle mr-1"></i>Tidak Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="font-medium">{{ $ruangan->sesi_ruangan_count }}</span> sesi
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Kelola Sesi">
                                                <i class="fa-solid fa-calendar-alt"></i>
                                            </a>
                                            <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <button onclick="deleteRoom({{ $ruangan->id }}, false)"
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                            <!-- Force Delete Button -->
                                            @if ($ruangan->sesi_ruangan_count > 0)
                                                <button onclick="deleteRoom({{ $ruangan->id }}, true)"
                                                    class="text-red-600 hover:text-red-900" title="Hapus Paksa">
                                                    <i class="fa-solid fa-radiation"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Hidden delete form for each room -->
                                        <form id="delete-form-{{ $ruangan->id }}"
                                            action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST"
                                            class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <!-- Hidden force delete form for each room -->
                                        <form id="force-delete-form-{{ $ruangan->id }}"
                                            action="{{ route('ruangan.force-delete', $ruangan->id) }}" method="POST"
                                            class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $ruangans->withQueryString()->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-4 0H3m2 0h3M9 7h6m-6 4h6m-6 4h6" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada ruangan</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada data ruangan yang tersedia.</p>
                    <div class="mt-6">
                        <a href="{{ route('ruangan.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Ruangan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Function to handle room deletion
        function deleteRoom(roomId, isForceDelete = false) {
            let confirmMessage =
                'Apakah Anda yakin ingin menghapus ruangan ini? Ruangan yang memiliki sesi tidak dapat dihapus.';
            let formId = 'delete-form-' + roomId;

            if (isForceDelete) {
                confirmMessage =
                    'PERHATIAN: Anda akan menghapus ruangan ini beserta semua sesi yang terkait! Tindakan ini TIDAK DAPAT dibatalkan dan dapat menyebabkan kerusakan data. Lanjutkan?';
                formId = 'force-delete-form-' + roomId;
            }

            if (confirm(confirmMessage)) {
                document.getElementById(formId).submit();
            }
        }

        // Bulk selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('select-all');
            const selectAllHeaderBtn = document.getElementById('select-all-header');
            const bulkDeleteBtn = document.getElementById('bulk-delete');
            const selectedCountSpan = document.getElementById('selected-count');
            const checkboxes = document.querySelectorAll('.room-checkbox');
            const bulkForm = document.getElementById('bulk-form');

            // Select all functionality
            function updateSelectAll() {
                const checkedCount = document.querySelectorAll('.room-checkbox:checked').length;
                const totalCount = checkboxes.length;

                if (selectAllBtn) {
                    selectAllBtn.innerHTML = checkedCount === totalCount && totalCount > 0 ?
                        '<i class="fa-regular fa-square mr-1"></i> Batal Pilih' :
                        '<i class="fa-regular fa-square-check mr-1"></i> Pilih Semua';
                }

                if (selectAllHeaderBtn) {
                    selectAllHeaderBtn.checked = checkedCount === totalCount && totalCount > 0;
                    selectAllHeaderBtn.indeterminate = checkedCount > 0 && checkedCount < totalCount;
                }
            }

            function updateSelectedCount() {
                const checkedCount = document.querySelectorAll('.room-checkbox:checked').length;
                if (selectedCountSpan) {
                    selectedCountSpan.textContent = checkedCount + ' ruangan dipilih';
                }
                if (bulkDeleteBtn) {
                    bulkDeleteBtn.disabled = checkedCount === 0;
                }
                updateSelectAll();
            }

            // Event listeners
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const isSelectingAll = this.innerHTML.includes('Pilih Semua');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = isSelectingAll;
                    });
                    updateSelectedCount();
                });
            }

            if (selectAllHeaderBtn) {
                selectAllHeaderBtn.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedCount();
                });
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Confirm before bulk delete
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const checkedCount = document.querySelectorAll('.room-checkbox:checked').length;
                    if (!confirm(
                            `Apakah Anda yakin ingin menghapus ${checkedCount} ruangan terpilih? Ruangan yang memiliki sesi tidak akan dihapus.`
                        )) {
                        e.preventDefault();
                    }
                });
            }

            // Initialize count
            updateSelectedCount();
        });
    </script>
@endsection
