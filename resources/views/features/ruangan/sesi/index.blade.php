@extends('layouts.admin')

@section('title', 'Daftar Sesi - ' . $ruangan->nama_ruangan)
@section('page-title', 'Daftar Sesi')
@section('page-description', 'Ruangan: ' . $ruangan->nama_ruangan)

@section('content')
    <div class="py-4">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Room Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-lg">
                        <i class="fa-solid fa-door-open text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $ruangan->nama_ruangan }}</h3>
                        <p class="text-sm text-gray-600">{{ $ruangan->kode_ruangan }}</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-sm text-gray-500">
                                <i class="fa-solid fa-users mr-1"></i>
                                Kapasitas: {{ $ruangan->kapasitas }} orang
                            </span>
                            <span class="text-sm text-gray-500">
                                <i class="fa-solid fa-map-marker-alt mr-1"></i>
                                {{ $ruangan->lokasi ?: 'Lokasi tidak ditentukan' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ruangan.index') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Sesi
                    </a>
                </div>
            </div>
        </div>

        <!-- Sessions List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Daftar Sesi Ruangan</h3>
                <p class="text-sm text-gray-500">Total: {{ $sesiList->count() }} sesi</p>
            </div>

            @if ($sesiList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Sesi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Sesi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengawas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sesiList as $sesi)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $sesi->kode_sesi }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $sesi->nama_sesi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $sesi->tanggal->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $sesi->durasi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $sesi->pengawas->nama ?? 'Belum ditentukan' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $sesi->sesi_ruangan_siswa_count }} / {{ $ruangan->kapasitas }}
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ ($sesi->sesi_ruangan_siswa_count / $ruangan->kapasitas) * 100 }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesi->status_label['class'] }}">
                                            {{ $sesi->status_label['text'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                                                class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.siswa.index', [$ruangan->id, $sesi->id]) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Kelola Siswa">
                                                <i class="fa-solid fa-users"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.edit', [$ruangan->id, $sesi->id]) }}"
                                                class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <button onclick="deleteSesi({{ $sesi->id }}, false)"
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                            <!-- Force Delete Button -->
                                            @if ($sesi->sesi_ruangan_siswa_count > 0)
                                                <button onclick="deleteSesi({{ $sesi->id }}, true)"
                                                    class="text-red-600 hover:text-red-900" title="Hapus Paksa">
                                                    <i class="fa-solid fa-radiation"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <form id="delete-sesi-{{ $sesi->id }}"
                                            action="{{ route('ruangan.sesi.destroy', [$ruangan->id, $sesi->id]) }}"
                                            method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <form id="force-delete-sesi-{{ $sesi->id }}"
                                            action="{{ route('ruangan.sesi.force-delete', [$ruangan->id, $sesi->id]) }}"
                                            method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 0V3m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada sesi</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada sesi ruangan yang dibuat untuk ruangan ini.</p>
                    <div class="mt-6">
                        <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fa-solid fa-plus mr-2"></i> Buat Sesi Pertama
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function deleteSesi(sesiId, isForceDelete = false) {
            let confirmMessage =
            'Apakah Anda yakin ingin menghapus sesi ini? Sesi yang memiliki siswa tidak dapat dihapus.';
            let formId = 'delete-sesi-' + sesiId;

            if (isForceDelete) {
                confirmMessage =
                    'PERHATIAN: Anda akan menghapus sesi ini beserta semua data siswa yang terkait! Tindakan ini TIDAK DAPAT dibatalkan dan dapat menyebabkan kerusakan data. Lanjutkan?';
                formId = 'force-delete-sesi-' + sesiId;
            }

            if (confirm(confirmMessage)) {
                document.getElementById(formId).submit();
            }
        }
    </script>
@endsection
