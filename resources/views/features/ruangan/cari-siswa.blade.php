@extends('layouts.admin')

@section('title', 'Atur Siswa')
@section('page-title', 'Atur Siswa')
@section('page-description', 'Cari siswa, atur sesi, dan enrollment ujian')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Notifikasi Sukses / Error --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card Utama Pencarian -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Atur Siswa</h3>
                        <button type="button" onclick="openAssignModal()"
                            class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-md">
                            <i class="fas fa-user-plus mr-2"></i> Tambah Siswa ke Sesi
                        </button>
                    </div>
                </div>
                <div class="p-6 bg-white">
                    <!-- Form Pencarian -->
                    <form action="{{ route('ruangan.cari-siswa') }}" method="GET" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" name="q" value="{{ request('q') }}"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Masukkan nama / ID Yayasan...">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-search mr-2"></i> Cari
                            </button>
                        </div>
                    </form>

                    <!-- Hasil Pencarian -->
                    @if (request()->has('q'))
                        @if ($siswas->count() > 0)
                            <div class="space-y-6">
                                @foreach ($siswas as $siswa)
                                    <!-- Card per Siswa -->
                                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                        <!-- Header Siswa -->
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" class="siswa-result-checkbox rounded border-gray-300 text-blue-600"
                                                            value="{{ $siswa->id }}">
                                                        <h4 class="text-lg font-semibold text-gray-800">{{ $siswa->nama }}</h4>
                                                    </div>
                                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 mt-1">
                                                        <span><span class="font-medium">ID Yayasan:</span>
                                                            {{ $siswa->idyayasan }}</span>
                                                        <span><span class="font-medium">Kelas:</span>
                                                            {{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-sm bg-white px-3 py-1 rounded-full border border-gray-300">
                                                    <span class="font-medium">Total Sesi:</span>
                                                    {{ $siswa->sesiRuanganSiswa->count() }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Body: Daftar Sesi -->
                                        <div class="p-4">
                                            @if ($siswa->sesiRuanganSiswa->count() > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach ($siswa->sesiRuanganSiswa as $sesiSiswa)
                                                        @php
                                                            $sesi = $sesiSiswa->sesiRuangan;
                                                            $ruangan = $sesi->ruangan ?? null;
                                                        @endphp
                                                        <div
                                                            class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:shadow transition flex flex-col h-full">
                                                            <!-- Info Ruangan & Status -->
                                                            <div class="flex justify-between items-start mb-2">
                                                                <a class="text-gray-800 hover:text-blue-600"
                                                                    href="{{ route('ruangan.sesi.show', ['ruangan' => $ruangan->id ?? 0, 'sesi' => $sesi->id]) }}">

                                                                    <span class="font-semibold  truncate"
                                                                        title="{{ $ruangan->nama_ruangan ?? 'Unknown' }}">
                                                                        {{ $ruangan->nama_ruangan ?? 'Unknown' }}
                                                                        - {{ $sesi->kode_sesi }}
                                                                    </span>
                                                                    <i class="fas fa-link ml-2"></i>
                                                                </a>

                                                                @php
                                                                    $statusColors = [
                                                                        'berlangsung' => 'bg-green-100 text-green-800',
                                                                        'selesai' => 'bg-gray-100 text-gray-800',
                                                                        'dibatalkan' => 'bg-red-100 text-red-800',
                                                                        'default' => 'bg-yellow-100 text-yellow-800',
                                                                    ];
                                                                    $color =
                                                                        $statusColors[$sesi->status] ??
                                                                        $statusColors['default'];
                                                                @endphp
                                                                <span
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                                    {{ ucfirst($sesi->status) }}
                                                                </span>
                                                            </div>

                                                            <!-- Detail Sesi -->
                                                            <div class="text-xs space-y-1 text-gray-700 flex-1">
                                                                <div><span class="font-medium">Sesi:</span>
                                                                    {{ $sesi->nama_sesi }}</div>
                                                                <div><span class="font-medium">Waktu:</span>
                                                                    {{ substr($sesi->waktu_mulai, 0, 5) }} -
                                                                    {{ substr($sesi->waktu_selesai, 0, 5) }}
                                                                </div>
                                                                <div><span class="font-medium">Kehadiran:</span>
                                                                    @php
                                                                        $kehadiranColors = [
                                                                            'hadir' => 'bg-green-100 text-green-800',
                                                                            'tidak_hadir' => 'bg-red-100 text-red-800',
                                                                            'sakit' => 'bg-yellow-100 text-yellow-800',
                                                                            'izin' => 'bg-blue-100 text-blue-800',
                                                                            'default' => 'bg-gray-100 text-gray-800',
                                                                        ];
                                                                        $kehadiran = $sesiSiswa->status_kehadiran;
                                                                        $kehadiranColor =
                                                                            $kehadiranColors[$kehadiran] ??
                                                                            $kehadiranColors['default'];
                                                                    @endphp
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $kehadiranColor }}">
                                                                        {{ ucfirst(str_replace('_', ' ', $kehadiran)) }}
                                                                    </span>
                                                                </div>

                                                                <!-- Jadwal Ujian (compact) -->
                                                                @if ($sesi->jadwalUjians->count() > 0)
                                                                    <div class="mt-2">
                                                                        <span class="font-medium">Tanggal Ujian:</span>
                                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                                            @foreach ($sesi->jadwalUjians as $jadwal)
                                                                                <span
                                                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs bg-indigo-100 text-indigo-800">
                                                                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m') }}
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <div class="mt-2 text-gray-400 italic text-xs">Tidak ada
                                                                        jadwal</div>
                                                                @endif
                                                            </div>

                                                            <!-- Tombol Hapus -->
                                                            <div class="mt-3 flex justify-end">
                                                                <form
                                                                    action="{{ route('ruangan.sesi.siswa.destroy', [$sesi->ruangan_id, $sesi->id, $siswa->id]) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('Yakin ingin menghapus siswa ini dari sesi? Semua data enrollment ujian terkait juga akan dihapus.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                        <i class="fas fa-trash mr-1"></i> Hapus dari sesi
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-8 text-gray-400">
                                                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                                    <p>Belum pernah terdaftar di sesi manapun</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $siswas->links('pagination::tailwind') }}
                            </div>
                        @else
                            <div class="rounded-md bg-blue-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Tidak ada siswa yang ditemukan dengan kata kunci "{{ request('q') }}".
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="assignSiswaModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Siswa ke Sesi</h3>
                <button type="button" onclick="closeAssignModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('ruangan.atur-siswa.assign') }}" method="POST" id="assignSiswaForm">
                @csrf
                <input type="hidden" name="q" value="{{ request('q') }}">
                <div id="selectedSiswaInputs"></div>
                <div class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">
                    <div class="rounded-md bg-blue-50 border border-blue-200 p-3 text-sm text-blue-800">
                        Centang siswa dari hasil pencarian, lalu pilih sesi. Jika jadwal dipilih manual, enrollment hanya dibuat untuk mapel yang eligible terhadap tingkat dan jurusan siswa.
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari siswa cepat</label>
                        <input type="text" id="modalStudentSearch"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            placeholder="Filter nama / ID Yayasan dari hasil pencarian saat ini">
                    </div>

                    <div class="space-y-4">
                        @foreach (($sesiOptions ?? collect()) as $ruanganNama => $sesiGroup)
                            <div class="border rounded-md overflow-hidden">
                                <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                                    <div class="font-semibold text-gray-900">{{ $ruanganNama }}</div>
                                    <label class="text-xs font-medium text-gray-700">
                                        <input type="checkbox" class="modal-select-room mr-1 rounded border-gray-300 text-blue-600"
                                            data-room="{{ \Illuminate\Support\Str::slug($ruanganNama) }}">
                                        Select all sesi
                                    </label>
                                </div>
                                <div class="divide-y">
                                    @foreach ($sesiGroup as $sesi)
                                        <label class="block p-3 hover:bg-gray-50">
                                            <div class="flex items-start gap-2">
                                                <input type="checkbox" name="sesi_ids[]" value="{{ $sesi->id }}"
                                                    class="modal-sesi-checkbox mt-1 rounded border-gray-300 text-blue-600"
                                                    data-room="{{ \Illuminate\Support\Str::slug($ruanganNama) }}">
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $sesi->nama_sesi }} - {{ $sesi->kode_sesi }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ substr($sesi->waktu_mulai, 0, 5) }} - {{ substr($sesi->waktu_selesai, 0, 5) }}
                                                    </div>
                                                    @if ($sesi->jadwalUjians->count() > 0)
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            @foreach ($sesi->jadwalUjians as $jadwal)
                                                                <label class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs">
                                                                    <input type="checkbox" name="jadwal_ids[]" value="{{ $jadwal->id }}"
                                                                        class="rounded border-gray-300 text-indigo-600">
                                                                    {{ $jadwal->mapel->nama_mapel ?? $jadwal->judul }}
                                                                    ({{ optional($jadwal->tanggal)->format('d/m/Y') }})
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="mt-1 text-xs text-gray-400">Belum terkait jadwal ujian</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <button type="button" onclick="closeAssignModal()"
                        class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md">
                        Simpan Pengaturan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openAssignModal() {
            const selected = [...document.querySelectorAll('.siswa-result-checkbox:checked')].map((checkbox) => checkbox.value);
            const container = document.getElementById('selectedSiswaInputs');
            container.innerHTML = '';

            if (selected.length === 0) {
                alert('Pilih minimal satu siswa dari hasil pencarian.');
                return;
            }

            selected.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'siswa_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            document.getElementById('assignSiswaModal').classList.remove('hidden');
            document.getElementById('assignSiswaModal').classList.add('flex');
        }

        function closeAssignModal() {
            document.getElementById('assignSiswaModal').classList.add('hidden');
            document.getElementById('assignSiswaModal').classList.remove('flex');
        }

        document.querySelectorAll('.modal-select-room').forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                document.querySelectorAll(`.modal-sesi-checkbox[data-room="${this.dataset.room}"]`)
                    .forEach((item) => item.checked = this.checked);
            });
        });

        document.getElementById('modalStudentSearch')?.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.siswa-result-checkbox').forEach((checkbox) => {
                const card = checkbox.closest('.border.border-gray-200');
                const text = card?.textContent.toLowerCase() || '';
                if (card) card.style.display = text.includes(term) ? '' : 'none';
            });
        });
    </script>
@endsection
