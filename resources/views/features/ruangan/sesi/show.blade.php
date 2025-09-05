{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\sesi\show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Sesi - ' . $sesi->nama_sesi)
@section('page-title', 'Detail Sesi Ruangan')
@section('page-description', $sesi->nama_sesi . ' - ' . $ruangan->nama_ruangan)

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

        <!-- Breadcrumb Navigation -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('ruangan.index') }}" class="text-gray-700 hover:text-blue-600">
                            <i class="fa-solid fa-door-open mr-2"></i>Ruangan
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-gray-400"></i>
                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">
                                {{ $ruangan->nama_ruangan }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-gray-400"></i>
                            <span class="ml-1 text-gray-500 md:ml-2">{{ $sesi->nama_sesi }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Sesi Information Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $sesi->nama_sesi }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $sesi->kode_sesi }}</p>
                </div>
                <div class="flex space-x-2">
                    <span
                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $sesi->status_label['class'] }}">
                        {{ $sesi->status_label['text'] }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Jadwal</h3>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fa-solid fa-calendar mr-3 text-gray-400"></i>
                                {{ $sesi->tanggal->format('l, d F Y') }}
                            </div>
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fa-solid fa-clock mr-3 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                <span class="ml-2 text-gray-500">({{ $sesi->durasi }})</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ruangan</h3>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fa-solid fa-door-open mr-3 text-gray-400"></i>
                                {{ $ruangan->nama_ruangan }} ({{ $ruangan->kode_ruangan }})
                            </div>
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fa-solid fa-map-marker-alt mr-3 text-gray-400"></i>
                                {{ $ruangan->lokasi ?: 'Lokasi tidak ditentukan' }}
                            </div>
                            <div class="flex items-center text-sm text-gray-900">
                                <i class="fa-solid fa-users mr-3 text-gray-400"></i>
                                Kapasitas: {{ $ruangan->kapasitas }} orang
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pengawas</h3>
                        <div class="mt-2">
                            @if ($sesi->pengawas)
                                <div class="flex items-center text-sm text-gray-900">
                                    <i class="fa-solid fa-user-tie mr-3 text-gray-400"></i>
                                    {{ $sesi->pengawas->nama }}
                                </div>
                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                    <i class="fa-solid fa-id-card mr-3 text-gray-400"></i>
                                    {{ $sesi->pengawas->nip }}
                                </div>
                            @else
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fa-solid fa-user-slash mr-3 text-gray-400"></i>
                                    Belum ditentukan
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Jadwal Ujian</h3>
                        <div class="mt-2">
                            @if ($sesi->jadwalUjians->count() > 0)
                                <div class="space-y-2">
                                    @foreach ($sesi->jadwalUjians as $jadwal)
                                        <div class="flex items-start">
                                            <i class="fa-solid fa-calendar-check mr-3 text-blue-400 mt-1"></i>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $jadwal->judul }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $jadwal->mapel->nama_mapel ?? 'N/A' }} •
                                                    {{ $jadwal->jenis_ujian }}
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $jadwal->status == 'aktif' ? 'green' : ($jadwal->status == 'draft' ? 'gray' : 'blue') }}-100 text-{{ $jadwal->status == 'aktif' ? 'green' : ($jadwal->status == 'draft' ? 'gray' : 'blue') }}-800 mt-1">
                                                    {{ ucfirst($jadwal->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fa-solid fa-calendar-xmark mr-3 text-gray-400"></i>
                                    Belum ada jadwal ujian
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Peserta</h3>
                        <div class="mt-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-900">
                                    <i class="fa-solid fa-users mr-3 text-gray-400"></i>
                                    {{ $sesi->siswa_count }} / {{ $ruangan->kapasitas }} siswa
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ round(($sesi->siswa_count / $ruangan->kapasitas) * 100, 1) }}% terisi
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ ($sesi->siswa_count / $ruangan->kapasitas) * 100 }}%"></div>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                Hadir: {{ $sesi->siswa_hadir_count }} siswa
                            </div>
                        </div>
                    </div>

                    @if ($sesi->token_ujian)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Token Ujian</h3>
                            <div class="mt-2">
                                <div class="flex items-center justify-between bg-gray-100 p-3 rounded-md">
                                    <span class="font-mono text-lg font-bold text-gray-900">{{ $sesi->token_ujian }}</span>
                                    <button onclick="copyToken('{{ $sesi->token_ujian }}')"
                                        class="text-blue-600 hover:text-blue-800" title="Salin Token">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </div>
                                @if ($sesi->token_expired_at)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Berlaku sampai: {{ $sesi->token_expired_at->format('d M Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('ruangan.sesi.edit', [$ruangan->id, $sesi->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                    <i class="fa-solid fa-edit mr-2"></i> Edit Sesi
                </a>

                <a href="{{ route('ruangan.sesi.siswa.index', [$ruangan->id, $sesi->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fa-solid fa-users mr-2"></i> Kelola Siswa
                </a>

                <a href="{{ route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    <i class="fa-solid fa-calendar-plus mr-2"></i> Kelola Jadwal Ujian
                </a>

                @if (!$sesi->token_ujian)
                    <button onclick="generateToken()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fa-solid fa-key mr-2"></i> Generate Token
                    </button>
                @endif

                <button onclick="deleteSesi({{ $sesi->id }})"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fa-solid fa-trash mr-2"></i> Hapus Sesi
                </button>
            </div>
        </div>

        <!-- Students List -->
        @if ($sesi->sesiRuanganSiswa->count() > 0)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Siswa Peserta</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    Kehadiran
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sesi->sesiRuanganSiswa as $index => $sesiSiswa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $sesiSiswa->siswa->nama }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sesiSiswa->siswa->nis }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sesiSiswa->siswa->kelas->nama_kelas ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($sesiSiswa->status == 'hadir')
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fa-solid fa-check mr-1"></i>Hadir
                                            </span>
                                        @elseif($sesiSiswa->status == 'logout')
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fa-solid fa-sign-out-alt mr-1"></i>Logout
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fa-solid fa-times mr-1"></i>Tidak Hadir
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Hidden delete form -->
        <form id="delete-sesi-form" action="{{ route('ruangan.sesi.destroy', [$ruangan->id, $sesi->id]) }}"
            method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function deleteSesi(sesiId) {
            if (confirm('Apakah Anda yakin ingin menghapus sesi ini? Sesi yang memiliki siswa tidak dapat dihapus.')) {
                document.getElementById('delete-sesi-form').submit();
            }
        }

        function generateToken() {
            // You can implement AJAX call to generate token
            if (confirm('Generate token ujian untuk sesi ini?')) {
                // Implement token generation logic
                alert('Fitur generate token akan diimplementasikan');
            }
        }

        function copyToken(token) {
            navigator.clipboard.writeText(token).then(function() {
                alert('Token berhasil disalin: ' + token);
            });
        }
    </script>
@endsection
