@extends('layouts.app')

@section('title', 'Tambah Enrollment Ujian')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Tambah Enrollment Ujian</h1>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-plus me-1"></i>
                Form Tambah Enrollment Ujian
            </div>
            <div class="card-body">
                <form action="{{ route('naskah.enrollment-ujian.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jadwal_ujian_id" class="form-label">Jadwal Ujian <span
                                        class="text-danger">*</span></label>
                                <select name="jadwal_ujian_id" id="jadwal_ujian_id"
                                    class="form-select @error('jadwal_ujian_id') is-invalid @enderror" required>
                                    <option value="">Pilih Jadwal Ujian</option>
                                    @foreach ($jadwalUjians as $jadwal)
                                        <option value="{{ $jadwal->id }}"
                                            {{ old('jadwal_ujian_id') == $jadwal->id ? 'selected' : '' }}>
                                            {{ $jadwal->judul }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jadwal_ujian_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sesi_ujian_id" class="form-label">Sesi Ujian <span
                                        class="text-danger">*</span></label>
                                <select name="sesi_ujian_id" id="sesi_ujian_id"
                                    class="form-select @error('sesi_ujian_id') is-invalid @enderror" required disabled>
                                    <option value="">Pilih Sesi Ujian</option>
                                    @if (old('jadwal_ujian_id') && old('sesi_ujian_id'))
                                        <option value="{{ old('sesi_ujian_id') }}" selected>
                                            {{ App\Models\SesiUjian::find(old('sesi_ujian_id'))->nama_sesi }}</option>
                                    @endif
                                </select>
                                @error('sesi_ujian_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kelas_id" class="form-label">Kelas Siswa</label>
                                <select name="kelas_id" id="kelas_id"
                                    class="form-select @error('kelas_id') is-invalid @enderror">
                                    <option value="">Pilih Kelas Siswa</option>
                                    @foreach ($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}"
                                            {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih kelas untuk mempermudah pencarian siswa</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="siswa_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                <select name="siswa_id" id="siswa_id"
                                    class="form-select @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">Pilih Siswa</option>
                                    @if (old('siswa_id'))
                                        <option value="{{ old('siswa_id') }}" selected>
                                            {{ App\Models\Siswa::find(old('siswa_id'))->nama }}
                                            ({{ App\Models\Siswa::find(old('siswa_id'))->nis }})
                                        </option>
                                    @endif
                                </select>
                                @error('siswa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('naskah.enrollment-ujian.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('#siswa_id').select2({
                placeholder: 'Cari siswa berdasarkan nama atau NIS',
                allowClear: true,
                ajax: {
                    url: '{{ route('naskah.enrollment-ujian.get-siswa-options') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            kelas_id: $('#kelas_id').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });

            // Jadwal and Sesi relationship
            $('#jadwal_ujian_id').change(function() {
                let jadwalId = $(this).val();
                let sesiSelect = $('#sesi_ujian_id');

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

            // Trigger change if value already selected (for edit form)
            if ($('#jadwal_ujian_id').val()) {
                $('#jadwal_ujian_id').trigger('change');
            }

            // Kelas filtering for siswa
            $('#kelas_id').change(function() {
                $('#siswa_id').val(null).trigger('change');
            });
        });
    </script>
@endpush
