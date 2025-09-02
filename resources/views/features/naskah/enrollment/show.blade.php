@extends('layouts.admin')

@section('title', 'Siswa Terdaftar - ' . $mapel->nama_mapel)

@section('content_header')
    <h1>Siswa Terdaftar - {{ $mapel->nama_mapel }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Mata Pelajaran</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Kode Mapel</th>
                            <td>{{ $mapel->kode_mapel }}</td>
                        </tr>
                        <tr>
                            <th>Nama Mapel</th>
                            <td>{{ $mapel->nama_mapel }}</td>
                        </tr>
                        <tr>
                            <th>Tingkat</th>
                            <td>{{ $mapel->tingkat ?: 'Semua' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Jurusan</th>
                            <td>{{ $mapel->jurusan ?: 'Semua' }}</td>
                        </tr>
                        <tr>
                            <th>Siswa Terdaftar</th>
                            <td>{{ $siswaEnrolled->total() }} Siswa</td>
                        </tr>
                        <tr>
                            <th>KKM</th>
                            <td>{{ $mapel->kkm ?: 'Belum ditentukan' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Siswa Terdaftar</h3>
            <div class="card-tools">
                <a href="{{ route('naskah.enrollment.create', $mapel) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus"></i> Tambah Siswa
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIS/NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($siswaEnrolled as $index => $siswa)
                        <tr>
                            <td>{{ $index + $siswaEnrolled->firstItem() }}</td>
                            <td>{{ $siswa->nis }} / {{ $siswa->nisn }}</td>
                            <td>{{ $siswa->nama_lengkap }}</td>
                            <td>{{ optional($siswa->kelas)->nama_kelas ?: 'Tidak ada kelas' }}</td>
                            <td>
                                @if ($siswa->pivot->status_enrollment == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @elseif($siswa->pivot->status_enrollment == 'tidak_aktif')
                                    <span class="badge badge-warning">Tidak Aktif</span>
                                @elseif($siswa->pivot->status_enrollment == 'lulus')
                                    <span class="badge badge-info">Lulus</span>
                                @endif
                            </td>
                            <td>{{ $siswa->pivot->tanggal_daftar ? date('d/m/Y', strtotime($siswa->pivot->tanggal_daftar)) : '-' }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-warning dropdown-toggle"
                                        data-toggle="dropdown">
                                        <i class="fas fa-cog"></i> Status
                                    </button>
                                    <div class="dropdown-menu">
                                        <form action="{{ route('naskah.enrollment.update-status', [$mapel, $siswa]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="aktif">
                                            <button type="submit" class="dropdown-item">Aktif</button>
                                        </form>
                                        <form action="{{ route('naskah.enrollment.update-status', [$mapel, $siswa]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="tidak_aktif">
                                            <button type="submit" class="dropdown-item">Tidak Aktif</button>
                                        </form>
                                        <form action="{{ route('naskah.enrollment.update-status', [$mapel, $siswa]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="lulus">
                                            <button type="submit" class="dropdown-item">Lulus</button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('naskah.enrollment.destroy', [$mapel, $siswa]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin mengeluarkan siswa dari mata pelajaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash"></i> Keluarkan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada siswa yang terdaftar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $siswaEnrolled->links() }}
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('naskah.enrollment.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Mapel
        </a>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Add any custom JavaScript here
        });
    </script>
@stop
