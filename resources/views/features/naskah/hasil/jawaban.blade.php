@extends('layouts.admin')

@section('title', 'Detail Jawaban')
@section('page-title', 'Detail Jawaban Siswa')
@section('page-description', "{$hasilUjian->siswa->nama} - {$hasilUjian->jadwalUjian->judul}")

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.hasil.show', $hasilUjian->id) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('naskah.hasil.print', ['id' => $hasilUjian->id, 'with_answers' => true]) }}" target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-2"></i> Cetak dengan Jawaban
                </a>
            </div>
        </div>

        <!-- Exam Info -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b">
                <div class="flex flex-wrap justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Detail Jawaban</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $hasilUjian->siswa->nama }} ({{ $hasilUjian->siswa->nis }})
                        </p>
                    </div>
                    <div class="text-right">
                        <div
                            class="text-lg font-bold {{ $hasilUjian->nilai >= ($mapel->kkm ?? 75) ? 'text-green-600' : 'text-red-600' }}">
                            Nilai: {{ number_format($hasilUjian->nilai, 2) }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $hasilUjian->jawaban_benar }} benar /
                            {{ $hasilUjian->total_soal }} soal</div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-2 bg-gray-50 border-b flex justify-between items-center">
                <div>
                    <span class="text-sm text-gray-700">
                        <i class="fa-solid fa-calendar mr-1"></i> {{ $hasilUjian->created_at->format('d M Y, H:i') }} WIB
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-700">
                        <i class="fa-solid fa-book mr-1"></i> {{ $mapel->nama_mapel }}
                    </span>
                    <span class="text-sm text-gray-700">
                        <i class="fa-solid fa-users mr-1"></i> {{ $hasilUjian->siswa->kelas->nama_kelas }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Answer List -->
        <div class="space-y-6">
            @forelse($jawaban as $index => $soal)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-900 mr-2">Soal #{{ $index + 1 }}</span>
                            @if (isset($soal['kategori']) && $soal['kategori'])
                                <span
                                    class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">{{ $soal['kategori'] }}</span>
                            @endif
                        </div>
                        <div>
                            @if ($soal['status'] === 'benar')
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Benar</span>
                            @elseif($soal['status'] === 'salah')
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Salah</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak
                                    Dijawab</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Pertanyaan -->
                        <div class="prose max-w-none mb-4">
                            {!! $soal['pertanyaan'] !!}
                        </div>

                        @if (isset($soal['image']) && $soal['image'])
                            <div class="my-4">
                                <img src="{{ $soal['image'] }}" alt="Soal Image" class="max-h-64 rounded-lg">
                            </div>
                        @endif

                        <!-- Pilihan Jawaban -->
                        <div class="space-y-2 mt-4">
                            <h5 class="text-sm font-medium text-gray-700">Pilihan Jawaban:</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach ($soal['pilihan'] as $key => $pilihan)
                                    <div
                                        class="flex items-start space-x-2 p-2 rounded-md 
                                    {{ $soal['kunci'] == $key
                                        ? 'bg-green-50 border border-green-200'
                                        : ($soal['jawaban'] == $key
                                            ? 'bg-red-50 border border-red-200'
                                            : '') }}">
                                        <div class="flex-shrink-0 pt-1">
                                            @if ($soal['kunci'] == $key)
                                                <span
                                                    class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-green-100 text-green-800">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                </span>
                                            @elseif($soal['jawaban'] == $key)
                                                <span
                                                    class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-red-100 text-red-800">
                                                    <i class="fa-solid fa-times text-xs"></i>
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-100 text-gray-800">
                                                    {{ strtoupper($key) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-1 prose max-w-none text-sm">
                                            {!! $pilihan !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Jawaban Siswa & Kunci -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex flex-wrap gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Jawaban Siswa:</span>
                                    @if ($soal['jawaban'])
                                        <span
                                            class="ml-2 text-sm {{ $soal['status'] === 'benar' ? 'font-bold text-green-600' : 'font-bold text-red-600' }}">
                                            {{ strtoupper($soal['jawaban']) }}
                                        </span>
                                    @else
                                        <span class="ml-2 text-sm italic text-gray-500">Tidak dijawab</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Kunci Jawaban:</span>
                                    <span
                                        class="ml-2 text-sm font-bold text-green-600">{{ strtoupper($soal['kunci']) }}</span>
                                </div>
                                @if (isset($soal['waktu_jawab']))
                                    <div>
                                        <span class="text-sm font-medium text-gray-600">Waktu menjawab:</span>
                                        <span class="ml-2 text-sm text-gray-600">{{ $soal['waktu_jawab'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pembahasan -->
                        @if (isset($soal['pembahasan']) && $soal['pembahasan'])
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Pembahasan:</h5>
                                <div class="prose max-w-none text-sm bg-gray-50 p-3 rounded-md">
                                    {!! $soal['pembahasan'] !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-md rounded-lg overflow-hidden p-8 text-center">
                    <i class="fa-solid fa-file-circle-question text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 text-lg">Detail jawaban tidak tersedia</p>
                    <p class="text-gray-400 text-sm mt-2">Jawaban siswa mungkin belum tersedia atau telah dihapus</p>
                </div>
            @endforelse
        </div>

        <!-- Navigation -->
        <div class="flex justify-between">
            <a href="{{ route('naskah.hasil.show', $hasilUjian->id) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Detail Hasil
            </a>
            <a href="{{ route('naskah.hasil.print', ['id' => $hasilUjian->id, 'with_answers' => true]) }}" target="_blank"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fa-solid fa-print mr-2"></i> Cetak Lembar Jawaban
            </a>
        </div>
    </div>
@endsection
