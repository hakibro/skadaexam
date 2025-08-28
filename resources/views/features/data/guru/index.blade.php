<!-- filepath: c:\laragon\www\skadaexam\resources\views\teachers\index.blade.php -->
@extends('layouts.admin')

@section('title', 'Manage Guru')
@section('page-title', 'Manage Guru')
@section('page-description', 'List and manage all guru data')

@section('content')
    <div class="space-y-6">

        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">All Guru</h3>
                <p class="text-sm text-gray-500">Total: <span id="guru-count">{{ $gurus->total() }}</span> guru</p>
            </div>
            <div class="flex space-x-3">
                <!-- Import Button -->
                <a href="{{ route('data.guru.import') }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center space-x-2">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel</span>
                </a>
                <!-- Add New Button -->
                <a href="{{ route('data.guru.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add New Guru</span>
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400" id="search-icon"></i>
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 hidden" id="loading-icon"></i>
                        </div>
                        <input type="text" id="search-input"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search by name, NIP, email, or role..." autocomplete="off">
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <label for="per-page-select" class="text-sm text-gray-600">Show:</label>
                    <select id="per-page-select"
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <button id="clear-search" class="text-gray-400 hover:text-gray-600 px-2 py-1 rounded hidden">
                    <i class="fa-solid fa-times"></i>
                    Clear
                </button>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search Results Info -->
        <div id="search-info" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                <span class="text-blue-800 text-sm">
                    Showing search results for: <strong id="search-term"></strong>
                </span>
            </div>
        </div>

        <!-- Guru Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Guru List</h3>
            </div>

            <div id="guru-table-container">
                @include('features.data.guru.partials.table', ['gurus' => $gurus])
            </div>

            <!-- Pagination -->
            <div id="guru-pagination-container">
                @include('features.data.guru.partials.pagination', ['gurus' => $gurus])
            </div>
        </div>

    </div>

    <!-- Live Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const perPageSelect = document.getElementById('per-page-select');
            const clearSearchBtn = document.getElementById('clear-search');
            const searchIcon = document.getElementById('search-icon');
            const loadingIcon = document.getElementById('loading-icon');
            const searchInfo = document.getElementById('search-info');
            const searchTerm = document.getElementById('search-term');
            const tableContainer = document.getElementById('guru-table-container');
            const paginationContainer = document.getElementById('guru-pagination-container');

            let searchTimeout;

            // Live search function
            function performSearch() {
                const query = searchInput.value.trim();
                const perPage = perPageSelect.value;

                console.log('Searching for:', query); // Debug log

                // Show loading
                searchIcon.classList.add('hidden');
                loadingIcon.classList.remove('hidden');

                // Clear previous timeout
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    // Build URL - FIXED ROUTE
                    const searchUrl = new URL('{{ route('data.guru.search') }}');
                    if (query) searchUrl.searchParams.set('q', query);
                    if (perPage) searchUrl.searchParams.set('per_page', perPage);
                    searchUrl.searchParams.set('_t', Date.now()); // Cache buster

                    console.log('Fetching URL:', searchUrl.toString()); // Debug log

                    fetch(searchUrl.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Search response:', data); // Debug log

                            if (data.success) {
                                // Update table and pagination
                                tableContainer.innerHTML = data.html;
                                paginationContainer.innerHTML = data.pagination;

                                // Update search info
                                if (query) {
                                    searchTerm.textContent = query;
                                    searchInfo.classList.remove('hidden');
                                    clearSearchBtn.classList.remove('hidden');
                                } else {
                                    searchInfo.classList.add('hidden');
                                    clearSearchBtn.classList.add('hidden');
                                }

                                // Highlight search terms
                                highlightSearchTerms(query);
                            } else {
                                console.error('Search failed:', data.message);
                                alert('Search failed: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            alert('Error performing search. Please try again.');
                        })
                        .finally(() => {
                            // Hide loading
                            searchIcon.classList.remove('hidden');
                            loadingIcon.classList.add('hidden');
                        });
                }, 300); // Debounce 300ms
            }

            // Highlight search terms
            function highlightSearchTerms(query) {
                if (!query) return;

                const elements = document.querySelectorAll('.search-highlight');
                elements.forEach(element => {
                    const originalText = element.getAttribute('data-original') || element.textContent;
                    element.setAttribute('data-original', originalText);

                    const highlightedText = originalText.replace(
                        new RegExp(`(${escapeRegExp(query)})`, 'gi'),
                        '<mark class="bg-yellow-200 px-1 rounded">$1</mark>'
                    );
                    element.innerHTML = highlightedText;
                });
            }

            // Escape special regex characters
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Search input event
            searchInput.addEventListener('input', performSearch);

            // Per page change event
            perPageSelect.addEventListener('change', performSearch);

            // Clear search event
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                console.log('Search cleared'); // Debug log
                performSearch();
            });

            // Handle pagination clicks (updated)
            document.addEventListener('click', function(e) {
                if (e.target.matches('.pagination a')) {
                    e.preventDefault();
                    const url = new URL(e.target.href);
                    const page = url.searchParams.get('page');
                    const query = searchInput.value.trim();
                    const perPage = perPageSelect.value;

                    console.log('Pagination clicked, page:', page, 'query:', query); // Debug log

                    // Show loading
                    searchIcon.classList.add('hidden');
                    loadingIcon.classList.remove('hidden');

                    const paginationUrl = new URL('{{ route('data.guru.search') }}');
                    if (query) paginationUrl.searchParams.set('q', query);
                    if (perPage) paginationUrl.searchParams.set('per_page', perPage);
                    if (page) paginationUrl.searchParams.set('page', page);
                    paginationUrl.searchParams.set('_t', Date.now());

                    fetch(paginationUrl.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                tableContainer.innerHTML = data.html;
                                paginationContainer.innerHTML = data.pagination;
                                highlightSearchTerms(query);
                            }
                        })
                        .catch(error => {
                            console.error('Pagination error:', error);
                        })
                        .finally(() => {
                            // Hide loading
                            searchIcon.classList.remove('hidden');
                            loadingIcon.classList.add('hidden');
                        });
                }
            });
        });
    </script>

    <style>
        /* Custom styles for search highlights */
        mark {
            background-color: #fef08a;
            padding: 2px 4px;
            border-radius: 2px;
        }

        .search-highlight {
            transition: all 0.2s;
        }
    </style>

@endsection
