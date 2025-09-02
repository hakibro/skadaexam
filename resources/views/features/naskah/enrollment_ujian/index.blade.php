@extends('layouts.app')

@section('title', 'Manajemen Enrollment Ujian')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manajemen Enrollment Ujian</h1>

        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Enrollment Ujian</h5>
                        <div>
                            <a href="{{ route('naskah.enrollment-ujian.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Tambah Enrollment
                            </a>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#bulkEnrollmentModal">
                                <i class="fas fa-users me-1"></i> Enrollment Massal
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="mb-4">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Filter Jadwal</label>
                                    <select id="jadwalFilter" class="form-select form-select-sm">
                                        <option value="">Semua Jadwal</option>
                                        @foreach ($jadwalUjians as $jadwal)
                                            <option value="{{ $jadwal->id }}"
                                                {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                                {{ $jadwal->judul }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Filter Sesi</label>
                                    <select id="sesiFilter" class="form-select form-select-sm">
                                        <option value="">Semua Sesi</option>
                                        @foreach ($sesiUjians as $sesi)
                                            <option value="{{ $sesi->id }}"
                                                {{ request('sesi_id') == $sesi->id ? 'selected' : '' }}>
                                                {{ $sesi->nama_sesi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Status Enrollment</label>
                                    <select id="statusFilter" class="form-select form-select-sm">
                                        <option value="">Semua Status</option>
                                        <option value="enrolled" {{ request('status') == 'enrolled' ? 'selected' : '' }}>
                                            Terdaftar</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                            Selesai</option>
                                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak
                                            Hadir</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                            Dibatalkan</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Status Kehadiran</label>
                                    <select id="kehadiranFilter" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        <option value="belum_hadir"
                                            {{ request('kehadiran') == 'belum_hadir' ? 'selected' : '' }}>Belum Hadir
                                        </option>
                                        <option value="hadir" {{ request('kehadiran') == 'hadir' ? 'selected' : '' }}>
                                            Hadir</option>
                                        <option value="tidak_hadir"
                                            {{ request('kehadiran') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button id="applyFilter" class="btn btn-primary btn-sm w-100">Terapkan Filter</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Jadwal Ujian</th>
                                        <th>Sesi</th>
                                        <th>Token</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($enrollments as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->siswa->nis ?? 'N/A' }}</td>
                                            <td>{{ $enrollment->siswa->nama ?? 'N/A' }}</td>
                                            <td>{{ $enrollment->siswa->kelas->nama ?? 'N/A' }}</td>
                                            <td>{{ $enrollment->sesiUjian->jadwalUjian->judul ?? 'N/A' }}</td>
                                            <td>{{ $enrollment->sesiUjian->nama_sesi ?? 'N/A' }}</td>
                                            <td>
                                                @if (
                                                    $enrollment->token_login &&
                                                        (!$enrollment->token_digunakan_pada || $enrollment->token_digunakan_pada > now()->subHours(2)))
                                                    <span class="badge bg-success">{{ $enrollment->token_login }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($enrollment->status_enrollment == 'enrolled')
                                                    @if ($enrollment->status_kehadiran == 'belum_hadir')
                                                        <span class="badge bg-warning text-dark">Belum Hadir</span>
                                                    @elseif($enrollment->status_kehadiran == 'hadir')
                                                        <span class="badge bg-success">Hadir</span>
                                                    @else
                                                        <span class="badge bg-danger">Tidak Hadir</span>
                                                    @endif
                                                @elseif($enrollment->status_enrollment == 'completed')
                                                    <span class="badge bg-info">Selesai</span>
                                                @elseif($enrollment->status_enrollment == 'absent')
                                                    <span class="badge bg-danger">Absen</span>
                                                @else
                                                    <span class="badge bg-secondary">Dibatalkan</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('naskah.enrollment-ujian.show', $enrollment->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('naskah.enrollment-ujian.edit', $enrollment->id) }}"
                                                        class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus pendaftaran ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada data enrollment</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $enrollments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Enrollment Modal -->
    <div class="modal fade" id="bulkEnrollmentModal" tabindex="-1" aria-labelledby="bulkEnrollmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkEnrollmentModalLabel">Enrollment Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('naskah.enrollment-ujian.bulk') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jadwal Ujian</label>
                            <select name="jadwal_id" id="jadwalSelect" class="form-select" required>
                                <option value="">Pilih Jadwal Ujian</option>
                                @foreach ($jadwalUjians as $jadwal)
                                    <option value="{{ $jadwal->id }}">{{ $jadwal->judul }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sesi Ujian</label>
                            <select name="sesi_ujian_id" id="sesiSelect" class="form-select" required disabled>
                                <option value="">Pilih Sesi Ujian</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <div class="border p-3 rounded">
                                <div class="row">
                                    @foreach ($kelasList->chunk(3) as $chunk)
                                        <div class="col-md-4">
                                            @foreach ($chunk as $kelas)
                                                <div class="form-check">
                                                    <input class="form-check-input kelas-checkbox" type="checkbox"
                                                        name="kelas_ids[]" value="{{ $kelas->id }}"
                                                        id="kelas{{ $kelas->id }}">
                                                    <label class="form-check-label" for="kelas{{ $kelas->id }}">
                                                        {{ $kelas->nama }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Daftarkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Filter application
            $('#applyFilter').click(function() {
                let url = new URL(window.location);

                let jadwalId = $('#jadwalFilter').val();
                let sesiId = $('#sesiFilter').val();
                let status = $('#statusFilter').val();
                let kehadiran = $('#kehadiranFilter').val();

                if (jadwalId) {
                    url.searchParams.set('jadwal_id', jadwalId);
                } else {
                    url.searchParams.delete('jadwal_id');
                }

                if (sesiId) {
                    url.searchParams.set('sesi_id', sesiId);
                } else {
                    url.searchParams.delete('sesi_id');
                }

                if (status) {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }

                if (kehadiran) {
                    url.searchParams.set('kehadiran', kehadiran);
                } else {
                    url.searchParams.delete('kehadiran');
                }

                window.location = url.toString();
            });

            // Jadwal and Sesi relationship for modal
            $('#jadwalSelect').change(function() {
                let jadwalId = $(this).val();
                let sesiSelect = $('#sesiSelect');

                sesiSelect.empty().prop('disabled', true);
                sesiSelect.append('<option value="">Pilih Sesi Ujian</option>');

                if (jadwalId) {
                    $.ajax({
                        url: '{{ route('naskah.enrollment-ujian.get-sesi-options') }}',
                        type: 'GET',
                        data: {
                            jadwal_id: jadwalId
                        },
                        success: function(response) {
                            if (response && response.length > 0) {
                                $.each(response, function(index, item) {
                                    sesiSelect.append('<option value="' + item.id +
                                        '">' + item.text + '</option>');
                                });
                                sesiSelect.prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading sesi options', xhr);
                            toastr.error('Gagal memuat sesi ujian');
                        }
                    });
                }
            });
        });
    </script>
@endpush
