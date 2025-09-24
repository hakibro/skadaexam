@extends('layouts.admin')

@section('title', 'Detail Soal')
@section('page-title', 'Detail Soal')
@section('page-description', 'Informasi lengkap tentang soal')

@section('content')
    <div class="space-y-6">
        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('naskah.banksoal.show', $soal->bankSoal) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('naskah.soal.edit', $soal) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fa-solid fa-edit mr-2"></i> Edit
                </a>
                <form action="{{ route('naskah.soal.destroy', $soal) }}" method="POST" class="inline"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fa-solid fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Soal Info -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900">Informasi Soal</h3>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Bank Soal</h4>
                    <p class="text-base text-gray-900">
                        <a href="{{ route('naskah.banksoal.show', $soal->bankSoal) }}"
                            class="text-blue-600 hover:text-blue-800">
                            {{ $soal->bankSoal->judul }}
                        </a>
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Nomor Soal</h4>
                    <p class="text-base text-gray-900">{{ $soal->nomor_soal }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Tipe Soal</h4>
                    <p class="text-base text-gray-900">
                        @if ($soal->tipe_soal === 'pilihan_ganda')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Pilihan Ganda
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Essay
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Kunci Jawaban</h4>
                    <p class="text-base text-gray-900">
                        @if ($soal->tipe_soal === 'pilihan_ganda' && $soal->kunci_jawaban)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $soal->kunci_jawaban }}
                            </span>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Bobot</h4>
                    <p class="text-base text-gray-900">{{ $soal->bobot }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Kategori</h4>
                    <p class="text-base text-gray-900">{{ $soal->kategori ?? 'Tidak ada kategori' }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Dibuat pada</h4>
                    <p class="text-base text-gray-900">{{ $soal->created_at->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Terakhir diupdate</h4>
                    <p class="text-base text-gray-900">{{ $soal->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Soal Preview -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900">Preview Soal</h3>
            </div>
            <div class="px-6 py-5">
                <x-soal-card :soal="$soal" />
            </div>
        </div>
    </div>
@endsection
