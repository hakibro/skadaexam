@extends('layouts.admin')

@section('title', $paketUjian->nama)
@section('page-title', $paketUjian->nama)
@section('page-description', 'Paket Ujian - ' . ($paketUjian->tahunAjaran->nama ?? '-'))

@section('content')
    <div class="space-y-4">
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex flex-wrap justify-between gap-3">
                <div>
                    <div class="text-sm text-gray-500">Tahun Ajaran</div>
                    <div class="font-semibold">{{ $paketUjian->tahunAjaran->nama ?? '-' }}</div>
                    <div class="text-sm text-gray-600">
                        {{ $paketUjian->tanggal_mulai?->format('d/m/Y') ?? '-' }} -
                        {{ $paketUjian->tanggal_selesai?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    @if (!$paketUjian->isReadOnly())
                        <a href="{{ route('naskah.jadwal.create', ['paket_ujian_id' => $paketUjian->id]) }}"
                            class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            <i class="fa-solid fa-calendar-plus mr-1"></i> Tambah Jadwal
                        </a>
                        <a href="{{ route('naskah.paket-ujian.edit', $paketUjian) }}"
                            class="px-3 py-2 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600">
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-900">Jadwal dalam Paket</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left">Mapel</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($paketUjian->jadwalUjian as $jadwal)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $jadwal->kode_ujian }}</td>
                            <td class="px-4 py-3">{{ $jadwal->judul }}</td>
                            <td class="px-4 py-3">{{ $jadwal->mapel->nama_mapel ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $jadwal->tanggal?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('naskah.jadwal.show', $jadwal) }}" class="text-blue-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
