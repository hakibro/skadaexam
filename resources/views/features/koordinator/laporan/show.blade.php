@extends('layouts.admin')

@section('title', 'Detail Berita Acara')
@section('page-title', 'Detail Berita Acara Ujian')
@section('page-description', 'Detail berita acara untuk sesi: ' . ($beritaAcara->sesiRuangan ? $beritaAcara->sesiRuangan->nama_sesi : 'N/A'))

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div class="flex items-center">
            <a href="{{ route('koordinator.laporan.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Kembali ke Daftar Laporan
            </a>
        </div>

        <!-- Report Header -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Berita Acara Ujian</h1>
                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                <span>{{ $beritaAcara->created_at->format('d F Y, H:i') }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fa-solid fa-file-alt mr-1"></i>
                                <span>ID: {{ $beritaAcara->id }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $beritaAcara->verification_badge_class }}">
                            {{ $beritaAcara->verification_status_text }}
                        </span>
                        @if ($beritaAcara->canEdit())
                            <a href="{{ route('koordinator.laporan.edit', $beritaAcara->id) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fa-solid fa-edit mr-1"></i>
                                Edit
                            </a>
                        @endif
                        <a href="{{ route('koordinator.laporan.download', $beritaAcara->id) }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-download mr-1"></i>
                            Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Session Information -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sesi Ujian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Nama Sesi</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->nama_sesi ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Ruangan</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->ruangan->nama ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Mata Pelajaran</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->jadwalUjian->mapel->nama ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Kelas</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->jadwalUjian->kelas->nama ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Tanggal</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->tanggal ? $beritaAcara->sesiRuangan->tanggal->format('d F Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Waktu</label>
                                <p class="text-gray-900">{{ ($beritaAcara->sesiRuangan->waktu_mulai ?? 'N/A') }} -
                                    {{ ($beritaAcara->sesiRuangan->waktu_selesai ?? 'N/A') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Durasi</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->durasi ?? 'N/A' }} menit</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Total Siswa</label>
                                <p class="text-gray-900">{{ $beritaAcara->sesiRuangan->sesiRuanganSiswa->count() ?? 0 }} siswa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Laporan Pelaksanaan Ujian</h3>

                    <!-- Student Attendance -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Kehadiran Siswa</h4>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="text-2xl font-bold text-green-600">{{ $beritaAcara->jumlah_hadir }}</div>
                                <div class="text-sm text-gray-600">Hadir</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ $beritaAcara->jumlah_tidak_hadir }}</div>
                                <div class="text-sm text-gray-600">Tidak Hadir</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="text-2xl font-bold text-yellow-600">{{ $beritaAcara->jumlah_logout }}</div>
                                <div class="text-sm text-gray-600">Logout</div>
                            </div>
                        </div>

                        @if ($beritaAcara->daftar_tidak_hadir)
                            <div class="mt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Daftar Siswa Tidak Hadir:</h5>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <p class="text-sm text-gray-700 whitespace-pre-line">
                                        {{ $beritaAcara->daftar_tidak_hadir }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Exam Progress -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Pelaksanaan Ujian</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Waktu Mulai Aktual</label>
                                <p class="text-gray-900">
                                    {{ $beritaAcara->waktu_mulai_aktual ? $beritaAcara->waktu_mulai_aktual->format('H:i') : 'Belum dimulai' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Waktu Selesai Aktual</label>
                                <p class="text-gray-900">
                                    {{ $beritaAcara->waktu_selesai_aktual ? $beritaAcara->waktu_selesai_aktual->format('H:i') : 'Belum selesai' }}
                                </p>
                            </div>
                            @if ($beritaAcara->kendala_teknis)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Kendala Teknis</label>
                                    <div class="bg-red-50 p-3 rounded border border-red-200 mt-1">
                                        <p class="text-sm text-red-700 whitespace-pre-line">
                                            {{ $beritaAcara->kendala_teknis }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Incidents and Notes -->
                    @if ($beritaAcara->catatan_khusus || $beritaAcara->kejadian_khusus)
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Catatan dan Kejadian Khusus</h4>
                            @if ($beritaAcara->kejadian_khusus)
                                <div class="mb-4">
                                    <label class="text-sm font-medium text-gray-500">Kejadian Khusus</label>
                                    <div class="bg-yellow-50 p-3 rounded border border-yellow-200 mt-1">
                                        <p class="text-sm text-yellow-800 whitespace-pre-line">
                                            {{ $beritaAcara->kejadian_khusus }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($beritaAcara->catatan_khusus)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Catatan Khusus</label>
                                    <div class="bg-blue-50 p-3 rounded border border-blue-200 mt-1">
                                        <p class="text-sm text-blue-800 whitespace-pre-line">
                                            {{ $beritaAcara->catatan_khusus }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Recommendations -->
                    @if ($beritaAcara->saran_perbaikan)
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Saran Perbaikan</h4>
                            <div class="bg-green-50 p-3 rounded border border-green-200">
                                <p class="text-sm text-green-800 whitespace-pre-line">{{ $beritaAcara->saran_perbaikan }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Student List -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Siswa</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Siswa</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        NISN</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu Masuk</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu Keluar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if($beritaAcara->sesiRuangan && $beritaAcara->sesiRuangan->sesiRuanganSiswa)
                                    @foreach ($beritaAcara->sesiRuangan->sesiRuanganSiswa as $index => $siswaSession)
                                        <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $siswaSession->siswa->nama ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $siswaSession->siswa->nisn ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $siswaSession->status_badge_class }}">
                                                    {{ ucfirst($siswaSession->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $siswaSession->waktu_masuk ? $siswaSession->waktu_masuk->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $siswaSession->waktu_keluar ? $siswaSession->waktu_keluar->format('H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            Tidak ada data siswa ditemukan
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Supervisor Info -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengawas</h3>
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 text-purple-600 p-3 rounded-full mr-3">
                            <i class="fa-solid fa-user-tie text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $beritaAcara->pengawas->nama }}</h4>
                            <p class="text-sm text-gray-600">{{ $beritaAcara->pengawas->nip ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email:</span>
                            <span class="text-gray-900">{{ $beritaAcara->pengawas->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Telepon:</span>
                            <span class="text-gray-900">{{ $beritaAcara->pengawas->telepon ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if ($beritaAcara->status_verifikasi === 'pending')
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex space-x-2">
                                <button onclick="showVerificationModal('verify')"
                                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    <i class="fa-solid fa-check mr-1"></i>
                                    Verifikasi
                                </button>
                                <button onclick="showVerificationModal('reject')"
                                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                    <i class="fa-solid fa-times mr-1"></i>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Verification History -->
                @if ($beritaAcara->status_verifikasi !== 'pending')
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Verifikasi</h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div
                                    class="bg-{{ $beritaAcara->status_verifikasi === 'verified' ? 'green' : 'red' }}-100 text-{{ $beritaAcara->status_verifikasi === 'verified' ? 'green' : 'red' }}-600 p-2 rounded-full mr-3 mt-1">
                                    <i
                                        class="fa-solid fa-{{ $beritaAcara->status_verifikasi === 'verified' ? 'check' : 'times' }} text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $beritaAcara->status_verifikasi === 'verified' ? 'Diverifikasi' : 'Ditolak' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $beritaAcara->tanggal_verifikasi?->format('d F Y, H:i') }}
                                    </p>
                                    @if ($beritaAcara->koordinator)
                                        <p class="text-xs text-gray-500">
                                            oleh {{ $beritaAcara->koordinator->nama }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if ($beritaAcara->catatan_koordinator)
                                <div class="bg-gray-50 p-3 rounded border">
                                    <p class="text-sm text-gray-700">{{ $beritaAcara->catatan_koordinator }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                    <div class="space-y-2">
                        @if($beritaAcara->sesiRuangan)
                            <a href="{{ route('koordinator.monitoring.show', $beritaAcara->sesiRuangan->id) }}"
                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fa-solid fa-eye mr-2"></i>
                                Lihat Monitoring
                            </a>
                        @endif
                        <a href="{{ route('koordinator.laporan.download', $beritaAcara->id) }}"
                            class="w-full inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-download mr-2"></i>
                            Download PDF
                        </a>
                        <button onclick="printReport()"
                            class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fa-solid fa-print mr-2"></i>
                            Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div id="verification-modal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="verification-title">Verifikasi Berita Acara</h3>
                    <button onclick="closeVerificationModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form id="verification-form">
                    <input type="hidden" id="verification-action" name="action">

                    <div class="mb-4">
                        <label for="verification-notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="notes-label">Catatan Verifikasi</span>
                        </label>
                        <textarea id="verification-notes" name="catatan" rows="4"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Tambahkan catatan atau komentar..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeVerificationModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Batal
                        </button>
                        <button type="submit" id="verification-submit-btn"
                            class="px-4 py-2 text-sm font-medium text-white rounded-md">
                            <i class="fa-solid fa-check mr-1"></i>
                            Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showVerificationModal(action) {
                document.getElementById('verification-action').value = action;

                const title = document.getElementById('verification-title');
                const notesLabel = document.getElementById('notes-label');
                const submitBtn = document.getElementById('verification-submit-btn');

                if (action === 'verify') {
                    title.textContent = 'Verifikasi Berita Acara';
                    notesLabel.textContent = 'Catatan Verifikasi';
                    submitBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Verifikasi';
                    submitBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700';
                } else {
                    title.textContent = 'Tolak Berita Acara';
                    notesLabel.textContent = 'Alasan Penolakan';
                    submitBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i>Tolak';
                    submitBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700';
                }

                document.getElementById('verification-modal').classList.remove('hidden');
            }

            function closeVerificationModal() {
                document.getElementById('verification-modal').classList.add('hidden');
                document.getElementById('verification-form').reset();
            }

            document.getElementById('verification-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('berita_acara_id', '{{ $beritaAcara->id }}');

                fetch('{{ route('koordinator.laporan.verify') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Berita acara berhasil diproses', 'success');
                            closeVerificationModal();
                            location.reload();
                        } else {
                            showToast(data.message || 'Terjadi kesalahan', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Terjadi kesalahan saat memproses verifikasi', 'error');
                    });
            });

            function printReport() {
                window.print();
            }

            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
                toast.textContent = message;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        </script>
    @endpush
@endsection
