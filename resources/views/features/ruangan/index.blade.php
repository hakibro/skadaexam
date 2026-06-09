@extends('layouts.admin')

@section('title', 'Daftar Ruangan')
@section('page-title', 'Manajemen Ruangan')
@section('page-description', 'Kelola ruangan ujian berdasarkan tahun ajaran dan paket ujian')

@section('content')
    @php
        $hasFilters =
            request('search') ||
            request('status') ||
            request('tahun_ajaran_id') ||
            request('paket_ujian_id') ||
            request('sort', 'nama_asc') !== 'nama_asc';
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

            <form action="{{ route('ruangan.index') }}" method="GET" class="px-4 py-3 border-b border-gray-200"
                data-auto-submit>
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
                            <i
                                class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
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

            <div
                class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200 border-b border-gray-200">
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
                <div
                    class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
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

                <div class="divide-y divide-gray-200 bg-white">
                    @foreach ($ruangans as $ruangan)
                        @php
                            $sourceSesi = $ruangan->sesiRuangan;
                            $accordionId = 'room-sessions-' . $ruangan->id;
                        @endphp
                        <article class="room-accordion">
                            <div
                                class="grid grid-cols-[auto,1fr,auto] gap-3 px-4 py-4 hover:bg-gray-50 lg:grid-cols-[auto,1.6fr,1fr,1fr,auto]">
                                <div class="pt-1">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $ruangan->id }}"
                                        class="room-checkbox h-4 w-4 rounded border-gray-300 text-blue-600">
                                </div>

                                <button type="button" class="room-accordion-toggle min-w-0 text-left"
                                    data-target="{{ $accordionId }}" aria-expanded="false"
                                    aria-controls="{{ $accordionId }}">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span
                                            class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                                            <i class="fa-solid fa-door-open"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span
                                                class="block truncate font-semibold text-gray-900">{{ $ruangan->nama_ruangan }}</span>
                                            <span class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                <span
                                                    class="rounded bg-blue-50 px-2 py-0.5 font-medium text-blue-700">{{ $ruangan->kode_ruangan }}</span>
                                                <span>{{ $ruangan->lokasi ?: 'Lokasi belum diisi' }}</span>
                                            </span>
                                            @if ($ruangan->keterangan)
                                                <span
                                                    class="mt-1 block text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($ruangan->keterangan, 90) }}</span>
                                            @endif
                                        </span>
                                    </div>
                                </button>

                                <div class="col-start-2 text-sm text-gray-700 lg:col-start-auto">
                                    <div class="font-medium text-gray-900">
                                        {{ $ruangan->paketUjian->nama ?? 'Belum terikat paket' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $ruangan->kapasitas }} siswa</div>
                                </div>

                                <div class="col-start-2 flex flex-wrap items-center gap-2 text-sm lg:col-start-auto">
                                    <span
                                        class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $ruangan->source_sesi_count ?? 0 }} sesi sumber
                                    </span>
                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        {{ $ruangan->sesi_ruangan_count ?? 0 }} total sesi
                                    </span>
                                    <span
                                        class="{{ $ruangan->status_badge_class }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                        {{ $ruangan->status_label['text'] }}
                                    </span>
                                </div>

                                <div class="col-span-2 flex items-center justify-end gap-2 lg:col-span-1">
                                    <button type="button"
                                        class="room-accordion-toggle inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100"
                                        data-target="{{ $accordionId }}" aria-expanded="false"
                                        aria-controls="{{ $accordionId }}" title="Buka sesi sumber">
                                        <i class="fa-solid fa-chevron-down transition-transform"></i>
                                    </button>
                                    <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-blue-50 hover:text-blue-700"
                                        title="Detail ruangan">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-yellow-50 hover:text-yellow-700"
                                        title="Edit ruangan">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-green-50 hover:text-green-700"
                                        title="Kelola semua sesi">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </a>
                                    <form action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-red-50 hover:text-red-700"
                                            title="Hapus ruangan"
                                            onclick="return confirm('Yakin ingin menghapus ruangan ini? Ruangan yang masih memiliki sesi tidak dapat dihapus normal.')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="{{ $accordionId }}" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-4">
                                <div class="grid grid-cols-1 gap-4 xl:grid-cols-[360px,1fr]">
                                    <form action="{{ route('ruangan.sesi.store', $ruangan->id) }}" method="POST"
                                        class="rounded-md border border-gray-200 bg-white p-4">
                                        @csrf
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="text-sm font-semibold text-gray-900">Tambah Sesi Sumber</h4>
                                            <span
                                                class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Sumber</span>
                                        </div>
                                        <div class="mt-3 space-y-3">
                                            <label class="block">
                                                <span class="text-xs font-medium text-gray-600">Nama Sesi</span>
                                                <input type="text" name="nama_sesi" required maxlength="191"
                                                    placeholder="Contoh: Sesi 1"
                                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-medium text-gray-600">Kode Sesi</span>
                                                <input type="text" name="kode_sesi" maxlength="20"
                                                    placeholder="Kosongkan untuk otomatis"
                                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <label class="block">
                                                    <span class="text-xs font-medium text-gray-600">Mulai</span>
                                                    <input type="time" name="waktu_mulai" required
                                                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </label>
                                                <label class="block">
                                                    <span class="text-xs font-medium text-gray-600">Selesai</span>
                                                    <input type="time" name="waktu_selesai" required
                                                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </label>
                                            </div>
                                            <button type="submit"
                                                class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                                <i class="fa-solid fa-plus"></i>
                                                Simpan Sesi Sumber
                                            </button>
                                        </div>
                                    </form>

                                    <div class="rounded-md border border-gray-200 bg-white">
                                        <div
                                            class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-4 py-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">Sesi Sumber</h4>
                                                <p class="text-xs text-gray-500">Dipakai sebagai acuan duplikasi dan enroll
                                                    siswa ke jadwal ujian.</p>
                                            </div>
                                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                                class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Kelola detail
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </a>
                                        </div>

                                        @if ($sourceSesi->count() > 0)
                                            <div class="divide-y divide-gray-100">
                                                @foreach ($sourceSesi as $sesi)
                                                    <div
                                                        class="grid grid-cols-1 gap-3 px-4 py-3 hover:bg-gray-50 lg:grid-cols-[1fr,auto]">
                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                                <div class="min-w-0">
                                                                    <div class="flex flex-wrap items-center gap-2">
                                                                        <span
                                                                            class="font-semibold text-gray-900">{{ $sesi->nama_sesi }}</span>
                                                                        <span
                                                                            class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                                                            {{ $sesi->kode_sesi }}
                                                                        </span>
                                                                        <span
                                                                            @class([
                                                                                'rounded-full px-2 py-0.5 text-xs font-semibold',
                                                                                'bg-yellow-100 text-yellow-800' => $sesi->status === 'belum_mulai',
                                                                                'bg-green-100 text-green-800' => $sesi->status === 'berlangsung',
                                                                                'bg-gray-100 text-gray-800' => $sesi->status === 'selesai',
                                                                                'bg-red-100 text-red-800' => $sesi->status === 'dibatalkan',
                                                                            ])>{{ $sesi->status }}</span>
                                                                    </div>
                                                                    @if ($sesi->jadwalUjians->count() > 0)
                                                                        <div class="mt-1 truncate text-xs text-gray-500">
                                                                            {{ $sesi->jadwalUjians->pluck('judul')->take(2)->join(', ') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div
                                                                    class="flex shrink-0 items-center gap-2 text-xs text-gray-500">
                                                                    <span
                                                                        class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1">
                                                                        <i class="fa-solid fa-users text-gray-400"></i>
                                                                        {{ $sesi->sesi_ruangan_siswa_count }}
                                                                    </span>
                                                                    <span
                                                                        class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1">
                                                                        <i
                                                                            class="fa-solid fa-calendar-check text-gray-400"></i>
                                                                        {{ $sesi->jadwal_ujians_count }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-gray-500">
                                                                <form
                                                                    action="{{ route('ruangan.sesi.update', [$ruangan->id, $sesi->id]) }}"
                                                                    method="POST"
                                                                    class="flex flex-wrap items-center gap-2 rounded-md border border-gray-200 bg-white px-2 py-1.5 shadow-sm">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="inline_update"
                                                                        value="1">
                                                                    <input type="hidden" name="nama_sesi"
                                                                        value="{{ $sesi->nama_sesi }}">
                                                                    <input type="hidden" name="kode_sesi"
                                                                        value="{{ $sesi->kode_sesi }}">
                                                                    <input type="hidden" name="status"
                                                                        value="{{ $sesi->status }}">
                                                                    <span class="font-medium text-gray-600">
                                                                        <i class="fa-regular fa-clock mr-1"></i>Waktu
                                                                    </span>
                                                                    <label class="sr-only"
                                                                        for="sesi_{{ $sesi->id }}_mulai">Waktu
                                                                        mulai</label>
                                                                    <input type="time"
                                                                        id="sesi_{{ $sesi->id }}_mulai"
                                                                        name="waktu_mulai" required
                                                                        value="{{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }}"
                                                                        class="h-8 w-24 rounded-md border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                                    <span class="text-gray-400">-</span>
                                                                    <label class="sr-only"
                                                                        for="sesi_{{ $sesi->id }}_selesai">Waktu
                                                                        selesai</label>
                                                                    <input type="time"
                                                                        id="sesi_{{ $sesi->id }}_selesai"
                                                                        name="waktu_selesai" required
                                                                        value="{{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}"
                                                                        class="h-8 w-24 rounded-md border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                                    <button type="submit"
                                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-700"
                                                                        title="Simpan waktu">
                                                                        <i class="fa-solid fa-floppy-disk"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-wrap items-center gap-1.5 lg:justify-end">
                                                            <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-blue-700"
                                                                title="Detail sesi">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('ruangan.sesi.edit', [$ruangan->id, $sesi->id]) }}"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-amber-200 text-amber-700 hover:bg-amber-50"
                                                                title="Edit sesi">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </a>
                                                            <a href="{{ route('ruangan.sesi.siswa.index', [$ruangan->id, $sesi->id]) }}"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50"
                                                                title="Kelola siswa">
                                                                <i class="fa-solid fa-users"></i>
                                                            </a>
                                                            <a href="{{ route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id]) }}"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-indigo-200 text-indigo-700 hover:bg-indigo-50"
                                                                title="Kelola jadwal">
                                                                <i class="fa-solid fa-calendar-check"></i>
                                                            </a>
                                                            <form
                                                                action="{{ route('ruangan.sesi.duplicate', [$ruangan->id, $sesi->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                                                                    title="Duplikat sesi">
                                                                    <i class="fa-regular fa-copy"></i>
                                                                </button>
                                                            </form>
                                                            <form
                                                                action="{{ route('ruangan.sesi.destroy', [$ruangan->id, $sesi->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    onclick="return confirm('Hapus sesi sumber ini? Jika masih memiliki siswa, gunakan hapus paksa dari menu ini.')"
                                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 text-red-700 hover:bg-red-50"
                                                                    title="Hapus sesi">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                            <form
                                                                action="{{ route('ruangan.sesi.force-delete', [$ruangan->id, $sesi->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    onclick="return confirm('HAPUS PAKSA sesi sumber ini beserta data siswa terkait?')"
                                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700"
                                                                    title="Hapus paksa">
                                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                                Belum ada sesi sumber untuk ruangan ini.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $ruangans->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <i class="fa-solid fa-door-open text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Tidak ada ruangan</h3>
                    <p class="mt-1 text-sm text-gray-500">Ubah filter atau tambahkan ruangan baru untuk paket ujian ini.
                    </p>
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

            document.querySelectorAll('.room-accordion-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.dataset.target;
                    const panel = document.getElementById(targetId);
                    if (!panel) return;

                    const shouldOpen = panel.classList.contains('hidden');
                    panel.classList.toggle('hidden', !shouldOpen);

                    document.querySelectorAll(`.room-accordion-toggle[data-target="${targetId}"]`)
                        .forEach((toggle) => {
                            toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                            toggle.querySelector('.fa-chevron-down')?.classList.toggle(
                                'rotate-180', shouldOpen);
                        });
                });
            });

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
