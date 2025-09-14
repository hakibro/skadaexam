@extends('layouts.admin')

@section('title', 'Detail Hasil Ujian')
@section('page-title', 'Detail Hasil Ujian')
@section('page-description', "{$hasilUjian->siswa->nama} - {$hasilUjian->jadwalUjian->judul}")

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.hasil.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('naskah.hasil.print', $hasilUjian->id) }}" target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-2"></i> Cetak
                </a>

                @if ($hasilUjian->status === 'selesai')
                    <a href="{{ route('naskah.hasil.jawaban', $hasilUjian->id) }}"
                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fa-solid fa-list-check mr-2"></i> Lihat Jawaban
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Score Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b">
                    <h3 class="text-sm font-medium text-gray-500">Nilai Ujian</h3>
                </div>
                <div class="p-4 flex items-center justify-between">
                    @if ($hasilUjian->status === 'selesai')
                        <div
                            class="text-5xl font-bold {{ $hasilUjian->nilai >= ($mapel->kkm ?? 75) ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($hasilUjian->nilai, 2) }}
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Nilai KKM: {{ $mapel->kkm ?? '75.00' }}</div>
                            <div class="mt-1">
                                @if ($hasilUjian->nilai >= ($mapel->kkm ?? 75))
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Lulus</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak
                                        Lulus</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-lg text-gray-500 italic">Ujian belum selesai</div>
                    @endif
                </div>
            </div>

            <!-- Time Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b">
                    <h3 class="text-sm font-medium text-gray-500">Waktu Ujian</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Mulai:</span>
                            <span class="text-sm font-medium">{{ $hasilUjian->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Selesai:</span>
                            <span class="text-sm font-medium">
                                {{ $hasilUjian->waktu_selesai ? $hasilUjian->waktu_selesai->format('d M Y, H:i') : 'Belum selesai' }}
                            </span>
                        </div>
                        @if ($hasilUjian->waktu_selesai)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Durasi:</span>
                                <span class="text-sm font-medium">
                                    {{ $hasilUjian->waktu_selesai->diffForHumans($hasilUjian->created_at, ['parts' => 2, 'short' => true]) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Answer Statistics Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b">
                    <h3 class="text-sm font-medium text-gray-500">Statistik Jawaban</h3>
                </div>
                <div class="p-4">
                    @if ($hasilUjian->status === 'selesai')
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Soal:</span>
                                <span class="text-sm font-medium">{{ $hasilUjian->total_soal ?? $totalSoal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Dijawab Benar:</span>
                                <span
                                    class="text-sm font-medium text-green-600">{{ $hasilUjian->jawaban_benar ?? $jawabanBenar }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Dijawab Salah:</span>
                                <span
                                    class="text-sm font-medium text-red-600">{{ $hasilUjian->jawaban_salah ?? $jawabanSalah }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tidak Dijawab:</span>
                                <span
                                    class="text-sm font-medium text-gray-600">{{ ($hasilUjian->total_soal ?? $totalSoal) - ($hasilUjian->jawaban_benar + $hasilUjian->jawaban_salah) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-gray-500 italic">Statistik akan tersedia setelah ujian selesai</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2">
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Ujian</h3>
                        <p class="mt-1 text-sm text-gray-500">Detail hasil ujian siswa</p>
                    </div>

                    <div class="border-b border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Nama Siswa</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $hasilUjian->siswa->nama }}
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">NIS / NISN</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $hasilUjian->siswa->nis }}
                                    / {{ $hasilUjian->siswa->nisn }}</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $hasilUjian->siswa->kelas->nama_kelas }}</dd>
                            </div>
                            <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Jadwal Ujian</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $hasilUjian->jadwalUjian->judul }}
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $hasilUjian->jadwalUjian->tanggal->format('d M Y') }} -
                                        {{ $hasilUjian->jadwalUjian->tanggal_selesai->format('d M Y') }}</div>
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Sesi Ujian</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $hasilUjian->jadwalUjian->sesiRuangan->nama ?? 'Default' }}
                                    @if ($hasilUjian->jadwalUjian->sesiRuangan)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Durasi: {{ $hasilUjian->jadwalUjian->sesiRuangan->durasi }} menit
                                        </div>
                                    @endif
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Mata Pelajaran</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $mapel->nama_mapel }}</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Bank Soal</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $hasilUjian->jadwalUjian->sesiRuangan->bankSoal->judul }}</dd>
                            </div>
                            <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                    @if ($hasilUjian->status === 'selesai')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Sedang
                                            Berlangsung</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($hasilUjian->keterangan)
                                <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $hasilUjian->keterangan }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($hasilUjian->status === 'selesai')
                    <!-- Answer Analysis -->
                    <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:px-6 border-b">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Analisis Jawaban</h3>
                            <p class="mt-1 text-sm text-gray-500">Persentase jawaban benar berdasarkan kategori</p>
                        </div>

                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-6">
                                @forelse($kategoriAnalisis as $kategori => $data)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $kategori }}</h4>
                                        <div class="relative pt-1">
                                            <div class="flex mb-2 items-center justify-between">
                                                <div>
                                                    <span class="text-xs font-semibold inline-block text-blue-600">
                                                        {{ $data['benar'] }} benar / {{ $data['total'] }} soal
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs font-semibold inline-block text-blue-600">
                                                        {{ $data['persentase'] }}%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="overflow-hidden h-2 text-xs flex rounded bg-blue-200">
                                                <div style="width:{{ $data['persentase'] }}%"
                                                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-sm text-center py-4">Tidak ada data analisis kategori yang
                                        tersedia</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Student Info Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b">
                        <h3 class="text-sm font-medium text-gray-500">Informasi Siswa</h3>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center space-x-4 mb-4">
                            @if ($hasilUjian->siswa->photo)
                                <img src="{{ $hasilUjian->siswa->photo_url }}" alt="{{ $hasilUjian->siswa->nama }}"
                                    class="h-16 w-16 rounded-full object-cover">
                            @else
                                <div
                                    class="h-16 w-16 rounded-full flex items-center justify-center bg-gray-200 text-gray-500">
                                    <i class="fa-solid fa-user text-2xl"></i>
                                </div>
                            @endif
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">{{ $hasilUjian->siswa->nama }}</h4>
                                <p class="text-gray-600">NIS: {{ $hasilUjian->siswa->nis }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $hasilUjian->siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-500">Kelas:</span>
                                <span class="font-medium ml-2">{{ $hasilUjian->siswa->kelas->nama_kelas }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Angkatan:</span>
                                <span class="font-medium ml-2">{{ $hasilUjian->siswa->tahun_masuk }}</span>
                            </div>
                            @if ($hasilUjian->siswa->telepon)
                                <div>
                                    <span class="text-gray-500">Telepon:</span>
                                    <span class="font-medium ml-2">{{ $hasilUjian->siswa->telepon }}</span>
                                </div>
                            @endif
                            @if ($hasilUjian->siswa->email)
                                <div>
                                    <span class="text-gray-500">Email:</span>
                                    <span class="font-medium ml-2">{{ $hasilUjian->siswa->email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 text-right border-t">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-900">
                            Lihat Semua Hasil Ujian Siswa <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b">
                        <h3 class="text-sm font-medium text-gray-500">Aksi</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('naskah.hasil.print', $hasilUjian->id) }}" target="_blank"
                                    class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                    <i class="fa-solid fa-print w-5 h-5 mr-3 text-gray-400"></i>
                                    Cetak Hasil Ujian
                                </a>
                            </li>
                            @if ($hasilUjian->status === 'selesai')
                                <li>
                                    <a href="{{ route('naskah.hasil.jawaban', $hasilUjian->id) }}"
                                        class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                        <i class="fa-solid fa-list-check w-5 h-5 mr-3 text-blue-500"></i>
                                        Lihat Detail Jawaban
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('naskah.hasil.export.single', ['hasil' => $hasilUjian, 'format' => 'pdf']) }}"
                                        class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                        <i class="fa-solid fa-file-pdf w-5 h-5 mr-3 text-red-500"></i>
                                        Export ke PDF
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('naskah.jadwal.show', $hasilUjian->jadwalUjian->id) }}"
                                    class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                    <i class="fa-solid fa-calendar w-5 h-5 mr-3 text-indigo-500"></i>
                                    Lihat Jadwal Ujian
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('naskah.hasil.destroy', $hasilUjian->id) }}" method="post"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil ujian ini? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex items-center w-full p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                        <i class="fa-solid fa-trash w-5 h-5 mr-3 text-red-500"></i>
                                        Hapus Hasil Ujian
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Other Exams Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b">
                        <h3 class="text-sm font-medium text-gray-500">Ujian Lain Siswa Ini</h3>
                    </div>
                    <div class="p-4">
                        @if (count($otherResults) > 0)
                            <ul class="space-y-3">
                                @foreach ($otherResults as $hasil)
                                    <li>
                                        <a href="{{ route('naskah.hasil.show', $hasil->id) }}"
                                            class="flex items-center justify-between hover:bg-gray-50 p-2 rounded">
                                            <div>
                                                <div class="text-sm font-medium text-gray-700">
                                                    {{ $hasil->jadwalUjian->sesiRuangan->bankSoal->mapel->nama_mapel }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $hasil->created_at->format('d M Y, H:i') }}</div>
                                            </div>
                                            @if ($hasil->status === 'selesai')
                                                <div
                                                    class="font-medium {{ $hasil->nilai >= ($hasil->jadwalUjian->sesiRuangan->bankSoal->mapel->kkm ?? 75) ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($hasil->nilai, 2) }}
                                                </div>
                                            @else
                                                <div class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">
                                                    Berlangsung</div>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-4">
                                <p class="text-gray-500">Tidak ada ujian lain yang diambil oleh siswa ini</p>
                            </div>
                        @endif
                    </div>
                    @if (count($otherResults) > 0)
                        <div class="bg-gray-50 px-4 py-3 text-right border-t">
                            <a href="{{ route('naskah.hasil.index', ['siswa_id' => $hasilUjian->siswa->id]) }}"
                                class="text-sm text-blue-600 hover:text-blue-900">
                                Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
