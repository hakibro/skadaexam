@extends('layouts.admin')

@section('title', 'Cetak Kartu Ujian')
@section('page-title', 'Cetak Kartu Ujian')
@section('page-description', 'Cetak massal kartu ujian siswa ukuran ISO ID-1')

@section('content')
    <div class="space-y-6">
        <form method="GET" action="{{ route('ruangan.kartu-ujian.index') }}" class="bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                <div>
                    <label class="block text-sm font-medium text-gray-700">Paket Ujian</label>
                    <select name="paket_ujian_id" class="mt-1 w-full rounded-md border-gray-300">
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
                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 rounded-md bg-blue-600 text-white">Filter</button>
                    <a href="{{ route('ruangan.kartu-ujian.index') }}" class="px-4 py-2 rounded-md border text-gray-700">Reset</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b flex flex-wrap gap-2 justify-between items-center">
                <div class="font-semibold text-gray-900">{{ $students->total() }} siswa ditemukan</div>
                <div class="flex gap-2">
                    <a target="_blank"
                        href="{{ route('ruangan.kartu-ujian.print', request()->all() + ['mode' => 'front', 'paket_ujian_id' => $selectedPaketId]) }}"
                        class="px-3 py-2 rounded-md bg-green-600 text-white text-sm">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Depan
                    </a>
                    <a target="_blank"
                        href="{{ route('ruangan.kartu-ujian.print', request()->all() + ['mode' => 'back', 'paket_ujian_id' => $selectedPaketId]) }}"
                        class="px-3 py-2 rounded-md bg-gray-800 text-white text-sm">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Belakang
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID Yayasan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($students as $siswa)
                            @php($record = $siswa->tahunAjaranRecords->first())
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $siswa->nama }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $siswa->idyayasan }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $record?->kelas?->nama_kelas ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $students->links() }}</div>
        </div>
    </div>
@endsection
