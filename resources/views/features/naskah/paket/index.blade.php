@extends('layouts.admin')

@section('title', 'Paket Ujian')
@section('page-title', 'Paket Ujian')
@section('page-description', 'Wadah pelaksanaan ujian per tahun ajaran')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b bg-gray-50">
            <div>
                <h3 class="font-semibold text-gray-900">Daftar Paket Ujian</h3>
                <p class="text-xs text-gray-500">Default menampilkan tahun ajaran aktif.</p>
            </div>
            <a href="{{ route('naskah.paket-ujian.create') }}"
                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                <i class="fa-solid fa-plus mr-2"></i> Buat Ujian
            </a>
        </div>

        <div class="p-4 border-b">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="tahun_ajaran_id" class="rounded border-gray-300 text-sm">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach ($tahunAjarans as $tahunAjaran)
                        <option value="{{ $tahunAjaran->id }}" {{ (string) $tahunAjaranId === (string) $tahunAjaran->id ? 'selected' : '' }}>
                            {{ $tahunAjaran->nama }}{{ $tahunAjaran->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <button class="px-3 py-2 bg-blue-600 text-white text-sm rounded">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Paket</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tahun Ajaran</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Jadwal</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($paketUjians as $paket)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $paket->nama }}</td>
                            <td class="px-4 py-3">{{ $paket->tahunAjaran->nama ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ $paket->tanggal_mulai?->format('d/m/Y') ?? '-' }} -
                                {{ $paket->tanggal_selesai?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ ucfirst($paket->status) }}</td>
                            <td class="px-4 py-3 text-center">{{ $paket->jadwal_ujian_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('naskah.paket-ujian.show', $paket) }}" class="text-blue-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada paket ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $paketUjians->links() }}</div>
    </div>
@endsection
