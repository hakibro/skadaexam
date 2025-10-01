@extends('layouts.admin')

@section('title', 'Edit Enrollment Ujian')
@section('page-title', 'Edit Enrollment Ujian')
@section('page-description', 'Ubah data pendaftaran siswa pada ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fa-solid fa-edit mr-2"></i>Form Edit Enrollment Ujian
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('naskah.enrollment-ujian.update', $enrollment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="jadwal_ujian_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Jadwal Ujian <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="form-input w-full rounded-md shadow-sm bg-gray-50"
                                value="{{ $enrollment->jadwalUjian->judul ?? ($enrollment->sesiRuangan->jadwalUjians->first()?->judul ?? 'N/A') }}"
                                readonly>
                        </div>

                        <div>
                            <label for="sesi_ruangan_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Sesi Ujian <span class="text-red-500">*</span>
                            </label>
                            <select name="sesi_ruangan_id" id="sesi_ruangan_id"
                                class="form-select w-full rounded-md shadow-sm @error('sesi_ruangan_id') border-red-500 @enderror"
                                required>
                                <option value="">Pilih Sesi Ujian</option>
                                @foreach ($sesiRuangans as $sesi)
                                    <option value="{{ $sesi->id }}"
                                        {{ old('sesi_ruangan_id', $enrollment->sesi_ruangan_id) == $sesi->id ? 'selected' : '' }}>
                                        {{ $sesi->nama_sesi }} ({{ $sesi->waktu_mulai }} -
                                        {{ $sesi->waktu_selesai }})
                                    </option>
                                @endforeach
                            </select>
                            @error('sesi_ruangan_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Yayasan</label>
                            <input type="text" class="form-input w-full rounded-md shadow-sm bg-gray-50"
                                value="{{ $enrollment->siswa->idyayasan }}" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Siswa</label>
                            <input type="text" class="form-input w-full rounded-md shadow-sm bg-gray-50"
                                value="{{ $enrollment->siswa->nama }}" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="status_enrollment" class="block text-sm font-medium text-gray-700 mb-1">
                                Status Enrollment <span class="text-red-500">*</span>
                            </label>
                            <select name="status_enrollment" id="status_enrollment"
                                class="form-select w-full rounded-md shadow-sm @error('status_enrollment') border-red-500 @enderror"
                                required>
                                <option value="enrolled"
                                    {{ old('status_enrollment', $enrollment->status_enrollment) == 'enrolled' ? 'selected' : '' }}>
                                    Terdaftar</option>
                                <option value="active"
                                    {{ old('status_enrollment', $enrollment->status_enrollment) == 'active' ? 'selected' : '' }}>
                                    Aktif</option>
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
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Kehadiran (Read Only)</label>
                            <input type="text" class="form-input w-full rounded-md shadow-sm bg-gray-50"
                                value="{{ match ($enrollment->sesiRuanganSiswa?->status_kehadiran ?? 'belum_hadir') {
                                    'belum_hadir' => 'Belum Hadir',
                                    'hadir' => 'Hadir',
                                    'tidak_hadir' => 'Tidak Hadir',
                                    'sakit' => 'Sakit',
                                    'izin' => 'Izin',
                                    default => 'Tidak Diketahui',
                                } }}"
                                readonly>
                            <p class="mt-1 text-sm text-gray-500">Status kehadiran dikelola melalui sistem sesi ruangan</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan" id="catatan"
                            class="form-textarea w-full rounded-md shadow-sm @error('catatan') border-red-500 @enderror" rows="3">{{ old('catatan', $enrollment->catatan) }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('naskah.enrollment-ujian.show', $enrollment->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden mt-6">
            <div class="bg-red-600 text-white p-4 border-b">
                <h3 class="text-lg font-medium">
                    <i class="fa-solid fa-exclamation-triangle mr-2"></i>Zona Berbahaya
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Reset Token Login</h4>
                    <p class="text-gray-600 mb-3">Gunakan tombol di bawah untuk mereset token login siswa. Token baru akan
                        dibuat dan token lama akan tidak
                        berlaku.</p>
                    <form action="{{ route('naskah.enrollment-ujian.generate-token', $enrollment->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-white border border-red-600 text-red-600 hover:bg-red-50 rounded-md transition duration-150"
                            onclick="return confirm('Apakah Anda yakin ingin mereset token login?')">
                            <i class="fa-solid fa-sync-alt mr-2"></i> Reset Token Login
                        </button>
                    </form>
                </div>

                <hr class="my-6 border-gray-200">

                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Hapus Enrollment</h4>
                    <p class="text-gray-600 mb-3">Hapus enrollment ini dari sistem. Tindakan ini tidak dapat dibatalkan!</p>
                    <form action="{{ route('naskah.enrollment-ujian.destroy', $enrollment->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition duration-150"
                            onclick="return confirm('PERINGATAN: Semua data enrollment akan dihapus dan tidak dapat dipulihkan. Lanjutkan?')">
                            <i class="fa-solid fa-trash mr-2"></i> Hapus Enrollment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
