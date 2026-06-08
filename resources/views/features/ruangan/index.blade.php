@extends('layouts.admin')

@section('title', 'Daftar Ruangan')
@section('page-title', 'Manajemen Ruangan')
@section('page-description', 'Kelola ruangan ujian berdasarkan tahun ajaran dan paket ujian')

@section('content')
    @php
        $hasFilters = request('search')
            || request('status')
            || request('tahun_ajaran_id')
            || request('paket_ujian_id')
            || request('sort', 'nama_asc') !== 'nama_asc';
    @endphp

    <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Daftar Ruangan</h3>
                    <p class="text-sm text-gray-500">{{ $ruangans->total() }} ruangan ditemukan</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('ruangan.create') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-blue-600 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                        Tambah
                    </a>
                    <a href="{{ route('ruangan.import.comprehensive') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-file-import"></i>
                        Import
                    </a>
                    <a href="{{ route('ruangan.export') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-file-export"></i>
                        Export
                    </a>
                </div>
            </div>

            <form action="{{ route('ruangan.index') }}" method="GET" class="px-4 py-3 border-b border-gray-200" data-auto-submit>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2">
                    <label class="block">
                        <span class="sr-only">Tahun Ajaran</span>
                        <select name="tahun_ajaran_id" id="tahun_ajaran_id"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($tahunAjarans as $tahun)
                                <option value="{{ $tahun->id }}" @selected((string) $tahunAjaranId === (string) $tahun->id)>
                                    {{ $tahun->nama }}{{ $tahun->is_active ? ' - Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="sr-only">Paket Ujian</span>
                        <select name="paket_ujian_id" id="paket_ujian_id"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="__all" @selected($showAllPaket)>Semua Paket</option>
                            <option value="__null" @selected($paketUjianId === '__null')>Belum Terikat Paket</option>
                            @foreach ($paketUjians as $paket)
                                <option value="{{ $paket->id }}" @selected((string) $paketUjianId === (string) $paket->id)>
                                    {{ $paket->nama }}{{ $paket->status === 'aktif' ? ' - Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block lg:col-span-2">
                        <span class="sr-only">Cari Ruangan</span>
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Cari nama, kode, atau lokasi"
                                class="w-full rounded-md border-gray-300 pl-9 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </label>

                    <label class="block">
                        <span class="sr-only">Status</span>
                        <select name="status" id="status"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                            <option value="tidak_aktif" @selected(request('status') === 'tidak_aktif')>Tidak Aktif</option>
                            <option value="perbaikan" @selected(request('status') === 'perbaikan')>Perbaikan</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="sr-only">Urutkan</span>
                        <select name="sort" id="sort"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="nama_asc" @selected(request('sort', 'nama_asc') === 'nama_asc')>Nama A-Z</option>
                            <option value="nama_desc" @selected(request('sort') === 'nama_desc')>Nama Z-A</option>
                            <option value="kapasitas_asc" @selected(request('sort') === 'kapasitas_asc')>Kapasitas kecil</option>
                            <option value="kapasitas_desc" @selected(request('sort') === 'kapasitas_desc')>Kapasitas besar</option>
                            <option value="created_at_desc" @selected(request('sort') === 'created_at_desc')>Terbaru</option>
                            <option value="created_at_asc" @selected(request('sort') === 'created_at_asc')>Terlama</option>
                        </select>
                    </label>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-gray-100 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        <i class="fa-solid fa-filter"></i>
                        Terapkan
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('ruangan.index') }}"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100">
                            <i class="fa-solid fa-times"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200 border-b border-gray-200">
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-gray-500">Total</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $statistics['total'] }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-gray-500">Aktif</p>
                    <p class="mt-1 text-2xl font-semibold text-green-700">{{ $statistics['aktif'] }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-gray-500">Tidak Aktif</p>
                    <p class="mt-1 text-2xl font-semibold text-red-700">{{ $statistics['nonaktif'] }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium uppercase text-gray-500">Perbaikan</p>
                    <p class="mt-1 text-2xl font-semibold text-yellow-700">{{ $statistics['perbaikan'] }}</p>
                </div>
            </div>

            @if (session('error_with_force'))
                <div class="m-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span><strong>Gagal.</strong> {{ session('error_with_force')['message'] }}</span>
                        <form action="{{ route('ruangan.bulk-action') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="hapus_paksa">
                            <input type="hidden" name="ids" value="{{ session('error_with_force')['ids'] }}">
                            <button type="submit"
                                onclick="return confirm('Yakin ingin HAPUS PAKSA ruangan ini beserta semua data terkait?')"
                                class="rounded bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">
                                Hapus Paksa
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($ruangans->count() > 0)
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="select-all" class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        Pilih semua
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="selected-count" class="hidden text-sm text-gray-600">0 ruangan dipilih</span>
                        <div id="bulk-buttons" class="hidden flex-wrap gap-2">
                            <button type="button" onclick="confirmBulkAction('aktifkan')"
                                class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                                Aktifkan
                            </button>
                            <button type="button" onclick="confirmBulkAction('nonaktifkan')"
                                class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">
                                Nonaktifkan
                            </button>
                            <button type="button" onclick="confirmBulkAction('perbaikan')"
                                class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-yellow-600">
                                Perbaikan
                            </button>
                            <button type="button" onclick="confirmBulkAction('hapus')"
                                class="rounded-md bg-gray-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-900">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <form id="bulk-action-form" action="{{ route('ruangan.bulk-action') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="action" id="bulk-action">
                    <input type="hidden" name="ids" id="bulk-ids">
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left"><span class="sr-only">Pilih</span></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ruangan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Paket & Kapasitas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sesi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($ruangans as $ruangan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 align-top">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $ruangan->id }}"
                                            class="room-checkbox h-4 w-4 rounded border-gray-300 text-blue-600">
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900">{{ $ruangan->nama_ruangan }}</div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span class="rounded bg-blue-50 px-2 py-0.5 font-medium text-blue-700">
                                                {{ $ruangan->kode_ruangan }}
                                            </span>
                                            <span>{{ $ruangan->lokasi ?: 'Lokasi belum diisi' }}</span>
                                        </div>
                                        @if ($ruangan->keterangan)
                                            <div class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($ruangan->keterangan, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-sm text-gray-900">{{ $ruangan->paketUjian->nama ?? 'Belum terikat paket' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $ruangan->kapasitas }} siswa</div>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-sm text-gray-900">{{ $ruangan->sesi_ruangan_count ?? 0 }} sesi</div>
                                        <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                            class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800">
                                            Atur sesi
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <span class="{{ $ruangan->status_badge_class }} inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                            {{ $ruangan->status_label['text'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                                class="text-gray-500 hover:text-blue-700" title="Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                                                class="text-gray-500 hover:text-yellow-700" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                                class="text-gray-500 hover:text-green-700" title="Sesi">
                                                <i class="fa-solid fa-calendar-days"></i>
                                            </a>
                                            <form action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-500 hover:text-red-700" title="Hapus"
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

                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $ruangans->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <i class="fa-solid fa-door-open text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Tidak ada ruangan</h3>
                    <p class="mt-1 text-sm text-gray-500">Ubah filter atau tambahkan ruangan baru untuk paket ujian ini.</p>
                    <a href="{{ route('ruangan.create') }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Ruangan
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAllCheckbox = document.getElementById('select-all');
            const roomCheckboxes = document.querySelectorAll('.room-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const bulkButtons = document.getElementById('bulk-buttons');

            if (!selectAllCheckbox || !roomCheckboxes.length) return;

            function updateSelected() {
                const selected = Array.from(roomCheckboxes).filter(cb => cb.checked);
                selectedCount.textContent = `${selected.length} ruangan dipilih`;
                selectedCount.classList.toggle('hidden', selected.length === 0);
                bulkButtons.classList.toggle('hidden', selected.length === 0);
                bulkButtons.classList.toggle('flex', selected.length > 0);
                selectAllCheckbox.checked = selected.length === roomCheckboxes.length;
                selectAllCheckbox.indeterminate = selected.length > 0 && selected.length < roomCheckboxes.length;
            }

            selectAllCheckbox.addEventListener('change', (event) => {
                roomCheckboxes.forEach(cb => cb.checked = event.target.checked);
                updateSelected();
            });

            roomCheckboxes.forEach(cb => cb.addEventListener('change', updateSelected));

            window.confirmBulkAction = function(action) {
                const selected = Array.from(roomCheckboxes).filter(cb => cb.checked);
                if (!selected.length) return;

                let message = `Yakin ingin ${action} ${selected.length} ruangan?`;
                if (action === 'hapus') {
                    message += ' Semua sesi ruangan dan data terkait akan ikut terhapus.';
                }

                if (confirm(message)) {
                    document.getElementById('bulk-action').value = action;
                    document.getElementById('bulk-ids').value = selected.map(cb => cb.value).join(',');
                    document.getElementById('bulk-action-form').submit();
                }
            };
        });
    </script>
@endsection
