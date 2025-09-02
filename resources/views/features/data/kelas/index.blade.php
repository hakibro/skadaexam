@extends('layouts.admin')

@section('title', 'Manajemen Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div id="flash-message" class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="text-green-400 hover:text-green-600">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div id="flash-message" class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="text-red-400 hover:text-red-600">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Data Kelas</h3>
                <p class="text-sm text-gray-500">
                    Total: <span id="kelas-count">{{ $kelas->count() ?? 0 }}</span> kelas
                </p>
            </div>

        </div>



        {{-- Filters --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- Search Input --}}
                <div class="md:col-span-5">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400" id="search-icon"></i>
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 hidden" id="loading-icon"></i>
                        </div>
                        <input type="text" id="search-input" name="search"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Cari nama kelas...">
                    </div>
                </div>

                {{-- Tingkat Filter --}}
                <div class="md:col-span-2">
                    <label for="tingkat-filter" class="block text-sm font-medium text-gray-700 mb-2">Tingkat</label>
                    <select id="tingkat-filter" name="tingkat"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tingkat</option>
                        @foreach ($tingkatList as $tingkat)
                            <option value="{{ $tingkat }}">{{ $tingkat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jurusan Filter --}}
                <div class="md:col-span-2">
                    <label for="jurusan-filter" class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                    <select id="jurusan-filter" name="jurusan"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusanList as $jurusan)
                            <option value="{{ $jurusan }}">{{ $jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Clear Button --}}
                <div class="md:col-span-3 flex justify-end">
                    <button type="button" id="clear-filters"
                        class="w-auto bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md transition-colors inline-flex items-center justify-center">
                        <i class="fa-solid fa-times mr-1"></i>Bersihkan Filter
                    </button>
                </div>
            </div>
        </div>


        {{-- Main Table --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg" id="results-container">
            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Kelas
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tingkat
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jurusan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Siswa
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="kelas-table-body">
                        @forelse($kelas as $index => $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $k->nama_kelas }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $k->tingkat ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $k->jurusan ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $k->student_count ?? 0 }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada data kelas. Gunakan tombol "Sinkronisasi Kelas" untuk memuat data kelas dari
                                    data siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Kelas Management JS loaded');

            // Basic elements
            const searchInput = document.getElementById('search-input');
            const tingkatFilter = document.getElementById('tingkat-filter');
            const jurusanFilter = document.getElementById('jurusan-filter');
            const clearFiltersBtn = document.getElementById('clear-filters');
            const loadingState = document.getElementById('loading-state');
            const resultsContainer = document.getElementById('results-container');


            let searchTimeout;

            // Filtering functionality
            function performSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterTable();
                }, 300);
            }

            function filterTable() {
                const searchTerm = (searchInput?.value || '').toLowerCase();
                const tingkat = tingkatFilter?.value || '';
                const jurusan = jurusanFilter?.value || '';

                const rows = document.querySelectorAll('#kelas-table-body tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    const namaKelas = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const rowTingkat = row.querySelector('td:nth-child(3)')?.textContent || '';
                    const rowJurusan = row.querySelector('td:nth-child(4)')?.textContent || '';

                    const matchesSearch = !searchTerm || namaKelas.includes(searchTerm);
                    const matchesTingkat = !tingkat || rowTingkat.includes(tingkat);
                    const matchesJurusan = !jurusan || rowJurusan.includes(jurusan);

                    const isVisible = matchesSearch && matchesTingkat && matchesJurusan;
                    row.classList.toggle('hidden', !isVisible);

                    if (isVisible) visibleCount++;
                });

                // Update counter
                const kelasCount = document.getElementById('kelas-count');
                if (kelasCount) {
                    kelasCount.textContent = visibleCount;
                }

                // Show empty message if needed
                const tableBody = document.getElementById('kelas-table-body');
                if (tableBody) {
                    // Check if there's already an empty message
                    let emptyMessage = tableBody.querySelector('.empty-message');

                    if (visibleCount === 0) {
                        // Show empty message if not already present
                        if (!emptyMessage) {
                            emptyMessage = document.createElement('tr');
                            emptyMessage.className = 'empty-message';
                            emptyMessage.innerHTML = `
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ditemukan data kelas yang sesuai dengan filter.
                                </td>
                            `;
                            tableBody.appendChild(emptyMessage);
                        }
                    } else if (emptyMessage) {
                        // Remove empty message if we have results
                        emptyMessage.remove();
                    }
                }
            }

            // Attach search/filter event listeners
            if (searchInput) {
                searchInput.addEventListener('input', performSearch);
            }

            if (tingkatFilter) {
                tingkatFilter.addEventListener('change', performSearch);
            }

            if (jurusanFilter) {
                jurusanFilter.addEventListener('change', performSearch);
            }

            // Clear filters
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (tingkatFilter) tingkatFilter.value = '';
                    if (jurusanFilter) jurusanFilter.value = '';
                    performSearch();
                });
            }


        });
    </script>
@endsection
