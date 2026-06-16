@extends('layouts.admin')

@section('title', 'Cetak Sesi Ruangan')
@section('page-title', 'Cetak Sesi Ruangan')
@section('page-description', 'Cetak daftar siswa per sesi ruangan dalam format A4')

@section('content')
    <div class="space-y-5">

        {{-- Filter --}}
        <form method="GET" action="{{ route('ruangan.cetak-sesi.index') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4" data-auto-submit>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" @selected((string) $tahunAjaranId === (string) $tahun->id)>
                                {{ $tahun->nama }}{{ $tahun->is_active ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Paket Ujian</label>
                    <select name="paket_ujian_id"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Paket</option>
                        @foreach ($paketUjians as $paket)
                            <option value="{{ $paket->id }}" @selected((string) $selectedPaketId === (string) $paket->id)>
                                {{ $paket->nama }}{{ $paket->status === 'aktif' ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                        <i class="fa-solid fa-filter mr-1"></i> Terapkan
                    </button>
                    <a href="{{ route('ruangan.cetak-sesi.index') }}"
                        class="px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Daftar Ruangan + Pilih --}}
        <form method="GET" action="{{ route('ruangan.cetak-sesi.print') }}" target="_blank" id="form-cetak">
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaranId }}">
            <input type="hidden" name="paket_ujian_id" value="{{ $selectedPaketId }}">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Pilih Ruangan yang Akan Dicetak</h3>
                        <p class="text-xs text-gray-500">Kosongkan pilihan untuk mencetak semua ruangan</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btn-select-all"
                            class="text-xs text-blue-600 hover:underline font-medium">Pilih Semua</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" id="btn-clear-all"
                            class="text-xs text-gray-500 hover:underline">Bersihkan</button>
                        <button type="submit"
                            formaction="{{ route('ruangan.cetak-sesi.siswa-di-ruangan') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                            <i class="fa-solid fa-table-cells-large"></i>
                            Siswa di Ruangan
                        </button>
                        <button type="submit"
                            formaction="{{ route('ruangan.cetak-sesi.print') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                            <i class="fa-solid fa-print"></i>
                            Cetak Sesi
                        </button>
                    </div>
                </div>

                @if ($ruangans->isEmpty())
                    <div class="p-10 text-center text-gray-400">
                        <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">Tidak ada ruangan untuk filter yang dipilih</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-10 px-4 py-2">
                                        <input type="checkbox" id="check-all" class="rounded border-gray-300 text-blue-600">
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ruangan
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sesi Sumber
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($ruangans as $ruangan)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="ruangan_ids[]" value="{{ $ruangan->id }}"
                                                class="ruangan-check rounded border-gray-300 text-blue-600"
                                                @checked(in_array($ruangan->id, $selectedRuanganIds))>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ $ruangan->nama_ruangan }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $ruangan->kode_ruangan }}
                                                @if ($ruangan->lokasi)
                                                    · {{ $ruangan->lokasi }}
                                                @endif
                                                · Kapasitas {{ $ruangan->kapasitas }} siswa
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            @if ($ruangan->sesi_count > 0)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                                    {{ $ruangan->sesi_count }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <script>
        const checkAll = document.getElementById('check-all');
        const checks = () => document.querySelectorAll('.ruangan-check');

        checkAll?.addEventListener('change', () => checks().forEach(c => c.checked = checkAll.checked));
        checks().forEach(c => c.addEventListener('change', () => {
            checkAll.checked = [...checks()].every(c => c.checked);
        }));

        document.getElementById('btn-select-all')?.addEventListener('click', () => {
            checks().forEach(c => c.checked = true);
            if (checkAll) checkAll.checked = true;
        });
        document.getElementById('btn-clear-all')?.addEventListener('click', () => {
            checks().forEach(c => c.checked = false);
            if (checkAll) checkAll.checked = false;
        });
    </script>
@endsection
