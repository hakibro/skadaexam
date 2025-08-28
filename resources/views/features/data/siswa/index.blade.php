<!-- filepath: resources\views\features\data\siswa\index.blade.php -->

@extends('layouts.admin')

@section('title', 'Manage Siswa')
@section('page-title', 'Manage Siswa')

@section('content')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">All Siswa</h3>
                <p class="text-sm text-gray-500">Total: <span id="siswa-count">{{ $siswas->total() }}</span> siswa</p>
                <p class="text-sm text-gray-500">
                    <span id="sync-stats-text">Loading sync stats...</span>
                </p>
            </div>
            <div class="flex space-x-3">
                <!-- Import Siswa Button -->
                <a href="{{ route('data.siswa.import') }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 flex items-center space-x-2">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import Excel</span>
                </a>

                <!-- Sync All Payments Button -->
                <button id="sync-all-payments-btn"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center space-x-2">
                    <i class="fa-solid fa-sync-alt"></i>
                    <span>Sync All Payments</span>
                </button>

                <!-- Add New Siswa Button -->
                <a href="{{ route('data.siswa.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add New Siswa</span>
                </a>
            </div>
        </div>

        <!-- Sync Progress Modal -->
        <div id="sync-progress-modal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Syncing Payment Data</h3>
                        <button id="close-sync-modal" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Progress</span>
                            <span id="sync-progress-text">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="sync-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="sync-processed">0</div>
                            <div class="text-sm text-gray-500">Processed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="sync-success">0</div>
                            <div class="text-sm text-gray-500">Success</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600" id="sync-failed">0</div>
                            <div class="text-sm text-gray-500">Failed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600" id="sync-updated">0</div>
                            <div class="text-sm text-gray-500">Updated</div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div id="sync-status" class="text-sm text-gray-600 mb-4">
                        Preparing to sync...
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button id="stop-sync-btn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 hidden">
                            Stop Sync
                        </button>
                        <button id="close-sync-btn" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                <!-- Search Input -->
                <div class="md:col-span-5">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400" id="search-icon"></i>
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 hidden" id="loading-icon"></i>
                        </div>
                        <input type="text" id="search-input"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search by name, ID yayasan, email, or class..." value="{{ request('q') }}">
                    </div>
                </div>

                <!-- Payment Status Filter -->
                <div class="md:col-span-2">
                    <label for="payment-filter" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select id="payment-filter"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="Lunas" {{ request('payment_status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Belum Lunas" {{ request('payment_status') == 'Belum Lunas' ? 'selected' : '' }}>Belum
                            Lunas</option>
                        <option value="Cicilan" {{ request('payment_status') == 'Cicilan' ? 'selected' : '' }}>Cicilan
                        </option>
                    </select>
                </div>

                <!-- Rekomendasi Filter -->
                <div class="md:col-span-2">
                    <label for="rekomendasi-filter"
                        class="block text-sm font-medium text-gray-700 mb-2">Recommendation</label>
                    <select id="rekomendasi-filter"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="ya" {{ request('rekomendasi') == 'ya' ? 'selected' : '' }}>Ya</option>
                        <option value="tidak" {{ request('rekomendasi') == 'tidak' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="md:col-span-2">
                    <label for="per-page" class="block text-sm font-medium text-gray-700 mb-2">Show</label>
                    <select id="per-page"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="md:col-span-1">
                    <button id="clear-filters"
                        class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center justify-center">
                        <i class="fa-solid fa-times mr-1"></i>
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Search Info -->
        <div id="search-info" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                <span class="text-blue-800 text-sm" id="search-results-text"></span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Students List</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Showing <span id="showing-count">{{ $siswas->count() }}</span> of <span
                        id="total-count">{{ $siswas->total() }}</span> results
                </p>
            </div>

            <div id="table-container">
                @include('features.data.siswa.partials.table', ['siswas' => $siswas])
            </div>

            <div id="pagination-container">
                @include('features.data.siswa.partials.pagination', ['siswas' => $siswas])
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const searchInput = document.getElementById('search-input');
            const paymentFilter = document.getElementById('payment-filter');
            const rekomendasiFilter = document.getElementById('rekomendasi-filter');
            const perPageSelect = document.getElementById('per-page');
            const clearBtn = document.getElementById('clear-filters');
            const searchIcon = document.getElementById('search-icon');
            const loadingIcon = document.getElementById('loading-icon');
            const searchInfo = document.getElementById('search-info');
            const searchResultsText = document.getElementById('search-results-text');
            const tableContainer = document.getElementById('table-container');
            const paginationContainer = document.getElementById('pagination-container');
            const siswaCount = document.getElementById('siswa-count');
            const showingCount = document.getElementById('showing-count');

            // Sync elements
            const syncAllBtn = document.getElementById('sync-all-payments-btn');
            const syncModal = document.getElementById('sync-progress-modal');
            const closeSyncModal = document.getElementById('close-sync-modal');
            const closeSyncBtn = document.getElementById('close-sync-btn');
            const stopSyncBtn = document.getElementById('stop-sync-btn');
            const syncStatsText = document.getElementById('sync-stats-text');

            let searchTimeout;
            let syncInProgress = false;
            let stopSync = false;

            // Load sync stats on page load
            loadSyncStats();

            // Perform search
            function performSearch() {
                const query = searchInput.value.trim();
                const paymentStatus = paymentFilter.value;
                const rekomendasi = rekomendasiFilter.value;
                const perPage = perPageSelect.value;

                // Show loading
                searchIcon.classList.add('hidden');
                loadingIcon.classList.remove('hidden');

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchUrl = new URL('{{ route('data.siswa.search') }}');

                    if (query) searchUrl.searchParams.set('q', query);
                    if (paymentStatus) searchUrl.searchParams.set('payment_status', paymentStatus);
                    if (rekomendasi) searchUrl.searchParams.set('rekomendasi', rekomendasi);
                    if (perPage) searchUrl.searchParams.set('per_page', perPage);

                    fetch(searchUrl.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update table and pagination
                                tableContainer.innerHTML = data.html;
                                paginationContainer.innerHTML = data.pagination;

                                // Update counts
                                siswaCount.textContent = data.count;
                                showingCount.textContent = data.showing;

                                // Update search info
                                updateSearchInfo(query, paymentStatus, rekomendasi);

                                // Re-attach individual sync buttons
                                attachIndividualSyncButtons();
                            } else {
                                alert('Search failed: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            alert('Error performing search. Please try again.');
                        })
                        .finally(() => {
                            searchIcon.classList.remove('hidden');
                            loadingIcon.classList.add('hidden');
                        });
                }, 300);
            }

            // Update search info
            function updateSearchInfo(query, paymentStatus, rekomendasi) {
                const filters = [];
                if (query) filters.push(`"${query}"`);
                if (paymentStatus) filters.push(`Payment: ${paymentStatus}`);
                if (rekomendasi) filters.push(`Recommendation: ${rekomendasi}`);

                if (filters.length > 0) {
                    searchResultsText.textContent = `Filtered by: ${filters.join(', ')}`;
                    searchInfo.classList.remove('hidden');
                } else {
                    searchInfo.classList.add('hidden');
                }
            }

            // Clear all filters
            function clearAllFilters() {
                searchInput.value = '';
                paymentFilter.value = '';
                rekomendasiFilter.value = '';
                perPageSelect.value = '25';
                performSearch();
            }

            // Load sync statistics
            function loadSyncStats() {
                fetch('{{ route('data.siswa.sync-stats') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const stats = data.stats;
                            syncStatsText.innerHTML = `
                            Synced: <span class="font-medium text-green-600">${stats.synced_count}</span> |
                            Failed: <span class="font-medium text-red-600">${stats.failed_count}</span> |
                            Pending: <span class="font-medium text-yellow-600">${stats.pending_count}</span> |
                            Last sync: <span class="font-medium">${stats.last_sync_time}</span>
                        `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading sync stats:', error);
                        syncStatsText.textContent = 'Error loading sync stats';
                    });
            }

            // Sync all payments
            function syncAllPayments() {
                if (syncInProgress) {
                    alert('Sync already in progress!');
                    return;
                }

                syncInProgress = true;
                stopSync = false;
                syncModal.classList.remove('hidden');
                syncAllBtn.disabled = true;
                stopSyncBtn.classList.remove('hidden');

                // Reset progress
                document.getElementById('sync-progress-bar').style.width = '0%';
                document.getElementById('sync-progress-text').textContent = '0%';
                document.getElementById('sync-processed').textContent = '0';
                document.getElementById('sync-success').textContent = '0';
                document.getElementById('sync-failed').textContent = '0';
                document.getElementById('sync-updated').textContent = '0';
                document.getElementById('sync-status').textContent = 'Starting sync...';

                // Start syncing
                syncBatch(0, {
                    total_processed: 0,
                    success_count: 0,
                    failed_count: 0,
                    updated_count: 0
                });
            }

            // Sync batch of students
            function syncBatch(offset, cumulativeStats) {
                if (stopSync) {
                    document.getElementById('sync-status').textContent = 'Sync stopped by user.';
                    finalizSync();
                    return;
                }

                document.getElementById('sync-status').textContent = `Processing batch starting at ${offset}...`;

                fetch('{{ route('data.siswa.sync-all-payments') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            offset: offset,
                            limit: 10 // Process 10 students per batch
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cumulative stats
                            cumulativeStats.total_processed += data.stats.total_processed;
                            cumulativeStats.success_count += data.stats.success_count;
                            cumulativeStats.failed_count += data.stats.failed_count;
                            cumulativeStats.updated_count += data.stats.updated_count;

                            // Update UI
                            document.getElementById('sync-processed').textContent = cumulativeStats
                                .total_processed;
                            document.getElementById('sync-success').textContent = cumulativeStats.success_count;
                            document.getElementById('sync-failed').textContent = cumulativeStats.failed_count;
                            document.getElementById('sync-updated').textContent = cumulativeStats.updated_count;

                            if (data.progress_percentage) {
                                document.getElementById('sync-progress-bar').style.width =
                                    `${data.progress_percentage}%`;
                                document.getElementById('sync-progress-text').textContent =
                                    `${data.progress_percentage}%`;
                            }

                            // Continue with next batch if there are more
                            if (data.has_more && !stopSync) {
                                setTimeout(() => {
                                    syncBatch(data.next_offset, cumulativeStats);
                                }, 1000); // 1 second delay between batches
                            } else {
                                // All done
                                document.getElementById('sync-status').textContent =
                                    'Sync completed successfully!';
                                document.getElementById('sync-progress-bar').style.width = '100%';
                                document.getElementById('sync-progress-text').textContent = '100%';
                                finalizSync();
                            }
                        } else {
                            document.getElementById('sync-status').textContent = `Error: ${data.message}`;
                            finalizSync();
                        }
                    })
                    .catch(error => {
                        console.error('Sync error:', error);
                        document.getElementById('sync-status').textContent = `Error: ${error.message}`;
                        finalizSync();
                    });
            }

            // Finalize sync process
            function finalizSync() {
                syncInProgress = false;
                syncAllBtn.disabled = false;
                stopSyncBtn.classList.add('hidden');
                loadSyncStats(); // Refresh stats
                performSearch(); // Refresh table
            }

            // Individual sync payment
            function syncIndividualPayment(siswaId, button) {
                if (button.disabled) return;

                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';

                fetch(`/data/siswa/${siswaId}/sync-payment`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const message = `Payment synced: ${data.data.old_status} → ${data.data.new_status}`;
                            showNotification(message, 'success');

                            // Refresh the table to show updated status
                            performSearch();
                        } else {
                            showNotification(`Sync failed: ${data.message}`, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Individual sync error:', error);
                        showNotification('Sync error occurred', 'error');
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    });
            }

            // Attach individual sync buttons
            function attachIndividualSyncButtons() {
                document.querySelectorAll('.sync-payment-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const siswaId = this.dataset.siswaId;
                        syncIndividualPayment(siswaId, this);
                    });
                });
            }

            // Show notification
            function showNotification(message, type = 'info') {
                // Simple notification - you can replace with a toast library
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-6 py-3 rounded shadow-lg z-50 ${
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'error' ? 'bg-red-500 text-white' :
                    'bg-blue-500 text-white'
                }`;
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }

            // Event listeners
            searchInput.addEventListener('input', performSearch);
            paymentFilter.addEventListener('change', performSearch);
            rekomendasiFilter.addEventListener('change', performSearch);
            perPageSelect.addEventListener('change', performSearch);
            clearBtn.addEventListener('click', clearAllFilters);

            // Sync event listeners
            syncAllBtn.addEventListener('click', syncAllPayments);
            closeSyncModal.addEventListener('click', () => syncModal.classList.add('hidden'));
            closeSyncBtn.addEventListener('click', () => syncModal.classList.add('hidden'));
            stopSyncBtn.addEventListener('click', () => stopSync = true);

            // Initialize
            updateSearchInfo(searchInput.value, paymentFilter.value, rekomendasiFilter.value);
            attachIndividualSyncButtons();
        });
    </script>
@endsection
