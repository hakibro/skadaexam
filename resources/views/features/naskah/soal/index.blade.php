<!-- filepath: resources\views\features\naskah\soal\index.blade.php -->

@extends('layouts.admin')

@section('title', 'Manajemen Soal')
@section('page-title', 'Manajemen Soal')
@section('page-description', 'Buat dan kelola soal ujian')

@section('content')
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Daftar Soal</h3>
                <p class="text-sm text-gray-500">Total: <span id="soal-count">{{ $soals->total() }}</span> soal</p>
            </div>
            <div class="flex space-x-3">
                <!-- Import Button -->
                <a href="{{ route('naskah.soal.import') }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center space-x-2">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel</span>
                </a>
                <!-- Add New Button -->
                <a href="{{ route('naskah.soal.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Soal Baru</span>
                </a>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Search Input -->
                <div class="md:col-span-3">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-search mr-1 text-gray-400"></i>
                        Cari Soal
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400" id="search-icon"></i>
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 hidden" id="loading-icon"></i>
                        </div>
                        <input type="text" id="search-input"
                            placeholder="Cari soal berdasarkan pertanyaan atau kategori..."
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
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach (App\Models\Mapel::active()->get() as $mapel)
                            <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Bank Soal Filter -->
                <div class="md:col-span-2">
                    <label for="bank-soal-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-folder mr-1 text-gray-400"></i>
                        Bank Soal
                    </label>
                    <select id="bank-soal-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Bank Soal</option>
                        @foreach ($bankSoals as $bank)
                            <option value="{{ $bank->id }}"
                                {{ request('bank_soal_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->judul }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tingkat Filter -->
                <div class="md:col-span-1">
                    <label for="tingkat-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-layer-group mr-1 text-gray-400"></i>
                        Tingkat
                    </label>
                    <select id="tingkat-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tingkat</option>
                        @isset($tingkatList)
                            @foreach ($tingkatList as $tingkat)
                                <option value="{{ $tingkat }}" {{ request('tingkat') == $tingkat ? 'selected' : '' }}>
                                    Kelas {{ $tingkat }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <!-- Tipe Soal Filter -->
                <div class="md:col-span-2">
                    <label for="tipe-soal-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-list-ul mr-1 text-gray-400"></i>
                        Tipe Soal
                    </label>
                    <select id="tipe-soal-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tipe</option>
                        <option value="pilihan_ganda" {{ request('tipe_soal') == 'pilihan_ganda' ? 'selected' : '' }}>
                            Pilihan Ganda</option>
                        <option value="essay" {{ request('tipe_soal') == 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>
                </div>

                <!-- Per Page Select -->
                <div class="md:col-span-2">
                    <label for="per-page-select" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-list-ol mr-1 text-gray-400"></i>
                        Tampilkan
                    </label>
                    <select id="per-page-select"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 soal</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 soal</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 soal</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 soal</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="md:col-span-1">
                    <button id="clear-filters"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-times mr-1"></i>
                        <span>Clear</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div id="bulk-actions" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle text-yellow-600 mr-2"></i>
                    <span class="text-sm font-medium text-yellow-800">
                        <span id="selected-count">0</span> soal dipilih
                    </span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkDelete()" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                        <i class="fa-solid fa-trash mr-1"></i>Hapus Terpilih
                    </button>
                    <button onclick="clearSelection()"
                        class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                        <i class="fa-solid fa-times mr-1"></i>Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="hidden bg-white shadow rounded-lg p-8 text-center">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Memuat data soal...</p>
        </div>

        <!-- Soal Table -->
        <div id="soal-table-container" class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Soal</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Menampilkan <span id="showing-count">{{ $soals->count() }}</span> dari
                    <span id="total-count">{{ $soals->total() }}</span> soal
                </p>
            </div>

            @if ($soals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No. Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pertanyaan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bank Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kunci <br />Jawaban
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($soals as $soal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="soal-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            value="{{ $soal->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $soal->nomor_soal }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs">
                                            @if ($soal->tipe_pertanyaan === 'teks')
                                                <div class="text-sm text-gray-900 search-highlight">
                                                    {{ Str::limit($soal->pertanyaan, 100) }}
                                                </div>
                                            @elseif($soal->tipe_pertanyaan === 'gambar')
                                                <div class="flex items-center">
                                                    <i class="fa-solid fa-image text-green-500 mr-2"></i>
                                                    <span class="text-sm text-gray-500">Soal Gambar</span>
                                                </div>
                                                @if ($soal->gambar_pertanyaan_url)
                                                    <img src="{{ $soal->gambar_pertanyaan_url }}" alt="Preview"
                                                        class="mt-1 h-12 w-auto rounded border">
                                                @endif
                                            @else
                                                <div class="text-sm text-gray-900 mb-1">
                                                    {{ Str::limit($soal->pertanyaan, 80) }}
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fa-solid fa-image text-green-500 mr-1"></i>
                                                    <span class="text-xs text-gray-500">+ Gambar</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 ">
                                        <div class="text-sm text-gray-900 search-highlight">
                                            {{ $soal->bankSoal->judul ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $soal->kategori ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $soal->bankSoal->mapel->nama_mapel ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($soal->tipe_soal === 'pilihan_ganda')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Pilihan Ganda
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Essay
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($soal->tipe_soal === 'pilihan_ganda')
                                            <span class="font-medium">{{ $soal->kunci_jawaban ?? '-' }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-2">
                                        <a href="{{ route('naskah.soal.show', $soal) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('naskah.soal.edit', $soal) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <button onclick="deleteSoal({{ $soal->id }})"
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <button onclick="duplicateSoal({{ $soal->id }})"
                                            class="text-yellow-600 hover:text-yellow-900" title="Duplikat">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $soals->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fa-solid fa-question-circle text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Soal</h3>
                    <p class="text-gray-500 mb-6">Mulai buat soal untuk bank soal Anda.</p>
                    <a href="{{ route('naskah.soal.create') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fa-solid fa-plus mr-1"></i>Tambah Soal Baru
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get filter elements
            const searchInput = document.getElementById('search-input');
            const mapelFilter = document.getElementById('mapel-filter');
            const bankSoalFilter = document.getElementById('bank-soal-filter');
            const tipeSoalFilter = document.getElementById('tipe-soal-filter');
            const perPageSelect = document.getElementById('per-page-select');
            const clearFiltersBtn = document.getElementById('clear-filters');

            // Get UI elements
            const searchIcon = document.getElementById('search-icon');
            const loadingIcon = document.getElementById('loading-icon');
            const tableContainer = document.getElementById('soal-table-container');
            const loadingState = document.getElementById('loading-state');
            const totalCount = document.getElementById('total-count');
            const showingCount = document.getElementById('showing-count');
            const soalCount = document.getElementById('soal-count');

            let searchTimeout;
            let isLoading = false;

            // Function to show loading state
            function showLoading() {
                if (isLoading) return;
                isLoading = true;

                if (searchIcon) searchIcon.classList.add('hidden');
                if (loadingIcon) loadingIcon.classList.remove('hidden');
                if (tableContainer) tableContainer.classList.add('hidden');
                if (loadingState) loadingState.classList.remove('hidden');
            }

            // Function to hide loading state
            function hideLoading() {
                isLoading = false;

                if (searchIcon) searchIcon.classList.remove('hidden');
                if (loadingIcon) loadingIcon.classList.add('hidden');
                if (tableContainer) tableContainer.classList.remove('hidden');
                if (loadingState) loadingState.classList.add('hidden');
            }

            // Main search function
            function performSearch() {
                // Clear previous timeout to avoid multiple requests
                clearTimeout(searchTimeout);

                // Set a new timeout for debouncing
                searchTimeout = setTimeout(() => {
                    // Get filter values
                    const query = searchInput?.value?.trim() || '';
                    const mapelId = mapelFilter?.value || '';
                    const bankSoalId = bankSoalFilter?.value || '';
                    const tingkat = document.getElementById('tingkat-filter')?.value || '';
                    const tipeSoal = tipeSoalFilter?.value || '';
                    const perPage = perPageSelect?.value || '10';

                    // Show loading state
                    showLoading();

                    // Build search URL with all params
                    const searchUrl = new URL('{{ route('naskah.soal.index') }}', window.location.origin);
                    if (query) searchUrl.searchParams.set('search', query);
                    if (mapelId) searchUrl.searchParams.set('mapel_id', mapelId);
                    if (bankSoalId) searchUrl.searchParams.set('bank_soal_id', bankSoalId);
                    if (tingkat) searchUrl.searchParams.set('tingkat', tingkat);
                    if (tipeSoal) searchUrl.searchParams.set('tipe_soal', tipeSoal);
                    if (perPage) searchUrl.searchParams.set('per_page', perPage);
                    searchUrl.searchParams.set('_t', Date.now()); // Cache buster

                    // Perform AJAX request with proper headers
                    fetch(searchUrl.toString(), {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html, application/xhtml+xml'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                            return response.text();
                        })
                        .then(html => {
                            // Use window.location.href to reload page with the new filters
                            // This is a more reliable approach than trying to replace the HTML content
                            window.location.href = searchUrl.toString();
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            hideLoading();
                            alert('Error: ' + error.message);
                        });
                }, 400); // Debounce for 400ms
            }

            // Attach event listeners
            if (searchInput) {
                searchInput.addEventListener('input', performSearch);
            }

            if (mapelFilter) {
                mapelFilter.addEventListener('change', performSearch);
            }

            if (bankSoalFilter) {
                bankSoalFilter.addEventListener('change', performSearch);
            }

            const tingkatFilter = document.getElementById('tingkat-filter');
            if (tingkatFilter) {
                tingkatFilter.addEventListener('change', performSearch);
            }

            if (tipeSoalFilter) {
                tipeSoalFilter.addEventListener('change', performSearch);
            }

            if (perPageSelect) {
                perPageSelect.addEventListener('change', performSearch);
            }

            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    // Reset all filters
                    if (searchInput) searchInput.value = '';
                    if (mapelFilter) mapelFilter.value = '';
                    if (bankSoalFilter) bankSoalFilter.value = '';
                    if (document.getElementById('tingkat-filter')) document.getElementById('tingkat-filter')
                        .value = '';
                    if (tipeSoalFilter) tipeSoalFilter.value = '';
                    if (perPageSelect) perPageSelect.value = '10';

                    // Perform search with reset filters
                    performSearch();
                });
            }

            // Bulk selection functionality
            const selectAllCheckbox = document.getElementById('select-all');
            const soalCheckboxes = document.querySelectorAll('.soal-checkbox');
            const bulkActionsDiv = document.getElementById('bulk-actions');
            const selectedCountSpan = document.getElementById('selected-count');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    soalCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkActions();
                });
            }

            soalCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkActions);
            });

            function updateBulkActions() {
                const selectedCount = document.querySelectorAll('.soal-checkbox:checked').length;

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

            window.bulkDelete = function() {
                const selectedIds = Array.from(
                    document.querySelectorAll('.soal-checkbox:checked')
                ).map(checkbox => checkbox.value);

                if (selectedIds.length === 0) {
                    alert('Pilih minimal satu soal untuk dihapus');
                    return;
                }

                if (confirm(`Yakin ingin menghapus ${selectedIds.length} soal terpilih?`)) {
                    // Create a form for POST submission
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('naskah.soal.bulk-delete') }}';

                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Add selected IDs
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'soal_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    // Add to DOM and submit
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            window.clearSelection = function() {
                soalCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }
                updateBulkActions();
            }

            window.deleteSoal = function(id) {
                if (confirm('Yakin ingin menghapus soal ini?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('naskah.soal.destroy', '__id__') }}'.replace('__id__', id);

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            window.duplicateSoal = function(id) {
                if (confirm('Ingin menduplikasi soal ini?')) {
                    window.location.href = '{{ route('naskah.soal.duplicate', '__id__') }}'.replace('__id__',
                        id);
                }
            }
        });
    </script>
@endsection
