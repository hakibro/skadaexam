@extends('layouts.admin')

@section('title', 'Rekap Kehadiran Siswa')
@section('page-title', 'Rekap Kehadiran Siswa')
@section('page-description', 'Rekap kehadiran siswa berdasarkan sesi ujian')

@section('content')
    <div class="space-y-6">

        <!-- FILTER -->
        <form method="GET" action="" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

                <!-- Status Kehadiran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status Kehadiran
                    </label>
                    <select name="status"
                        class="w-full rounded-lg border-gray-300 text-sm
                       focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Semua</option>
                        <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>
                            Hadir
                        </option>
                        <option value="tidak_hadir" {{ request('status') == 'tidak_hadir' ? 'selected' : '' }}>
                            Tidak Hadir
                        </option>
                    </select>
                </div>

                <!-- Range Tanggal -->
                @php
                    $invalidDate =
                        request('start_date') && request('end_date') && request('end_date') < request('start_date');
                @endphp

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Rentang Tanggal
                    </label>

                    <div class="flex gap-2">
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full rounded-lg text-sm
                   border {{ $invalidDate ? 'border-red-500' : 'border-gray-300' }}
                   focus:ring">

                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full rounded-lg text-sm
                   border {{ $invalidDate ? 'border-red-500' : 'border-gray-300' }}
                   focus:ring">
                    </div>

                    @if ($invalidDate)
                        <p class="mt-1 text-xs text-red-600">
                            Tanggal selesai tidak boleh lebih awal dari tanggal mulai
                        </p>
                    @endif
                </div>


                <!-- Ruangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ruangan
                    </label>
                    <select name="ruangan_id"
                        class="w-full rounded-lg border-gray-300 text-sm
                       focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Semua</option>
                        @foreach ($ruangan as $r)
                            <option value="{{ $r->id }}" {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan ?? $r->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tingkat -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tingkat
                    </label>
                    <select name="tingkat"
                        class="w-full rounded-lg border-gray-300 text-sm
                       focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Semua</option>
                        <option value="X" {{ request('tingkat') == 'X' ? 'selected' : '' }}>X</option>
                        <option value="XI" {{ request('tingkat') == 'XI' ? 'selected' : '' }}>XI</option>
                        <option value="XII" {{ request('tingkat') == 'XII' ? 'selected' : '' }}>XII</option>
                    </select>
                </div>

                <!-- Jurusan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jurusan
                    </label>
                    <select name="jurusan"
                        class="w-full rounded-lg border-gray-300 text-sm
                       focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Semua</option>
                        @foreach ($jurusan as $j)
                            <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>
                                {{ $j }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- ACTION -->
            <div class="flex flex-wrap items-center justify-between mt-6 gap-3">
                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg
                       bg-blue-600 text-white text-sm font-medium
                       hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">
                        Terapkan Filter
                    </button>

                    <a href="{{ url()->current() }}"
                        class="inline-flex items-center px-4 py-2 rounded-lg
                       border border-gray-300 text-sm font-medium text-gray-700
                       hover:bg-gray-100">
                        Reset
                    </a>
                </div>
            </div>
        </form>


        <!-- TABEL DATA -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Kelas</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal Ujian</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Ruangan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Sesi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($data as $row)
                        @php
                            $jadwal = optional($row->sesiRuangan->jadwalUjian->first());
                        @endphp

                        <tr class="hover:bg-gray-50 transition">
                            <!-- No -->
                            <td class="px-4 py-3 text-gray-500">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Nama -->
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $row->siswa->nama ?? '-' }}
                            </td>

                            <!-- Kelas -->
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->siswa->kelas->formatted_name ?? '-' }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                @php
                                    $status = $row->status_kehadiran;
                                    $statusClass = match ($status) {
                                        'hadir' => 'bg-green-100 text-green-700 ring-green-600/20',
                                        'izin', 'sakit' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
                                        default => 'bg-red-100 text-red-700 ring-red-600/20',
                                    };
                                @endphp

                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ring-1 {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>

                            <!-- Tanggal -->
                            <td class="px-4 py-3 text-gray-600">
                                {{ $jadwal?->tanggal?->format('d-m-Y') ?? '-' }}
                            </td>

                            <!-- Ruangan -->
                            <td class="px-4 py-3 text-gray-600">
                                {{ $row->sesiRuangan->ruangan->nama_ruangan ?? '-' }}
                            </td>

                            <!-- Sesi -->
                            <td class="px-4 py-3 text-gray-600">
                                {{ $row->sesiRuangan->nama_sesi ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="mt-4">
            {{ $data->links() }}
        </div>

    </div>
@endsection
