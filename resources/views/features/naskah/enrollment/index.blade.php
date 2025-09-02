@extends('layouts.admin')

@section('title', 'Enrollment Mata Pelajaran')

@section('content_header')
    <h1>Enrollment Mata Pelajaran</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Mata Pelajaran</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm">
                    <form class="form-inline" action="{{ route('naskah.enrollment.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari mata pelajaran..."
                                value="{{ request('search') }}">
                            <select name="tingkat" class="form-control ml-2">
                                <option value="">-- Semua Tingkat --</option>
                                @foreach ($tingkatList as $t)
                                    <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>
                                        {{ $t }}</option>
                                @endforeach
                            </select>
                            <select name="jurusan" class="form-control ml-2">
                                <option value="">-- Semua Jurusan --</option>
                                @foreach ($jurusanList as $j)
                                    <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>
                                        {{ $j }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mapel</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Siswa Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mapels as $mapel)
                        <tr>
                            <td>{{ $mapel->kode_mapel }}</td>
                            <td>{{ $mapel->nama_mapel }}</td>
                            <td>{{ $mapel->tingkat ?: 'Semua' }}</td>
                            <td>{{ $mapel->jurusan ?: 'Semua' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $mapel->enrolled_students_count ?? 0 }} Siswa</span>
                            </td>
                            <td>
                                <a href="{{ route('naskah.enrollment.show', $mapel) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                                <a href="{{ route('naskah.enrollment.create', $mapel) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-user-plus"></i> Tambah Siswa
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
