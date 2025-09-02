@extends('layouts.app')

@section('title', 'Detail Enrollment Ujian')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Detail Enrollment Ujian</h1>

        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Siswa</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <th style="width: 30%">NIS</th>
                                <td>{{ $enrollment->siswa->nis }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $enrollment->siswa->nama }}</td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td>{{ $enrollment->siswa->kelas->nama ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $enrollment->siswa->email ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Ujian</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <th style="width: 30%">Jadwal</th>
                                <td>{{ $enrollment->sesiUjian->jadwalUjian->judul }}</td>
                            </tr>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <td>{{ $enrollment->sesiUjian->jadwalUjian->mapel->nama ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Sesi</th>
                                <td>{{ $enrollment->sesiUjian->nama_sesi }}</td>
                            </tr>
                            <tr>
                                <th>Waktu Mulai</th>
                                <td>{{ $enrollment->sesiUjian->waktu_mulai->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Waktu Selesai</th>
                                <td>{{ $enrollment->sesiUjian->waktu_selesai->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Durasi</th>
                                <td>{{ $enrollment->sesiUjian->durasi_ujian }} menit</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Enrollment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 30%">Status Enrollment</th>
                                        <td>
                                            @if ($enrollment->status_enrollment == 'enrolled')
                                                <span class="badge bg-primary">Terdaftar</span>
                                            @elseif($enrollment->status_enrollment == 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif($enrollment->status_enrollment == 'absent')
                                                <span class="badge bg-danger">Tidak Hadir</span>
                                            @elseif($enrollment->status_enrollment == 'cancelled')
                                                <span class="badge bg-secondary">Dibatalkan</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status Kehadiran</th>
                                        <td>
                                            @if ($enrollment->status_kehadiran == 'belum_hadir')
                                                <span class="badge bg-warning text-dark">Belum Hadir</span>
                                            @elseif($enrollment->status_kehadiran == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($enrollment->status_kehadiran == 'tidak_hadir')
                                                <span class="badge bg-danger">Tidak Hadir</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Login</th>
                                        <td>{{ $enrollment->last_login_at ? $enrollment->last_login_at->format('d M Y H:i:s') : 'Belum Login' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Logout</th>
                                        <td>{{ $enrollment->last_logout_at ? $enrollment->last_logout_at->format('d M Y H:i:s') : 'Belum Logout' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 30%">Token Login</th>
                                        <td>
                                            @if (
                                                $enrollment->token_login &&
                                                    (!$enrollment->token_digunakan_pada || $enrollment->token_digunakan_pada > now()->subHours(2)))
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="badge bg-success me-2">{{ $enrollment->token_login }}</span>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="copyToken('{{ $enrollment->token_login }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Tidak ada token aktif</span>
                                                <form
                                                    action="{{ route('naskah.enrollment-ujian.generate-token', $enrollment->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary ms-2">Generate
                                                        Token</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Token Dibuat</th>
                                        <td>{{ $enrollment->token_dibuat_pada ? $enrollment->token_dibuat_pada->format('d M Y H:i:s') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Token Digunakan</th>
                                        <td>{{ $enrollment->token_digunakan_pada ? $enrollment->token_digunakan_pada->format('d M Y H:i:s') : 'Belum digunakan' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Catatan</th>
                                        <td>{{ $enrollment->catatan ?: 'Tidak ada catatan' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($enrollment->hasilUjian)
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Hasil Ujian</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <tr>
                                    <th style="width: 30%">Nilai</th>
                                    <td>{{ $enrollment->hasilUjian->nilai ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Benar</th>
                                    <td>{{ $enrollment->hasilUjian->jumlah_benar ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Salah</th>
                                    <td>{{ $enrollment->hasilUjian->jumlah_salah ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Tidak Dijawab</th>
                                    <td>{{ $enrollment->hasilUjian->jumlah_tidak_dijawab ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Waktu Mulai Mengerjakan</th>
                                    <td>{{ $enrollment->hasilUjian->waktu_mulai ? $enrollment->hasilUjian->waktu_mulai->format('d M Y H:i:s') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Waktu Selesai Mengerjakan</th>
                                    <td>{{ $enrollment->hasilUjian->waktu_selesai ? $enrollment->hasilUjian->waktu_selesai->format('d M Y H:i:s') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Durasi Mengerjakan</th>
                                    <td>
                                        @if ($enrollment->hasilUjian->waktu_mulai && $enrollment->hasilUjian->waktu_selesai)
                                            {{ $enrollment->hasilUjian->waktu_mulai->diffInMinutes($enrollment->hasilUjian->waktu_selesai) }}
                                            menit
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between mb-4">
            <div>
                <a href="{{ route('naskah.enrollment-ujian.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('naskah.enrollment-ujian.edit', $enrollment->id) }}" class="btn btn-warning">Edit</a>
            </div>

            <div>
                <div class="btn-group" role="group">
                    @if ($enrollment->status_enrollment == 'enrolled')
                        <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'completed']) }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Tandai ujian ini sebagai selesai?')">
                                <i class="fas fa-check me-1"></i> Tandai Selesai
                            </button>
                        </form>

                        <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'absent']) }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger ms-2"
                                onclick="return confirm('Tandai siswa ini sebagai tidak hadir?')">
                                <i class="fas fa-times me-1"></i> Tandai Tidak Hadir
                            </button>
                        </form>
                    @endif

                    @if ($enrollment->status_enrollment != 'enrolled')
                        <form action="{{ route('naskah.enrollment-ujian.update-status', [$enrollment->id, 'enrolled']) }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Kembalikan status ujian ini ke terdaftar?')">
                                <i class="fas fa-undo me-1"></i> Kembalikan ke Terdaftar
                            </button>
                        </form>
                    @endif
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
    </script>
@endpush
