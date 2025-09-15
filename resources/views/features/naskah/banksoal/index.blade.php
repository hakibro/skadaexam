@extends('layouts.admin')

@section('title', 'Manajemen Bank Soal')
@section('page-title', 'Manajemen Bank Soal')
@section('page-description', 'Kelola koleksi soal untuk ujian')

@section('content')
    <div class="space-y-6">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 relative" role="alert"
                id="success-alert">
                <p class="font-medium">Sukses!</p>
                <p>{{ session('success') }}</p>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 right-0 mt-4 mr-4 text-green-700">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 relative" role="alert" id="error-alert">
                <p class="font-medium">Error!</p>
                <p>{{ session('error') }}</p>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 right-0 mt-4 mr-4 text-red-700">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error_with_action'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 relative" role="alert"
                id="error-with-action-alert">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium">Tindakan Gagal!</p>
                        <p class="mb-2">{{ session('error_with_action')['message'] }}</p>
                        <button id="force-delete-btn" data-id="{{ session('error_with_action')['bank_soal_id'] }}"
                            data-name="{{ session('error_with_action')['bank_soal_name'] }}"
                            data-count="{{ session('error_with_action')['soal_count'] }}"
                            class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 flex items-center mt-2">
                            <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                            Hapus Paksa
                        </button>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none'" class="text-red-700">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Bank Soal</h3>
                <p class="text-sm text-gray-500">Total: <span id="bank-count">{{ $bankSoals->total() }}</span> bank soal</p>
            </div>
            <div>
                <a href="{{ route('naskah.banksoal.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Bank Soal</span>
                </a>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Search Input -->
                <div class="md:col-span-5">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-search mr-1 text-gray-400"></i>
                        Cari Bank Soal
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search-input" placeholder="Cari berdasarkan judul atau deskripsi..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Mapel Filter -->
                <div class="md:col-span-2">
                    <label for="mapel-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-book mr-1 text-gray-400"></i>
                        Mata Pelajaran
                    </label>
                    <select id="mapel-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tingkat Filter -->
                <div class="md:col-span-2">
                    <label for="tingkat-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-graduation-cap mr-1 text-gray-400"></i>
                        Tingkat / Kelas
                    </label>
                    <select id="tingkat-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tingkat</option>
                        <option value="X" {{ request('tingkat') == 'X' ? 'selected' : '' }}>Kelas X</option>
                        <option value="XI" {{ request('tingkat') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                        <option value="XII" {{ request('tingkat') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="md:col-span-1">
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-toggle-on mr-1 text-gray-400"></i>
                        Status
                    </label>
                    <select id="status-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="arsip" {{ request('status') == 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <!-- Jenis Soal Filter -->
                <div class="md:col-span-1">
                    <label for="jenis-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-list-check mr-1 text-gray-400"></i>
                        Jenis
                    </label>
                    <select id="jenis-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua</option>
                        <option value="uts" {{ request('jenis_soal') == 'uts' ? 'selected' : '' }}>UTS</option>
                        <option value="uas" {{ request('jenis_soal') == 'uas' ? 'selected' : '' }}>UAS</option>
                        <option value="ulangan" {{ request('jenis_soal') == 'ulangan' ? 'selected' : '' }}>Ulangan</option>
                        <option value="latihan" {{ request('jenis_soal') == 'latihan' ? 'selected' : '' }}>Latihan</option>
                    </select>
                </div>

                <div class="md:col-span-1">
                    <button id="clear-filters"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-times mr-1"></i>
                        <span>Clear</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bank Soal List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if ($bankSoals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Judul Bank Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tingkat
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dibuat
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($bankSoals as $bank)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $bank->judul }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($bank->deskripsi, 60) }}</div>
                                        <div class="text-xs text-gray-400 mt-1">Kode: {{ $bank->kode_bank }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $bank->mapel ? $bank->mapel->nama_mapel : 'Tidak ada mapel' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Kelas {{ $bank->tingkat }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bank->total_soal }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $jenisClasses = [
                                                'pilihan_ganda' => 'bg-blue-100 text-blue-800',
                                                'essay' => 'bg-purple-100 text-purple-800',
                                                'campuran' => 'bg-indigo-100 text-indigo-800',
                                                'uts' => 'bg-purple-100 text-purple-800',
                                                'uas' => 'bg-indigo-100 text-indigo-800',
                                                'ulangan' => 'bg-blue-100 text-blue-800',
                                                'latihan' => 'bg-green-100 text-green-800',
                                            ];
                                            $jenisClass =
                                                $jenisClasses[$bank->jenis_soal] ?? 'bg-gray-100 text-gray-800';
                                            $jenisLabel =
                                                [
                                                    'pilihan_ganda' => 'Pilihan Ganda',
                                                    'essay' => 'Essay',
                                                    'campuran' => 'Campuran',
                                                    'uts' => 'UTS',
                                                    'uas' => 'UAS',
                                                    'ulangan' => 'Ulangan',
                                                    'latihan' => 'Latihan',
                                                ][$bank->jenis_soal] ?? ucfirst($bank->jenis_soal);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $jenisClass }}">
                                            {{ $jenisLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'aktif' => 'bg-green-100 text-green-800',
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'arsip' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusClass = $statusClasses[$bank->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($bank->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bank->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('naskah.banksoal.edit', $bank->id) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fa-solid fa-edit"></i>
                                                <span class="hidden md:inline">Edit</span>
                                            </a>
                                            <a href="{{ route('naskah.banksoal.show', $bank->id) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fa-solid fa-eye"></i>
                                                <span class="hidden md:inline">Detail</span>
                                            </a>
                                            <form action="{{ route('naskah.banksoal.destroy', $bank->id) }}"
                                                method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 delete-btn"
                                                    data-name="{{ $bank->judul }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                    <span class="hidden md:inline">Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-gray-500">
                    Tidak ada bank soal ditemukan.
                </div>
            @endif
        </div>

        <!-- Pagination -->
        <div class="flex flex-col md:flex-row items-center justify-between py-4">
            <div class="text-sm text-gray-700">
                Menampilkan
                <span class="font-medium">{{ $bankSoals->firstItem() }}</span>
                s/d
                <span class="font-medium">{{ $bankSoals->lastItem() }}</span>
                dari
                <span class="font-medium">{{ $bankSoals->total() }}</span>
                bank soal
            </div>
            <div>
                {{ $bankSoals->links('features.naskah.banksoal.partials.pagination') }}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <i class="fa-solid fa-exclamation-triangle text-yellow-500 text-5xl mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus bank soal <span id="delete-item-name"
                        class="font-medium"></span>?</p>
            </div>
            <div class="flex justify-end space-x-4">
                <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </button>
                <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Force Delete Confirmation Modal -->
    <div id="forceDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <i class="fa-solid fa-exclamation-circle text-red-500 text-5xl mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Perhatian: Hapus Paksa Bank Soal</h3>
                <div class="text-gray-600 mb-6 text-left">
                    <p class="mb-2">Bank soal <span id="force-delete-name" class="font-semibold"></span> masih memiliki
                        <span id="force-delete-count" class="font-semibold"></span> soal di dalamnya.
                    </p>
                    <p class="font-medium text-red-600">Menghapus paksa akan menghapus semua soal yang terkait dengan bank
                        soal ini secara permanen!</p>
                    <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Tindakan ini tidak dapat dibatalkan. Semua soal akan dihapus secara permanen.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button id="cancelForceDelete" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </button>
                <button id="confirmForceDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center">
                    <i class="fa-solid fa-trash mr-2"></i> Hapus Paksa
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const mapelFilter = document.getElementById('mapel-filter'); // Filter mata pelajaran
            const tingkatFilter = document.getElementById('tingkat-filter');
            const statusFilter = document.getElementById('status-filter');
            const jenisFilter = document.getElementById('jenis-filter');
            const clearFiltersBtn = document.getElementById('clear-filters');

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const successAlert = document.getElementById('success-alert');
                const errorAlert = document.getElementById('error-alert');
                if (successAlert) {
                    successAlert.style.display = 'none';
                }
                if (errorAlert) {
                    errorAlert.style.display = 'none';
                }
            }, 5000);

            // Delete confirmation functionality
            const deleteModal = document.getElementById('deleteConfirmModal');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const deleteBtns = document.querySelectorAll('.delete-btn');
            let currentForm = null;

            // Force delete modal elements
            const forceDeleteModal = document.getElementById('forceDeleteModal');
            const cancelForceDeleteBtn = document.getElementById('cancelForceDelete');
            const confirmForceDeleteBtn = document.getElementById('confirmForceDelete');
            const forceDeleteBtn = document.getElementById('force-delete-btn');

            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const name = this.getAttribute('data-name');
                    document.getElementById('delete-item-name').textContent = name;
                    currentForm = this.closest('form');
                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex'); // Add flex when showing
                });
            });

            cancelDeleteBtn.addEventListener('click', function() {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex'); // Remove flex when hiding
                currentForm = null;
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (currentForm) {
                    currentForm.submit();
                }
            });

            // Force delete functionality
            if (forceDeleteBtn) {
                forceDeleteBtn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const count = this.getAttribute('data-count');

                    document.getElementById('force-delete-name').textContent = name;
                    document.getElementById('force-delete-count').textContent = count;

                    // Close the error alert
                    const errorAlert = document.getElementById('error-with-action-alert');
                    if (errorAlert) {
                        errorAlert.style.display = 'none';
                    }

                    // Show the force delete modal
                    forceDeleteModal.classList.remove('hidden');
                    forceDeleteModal.classList.add('flex');
                });
            }

            cancelForceDeleteBtn.addEventListener('click', function() {
                forceDeleteModal.classList.add('hidden');
                forceDeleteModal.classList.remove('flex');
            });

            confirmForceDeleteBtn.addEventListener('click', function() {
                // Create a form for force delete with force_delete parameter
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('naskah/banksoal') }}/${forceDeleteBtn.getAttribute('data-id')}`;

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add method DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                // Add force_delete parameter
                const forceDeleteInput = document.createElement('input');
                forceDeleteInput.type = 'hidden';
                forceDeleteInput.name = 'force_delete';
                forceDeleteInput.value = '1';
                form.appendChild(forceDeleteInput);

                // Append to document and submit
                document.body.appendChild(form);
                form.submit();
            });

            // Function to apply filters
            function applyFilters() {
                const searchValue = searchInput.value.trim();
                const mapelValue = mapelFilter.value; // Tambahkan mata pelajaran
                const tingkatValue = tingkatFilter.value;
                const statusValue = statusFilter.value;
                const jenisValue = jenisFilter ? jenisFilter.value : '';

                // Build URL with filters
                const url = new URL(window.location.href);
                url.searchParams.delete('page'); // Reset to page 1 when filtering

                if (searchValue) url.searchParams.set('search', searchValue);
                else url.searchParams.delete('search');

                if (mapelValue) url.searchParams.set('mapel_id', mapelValue); // Filter mata pelajaran
                else url.searchParams.delete('mapel_id');

                if (tingkatValue) url.searchParams.set('tingkat', tingkatValue);
                else url.searchParams.delete('tingkat');

                if (statusValue) url.searchParams.set('status', statusValue);
                else url.searchParams.delete('status');

                if (jenisValue) url.searchParams.set('jenis_soal', jenisValue);
                else url.searchParams.delete('jenis_soal');

                // Navigate to filtered URL
                window.location.href = url.toString();
            }

            // Attach event listeners
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyFilters();
                    }
                });
            }

            if (mapelFilter) {
                mapelFilter.addEventListener('change', applyFilters);
            }

            if (tingkatFilter) {
                tingkatFilter.addEventListener('change', applyFilters);
            }

            if (statusFilter) {
                statusFilter.addEventListener('change', applyFilters);
            }

            if (jenisFilter) {
                jenisFilter.addEventListener('change', applyFilters);
            }

            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    // Reset all filters
                    if (searchInput) searchInput.value = '';
                    if (mapelFilter) mapelFilter.value = '';
                    if (tingkatFilter) tingkatFilter.value = '';
                    if (statusFilter) statusFilter.value = '';
                    if (jenisFilter) jenisFilter.value = '';

                    // Go to base URL without parameters
                    window.location.href = '{{ route('naskah.banksoal.index') }}';
                });
            }
        });
    </script>
@endsection
