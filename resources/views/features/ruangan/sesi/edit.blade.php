{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\sesi\edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Sesi - ' . $sesi->nama_sesi)
@section('page-title', 'Edit Sesi')
@section('page-description', $sesi->nama_sesi . ' - ' . $ruangan->nama_ruangan)

@section('content')
    <div class="max-w-3xl mx-auto py-4">
        <!-- Flash Messages -->
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('ruangan.sesi.update', [$ruangan->id, $sesi->id]) }}" method="POST"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Edit Sesi Ruangan</h2>

                <!-- Nama Sesi -->
                <div>
                    <label for="nama_sesi" class="block text-sm font-medium text-gray-700">
                        Nama Sesi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_sesi" id="nama_sesi"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('nama_sesi', $sesi->nama_sesi) }}" required>
                    @error('nama_sesi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal -->
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" id="tanggal"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('tanggal', $sesi->tanggal->format('Y-m-d')) }}" required>
                    @error('tanggal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Waktu Mulai -->
                    <div>
                        <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu_mulai" id="waktu_mulai"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('waktu_mulai', \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i')) }}"
                            required>
                        @error('waktu_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">
                            Waktu Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu_selesai" id="waktu_selesai"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('waktu_selesai', \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i')) }}"
                            required>
                        @error('waktu_selesai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pengawas -->
                <div>
                    <label for="pengawas_id" class="block text-sm font-medium text-gray-700">
                        Pengawas
                    </label>
                    <select name="pengawas_id" id="pengawas_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Pengawas (Opsional)</option>
                        @foreach ($pengawasList as $pengawas)
                            <option value="{{ $pengawas->id }}"
                                {{ old('pengawas_id', $sesi->pengawas_id) == $pengawas->id ? 'selected' : '' }}>
                                {{ $pengawas->nama }} - {{ $pengawas->nip }}
                            </option>
                        @endforeach
                    </select>
                    @error('pengawas_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="belum_mulai" {{ old('status', $sesi->status) == 'belum_mulai' ? 'selected' : '' }}>
                            Belum Mulai</option>
                        <option value="berlangsung" {{ old('status', $sesi->status) == 'berlangsung' ? 'selected' : '' }}>
                            Berlangsung</option>
                        <option value="selesai" {{ old('status', $sesi->status) == 'selesai' ? 'selected' : '' }}>Selesai
                        </option>
                        <option value="dibatalkan" {{ old('status', $sesi->status) == 'dibatalkan' ? 'selected' : '' }}>
                            Dibatalkan</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan', $sesi->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Tambahan -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Informasi Tambahan</h3>
                    <div class="text-sm text-gray-600 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Kode Sesi:</strong> {{ $sesi->kode_sesi }}</p>
                            <p><strong>Ruangan:</strong> {{ $ruangan->nama_ruangan }}</p>
                            <p><strong>Kapasitas:</strong> {{ $ruangan->kapasitas }} orang</p>
                        </div>
                        <div>
                            <p><strong>Jumlah Siswa:</strong> {{ $sesi->siswa_count }}</p>
                            <p><strong>Dibuat:</strong> {{ $sesi->created_at->format('d M Y H:i') }}</p>
                            <p><strong>Diubah:</strong> {{ $sesi->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
