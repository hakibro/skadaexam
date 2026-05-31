@extends('layouts.admin')

@section('title', 'Tahun Ajaran')
@section('page-title', 'Tahun Ajaran')
@section('page-description', 'Kelola tahun ajaran aktif dan arsip')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
            <h3 class="font-semibold text-gray-900">Daftar Tahun Ajaran</h3>
            <a href="{{ route('admin.tahun-ajaran.create') }}"
                class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">
                <i class="fa-solid fa-plus mr-2"></i> Tambah
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tahun Ajaran</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Paket</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Jadwal</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($tahunAjarans as $tahunAjaran)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $tahunAjaran->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $tahunAjaran->kode }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $tahunAjaran->tanggal_mulai?->format('d/m/Y') ?? '-' }} -
                                {{ $tahunAjaran->tanggal_selesai?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs {{ $tahunAjaran->is_active ? 'bg-green-100 text-green-700' : ($tahunAjaran->status === 'arsip' ? 'bg-gray-100 text-gray-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ $tahunAjaran->is_active ? 'Aktif' : ucfirst($tahunAjaran->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">{{ $tahunAjaran->paket_ujian_count }}</td>
                            <td class="px-4 py-3 text-center">{{ $tahunAjaran->jadwal_ujian_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if (!$tahunAjaran->isReadOnly())
                                        <a href="{{ route('admin.tahun-ajaran.edit', $tahunAjaran) }}"
                                            class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        @if (!$tahunAjaran->is_active)
                                            <form method="POST" action="{{ route('admin.tahun-ajaran.activate', $tahunAjaran) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 bg-green-50 text-green-700 rounded hover:bg-green-100">
                                                    Jadikan Aktif
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500">Read-only</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada tahun ajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
