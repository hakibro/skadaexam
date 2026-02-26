@extends('layouts.admin')

@section('title', 'Manajemen Enrollment Ujian')
@section('page-title', 'Enrollment Ujian')
@section('page-description', 'Kelola pendaftaran siswa pada ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Daftar Enrollment Ujian</h3>
                <div class="flex space-x-2">
                    <div id="bulk-actions" class="flex gap-2 my-2" style="display:none;">
                        <button type="button" class="bulk-action-btn" data-action="enrolled">Enroll Ulang</button>
                        <button type="button" class="bulk-action-btn" data-action="cancelled">Batalkan</button>
                        <button type="button" class="bulk-action-btn" data-action="deleted">Hapus</button>
                    </div>
                    <a href="{{ route('naskah.enrollment-ujian.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Enrollment
                    </a>
                    <!-- Tombol Enroll Semua Jadwal (modal dengan select2) -->
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition duration-150"
                        data-modal-toggle="enrollSelectedModal">
                        <i class="fa-solid fa-user-plus mr-2"></i> Enroll Semua Jadwal
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150"
                        data-modal-toggle="bulkEnrollmentModal">
                        <i class="fa-solid fa-users mr-2"></i> Enrollment Massal
                    </button>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="p-3 bg-white border-b border-gray-200">
                <form action="{{ route('naskah.enrollment-ujian.index') }}" method="get" class="enrollment-filter-form">
                    <div class="flex flex-nowrap items-end gap-2 overflow-x-auto pb-2 lg:pb-0">

                        <div class="min-w-[180px] flex-1">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Cari Siswa</label>
                            <input type="text" name="siswa_search" value="{{ request('siswa_search') }}"
                                placeholder="Nama / ID..."
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                        </div>

                        <div class="min-w-[120px] flex-1">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Kelas</label>
                            <select name="kelas_id"
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="min-w-[150px] flex-1">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Jadwal</label>
                            <select name="jadwal_id"
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                                <option value="">Semua Jadwal</option>
                                @foreach ($jadwalUjians as $jadwal)
                                    <option value="{{ $jadwal->id }}"
                                        {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $jadwal->tanggal->format('d/m') }} - {{ Str::limit($jadwal->judul, 15) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="min-w-[110px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Status</label>
                            <select name="status"
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                                <option value="">Status</option>
                                <option value="enrolled" {{ request('status') == 'enrolled' ? 'selected' : '' }}>Terdaftar
                                </option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>

                        <div class="min-w-[110px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Kehadiran</label>
                            <select name="kehadiran"
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                                <option value="">Kehadiran</option>
                                <option value="hadir" {{ request('kehadiran') == 'hadir' ? 'selected' : '' }}>Hadir
                                </option>
                                <option value="tidak_hadir" {{ request('kehadiran') == 'tidak_hadir' ? 'selected' : '' }}>
                                    Absen</option>
                            </select>
                        </div>

                        <div class="min-w-[70px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1">Limit</label>
                            <select name="per_page"
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                                @foreach ([50, 100, 250] as $p)
                                    <option value="{{ $p }}"
                                        {{ request('per_page', 50) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-1">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md transition shadow-sm"
                                title="Cari">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </button>
                            <a href="{{ route('naskah.enrollment-ujian.index') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-md transition border border-gray-300"
                                title="Reset">
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if (count($enrollments) > 0)
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox" id="select_all"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">ID
                                        YYS</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">
                                        Nama Siswa</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">
                                        Kelas</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">
                                        Jadwal Ujian</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">
                                        Sesi</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">
                                        Status</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($enrollments as $enrollment)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4">
                                            <input type="checkbox"
                                                class="row_checkbox rounded border-gray-300 text-blue-600"
                                                value="{{ $enrollment->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-600">
                                            {{ $enrollment->siswa->idyayasan ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $enrollment->siswa->nama ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="text-gray-600">{{ $enrollment->siswa->kelas->nama ?? '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900 font-medium">
                                                {{ $enrollment->jadwalUjian->judul ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->judul ?? 'N/A') }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <i class="fa-regular fa-calendar-days mr-1"></i>
                                                {{ optional($enrollment->jadwalUjian->tanggal ?? $enrollment->sesiRuangan->jadwalUjians->first()?->tanggal)->format('d M Y') ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                            {{ $enrollment->sesiRuangan->nama_sesi ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap space-y-1">
                                            {{-- Status Enrollment --}}
                                            @php
                                                $statusClasses = [
                                                    'enrolled' => 'bg-blue-100 text-blue-700',
                                                    'active' => 'bg-green-100 text-green-700',
                                                    'completed' => 'bg-teal-100 text-teal-700',
                                                    'absent' => 'bg-yellow-100 text-yellow-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                ];
                                                $statusIcons = [
                                                    'enrolled' => 'fa-clipboard-list',
                                                    'active' => 'fa-play-circle',
                                                    'completed' => 'fa-check-circle',
                                                    'absent' => 'fa-user-slash',
                                                    'cancelled' => 'fa-ban',
                                                ];
                                                $currStatus = $enrollment->status_enrollment;
                                            @endphp

                                            <span
                                                class="px-2.5 py-1 inline-flex items-center text-xs font-bold rounded-md {{ $statusClasses[$currStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                                <i
                                                    class="fa-solid {{ $statusIcons[$currStatus] ?? 'fa-info-circle' }} mr-1.5"></i>
                                                {{ ucfirst($currStatus) }}
                                            </span>

                                            {{-- Status Kehadiran --}}
                                            @if ($enrollment->sesiRuanganSiswa?->status_kehadiran)
                                                <div class="block">
                                                    @switch($enrollment->sesiRuanganSiswa->status_kehadiran)
                                                        @case('hadir')
                                                            <span
                                                                class="px-2.5 py-0.5 text-[10px] uppercase tracking-wider font-bold rounded bg-green-50 text-green-600 border border-green-200">Hadir</span>
                                                        @break

                                                        @case('tidak_hadir')
                                                            <span
                                                                class="px-2.5 py-0.5 text-[10px] uppercase tracking-wider font-bold rounded bg-red-50 text-red-600 border border-red-200">Absen</span>
                                                        @break

                                                        @default
                                                            <span
                                                                class="px-2.5 py-0.5 text-[10px] uppercase tracking-wider font-bold rounded bg-gray-50 text-gray-500 border border-gray-200">Belum
                                                                Hadir</span>
                                                    @endswitch
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex justify-center gap-1.5">
                                                <a href="{{ route('naskah.enrollment-ujian.show', $enrollment->id) }}"
                                                    class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                                    title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-sm"></i>
                                                </a>

                                                <a href="{{ route('naskah.enrollment-ujian.edit', $enrollment->id) }}"
                                                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-500 hover:text-white transition-all shadow-sm"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                                                </a>

                                                <form
                                                    action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}"
                                                    method="POST" onsubmit="return confirm('Hapus pendaftaran ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm"
                                                        title="Hapus">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t">
                        {{ $enrollments->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="fa-solid fa-clipboard-user text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">Belum ada enrollment ujian</p>
                        <p class="text-gray-400 mb-4">Tambahkan enrollment ujian baru untuk memulai</p>
                        <a href="{{ route('naskah.enrollment-ujian.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Enrollment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal: Enroll Semua Jadwal (dengan Select2) -->
    <div id="enrollSelectedModal" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative p-4 w-full max-w-lg h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow-lg">
                <div class="flex justify-between items-center p-4 border-b rounded-t">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-user-plus mr-2"></i>Enroll Siswa ke Semua Jadwal
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-toggle="enrollSelectedModal">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('naskah.enrollment-ujian.enroll-selected') }}" method="POST"
                    id="enrollSelectedForm">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="siswa_select" class="block mb-2 text-sm font-medium text-gray-900">
                                Pilih Siswa <span class="text-red-500">*</span>
                            </label>
                            <select id="siswa_select" name="siswa_ids[]" multiple class="w-full"
                                style="width: 100%;"></select>
                            <p class="mt-2 text-sm text-gray-500">
                                Ketik untuk mencari siswa berdasarkan nama atau ID Yayasan. Bisa pilih lebih dari satu.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end p-4 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="button"
                            class="modal-close-btn text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900"
                            data-modal-toggle="enrollSelectedModal">
                            <i class="fa-solid fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            <i class="fa-solid fa-save mr-2"></i>Enroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Bulk Enrollment -->
    <div id="bulkEnrollmentModal" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow-lg">
                <div class="flex justify-between items-center p-4 border-b rounded-t">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-users mr-2"></i>Enrollment Massal
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-toggle="bulkEnrollmentModal">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('naskah.enrollment-ujian.bulk') }}" method="POST" id="bulkEnrollmentForm">
                    @csrf
                    <div class="p-6 space-y-6">
                        <div class="mb-4">
                            <label for="bulk_jadwal_id" class="block mb-2 text-sm font-medium text-gray-900">Jadwal
                                Ujian <span class="text-red-500">*</span></label>
                            <select id="bulk_jadwal_id" name="jadwal_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Pilih Jadwal Ujian</option>
                                @foreach ($jadwalUjians as $jadwal)
                                    <option value="{{ $jadwal->id }}">
                                        {{ $jadwal->judul }} - {{ $jadwal->tanggal->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Kelas <span
                                    class="text-red-500">*</span></label>
                            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 h-48 overflow-y-auto">
                                <div class="mb-2">
                                    <button type="button" id="selectAllKelas"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-check-square mr-1"></i>Pilih Semua
                                    </button>
                                    <button type="button" id="deselectAllKelas"
                                        class="ml-3 text-sm text-red-600 hover:text-red-800">
                                        <i class="fa-solid fa-times-square mr-1"></i>Batal Semua
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach ($kelasList as $kelas)
                                        <div class="flex items-center">
                                            <input id="bulk_kelas_{{ $kelas->id }}" type="checkbox"
                                                name="kelas_ids[]" value="{{ $kelas->id }}"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 kelas-checkbox">
                                            <label for="bulk_kelas_{{ $kelas->id }}"
                                                class="ml-2 text-sm font-medium text-gray-900">{{ $kelas->nama }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Pilih minimal satu kelas untuk enrollment massal</p>
                            <div id="kelas-error" class="hidden mt-2 text-sm text-red-600">Harap pilih minimal satu kelas
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end p-4 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900"
                            data-modal-toggle="bulkEnrollmentModal">
                            <i class="fa-solid fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            <i class="fa-solid fa-save mr-2"></i>Daftarkan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Load jQuery dan Select2 (pastikan urutannya benar) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== BULK ACTION (checkbox di tabel) =====
            const rowCheckboxes = document.querySelectorAll('.row_checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectAll = document.getElementById('select_all');

            function toggleBulkActions() {
                const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                if (bulkActions) bulkActions.style.display = anyChecked ? 'flex' : 'none';
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => cb.checked = this.checked);
                    toggleBulkActions();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', toggleBulkActions);
            });

            window.confirmBulkAction = function(action) {
                const selectedIds = Array.from(rowCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selectedIds.length === 0) {
                    alert('Pilih minimal 1 data');
                    return;
                }

                if (!confirm(`Apakah yakin ingin melakukan "${action}" untuk ${selectedIds.length} data?`))
                    return;

                fetch("{{ route('naskah.enrollment-ujian.bulk-action') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            action,
                            ids: selectedIds
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert('Bulk action berhasil!');
                            location.reload();
                        } else {
                            alert('Terjadi kesalahan: ' + res.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal melakukan bulk action.');
                    });
            };

            document.querySelectorAll('.bulk-action-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    confirmBulkAction(this.dataset.action);
                });
            });

            // ===== FILTER AUTO SUBMIT =====
            document.querySelectorAll('.enrollment-filter-form select').forEach(el => {
                el.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });

            // ===== MODAL BULK ENROLLMENT (existing) =====
            const bulkModal = document.getElementById('bulkEnrollmentModal');
            document.querySelectorAll('[data-modal-toggle="bulkEnrollmentModal"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    bulkModal.classList.toggle('hidden');
                    bulkModal.classList.toggle('flex');
                });
            });
            bulkModal.addEventListener('click', (e) => {
                if (e.target === bulkModal) {
                    bulkModal.classList.add('hidden');
                    bulkModal.classList.remove('flex');
                }
            });

            // Select all / deselect all kelas (bulk enrollment)
            const selectAllKelas = document.getElementById('selectAllKelas');
            const deselectAllKelas = document.getElementById('deselectAllKelas');
            const kelasCheckboxes = document.querySelectorAll('.kelas-checkbox');
            if (selectAllKelas) {
                selectAllKelas.addEventListener('click', () => {
                    kelasCheckboxes.forEach(cb => cb.checked = true);
                });
            }
            if (deselectAllKelas) {
                deselectAllKelas.addEventListener('click', () => {
                    kelasCheckboxes.forEach(cb => cb.checked = false);
                });
            }

            // ===== MODAL ENROLL SEMUA JADWAL (dengan Select2) =====
            const enrollModal = document.getElementById('enrollSelectedModal');
            const enrollToggles = document.querySelectorAll('[data-modal-toggle="enrollSelectedModal"]');

            // Fungsi inisialisasi Select2
            function initSelect2() {
                if (!$('#siswa_select').data('select2')) {
                    $('#siswa_select').select2({
                        ajax: {
                            url: '{{ route('naskah.enrollment-ujian.get-siswa-options') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    search: params.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Ketik nama atau ID Yayasan...',
                        minimumInputLength: 2,
                        allowClear: true,
                        width: '100%'
                    });
                }
            }

            // Buka modal (hanya untuk tombol pembuka)
            enrollToggles.forEach(btn => {
                btn.addEventListener('click', () => {
                    enrollModal.classList.remove('hidden');
                    enrollModal.classList.add('flex');
                    enrollModal.removeAttribute('aria-hidden');
                    initSelect2();
                });
            });

            // Tutup modal saat klik di luar area modal
            enrollModal.addEventListener('click', (e) => {
                if (e.target === enrollModal) {
                    enrollModal.classList.add('hidden');
                    enrollModal.classList.remove('flex');
                    enrollModal.setAttribute('aria-hidden', 'true');
                }
            });

            // Tutup modal via tombol batal (menggunakan class modal-close-btn)
            document.querySelectorAll('.modal-close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    enrollModal.classList.add('hidden');
                    enrollModal.classList.remove('flex');
                    enrollModal.setAttribute('aria-hidden', 'true');
                });
            });

        });
    </script>
@endsection
