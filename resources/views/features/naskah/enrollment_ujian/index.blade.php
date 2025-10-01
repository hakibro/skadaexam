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
                    <a href="{{ route('naskah.enrollment-ujian.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Enrollment
                    </a>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150"
                        data-modal-toggle="bulkEnrollmentModal">
                        <i class="fa-solid fa-users mr-2"></i> Enrollment Massal
                    </button>
                </div>
            </div>

            <div class="p-4 bg-gray-50">
                <form action="{{ route('naskah.enrollment-ujian.index') }}" method="get" class="enrollment-filter-form">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Ujian</label>
                            <select name="jadwal_id" id="jadwal_id"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Jadwal</option>
                                @foreach ($jadwalUjians as $jadwal)
                                    <option value="{{ $jadwal->id }}"
                                        {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $jadwal->judul }} - {{ $jadwal->tanggal->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sesi Ruangan</label>
                            <select name="sesi_id" id="sesi_ruangan_id"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Sesi</option>
                                @foreach ($sesiRuangans as $sesi)
                                    <option value="{{ $sesi->id }}"
                                        {{ request('sesi_id') == $sesi->id ? 'selected' : '' }}>
                                        {{ $sesi->nama_sesi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Enrollment</label>
                            <select name="status"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="enrolled" {{ request('status') == 'enrolled' ? 'selected' : '' }}>Terdaftar
                                </option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    Dibatalkan
                                </option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Kehadiran</label>
                            <select name="kehadiran"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="belum_hadir" {{ request('kehadiran') == 'belum_hadir' ? 'selected' : '' }}>
                                    Belum Hadir</option>
                                <option value="hadir" {{ request('kehadiran') == 'hadir' ? 'selected' : '' }}>Hadir
                                </option>
                                <option value="tidak_hadir" {{ request('kehadiran') == 'tidak_hadir' ? 'selected' : '' }}>
                                    Tidak Hadir</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Per Halaman</label>
                            <select name="per_page"
                                class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto flex items-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                                <i class="fa-solid fa-search mr-2"></i> Filter
                            </button>
                            <a href="{{ route('naskah.enrollment-ujian.index') }}"
                                class="inline-flex items-center px-4 py-2 ml-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition duration-150">
                                <i class="fa-solid fa-times mr-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if (count($enrollments) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID YYS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal Ujian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi</th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($enrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm">{{ $enrollment->siswa->idyayasan ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $enrollment->siswa->nama ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $enrollment->siswa->kelas->nama ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $enrollment->jadwalUjian->judul ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->judul ?? 'N/A') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $enrollment->jadwalUjian->tanggal ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->tanggal ?? 'N/A') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $enrollment->sesiRuangan->nama_sesi ?? 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($enrollment->status_enrollment)
                                            @case('enrolled')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fa-solid fa-clipboard-list mr-1"></i> Terdaftar
                                                </span>
                                            @break

                                            @case('active')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fa-solid fa-play-circle mr-1"></i> Aktif
                                                </span>
                                            @break

                                            @case('completed')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-100 text-teal-800">
                                                    <i class="fa-solid fa-check-circle mr-1"></i> Selesai
                                                </span>
                                            @break

                                            @case('absent')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fa-solid fa-user-slash mr-1"></i> Tidak Hadir
                                                </span>
                                            @break

                                            @case('cancelled')
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fa-solid fa-ban mr-1"></i> Dibatalkan
                                                </span>
                                            @break

                                            @default
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $enrollment->status_enrollment }}
                                                </span>
                                        @endswitch

                                        <!-- Kehadiran Status -->
                                        @if ($enrollment->sesiRuanganSiswa?->status_kehadiran)
                                            <div class="mt-1">
                                                @switch($enrollment->sesiRuanganSiswa->status_kehadiran)
                                                    @case('belum_hadir')
                                                        <span
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            <i class="fa-solid fa-clock mr-1"></i> Belum Hadir
                                                        </span>
                                                    @break

                                                    @case('hadir')
                                                        <span
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            <i class="fa-solid fa-user-check mr-1"></i> Hadir
                                                        </span>
                                                    @break

                                                    @case('tidak_hadir')
                                                        <span
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            <i class="fa-solid fa-user-xmark mr-1"></i> Tidak Hadir
                                                        </span>
                                                    @break
                                                @endswitch
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex gap-1 items-center justify-center">
                                            <a href="{{ route('naskah.enrollment-ujian.show', $enrollment->id) }}"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700">
                                                <i class="fa-solid fa-eye mr-1"></i>
                                            </a>
                                            <a href="{{ route('naskah.enrollment-ujian.edit', $enrollment->id) }}"
                                                class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-xs rounded-md hover:bg-yellow-700">
                                                <i class="fa-solid fa-edit mr-1"></i>
                                            </a>
                                            <form action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}"
                                                method="POST" class="inline-block"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus pendaftaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs rounded-md hover:bg-red-700">
                                                    <i class="fa-solid fa-trash mr-1"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

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

                        <div class="mb-4">
                            <label for="bulk_sesi_ruangan_id" class="block mb-2 text-sm font-medium text-gray-900">Sesi
                                Ruangan <span class="text-red-500">*</span></label>
                            <select id="bulk_sesi_ruangan_id" name="sesi_ruangan_id" required disabled
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Pilih Sesi Ruangan</option>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit filter form when selecting filters
            const autoSubmitElements = document.querySelectorAll('.enrollment-filter-form select');
            autoSubmitElements.forEach(element => {
                element.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });

            // Modal functionality
            const bulkEnrollmentModal = document.getElementById('bulkEnrollmentModal');
            const modalToggleButtons = document.querySelectorAll('[data-modal-toggle="bulkEnrollmentModal"]');

            modalToggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    bulkEnrollmentModal.classList.toggle('hidden');
                    bulkEnrollmentModal.classList.toggle('flex');
                });
            });

            // Close modal when clicking outside
            bulkEnrollmentModal.addEventListener('click', function(e) {
                if (e.target === bulkEnrollmentModal) {
                    bulkEnrollmentModal.classList.add('hidden');
                    bulkEnrollmentModal.classList.remove('flex');
                }
            });

            // Select all / Deselect all functionality
            const selectAllBtn = document.getElementById('selectAllKelas');
            const deselectAllBtn = document.getElementById('deselectAllKelas');
            const kelasCheckboxes = document.querySelectorAll('.kelas-checkbox');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    kelasCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                });
            }

            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    kelasCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                });
            }

            // Form validation
            const bulkForm = document.getElementById('bulkEnrollmentForm');
            const kelasError = document.getElementById('kelas-error');

            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const checkedKelas = document.querySelectorAll('.kelas-checkbox:checked');
                    const jadwalSelected = document.getElementById('bulk_jadwal_id').value;
                    const sesiSelected = document.getElementById('bulk_sesi_ruangan_id').value;

                    if (!jadwalSelected) {
                        alert('Harap pilih jadwal ujian terlebih dahulu');
                        e.preventDefault();
                        return false;
                    }

                    if (!sesiSelected) {
                        alert('Harap pilih sesi ruangan terlebih dahulu');
                        e.preventDefault();
                        return false;
                    }

                    if (checkedKelas.length === 0) {
                        kelasError.classList.remove('hidden');
                        alert('Harap pilih minimal satu kelas');
                        e.preventDefault();
                        return false;
                    } else {
                        kelasError.classList.add('hidden');
                    }

                    // Confirmation dialog
                    const totalKelas = checkedKelas.length;
                    if (!confirm(
                            `Apakah Anda yakin ingin mendaftarkan siswa dari ${totalKelas} kelas ke ujian ini? Proses ini tidak dapat dibatalkan.`
                        )) {
                        e.preventDefault();
                        return false;
                    }
                });
            }

            // Dynamic sesi options based on jadwal selection (for bulk enrollment modal)
            const bulkJadwalSelect = document.getElementById('bulk_jadwal_id');
            const bulkSesiSelect = document.getElementById('bulk_sesi_ruangan_id');

            if (bulkJadwalSelect && bulkSesiSelect) {
                bulkJadwalSelect.addEventListener('change', function() {
                    const jadwalId = this.value;
                    bulkSesiSelect.disabled = true;
                    bulkSesiSelect.innerHTML = '<option value="">Pilih Sesi Ruangan</option>';

                    if (jadwalId) {
                        fetch(
                                `{{ route('naskah.enrollment-ujian.get-sesi-options') }}?jadwal_id=${jadwalId}`
                            )
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.id;
                                        option.textContent = item.text;
                                        bulkSesiSelect.appendChild(option);
                                    });
                                    bulkSesiSelect.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error loading sesi options for bulk enrollment', error);
                                alert('Gagal memuat sesi ujian');
                            });
                    }
                });
            }

            // Dynamic sesi options based on jadwal selection
            const jadwalSelect = document.getElementById('jadwal_id');
            const sesiSelect = document.getElementById('sesi_ruangan_id');

            if (jadwalSelect && sesiSelect) {
                jadwalSelect.addEventListener('change', function() {
                    const jadwalId = this.value;
                    sesiSelect.disabled = true;
                    sesiSelect.innerHTML = '<option value="">Pilih Sesi Ruangan</option>';

                    if (jadwalId) {
                        fetch(
                                `{{ route('naskah.enrollment-ujian.get-sesi-options') }}?jadwal_id=${jadwalId}`
                            )
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.id;
                                        option.textContent = item.text;
                                        sesiSelect.appendChild(option);
                                    });
                                    sesiSelect.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error loading sesi options', error);
                                alert('Gagal memuat sesi ujian');
                            });
                    }
                });
            }
        });
    </script>
@endsection
