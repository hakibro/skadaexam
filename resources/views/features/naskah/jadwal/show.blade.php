@extends('layouts.admin')

@section('title', 'Detail Jadwal Ujian')
@section('page-title', 'Detail Jadwal Ujian')
@section('page-description', $jadwal->nama_ujian)

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.jadwal.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
                <a href="{{ route('naskah.jadwal.edit', $jadwal->id) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                    <i class="fa-solid fa-edit mr-2"></i> Edit Jadwal
                </a>
            </div>

            <div class="flex space-x-2">
                <form action="{{ route('naskah.jadwal.status', $jadwal->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    @if ($jadwal->status == 'draft')
                        <input type="hidden" name="status" value="aktif">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fa-solid fa-check mr-2"></i> Aktifkan Jadwal
                        </button>
                    @elseif($jadwal->status == 'aktif')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-flag-checkered mr-2"></i> Selesaikan
                        </button>
                    @elseif($jadwal->status == 'selesai' || $jadwal->status == 'dibatalkan')
                        <input type="hidden" name="status" value="aktif">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fa-solid fa-redo mr-2"></i> Aktifkan Kembali
                        </button>
                    @endif
                </form>

                @if ($jadwal->status != 'dibatalkan')
                    <form action="{{ route('naskah.jadwal.status', $jadwal->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="dibatalkan">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                            onclick="return confirm('Apakah Anda yakin ingin membatalkan jadwal ujian ini?')">
                            <i class="fa-solid fa-ban mr-2"></i> Batalkan
                        </button>
                    </form>
                @endif

                <form action="{{ route('naskah.jadwal.destroy', $jadwal->id) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ujian ini? Tindakan ini tidak dapat dibatalkan.')">
                        <i class="fa-solid fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Jadwal Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Jadwal Ujian</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Detail lengkap tentang jadwal ujian ini.</p>
                        </div>
                        <div>
                            @switch($jadwal->status)
                                @case('draft')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                @break

                                @case('aktif')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                @break

                                @case('selesai')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                                @break

                                @case('dibatalkan')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                @break

                                @default
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $jadwal->status }}</span>
                            @endswitch
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200">
                    <dl>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Kode Ujian</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $jadwal->kode_ujian }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Nama Ujian</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $jadwal->nama_ujian }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Jenis Ujian</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @switch($jadwal->jenis_ujian)
                                    @case('reguler')
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Reguler</span>
                                    @break

                                    @case('susulan')
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Susulan</span>
                                    @break

                                    @case('remedial')
                                        <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Remedial</span>
                                    @break

                                    @default
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $jadwal->jenis_ujian }}</span>
                                @endswitch
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Mata Pelajaran</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Bank Soal</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="{{ route('naskah.banksoal.show', $jadwal->bank_soal_id) }}"
                                    class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $jadwal->bankSoal->judul ?? 'N/A' }}
                                </a>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Tanggal</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $jadwal->tanggal_ujian->format('d M Y') }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $jadwal->waktu_mulai->format('H:i') }} - {{ $jadwal->waktu_selesai->format('H:i') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $jadwal->durasi_menit }} menit
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Jumlah Soal</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $jadwal->jumlah_soal }} soal
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Pengaturan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="space-y-1">
                                    <li>
                                        <i
                                            class="fa-solid {{ $jadwal->acak_soal ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500' }} mr-2"></i>
                                        Acak Soal
                                    </li>
                                    <li>
                                        <i
                                            class="fa-solid {{ $jadwal->acak_jawaban ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500' }} mr-2"></i>
                                        Acak Jawaban
                                    </li>
                                    <li>
                                        <i
                                            class="fa-solid {{ $jadwal->tampilkan_hasil ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500' }} mr-2"></i>
                                        Tampilkan Hasil Setelah Ujian
                                    </li>
                                </ul>
                            </dd>
                        </div>
                    </dl>
                </div>

                @if ($jadwal->deskripsi)
                    <div class="px-4 py-5 sm:px-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Deskripsi</h4>
                        <div class="prose max-w-none text-sm text-gray-900">
                            {{ $jadwal->deskripsi }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Sesi Ujian Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Sesi Ujian</h3>
                        <div class="flex space-x-2">
                            <button id="btnPilihSesi"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fa-solid fa-link mr-1"></i> Pilih Sesi
                            </button>
                            <a href="{{ route('naskah.sesi.create', $jadwal->id) }}"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <i class="fa-solid fa-plus mr-1"></i> Tambah
                            </a>
                        </div>
                    </div>

                    <div class="p-4">
                        @if (count($jadwal->sesiUjians) > 0)
                            <div class="space-y-3">
                                @foreach ($jadwal->sesiUjians as $sesi)
                                    <div
                                        class="p-3 border rounded-md {{ $sesi->status == 'aktif' ? 'border-green-300 bg-green-50' : ($sesi->status == 'penuh' ? 'border-orange-300 bg-orange-50' : 'border-gray-300 bg-gray-50') }}">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-medium">{{ $sesi->nama_sesi }}</div>
                                                <div class="text-sm text-gray-600">{{ $sesi->waktu_mulai->format('H:i') }}
                                                    - {{ $sesi->waktu_selesai->format('H:i') }}</div>
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <i class="fa-solid fa-user-group mr-1"></i>
                                                    {{ $sesi->peserta_terdaftar }}/{{ $sesi->kapasitas_maksimal }} peserta
                                                </div>
                                                @if ($sesi->ruangan)
                                                    <div class="text-sm text-gray-600"><i
                                                            class="fa-solid fa-door-open mr-1"></i>
                                                        {{ $sesi->ruangan ? $sesi->ruangan->nama_ruangan : '-' }}
                                                    </div>
                                                @endif
                                                @if ($sesi->pengawas)
                                                    <div class="text-sm text-gray-600"><i
                                                            class="fa-solid fa-user-tie mr-1"></i>
                                                        {{ $sesi->pengawas->nama }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('naskah.manajemen-sesi.show', $sesi->id) }}"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fa-solid fa-calendar-day text-gray-300 text-4xl mb-2"></i>
                                <p class="text-gray-500">Belum ada sesi ujian</p>
                                <p class="text-gray-400 text-sm">Pilih atau tambahkan sesi untuk jadwal ini</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal for selecting existing sessions -->
                <div id="modalPilihSesi"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
                        <div class="px-4 py-3 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Pilih Sesi Ujian</h3>
                            <button id="closeModalSesi" class="text-gray-400 hover:text-gray-500">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>

                        <div class="p-4">
                            <div class="mb-4">
                                <input type="text" id="searchSesi" class="form-input w-full"
                                    placeholder="Cari sesi...">
                            </div>

                            <div class="overflow-y-auto max-h-80">
                                <div id="sesiList" class="space-y-2">
                                    <!-- Session items will be loaded via AJAX -->
                                    <div class="text-center py-4">
                                        <i class="fa-solid fa-spinner fa-spin text-blue-500"></i>
                                        <p class="mt-2 text-gray-600">Memuat sesi...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 border-t flex justify-end">
                            <button id="cancelPilihSesi"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const modal = document.getElementById('modalPilihSesi');
                        const btnOpen = document.getElementById('btnPilihSesi');
                        const btnClose = document.getElementById('closeModalSesi');
                        const btnCancel = document.getElementById('cancelPilihSesi');
                        const searchInput = document.getElementById('searchSesi');
                        const sesiList = document.getElementById('sesiList');
                        const jadwalId = '{{ $jadwal->id }}';

                        // Open modal
                        btnOpen.addEventListener('click', function() {
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            loadSesiList();
                        });

                        // Close modal
                        [btnClose, btnCancel].forEach(btn => {
                            btn.addEventListener('click', function() {
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                            });
                        });

                        // Search functionality
                        searchInput.addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase();
                            const sesiItems = sesiList.querySelectorAll('.sesi-item');

                            sesiItems.forEach(item => {
                                const sesiName = item.querySelector('.sesi-name').textContent.toLowerCase();
                                if (sesiName.includes(searchTerm)) {
                                    item.classList.remove('hidden');
                                } else {
                                    item.classList.add('hidden');
                                }
                            });
                        });

                        // Load sessions via AJAX
                        function loadSesiList() {
                            fetch('/api/sesi-available?exclude_jadwal=' + jadwalId)
                                .then(response => response.json())
                                .then(data => {
                                    sesiList.innerHTML = '';

                                    if (data.length === 0) {
                                        sesiList.innerHTML = `
                                        <div class="text-center py-4">
                                            <p class="text-gray-600">Tidak ada sesi tersedia</p>
                                        </div>
                                    `;
                                        return;
                                    }

                                    data.forEach(sesi => {
                                        const sesiItem = document.createElement('div');
                                        sesiItem.className =
                                            'sesi-item p-3 border rounded hover:bg-gray-50 cursor-pointer';

                                        const ruangan = sesi.ruangan ? sesi.ruangan.nama_ruangan : '-';
                                        const pengawas = sesi.pengawas ? sesi.pengawas.nama : '-';

                                        sesiItem.innerHTML = `
                                        <div class="flex justify-between">
                                            <div>
                                                <div class="sesi-name font-medium">${sesi.nama_sesi}</div>
                                                <div class="text-sm text-gray-600">${sesi.waktu_mulai} - ${sesi.waktu_selesai}</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Ruangan: ${ruangan} | Pengawas: ${pengawas}
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-${sesi.status === 'aktif' ? 'green' : 'gray'}-100 text-${sesi.status === 'aktif' ? 'green' : 'gray'}-800">
                                                    ${sesi.status.charAt(0).toUpperCase() + sesi.status.slice(1)}
                                                </span>
                                            </div>
                                        </div>
                                    `;

                                        sesiItem.addEventListener('click', function() {
                                            attachSesiToJadwal(sesi.id);
                                        });

                                        sesiList.appendChild(sesiItem);
                                    });
                                })
                                .catch(error => {
                                    console.error('Error fetching sessions:', error);
                                    sesiList.innerHTML = `
                                    <div class="text-center py-4">
                                        <p class="text-red-600">Error loading sessions</p>
                                    </div>
                                `;
                                });
                        }

                        // Attach session to schedule
                        function attachSesiToJadwal(sesiId) {
                            const url = `/naskah/jadwal/${jadwalId}/attach-sesi`;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                            fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        sesi_id: sesiId
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        modal.classList.add('hidden');
                                        window.location.reload();
                                    } else {
                                        alert('Error: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error attaching session:', error);
                                    alert('An error occurred while attaching the session');
                                });
                        }
                    });
                </script>

                <!-- Information Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Tambahan</h3>
                    </div>

                    <div class="p-4">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dibuat oleh</dt>
                                <dd class="text-sm text-gray-900">{{ $jadwal->creator->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dibuat pada</dt>
                                <dd class="text-sm text-gray-900">{{ $jadwal->created_at->format('d M Y, H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Terakhir diupdate</dt>
                                <dd class="text-sm text-gray-900">{{ $jadwal->updated_at->format('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-gray-50 shadow-md rounded-lg overflow-hidden border border-gray-200">
                    <div class="p-4">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Aksi Lainnya</h3>
                        <div class="space-y-2">
                            <a href="{{ route('naskah.jadwal.edit', $jadwal->id) }}"
                                class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                <i class="fa-solid fa-edit w-5 h-5 mr-3 text-gray-400"></i>
                                Edit Jadwal
                            </a>
                            <form action="{{ route('naskah.jadwal.destroy', $jadwal->id) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex items-center w-full p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ujian ini? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="fa-solid fa-trash w-5 h-5 mr-3 text-red-400"></i>
                                    Hapus Jadwal
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
