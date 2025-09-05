@extends('layouts.app')

@section('title', 'Edit Enrollment Ujian')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Edit Enrollment Ujian</h1>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-edit me-1"></i>
                Form Edit Enrollment Ujian
            </div>
            <div class="card-body">
                <form action="{{ route('naskah.enrollment-ujian.update', $enrollment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jadwal_ujian_id" class="form-label">Jadwal Ujian <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    value="{{ $enrollment->sesiRuangan->jadwalUjian->judul }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sesi_ujian_id" class="form-label">Sesi Ujian <span
                                        class="text-danger">*</span></label>
                                <select name="sesi_ujian_id" id="sesi_ujian_id"
                                    class="form-select @error('sesi_ujian_id') is-invalid @enderror" required>
                                    <option value="">Pilih Sesi Ujian</option>
                                    @foreach ($sesiRuangans as $sesi)
                                        <option value="{{ $sesi->id }}"
                                            {{ old('sesi_ujian_id', $enrollment->sesi_ujian_id) == $sesi->id ? 'selected' : '' }}>
                                            {{ $sesi->nama_sesi }} ({{ $sesi->waktu_mulai->format('d M Y H:i') }} -
                                            {{ $sesi->waktu_selesai->format('d M Y H:i') }})
                                        </option>
                                    @endforeach
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
                                <label class="form-label">NIS</label>
                                <input type="text" class="form-control" value="{{ $enrollment->siswa->nis }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Siswa</label>
                                <input type="text" class="form-control" value="{{ $enrollment->siswa->nama }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status_enrollment" class="form-label">Status Enrollment <span
                                        class="text-danger">*</span></label>
                                <select name="status_enrollment" id="status_enrollment"
                                    class="form-select @error('status_enrollment') is-invalid @enderror" required>
                                    <option value="enrolled"
                                        {{ old('status_enrollment', $enrollment->status_enrollment) == 'enrolled' ? 'selected' : '' }}>
                                        Terdaftar</option>
                                    <option value="completed"
                                        {{ old('status_enrollment', $enrollment->status_enrollment) == 'completed' ? 'selected' : '' }}>
                                        Selesai</option>
                                    <option value="absent"
                                        {{ old('status_enrollment', $enrollment->status_enrollment) == 'absent' ? 'selected' : '' }}>
                                        Tidak Hadir</option>
                                    <option value="cancelled"
                                        {{ old('status_enrollment', $enrollment->status_enrollment) == 'cancelled' ? 'selected' : '' }}>
                                        Dibatalkan</option>
                                </select>
                                @error('status_enrollment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status_kehadiran" class="form-label">Status Kehadiran <span
                                        class="text-danger">*</span></label>
                                <select name="status_kehadiran" id="status_kehadiran"
                                    class="form-select @error('status_kehadiran') is-invalid @enderror" required>
                                    <option value="belum_hadir"
                                        {{ old('status_kehadiran', $enrollment->status_kehadiran) == 'belum_hadir' ? 'selected' : '' }}>
                                        Belum Hadir</option>
                                    <option value="hadir"
                                        {{ old('status_kehadiran', $enrollment->status_kehadiran) == 'hadir' ? 'selected' : '' }}>
                                        Hadir</option>
                                    <option value="tidak_hadir"
                                        {{ old('status_kehadiran', $enrollment->status_kehadiran) == 'tidak_hadir' ? 'selected' : '' }}>
                                        Tidak Hadir</option>
                                </select>
                                @error('status_kehadiran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $enrollment->catatan) }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('naskah.enrollment-ujian.show', $enrollment->id) }}"
                            class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Zona Berbahaya
            </div>
            <div class="card-body">
                <h5>Reset Token Login</h5>
                <p>Gunakan tombol di bawah untuk mereset token login siswa. Token baru akan dibuat dan token lama akan tidak
                    berlaku.</p>
                <form action="{{ route('naskah.enrollment-ujian.generate-token', $enrollment->id) }}" method="POST"
                    class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('Apakah Anda yakin ingin mereset token login?')">
                        <i class="fas fa-sync-alt me-1"></i> Reset Token Login
                    </button>
                </form>

                <hr>

                <h5>Hapus Enrollment</h5>
                <p>Hapus enrollment ini dari sistem. Tindakan ini tidak dapat dibatalkan!</p>
                <form action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('PERINGATAN: Semua data enrollment akan dihapus dan tidak dapat dipulihkan. Lanjutkan?')">
                        <i class="fas fa-trash me-1"></i> Hapus Enrollment
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
