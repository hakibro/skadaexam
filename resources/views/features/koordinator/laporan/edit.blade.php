@extends('layouts.admin')

@section('title', 'Edit Berita Acara')
@section('page-title', 'Edit Berita Acara Ujian')
@section('page-description', 'Edit berita acara untuk sesi: ' . $beritaAcara->sesiRuangan->nama_sesi)

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div class="flex items-center">
            <a href="{{ route('koordinator.laporan.show', $beritaAcara->id) }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Kembali ke Detail
            </a>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit Berita Acara</h3>
            
            <form method="POST" action="{{ route('koordinator.laporan.update', $beritaAcara->id) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <!-- Catatan Pembukaan -->
                    <div>
                        <label for="catatan_pembukaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Pembukaan
                        </label>
                        <textarea id="catatan_pembukaan" name="catatan_pembukaan" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Catatan persiapan dan pembukaan ujian">{{ old('catatan_pembukaan', $beritaAcara->catatan_pembukaan) }}</textarea>
                        @error('catatan_pembukaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Pelaksanaan -->
                    <div>
                        <label for="catatan_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Pelaksanaan
                        </label>
                        <textarea id="catatan_pelaksanaan" name="catatan_pelaksanaan" rows="4"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Catatan jalannya ujian, kendala teknis, dll.">{{ old('catatan_pelaksanaan', $beritaAcara->catatan_pelaksanaan) }}</textarea>
                        @error('catatan_pelaksanaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Penutupan -->
                    <div>
                        <label for="catatan_penutupan" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Penutupan
                        </label>
                        <textarea id="catatan_penutupan" name="catatan_penutupan" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Catatan penutupan ujian">{{ old('catatan_penutupan', $beritaAcara->catatan_penutupan) }}</textarea>
                        @error('catatan_penutupan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Pelaksanaan -->
                    <div>
                        <label for="status_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Pelaksanaan
                        </label>
                        <select id="status_pelaksanaan" name="status_pelaksanaan" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Pilih Status</option>
                            <option value="lancar" {{ old('status_pelaksanaan', $beritaAcara->status_pelaksanaan) == 'lancar' ? 'selected' : '' }}>Lancar</option>
                            <option value="terganggu" {{ old('status_pelaksanaan', $beritaAcara->status_pelaksanaan) == 'terganggu' ? 'selected' : '' }}>Terganggu</option>
                            <option value="dibatalkan" {{ old('status_pelaksanaan', $beritaAcara->status_pelaksanaan) == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        @error('status_pelaksanaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('koordinator.laporan.show', $beritaAcara->id) }}"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                        <i class="fa-solid fa-save mr-1"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Session Info (Read Only) -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-md font-medium text-gray-800 mb-4">Informasi Sesi (Read Only)</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Sesi:</span>
                    <span class="ml-2 font-medium">{{ $beritaAcara->sesiRuangan->nama_sesi }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Ruangan:</span>
                    <span class="ml-2 font-medium">{{ $beritaAcara->sesiRuangan->ruangan->nama }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Pengawas:</span>
                    <span class="ml-2 font-medium">{{ $beritaAcara->pengawas->nama }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
