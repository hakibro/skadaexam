<?php
// Create a completely new index.blade.php file with clean, fixed code
$content = <<<'EOD'
{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\data\guru\index.blade.php --}}
<!-- File updated on: 2025-09-14 11:20:00 -->

@extends('layouts.admin')

@section('title', 'Manage Guru')
@section('page-title', 'Manage Guru')
@section('page-description', 'List and manage all guru data')
@section('styles')
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

        <!-- Search & Filter Bar - ENHANCED -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Search Input -->
                <div class="md:col-span-5">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-search mr-1 text-gray-400"></i>
                        Search
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400" id="search-icon"></i>
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 hidden" id="loading-icon"></i>
                        </div>
                        <input type="text" id="search-input"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search by name, NIP, email..." autocomplete="off" value="{{ request('q') }}">
                    </div>
                </div>

                <!-- Role Filter - IMPROVED -->
                <div class="md:col-span-3">
                    <label for="role-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-user-tag mr-1 text-gray-400"></i>
                        Role Filter
                    </label>
                    <select id="role-filter"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru (Default)</option>
                        <option value="data" {{ request('role') == 'data' ? 'selected' : '' }}>Data Management</option>
                        <option value="naskah" {{ request('role') == 'naskah' ? 'selected' : '' }}>Naskah Management
                        </option>
                        <option value="pengawas" {{ request('role') == 'pengawas' ? 'selected' : '' }}>Pengawas</option>
                        <option value="koordinator" {{ request('role') == 'koordinator' ? 'selected' : '' }}>Koordinator
                        </option>
                        <option value="ruangan" {{ request('role') == 'ruangan' ? 'selected' : '' }}>Ruangan Management
                        </option>
                    </select>
                </div>

                <!-- Per Page Select -->
                <div class="md:col-span-2">
                    <label for="per-page-select" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-list-ol mr-1 text-gray-400"></i>
                        Show
                    </label>
                    <select id="per-page-select"
                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 entries</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 entries</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 entries</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 entries</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="md:col-span-2">
                    <button id="clear-filters"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-times mr-1"></i>
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div id="bulk-actions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-tasks text-blue-600"></i>
                    <span class="text-blue-800 font-medium">
                        <span id="selected-count">0</span> guru selected
                    </span>
                </div>
                <div class="flex space-x-3">
                    <!-- Bulk Role Update -->
                    <div class="flex items-center space-x-2">
                        <label for="bulk-role-select" class="text-sm text-blue-700 font-medium">Update Role:</label>
                        <select id="bulk-role-select" class="border border-blue-300 rounded-md px-2 py-1 text-sm">
                            <option value="">Select Role</option>
                            <option value="guru">Guru (Default)</option>
                            <option value="data">Data Management</option>
                            <option value="naskah">Naskah Management</option>
                            <option value="pengawas">Pengawas</option>
                            <option value="koordinator">Koordinator</option>
                            <option value="ruangan">Ruangan Management</option>
                        </select>
                        <button id="bulk-update-role"
                            class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 text-sm">
                            <i class="fa-solid fa-user-tag mr-1"></i>Update
                        </button>
                    </div>

                    <!-- Bulk Delete -->
                    <button id="bulk-delete" class="bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-sm">
                        <i class="fa-solid fa-trash mr-1"></i>Delete Selected
                    </button>

                    <!-- Clear Selection -->
                    <button id="clear-selection"
                        class="bg-gray-500 text-white px-3 py-1 rounded-md hover:bg-gray-600 text-sm">
                        <i class="fa-solid fa-times mr-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" onclick="this.parentElement.parentElement.remove();">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" onclick="this.parentElement.parentElement.remove();">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        <!-- Search Results Info -->
        <div id="search-info"
            class="bg-blue-50 border border-blue-200 rounded-lg p-3 {{ request('q') ? '' : 'hidden' }}">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                <span class="text-blue-800 text-sm">
                    Showing search results for: <strong id="search-term">{{ request('q') }}</strong>
                </span>
            </div>
        </div>

        <!-- Guru Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Guru List</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Showing <span id="showing-count">{{ $gurus->count() }}</span> of
                    <span id="total-count">{{ $gurus->total() }}</span> results
                </p>
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
@endsection
@section('scripts')
    <!-- Enhanced JavaScript for Search, Filter and Bulk Actions -->
    <script>
        // Debug helper function
        function debugLog(message, data = null) {
            const enableDebug = true;
            if (enableDebug) {
                console.log(`[DEBUG] ${message}`, data || '');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('DOM Content Loaded - Fixed Version');
            
            // Get filter elements
            const searchInput = document.getElementById('search-input');
            const roleFilter = document.getElementById('role-filter');
            const perPageSelect = document.getElementById('per-page-select');
            const clearFiltersBtn = document.getElementById('clear-filters');
            
            debugLog('Filter elements:', {
                searchInput: searchInput ? 'found' : 'missing',
                roleFilter: roleFilter ? 'found' : 'missing',
                perPageSelect: perPageSelect ? 'found' : 'missing',
                clearFiltersBtn: clearFiltersBtn ? 'found' : 'missing'
            });

            // Get UI elements
            const searchIcon = document.getElementById('search-icon');
            const loadingIcon = document.getElementById('loading-icon');
            const searchInfo = document.getElementById('search-info');
            const searchTerm = document.getElementById('search-term');
            const tableContainer = document.getElementById('guru-table-container');
            const paginationContainer = document.getElementById('guru-pagination-container');
            const totalCount = document.getElementById('total-count');
            const showingCount = document.getElementById('showing-count');
            const guruCount = document.getElementById('guru-count');

            // Bulk action elements
            const bulkActionsBar = document.getElementById('bulk-actions');
            const selectedCountSpan = document.getElementById('selected-count');
            const bulkDeleteBtn = document.getElementById('bulk-delete');
            const bulkUpdateRoleBtn = document.getElementById('bulk-update-role');
            const bulkRoleSelect = document.getElementById('bulk-role-select');
            const clearSelectionBtn = document.getElementById('clear-selection');

            let searchTimeout;
            let isLoading = false;

            // Function to show loading state
            function showLoading() {
                if (isLoading) return;
                isLoading = true;

                searchIcon?.classList.add('hidden');
                loadingIcon?.classList.remove('hidden');

                // Add loading indicator to table
                if (tableContainer) {
                    tableContainer.classList.add('opacity-50');
                    tableContainer.style.pointerEvents = 'none';
                }
            }

            // Function to hide loading state
            function hideLoading() {
                isLoading = false;

                searchIcon?.classList.remove('hidden');
                loadingIcon?.classList.add('hidden');

                // Remove loading indicator from table
                if (tableContainer) {
                    tableContainer.classList.remove('opacity-50');
                    tableContainer.style.pointerEvents = 'auto';
                }

                // Re-initialize bulk action handlers after table refresh
                initializeBulkActions();
            }

            // Initialize bulk actions
            function initializeBulkActions() {
                // Select all checkbox
                const selectAllCheckbox = document.getElementById('select-all');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.guru-checkbox');
                        checkboxes.forEach(cb => {
                            cb.checked = this.checked;
                        });
                        updateBulkActionsVisibility();
                    });
                }

                // Individual checkboxes
                const guruCheckboxes = document.querySelectorAll('.guru-checkbox');
                guruCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateBulkActionsVisibility);
                });
            }

            // Update bulk actions visibility based on selected items
            function updateBulkActionsVisibility() {
                const selectedCheckboxes = document.querySelectorAll('.guru-checkbox:checked');
                const selectedCount = selectedCheckboxes.length;

                if (selectedCount > 0) {
                    bulkActionsBar?.classList.remove('hidden');
                    if (selectedCountSpan) selectedCountSpan.textContent = selectedCount;
                } else {
                    bulkActionsBar?.classList.add('hidden');
                }

                // Update "select all" checkbox state
                const selectAllCheckbox = document.getElementById('select-all');
                const allCheckboxes = document.querySelectorAll('.guru-checkbox');
                if (selectAllCheckbox && allCheckboxes.length > 0) {
                    if (selectedCount === 0) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = false;
                    } else if (selectedCount === allCheckboxes.length) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = true;
                    } else {
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }

            // Get selected guru IDs
            function getSelectedGuruIds() {
                const selectedCheckboxes = document.querySelectorAll('.guru-checkbox:checked');
                return Array.from(selectedCheckboxes).map(cb => cb.value);
            }

            // Bulk delete functionality
            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const selectedIds = getSelectedGuruIds();
                    if (selectedIds.length === 0) {
                        alert('Please select gurus to delete');
                        return;
                    }

                    if (!confirm(
                            `Are you sure you want to delete ${selectedIds.length} selected guru(s)?`)) {
                        return;
                    }

                    // Show loading
                    this.disabled = true;
                    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Deleting...';

                    fetch('{{ route('data.guru.bulk-delete') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                ids: selectedIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                // Refresh the table
                                performSearch();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Bulk delete error:', error);
                            alert('Error deleting gurus: ' + error.message);
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = '<i class="fa-solid fa-trash mr-1"></i>Delete Selected';
                        });
                });
            }

            // Bulk role update functionality
            if (bulkUpdateRoleBtn) {
                bulkUpdateRoleBtn.addEventListener('click', function() {
                    const selectedIds = getSelectedGuruIds();
                    const selectedRole = bulkRoleSelect.value;

                    if (selectedIds.length === 0) {
                        alert('Please select gurus to update');
                        return;
                    }

                    if (!selectedRole) {
                        alert('Please select a role to assign');
                        return;
                    }

                    if (!confirm(
                            `Are you sure you want to update role for ${selectedIds.length} selected guru(s) to "${selectedRole}"?`
                        )) {
                        return;
                    }

                    // Show loading
                    this.disabled = true;
                    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Updating...';

                    fetch('{{ route('data.guru.bulk-update-role') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                ids: selectedIds,
                                role: selectedRole
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                // Reset bulk role select
                                bulkRoleSelect.value = '';
                                // Refresh the table
                                performSearch();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Bulk role update error:', error);
                            alert('Error updating roles: ' + error.message);
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = '<i class="fa-solid fa-user-tag mr-1"></i>Update';
                        });
                });
            }

            // Clear selection functionality
            if (clearSelectionBtn) {
                clearSelectionBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('.guru-checkbox, #select-all');
                    checkboxes.forEach(cb => cb.checked = false);
                    updateBulkActionsVisibility();
                });
            }

            // Main search function - FIXED with enhanced debugging
            function performSearch() {
                // Clear previous timeout to avoid multiple requests
                clearTimeout(searchTimeout);
                
                debugLog('Search triggered');

                // Set a new timeout for debouncing
                searchTimeout = setTimeout(() => {
                    // Get filter values
                    const query = searchInput?.value?.trim() || '';
                    const role = roleFilter?.value || '';
                    const perPage = perPageSelect?.value || '10';
                    
                    debugLog('Search parameters', { query, role, perPage });

                    // Show loading state
                    showLoading();

                    // Build search URL with all params
                    const searchUrl = new URL('{{ route('data.guru.search') }}', window.location.origin);
                    if (query) searchUrl.searchParams.set('q', query);
                    if (role) searchUrl.searchParams.set('role', role);
                    if (perPage) searchUrl.searchParams.set('per_page', perPage);
                    searchUrl.searchParams.set('_t', Date.now()); // Cache buster

                    // Debug log
                    debugLog('Searching with URL:', searchUrl.toString());

                    // Perform AJAX request
                    fetch(searchUrl.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        })
                        .then(response => {
                            debugLog('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            debugLog('Response data:', data);
                            if (data.success) {
                                // Update table content
                                if (tableContainer && data.html) {
                                    tableContainer.innerHTML = data.html;
                                }

                                // Update pagination
                                if (paginationContainer && data.pagination) {
                                    paginationContainer.innerHTML = data.pagination;
                                }

                                // Update counts
                                if (totalCount) totalCount.textContent = data.total || 0;
                                if (showingCount) showingCount.textContent = data.current_page || 0;
                                if (guruCount) guruCount.textContent = data.total || 0;

                                // Update search info display
                                if (query) {
                                    if (searchTerm) searchTerm.textContent = query;
                                    if (searchInfo) searchInfo.classList.remove('hidden');
                                } else {
                                    if (searchInfo) searchInfo.classList.add('hidden');
                                }

                                // Highlight search terms
                                if (query) {
                                    highlightSearchTerms(query);
                                }

                                // Update URL without reloading page
                                const url = new URL(window.location.href);
                                if (query) url.searchParams.set('q', query);
                                else url.searchParams.delete('q');
                                if (role) url.searchParams.set('role', role);
                                else url.searchParams.delete('role');
                                if (perPage) url.searchParams.set('per_page', perPage);
                                else url.searchParams.delete('per_page');

                                window.history.pushState({}, '', url.toString());
                            } else {
                                console.error('Search failed:', data.message || 'Unknown error');
                                alert('Search failed. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            alert('Error performing search: ' + error.message);
                        })
                        .finally(() => {
                            hideLoading();
                        });
                }, 400); // Debounce for 400ms
            }

            // Highlight search terms in results
            function highlightSearchTerms(query) {
                if (!query) return;

                const terms = query.split(' ').filter(term => term.length > 1);
                if (terms.length === 0) return;

                const elements = document.querySelectorAll('.search-highlight');
                elements.forEach(element => {
                    // Store original text if not already stored
                    const originalText = element.getAttribute('data-original') || element.textContent;
                    element.setAttribute('data-original', originalText);

                    // Apply highlighting for each term
                    let highlightedText = originalText;
                    terms.forEach(term => {
                        const regex = new RegExp('(' + escapeRegExp(term) + ')', 'gi');
                        highlightedText = highlightedText.replace(regex,
                            '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
                    });

                    element.innerHTML = highlightedText;
                });
            }

            // Escape special regex characters
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Attach event listeners
            if (searchInput) {
                debugLog('Attaching event listener to search input');
                searchInput.addEventListener('input', performSearch);
            }

            if (roleFilter) {
                debugLog('Attaching event listener to role filter');
                roleFilter.addEventListener('change', performSearch);
            }

            if (perPageSelect) {
                debugLog('Attaching event listener to per page select');
                perPageSelect.addEventListener('change', performSearch);
            }

            if (clearFiltersBtn) {
                debugLog('Attaching event listener to clear filters button');
                clearFiltersBtn.addEventListener('click', function clearFilters() {
                    debugLog('Clearing filters');
                    // Reset all filters
                    if (searchInput) searchInput.value = '';
                    if (roleFilter) roleFilter.value = '';
                    if (perPageSelect) perPageSelect.value = '10';

                    // Perform search with reset filters
                    performSearch();
                });
            }

            // Handle pagination clicks - with improved handling
            function handlePaginationClick(e) {
                // Find closest anchor that is a pagination link
                const paginationLink = e.target.closest('.pagination a');

                if (paginationLink) {
                    e.preventDefault();

                    // Extract page number from URL
                    const url = new URL(paginationLink.href);
                    const page = url.searchParams.get('page');

                    if (page) {
                        // Show loading state
                        showLoading();

                        // Build URL with current filters + page
                        const query = searchInput?.value?.trim() || '';
                        const role = roleFilter?.value || '';
                        const perPage = perPageSelect?.value || '10';

                        const paginationUrl = new URL('{{ route('data.guru.search') }}', window.location
                            .origin);
                        paginationUrl.searchParams.set('page', page);
                        if (query) paginationUrl.searchParams.set('q', query);
                        if (role) paginationUrl.searchParams.set('role', role);
                        if (perPage) paginationUrl.searchParams.set('per_page', perPage);
                        paginationUrl.searchParams.set('_t', Date.now());

                        // Debug log
                        debugLog('Paginating with URL:', paginationUrl.toString());

                        // Make AJAX request
                        fetch(paginationUrl.toString(), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Update content
                                    if (tableContainer) tableContainer.innerHTML = data.html;
                                    if (paginationContainer) paginationContainer.innerHTML = data
                                        .pagination;

                                    // Update counts
                                    if (showingCount) showingCount.textContent = data.current_page || 0;

                                    // Apply highlighting
                                    if (query) highlightSearchTerms(query);

                                    // Update URL
                                    const pageUrl = new URL(window.location.href);
                                    pageUrl.searchParams.set('page', page);
                                    window.history.pushState({}, '', pageUrl.toString());
                                }
                            })
                            .catch(error => {
                                console.error('Pagination error:', error);
                                alert('Error loading page: ' + error.message);
                            })
                            .finally(() => {
                                hideLoading();
                            });
                    }
                }
            }
            
            // Add pagination click handler
            document.addEventListener('click', handlePaginationClick);

            // Initialize with URL parameters if any
            const urlParams = new URLSearchParams(window.location.search);
            const urlQuery = urlParams.get('q');
            const urlRole = urlParams.get('role');
            const urlPerPage = urlParams.get('per_page');

            // Set form fields from URL
            if (urlQuery && searchInput) searchInput.value = urlQuery;
            if (urlRole && roleFilter) roleFilter.value = urlRole;
            if (urlPerPage && perPageSelect) perPageSelect.value = urlPerPage;

            // Apply initial highlighting if search term exists
            if (urlQuery) highlightSearchTerms(urlQuery);

            // Initialize bulk actions on page load
            initializeBulkActions();
        });
    </script>

@endsection
EOD;

// Write the content to the file
file_put_contents('c:/laragon/www/skadaexam/resources/views/features/data/guru/index.blade.php', $content);
echo "Created a completely new and clean index.blade.php file with fixed code.\n";
