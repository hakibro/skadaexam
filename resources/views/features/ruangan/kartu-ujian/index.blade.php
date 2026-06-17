@extends('layouts.admin')

@section('title', 'Cetak Kartu Ujian')
@section('page-title', 'Cetak Kartu Ujian')
@section('page-description', 'Cetak massal kartu ujian siswa ukuran ISO ID-1')

@section('content')
    <div class="space-y-6">
        <form method="GET" action="{{ route('ruangan.kartu-ujian.index') }}" class="bg-white rounded-lg shadow p-4" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Paket Ujian</label>
                    <select name="paket_ujian_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Pilih paket ujian</option>
                        @foreach ($paketUjians as $paket)
                            <option value="{{ $paket->id }}" {{ (string) $selectedPaketId === (string) $paket->id ? 'selected' : '' }}>
                                {{ $paket->nama }} - {{ ucfirst($paket->status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cari Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300"
                        placeholder="Nama / ID Yayasan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tingkat</label>
                    <select name="tingkat" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Semua tingkat</option>
                        @foreach ($tingkatList as $tingkat)
                            <option value="{{ $tingkat }}" {{ request('tingkat') == $tingkat ? 'selected' : '' }}>
                                {{ $tingkat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="kelas_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 rounded-md bg-blue-600 text-white" type="submit">Filter</button>
                    <a href="{{ route('ruangan.kartu-ujian.index') }}" class="px-4 py-2 rounded-md border text-gray-700" id="reset-link">Reset</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b flex flex-wrap gap-2 justify-between items-center">
                <div>
                    <div class="font-semibold text-gray-900">
                        <span id="total-students">{{ $students->total() }}</span> siswa ditemukan
                    </div>
                    <div class="text-xs text-gray-500">
                        <span id="selected-count">0</span> siswa terpilih
                        <button type="button" id="clear-selection" class="ml-2 text-blue-600 hover:underline">Bersihkan pilihan</button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-print-mode="front"
                        class="px-3 py-2 rounded-md bg-green-600 text-white text-sm">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Depan
                    </button>
                    <button type="button" data-print-mode="back"
                        class="px-3 py-2 rounded-md bg-gray-800 text-white text-sm">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Belakang
                    </button>
                </div>
            </div>
            <div id="student-table-wrap">
                @include('features.ruangan.kartu-ujian.partials.student-table', ['students' => $students])
            </div>
        </div>
    </div>

    <script>
        const filterForm = document.getElementById('filter-form');
        const tableWrap = document.getElementById('student-table-wrap');
        const totalStudents = document.getElementById('total-students');
        const selectedCount = document.getElementById('selected-count');
        const selectedIds = new Set();
        const indexUrl = @json(route('ruangan.kartu-ujian.index'));
        const printUrl = @json(route('ruangan.kartu-ujian.print'));
        let searchTimer = null;

        function formParams() {
            const params = new URLSearchParams(new FormData(filterForm));
            if (!params.get('paket_ujian_id')) {
                params.set('paket_ujian_id', @json($selectedPaketId));
            }
            return params;
        }

        function syncSelectionState() {
            tableWrap.querySelectorAll('.student-check').forEach((check) => {
                check.checked = selectedIds.has(check.value);
            });

            const currentChecks = [...tableWrap.querySelectorAll('.student-check')];
            const checkCurrentPage = tableWrap.querySelector('#check-current-page');
            if (checkCurrentPage) {
                checkCurrentPage.checked = currentChecks.length > 0 && currentChecks.every((check) => check.checked);
                checkCurrentPage.indeterminate = currentChecks.some((check) => check.checked) && !checkCurrentPage.checked;
            }

            selectedCount.textContent = selectedIds.size;
        }

        async function loadStudents(url = null) {
            const targetUrl = url ? new URL(url, window.location.origin) : new URL(indexUrl);
            if (!url) {
                const params = formParams();
                params.forEach((value, key) => targetUrl.searchParams.set(key, value));
            }

            tableWrap.style.opacity = '0.55';
            const response = await fetch(targetUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            tableWrap.innerHTML = data.table;
            totalStudents.textContent = data.total;
            tableWrap.style.opacity = '1';
            syncSelectionState();
            window.history.replaceState({}, '', targetUrl.toString());
        }

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            loadStudents();
        });

        filterForm.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                if (select.name === 'paket_ujian_id') {
                    selectedIds.clear();
                    syncSelectionState();
                }
                loadStudents();
            });
        });

        filterForm.querySelector('input[name="search"]')?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadStudents(), 350);
        });

        tableWrap.addEventListener('change', (event) => {
            if (event.target.classList.contains('student-check')) {
                event.target.checked ? selectedIds.add(event.target.value) : selectedIds.delete(event.target.value);
                syncSelectionState();
            }

            if (event.target.id === 'check-current-page') {
                tableWrap.querySelectorAll('.student-check').forEach((check) => {
                    check.checked = event.target.checked;
                    event.target.checked ? selectedIds.add(check.value) : selectedIds.delete(check.value);
                });
                syncSelectionState();
            }
        });

        tableWrap.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link) return;

            event.preventDefault();
            loadStudents(link.href);
        });

        document.getElementById('clear-selection').addEventListener('click', () => {
            selectedIds.clear();
            syncSelectionState();
        });

        document.querySelectorAll('[data-print-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                const params = formParams();
                params.set('mode', button.dataset.printMode);
                if (selectedIds.size > 0) {
                    params.set('siswa_ids', [...selectedIds].join(','));
                    params.delete('search');
                    params.delete('tingkat');
                    params.delete('kelas_id');
                }
                window.open(`${printUrl}?${params.toString()}`, '_blank');
            });
        });

        syncSelectionState();
    </script>
@endsection
