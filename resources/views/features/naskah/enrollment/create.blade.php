@extends('layouts.admin')

@section('title', 'Tambah Siswa - ' . $mapel->nama_mapel)

@section('content_header')
    <h1>Tambah Siswa - {{ $mapel->nama_mapel }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pilih Kelas</h3>
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
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="kelas_id">Pilih Kelas:</label>
                        <select id="kelas_id" class="form-control" name="kelas_id">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="enrollmentForm" action="{{ route('naskah.enrollment.store', $mapel) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pilih Siswa</h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" id="selectAll" class="btn btn-sm btn-primary">
                            <i class="fas fa-check-square"></i> Pilih Semua
                        </button>
                        <button type="button" id="deselectAll" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-square"></i> Batal Pilih Semua
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="siswaList">
                    <div class="text-center p-5">
                        <i class="fas fa-info-circle fa-2x mb-3 text-info"></i>
                        <p>Silahkan pilih kelas terlebih dahulu untuk melihat daftar siswa</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" id="enrollBtn" disabled>
                    <i class="fas fa-save"></i> Daftarkan Siswa
                </button>
                <a href="{{ route('naskah.enrollment.show', $mapel) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const mapelId = {{ $mapel->id }};

            $('#kelas_id').change(function() {
                const kelasId = $(this).val();
                if (kelasId) {
                    $('#siswaList').html(
                        '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><p>Memuat data siswa...</p></div>'
                        );

                    $.ajax({
                        url: "{{ route('naskah.enrollment.get-siswa') }}",
                        type: 'GET',
                        data: {
                            kelas_id: kelasId,
                            mapel_id: mapelId
                        },
                        success: function(response) {
                            if (response.success) {
                                let html = '';

                                if (response.data.length > 0) {
                                    html += '<div class="form-group">';
                                    html += '<div class="table-responsive">';
                                    html += '<table class="table table-bordered table-hover">';
                                    html += '<thead><tr>';
                                    html +=
                                        '<th style="width: 50px;"><input type="checkbox" id="checkAll"></th>';
                                    html += '<th>NIS</th>';
                                    html += '<th>Nama Siswa</th>';
                                    html += '</tr></thead>';
                                    html += '<tbody>';

                                    response.data.forEach(function(siswa) {
                                        html += '<tr>';
                                        html +=
                                            '<td><input type="checkbox" name="siswa_ids[]" value="' +
                                            siswa.id + '" class="siswa-checkbox"></td>';
                                        html += '<td>' + siswa.nis + '</td>';
                                        html += '<td>' + siswa.nama_lengkap + '</td>';
                                        html += '</tr>';
                                    });

                                    html += '</tbody></table></div></div>';
                                    $('#enrollBtn').prop('disabled', false);
                                } else {
                                    html = '<div class="alert alert-info">';
                                    html += '<i class="icon fas fa-info-circle"></i> ';
                                    html +=
                                        'Tidak ada siswa yang tersedia untuk di-enroll (Semua siswa di kelas ini mungkin sudah terdaftar)';
                                    html += '</div>';
                                    $('#enrollBtn').prop('disabled', true);
                                }

                                $('#siswaList').html(html);

                                // Check all functionality
                                $('#checkAll').click(function() {
                                    $('.siswa-checkbox').prop('checked', this.checked);
                                    updateEnrollButtonState();
                                });

                                $(document).on('change', '.siswa-checkbox', function() {
                                    updateEnrollButtonState();
                                    if ($('.siswa-checkbox:checked').length === $(
                                            '.siswa-checkbox').length) {
                                        $('#checkAll').prop('checked', true);
                                    } else {
                                        $('#checkAll').prop('checked', false);
                                    }
                                });

                                // Select all / Deselect all buttons
                                $('#selectAll').click(function() {
                                    $('.siswa-checkbox').prop('checked', true);
                                    $('#checkAll').prop('checked', true);
                                    updateEnrollButtonState();
                                });

                                $('#deselectAll').click(function() {
                                    $('.siswa-checkbox').prop('checked', false);
                                    $('#checkAll').prop('checked', false);
                                    updateEnrollButtonState();
                                });
                            } else {
                                $('#siswaList').html('<div class="alert alert-danger">Error: ' +
                                    response.message + '</div>');
                                $('#enrollBtn').prop('disabled', true);
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#siswaList').html(
                                '<div class="alert alert-danger">Terjadi kesalahan saat memuat data siswa.</div>'
                                );
                            $('#enrollBtn').prop('disabled', true);
                        }
                    });
                } else {
                    $('#siswaList').html(
                        '<div class="text-center p-5"><i class="fas fa-info-circle fa-2x mb-3 text-info"></i><p>Silahkan pilih kelas terlebih dahulu untuk melihat daftar siswa</p></div>'
                        );
                    $('#enrollBtn').prop('disabled', true);
                }
            });

            function updateEnrollButtonState() {
                if ($('.siswa-checkbox:checked').length > 0) {
                    $('#enrollBtn').prop('disabled', false);
                } else {
                    $('#enrollBtn').prop('disabled', true);
                }
            }

            // Form submit validation
            $('#enrollmentForm').submit(function(e) {
                if ($('.siswa-checkbox:checked').length === 0) {
                    e.preventDefault();
                    alert('Silahkan pilih minimal 1 siswa untuk di-enroll');
                    return false;
                }
                return true;
            });
        });
    </script>
@stop
