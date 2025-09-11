@extends('layouts.admin')

@section('title', 'Daftar Ruangan')
@section('page-title', 'Manajemen Ruangan')
@section('page-description', 'Daftar dan kelola semua ruangan ujian')

@section('content')
    <div class="py-4">
        <!-- Action Bar -->
        <div class="flex flex-wrap gap-4 justify-between items-center mb-6">
            <div class="flex items-center gap-2">
                <a href="{{ route('ruangan.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Ruangan</span>
                </a>
                <a href="{{ route('ruangan.import') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded flex items-center gap-2">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import Data</span>
                </a>
                <a href="{{ route('ruangan.import.comprehensive') }}"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded flex items-center gap-2">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import Komprehensif</span>
                </a>
                <a href="{{ route('ruangan.export') }}"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded flex items-center gap-2">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Export Data</span>
                </a>

            </div>


        </div>
        <!-- Room Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Ruangan</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $statistics['total'] }}</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3 text-blue-500">
                        <i class="fa-solid fa-door-open text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Ruangan Aktif</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $statistics['aktif'] }}</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-3 text-green-500">
                        <i class="fa-solid fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Ruangan Nonaktif</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $statistics['nonaktif'] }}</h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-3 text-red-500">
                        <i class="fa-solid fa-times-circle text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Dalam Perbaikan</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $statistics['perbaikan'] }}</h3>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3 text-yellow-500">
                        <i class="fa-solid fa-tools text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        @if ($ruangans->count() > 0)
            <div x-data="{ showBulkActions: false, selectedItems: [] }" class="mb-6">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="flex flex-wrap items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="select-all" x-on:change="selectAll($event)"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="select-all" class="ml-2 text-sm text-gray-700">Pilih Semua</label>
                            </div>
                            <span x-text="selectedItems.length + ' ruangan dipilih'" x-show="selectedItems.length > 0"
                                class="text-sm text-gray-600"></span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <form action="{{ route('ruangan.index') }}" method="GET" class="flex items-center gap-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" name="search" id="search" placeholder="Cari ruangan..."
                                        value="{{ request('search') }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div class="relative">
                                    <select name="status" id="status"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Semua Status</option>
                                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>
                                            Non-aktif
                                        </option>
                                        <option value="perbaikan" {{ request('status') == 'perbaikan' ? 'selected' : '' }}>
                                            Perbaikan</option>
                                    </select>
                                </div>

                                <div class="relative">
                                    <select name="sort" id="sort"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="nama_asc"
                                            {{ request('sort', 'nama_asc') == 'nama_asc' ? 'selected' : '' }}>
                                            Nama (A-Z)
                                        </option>
                                        <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>
                                            Nama
                                            (Z-A)</option>
                                        <option value="kapasitas_asc"
                                            {{ request('sort') == 'kapasitas_asc' ? 'selected' : '' }}>
                                            Kapasitas (Terkecil)
                                        </option>
                                        <option value="kapasitas_desc"
                                            {{ request('sort') == 'kapasitas_desc' ? 'selected' : '' }}>
                                            Kapasitas (Terbesar)
                                        </option>
                                        <option value="created_at_desc"
                                            {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>
                                            Terbaru
                                        </option>
                                        <option value="created_at_asc"
                                            {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>
                                            Terlama
                                        </option>
                                    </select>
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Filter
                                </button>

                                @if (request('search') || request('status') || request('sort') != 'nama_asc')
                                    <a href="{{ route('ruangan.index') }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                                        <i class="fa-solid fa-times mr-2"></i> Reset
                                    </a>
                                @endif
                            </form>
                        </div>

                        <div x-show="selectedItems.length > 0" class="flex flex-wrap gap-2 mt-2 sm:mt-0">
                            <button @click="confirmBulkAction('aktifkan')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fa-solid fa-check-circle mr-2"></i> Aktifkan
                            </button>
                            <button @click="confirmBulkAction('nonaktifkan')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fa-solid fa-times-circle mr-2"></i> Non-aktifkan
                            </button>
                            <button @click="confirmBulkAction('perbaikan')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <i class="fa-solid fa-tools mr-2"></i> Perbaikan
                            </button>
                            <button @click="confirmBulkAction('hapus')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i class="fa-solid fa-trash mr-2"></i> Hapus
                            </button>
                        </div>

                    </div>
                </div>

                <form id="bulk-action-form" action="{{ route('ruangan.bulk-action') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="action" id="bulk-action">
                    <input type="hidden" name="ids" id="bulk-ids">
                </form>
            </div>
        @endif



        <!-- Room List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fa-solid fa-door-open text-blue-600 mr-2"></i>
                    Daftar Ruangan
                </h3>
                <span class="text-sm text-gray-600">{{ $ruangans->total() }} ruangan ditemukan</span>
            </div>

            @if ($ruangans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-6 py-3 text-left">
                                    <span class="sr-only">Pilih</span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Ruangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kapasitas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi Hari Ini
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" x-ref="roomsTable">
                            @foreach ($ruangans as $ruangan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $ruangan->id }}"
                                                class="room-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                x-on:change="updateSelected">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ruangan->nama_ruangan }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $ruangan->lokasi ?? 'Lokasi tidak disetel' }}
                                        </div>
                                        @if ($ruangan->catatan)
                                            <div class="text-xs text-gray-500 italic mt-1">
                                                Catatan: {{ Str::limit($ruangan->catatan, 30) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $ruangan->kode_ruangan }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ruangan->kapasitas }} siswa</div>
                                        @if ($ruangan->today_capacity_used)
                                            <div class="w-24 bg-gray-200 rounded-full h-2 mt-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: {{ min($ruangan->today_capacity_percentage, 100) }}%">
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $ruangan->today_capacity_used }}/{{ $ruangan->kapasitas }} hari ini
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="{{ $ruangan->status_badge_class }} px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ $ruangan->status_label['text'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-sm text-gray-900">
                                            {{ $ruangan->sesi_ruangan_count ?? 0 }} sesi
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $ruangan->sesi_aktif_count ?? 0 }} sesi aktif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($ruangan->today_sessions_count > 0)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $ruangan->today_sessions_count }} sesi hari ini
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                @foreach ($ruangan->today_sessions as $session)
                                                    <div class="mb-1 flex items-center gap-1">
                                                        <span
                                                            class="{{ $session->status == 'belum_mulai' ? 'bg-blue-100 text-blue-800' : ($session->status == 'berlangsung' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }} px-1.5 py-0.5 rounded text-xs font-medium">
                                                            {{ \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i') }}
                                                        </span>
                                                        <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $session->id]) }}"
                                                            class="text-xs text-blue-600 hover:underline ml-1"
                                                            title="Lihat sesi">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Tidak ada sesi hari ini</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Detail Ruangan">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900" title="Edit Ruangan">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                                class="text-green-600 hover:text-green-900" title="Kelola Sesi">
                                                <i class="fa-solid fa-calendar-alt"></i>
                                            </a>
                                            <form action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST"
                                                class="inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Hapus Ruangan"
                                                    onclick="return confirm('Yakin ingin menghapus ruangan ini? Semua sesi terkait juga akan terhapus.')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $ruangans->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <i class="fa-solid fa-door-open text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada ruangan yang ditambahkan</h3>
                    <p class="text-gray-500 mb-6">Mulailah dengan menambahkan ruangan pertama</p>
                    <a href="{{ route('ruangan.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Ruangan Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bulkActions', () => ({
                showBulkActions: false,
                selectedItems: [],

                init() {
                    this.updateSelected();
                },

                selectAll(e) {
                    const checkboxes = document.querySelectorAll('.room-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                    });
                    this.updateSelected();
                },

                updateSelected() {
                    const checkboxes = document.querySelectorAll('.room-checkbox:checked');
                    this.selectedItems = Array.from(checkboxes).map(checkbox => checkbox.value);
                    this.showBulkActions = this.selectedItems.length > 0;
                },

                confirmBulkAction(action) {
                    let message = 'Apakah Anda yakin ingin ';

                    switch (action) {
                        case 'aktifkan':
                            message += 'mengaktifkan';
                            break;
                        case 'nonaktifkan':
                            message += 'menonaktifkan';
                            break;
                        case 'perbaikan':
                            message += 'mengubah status menjadi perbaikan untuk';
                            break;
                        case 'hapus':
                            message += 'menghapus';
                            break;
                    }

                    message += ` ${this.selectedItems.length} ruangan yang dipilih?`;

                    if (action === 'hapus') {
                        message +=
                            ' Semua sesi ruangan dan data terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan!';
                    }

                    if (confirm(message)) {
                        document.getElementById('bulk-action').value = action;
                        document.getElementById('bulk-ids').value = this.selectedItems.join(',');
                        document.getElementById('bulk-action-form').submit();
                    }
                }
            }));
        });

        // Status filter live change
        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });

        // Sort filter live change
        document.getElementById('sort').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
@endsection
