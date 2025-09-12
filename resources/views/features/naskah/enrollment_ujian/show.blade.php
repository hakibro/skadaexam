@extends('layouts.admin')

@section('title', 'Detail Enrollment Ujian')
@section('page-title', 'Detail Enrollment Ujian')
@section('page-description', 'Informasi lengkap pendaftaran siswa pada ujian')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-user-graduate mr-2"></i>Informasi Siswa
                    </h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900 w-1/3">NIS</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->siswa->nis }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Nama</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->siswa->nama }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Kelas</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->siswa->kelas->nama ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Email</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->siswa->email ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-calendar-alt mr-2"></i>Informasi Ujian
                    </h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900 w-1/3">Jadwal</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->jadwalUjian->judul ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->judul ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Mata Pelajaran</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->jadwalUjian->mapel->nama ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->mapel->nama ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Sesi</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->sesiRuangan->nama_sesi }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Waktu Mulai</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->sesiRuangan->waktu_mulai->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Waktu Selesai</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->sesiRuangan->waktu_selesai->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Durasi</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->sesiRuangan->durasi_ujian }} menit
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-50 p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fa-solid fa-clipboard-check mr-2"></i>Status Enrollment
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900 w-2/5">Status Enrollment
                                    </th>
                                    <td class="py-3 text-sm text-gray-700">
                                        @php
                                            $statusColor = 'gray';
                                            $statusIcon = 'fa-question-circle';

                                            switch ($enrollment->status_enrollment) {
                                                case 'enrolled':
                                                    $statusColor = 'blue';
                                                    $statusIcon = 'fa-clipboard-list';
                                                    $statusText = 'Terdaftar';
                                                    break;
                                                case 'active':
                                                    $statusColor = 'green';
                                                    $statusIcon = 'fa-play-circle';
                                                    $statusText = 'Aktif';
                                                    break;
                                                case 'completed':
                                                    $statusColor = 'teal';
                                                    $statusIcon = 'fa-check-circle';
                                                    $statusText = 'Selesai';
                                                    break;
                                                case 'absent':
                                                    $statusColor = 'yellow';
                                                    $statusIcon = 'fa-user-slash';
                                                    $statusText = 'Tidak Hadir';
                                                    break;
                                                case 'cancelled':
                                                    $statusColor = 'red';
                                                    $statusIcon = 'fa-ban';
                                                    $statusText = 'Dibatalkan';
                                                    break;
                                                default:
                                                    $statusText = 'Tidak Diketahui';
                                            }
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            <i class="fa-solid {{ $statusIcon }} mr-1"></i> {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Status Kehadiran</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        @php
                                            $hadirColor = 'gray';
                                            $hadirIcon = 'fa-question-circle';

                                            $statusKehadiran =
                                                $enrollment->sesiRuanganSiswa?->status_kehadiran ?? 'belum_hadir';
                                            switch ($statusKehadiran) {
                                                case 'belum_hadir':
                                                    $hadirColor = 'gray';
                                                    $hadirIcon = 'fa-clock';
                                                    $hadirText = 'Belum Hadir';
                                                    break;
                                                case 'hadir':
                                                    $hadirColor = 'green';
                                                    $hadirIcon = 'fa-user-check';
                                                    $hadirText = 'Hadir';
                                                    break;
                                                case 'tidak_hadir':
                                                    $hadirColor = 'red';
                                                    $hadirIcon = 'fa-user-xmark';
                                                    $hadirText = 'Tidak Hadir';
                                                    break;
                                                case 'sakit':
                                                    $hadirColor = 'yellow';
                                                    $hadirIcon = 'fa-thermometer';
                                                    $hadirText = 'Sakit';
                                                    break;
                                                case 'izin':
                                                    $hadirColor = 'blue';
                                                    $hadirIcon = 'fa-file-signature';
                                                    $hadirText = 'Izin';
                                                    break;
                                                default:
                                                    $hadirText = 'Tidak Diketahui';
                                            }
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-{{ $hadirColor }}-100 text-{{ $hadirColor }}-800">
                                            <i class="fa-solid {{ $hadirIcon }} mr-1"></i> {{ $hadirText }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Terakhir Login</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        {{ $enrollment->last_login_at ? $enrollment->last_login_at->format('d M Y H:i:s') : 'Belum Login' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Terakhir Logout</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        {{ $enrollment->last_logout_at ? $enrollment->last_logout_at->format('d M Y H:i:s') : 'Belum Logout' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900 w-2/5">Token Login</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        @if ($enrollment->sesiRuangan && $enrollment->sesiRuangan->token_ujian)
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $enrollment->sesiRuangan->token_ujian }}
                                                </span>
                                                <button type="button"
                                                    class="p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none"
                                                    onclick="copyToken('{{ $enrollment->sesiRuangan->token_ujian }}')">
                                                    <i class="fa-solid fa-copy"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Tidak ada token aktif
                                                </span>
                                                <form
                                                    action="{{ route('naskah.enrollment-ujian.generate-token', $enrollment->id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-2 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700">
                                                        Generate Token
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Token Dibuat</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        {{ $enrollment->token_dibuat_pada ? $enrollment->token_dibuat_pada->format('d M Y H:i:s') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Token Digunakan</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        {{ $enrollment->token_digunakan_pada ? $enrollment->token_digunakan_pada->format('d M Y H:i:s') : 'Belum digunakan' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-3 text-left text-sm font-medium text-gray-900">Catatan</th>
                                    <td class="py-3 text-sm text-gray-700">
                                        {{ $enrollment->catatan ?: 'Tidak ada catatan' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- QR Code section -->
                @if ($enrollment->sesiRuangan && $enrollment->sesiRuangan->token_ujian)
                    <div class="mt-8 text-center">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">QR Code Login</h4>
                        <div class="flex justify-center my-4">
                            <img class="border p-2 rounded-lg shadow-sm bg-white"
                                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('login.direct-token', $enrollment->sesiRuangan->token_ujian)) }}"
                                alt="QR Code for Login">
                        </div>
                        <a href="{{ route('naskah.enrollment-ujian.print-qr', $enrollment->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-md transition duration-150"
                            target="_blank">
                            <i class="fa-solid fa-print mr-2"></i> Cetak QR Code
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if ($enrollment->hasilUjian)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-chart-bar mr-2"></i>Hasil Ujian
                    </h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900 w-1/3">Nilai</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->hasilUjian->nilai ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Jumlah Benar</th>
                                <td class="py-3 text-sm text-gray-700">{{ $enrollment->hasilUjian->jumlah_benar ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Jumlah Salah</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->hasilUjian->jumlah_salah ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Jumlah Tidak Dijawab</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->hasilUjian->jumlah_tidak_dijawab ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Waktu Mulai Mengerjakan</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->hasilUjian->waktu_mulai ? $enrollment->hasilUjian->waktu_mulai->format('d M Y H:i:s') : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Waktu Selesai Mengerjakan</th>
                                <td class="py-3 text-sm text-gray-700">
                                    {{ $enrollment->hasilUjian->waktu_selesai ? $enrollment->hasilUjian->waktu_selesai->format('d M Y H:i:s') : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 text-left text-sm font-medium text-gray-900">Durasi Mengerjakan</th>
                                <td class="py-3 text-sm text-gray-700">
                                    @if ($enrollment->hasilUjian->waktu_mulai && $enrollment->hasilUjian->waktu_selesai)
                                        {{ $enrollment->hasilUjian->waktu_mulai->diffInMinutes($enrollment->hasilUjian->waktu_selesai) }}
                                        menit
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-center my-6 gap-4">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.enrollment-ujian.index') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-150 inline-flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
                <a href="{{ route('naskah.enrollment-ujian.edit', $enrollment->id) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150 inline-flex items-center">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
                </a>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($enrollment->status_enrollment == 'enrolled')
                    <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'active']) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition duration-150 inline-flex items-center"
                            onclick="return confirm('Tandai ujian ini sebagai aktif?')">
                            <i class="fa-solid fa-play-circle mr-2"></i> Tandai Aktif
                        </button>
                    </form>

                    <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'completed']) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150 inline-flex items-center"
                            onclick="return confirm('Tandai ujian ini sebagai selesai?')">
                            <i class="fa-solid fa-check mr-2"></i> Tandai Selesai
                        </button>
                    </form>

                    <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'absent']) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150 inline-flex items-center"
                            onclick="return confirm('Tandai siswa ini sebagai tidak hadir?')">
                            <i class="fa-solid fa-times mr-2"></i> Tandai Tidak Hadir
                        </button>
                    </form>
                @endif

                @if ($enrollment->status_enrollment != 'enrolled')
                    <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'enrolled']) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition duration-150 inline-flex items-center"
                            onclick="return confirm('Kembalikan status ujian ini ke terdaftar?')">
                            <i class="fa-solid fa-undo mr-2"></i> Kembalikan ke Terdaftar
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Delete Modal -->
        <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modalBackdrop"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Konfirmasi Hapus</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus enrollment untuk siswa
                                        <strong>{{ $enrollment->siswa->nama }}</strong> pada jadwal ujian
                                        <strong>{{ $enrollment->jadwalUjian->nama_jadwal }}</strong>?
                                    </p>
                                    <p class="text-sm text-red-600 mt-2 font-medium">Tindakan ini tidak dapat dibatalkan!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, Hapus Enrollment
                            </button>
                        </form>
                        <button type="button" id="cancelDelete"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function copyToken(token) {
            navigator.clipboard.writeText(token).then(function() {
                toastr.success('Token berhasil disalin');
            }, function() {
                toastr.error('Gagal menyalin token');
            });
        }

        // Modal handling for delete button
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButton = document.querySelector('.delete-button');
            const deleteModal = document.getElementById('deleteModal');
            const modalBackdrop = document.getElementById('modalBackdrop');
            const cancelDelete = document.getElementById('cancelDelete');

            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    deleteModal.classList.remove('hidden');
                });
            }

            if (cancelDelete) {
                cancelDelete.addEventListener('click', function() {
                    deleteModal.classList.add('hidden');
                });
            }

            if (modalBackdrop) {
                modalBackdrop.addEventListener('click', function() {
                    deleteModal.classList.add('hidden');
                });
            }
        });
    </script>
@endpush
