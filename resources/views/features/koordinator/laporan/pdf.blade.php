<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1000">
    <title>Berita Acara Ujian</title>
    <link href="{{ public_path('css/pdf.css') }}" rel="stylesheet" type="text/css">


    <style>
        body {
            box-sizing: border-box;
            width: 950px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                font-size: 12px;
            }

            .page-break {
                page-break-before: always;
            }

            .header-section {
                page-break-after: avoid;
            }

            .content-section {
                page-break-inside: avoid;
            }

            .signature-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-2 font-sans">
    <!-- Hero Section Header - Full Width -->
    <div class="relative  overflow-hidden w-full header-section print-container">
        <!-- Background Pattern -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 "></div>
            <svg class="absolute inset-0 h-full w-full stroke-slate-200/50 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                aria-hidden="true">
                <defs>
                    <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M.5 40V.5H40" fill="none" stroke-width="1" />
                </defs>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-pattern)" />
            </svg>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-12">
            <svg class="h-64 w-64 text-slate-200" fill="currentColor" viewBox="0 0 256 256">
                <circle cx="128" cy="128" r="128" opacity="0.1" />
            </svg>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12">
            <svg class="h-48 w-48 text-blue-200" fill="currentColor" viewBox="0 0 256 256">
                <circle cx="128" cy="128" r="128" opacity="0.1" />
            </svg>
        </div>

        <div class="relative px-6">
            <div class="mx-auto max-w-6xl text-center">
                <!-- Background Pattern -->
                <div class="absolute inset-0">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50"></div>
                    <svg class="absolute inset-0 h-full w-full stroke-slate-200/50 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                        aria-hidden="true">
                        <defs>
                            <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M.5 40V.5H40" fill="none" stroke-width="1" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid-pattern)" />
                    </svg>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 -translate-y-12 translate-x-12">
                    <svg class="h-64 w-64 text-slate-200" fill="currentColor" viewBox="0 0 256 256">
                        <circle cx="128" cy="128" r="128" opacity="0.1" />
                    </svg>
                </div>
                <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12">
                    <svg class="h-48 w-48 text-blue-200" fill="currentColor" viewBox="0 0 256 256">
                        <circle cx="128" cy="128" r="128" opacity="0.1" />
                    </svg>
                </div>

                <div class="relative px-6 py-8 mt-12">
                    <div class="mx-auto max-w-4xl text-center">
                        <!-- Header Section - Two Columns -->
                        <div class="mb-8">
                            <div class="flex gap-8 items-center justify-center">
                                <!-- Logo Section -->
                                <div
                                    class="flex flex-wrap justify-center gap-4 bg-white rounded-xl p-4 shadow-xl ring-1 ring-slate-200">

                                    <img src="{{ public_path('assets/logo-compressed.png') }}"
                                        class="h-16 object-contain">
                                    <img src="{{ public_path('assets/smk-pk-compressed.png') }}"
                                        class="h-16 object-contain">
                                    <img src="{{ public_path('assets/pusmendik.svg') }}"
                                        class="h-12 object-contain w-full">
                                </div>




                                <!-- Text Section -->
                                <div class="text-left">
                                    <p class="text-lg font-semibold leading-7 text-blue-600 mb-3">
                                        YAYASAN DARUT TAQWA
                                    </p>

                                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-3">
                                        <span class="block text-blue-600">SMK DARUT TAQWA</span>
                                    </h1>

                                    <p class="text-lg leading-8 text-slate-600">
                                        SENGONAGUNG PURWOSARI PASURUAN
                                    </p>
                                </div>
                            </div>
                        </div>


                        <!-- Information Card -->
                        <div class="mx-auto max-w-4xl mb-4">
                            <div class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
                                <div class="flex items-center justify-center gap-6 text-left text-sm">
                                    <!-- Kolom 1 -->
                                    <div class="space-y-2">
                                        <div>
                                            <span class=" text-slate-600">NSS: </span>
                                            <span class="font-semibold text-slate-900">32.2.05.19.08.027</span>
                                        </div>
                                        <div>
                                            <span class=" text-slate-600">NPSN: </span>
                                            <span class="font-semibold text-slate-900">20542535</span>
                                        </div>
                                    </div>

                                    <!-- Kolom 2 -->
                                    <div class="space-y-2">
                                        <div>
                                            <span class=" text-slate-600">Akte Notaris: </span>
                                            <span class="font-semibold text-slate-900">Sjariefuddin Pasuruan Jo Akhmad
                                                Shohib, Sh No. 04 Tahun 2000</span>
                                        </div>
                                        <div>
                                            <span class=" text-slate-600">Alamat: </span>
                                            <span class="font-semibold text-slate-900">Jl. Pesantren Ngalah No. 16
                                                Pandean Sengonagung Purwosari Pasuruan</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information - Left Aligned Below -->
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <div class="flex flex-wrap justify-between items-center gap-y-1 text-xs">
                                        <div>
                                            <span class=" text-slate-600">Telepon: </span>
                                            <span class="font-semibold text-slate-900">(0343) 612026</span>
                                        </div>
                                        <div>
                                            <span class=" text-slate-600">Kotak Pos: </span>
                                            <span class="font-semibold text-slate-900">Po. Box 04 Pas. 67162</span>
                                        </div>
                                        <div>
                                            <span class=" text-slate-600">Website: </span>
                                            <span class="font-semibold text-slate-900">www.smkdata.sch.id</span>
                                        </div>
                                        <div>
                                            <span class=" text-slate-600">Email: </span>
                                            <span class="font-semibold text-slate-900">info@smkdata.sch.id</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Title -->
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-slate-300"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <div
                                    class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4 rounded-2xl shadow-xl">
                                    <h2 class="text-xl font-bold text-white tracking-wide">
                                        BERITA ACARA UJIAN
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section - Seamlessly Connected -->
        <div
            class="relative max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden -mt-4 p-6 space-y-6 content-section print-container">
            <!-- Informasi Dasar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-lg">
                <div class="flex-1 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-3">Informasi Ujian</h3>
                        <div class="flex flex-col items-start space-y-2 text-sm">
                            <div class="flex">
                                <span class="text-gray-600">Tanggal:</span>
                                <span
                                    class=" ml-2">{{ $beritaAcara->sesiRuangan->jadwalUjians->first()
                                        ? $beritaAcara->sesiRuangan->jadwalUjians->first()->tanggal->format('d F Y')
                                        : 'N/A' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-600">Ruangan:</span>
                                <span
                                    class=" ml-2">{{ $beritaAcara->sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-600">Sesi:</span>
                                <span class=" ml-2">{{ $beritaAcara->sesiRuangan->nama_sesi ?? 'N/A' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-600">Waktu:</span>
                                <span class=" ml-2">{{ $beritaAcara->sesiRuangan->waktu_mulai ?? 'N/A' }} -
                                    {{ $beritaAcara->sesiRuangan->waktu_selesai ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-3">Status Pelaksanaan</h3>
                        <div class="flex items-center space-x-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm {{ $beritaAcara->status_badge_class }}">
                                {{ $beritaAcara->status_text ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-3">Jumlah Kehadiran</h3>
                        <div class="grid grid-cols-3 gap-2 text-center text-sm">
                            <div class="bg-blue-100 p-2 rounded">
                                <div class="font-bold text-blue-800">
                                    {{ $beritaAcara->sesiRuangan->sesiRuanganSiswa->count() ?? 0 }}
                                </div>
                                <div class="text-blue-600">Terdaftar</div>
                            </div>
                            <div class="bg-green-100 p-2 rounded">
                                <div class="font-bold text-green-800">{{ $beritaAcara->jumlah_hadir }}</div>
                                <div class="text-green-600">Hadir</div>
                            </div>
                            <div class="bg-red-100 p-2 rounded">
                                <div class="font-bold text-red-800">{{ $beritaAcara->jumlah_tidak_hadir }}</div>
                                <div class="text-red-600">Tidak Hadir</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Pelaksanaan -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-4">Laporan Pelaksanaan Ujian</h3>

                <div class="space-y-4">
                    <div class="bg-white p-4 rounded border-l-4 border-blue-500">
                        <h4 class=" text-blue-700 mb-2"><i class="fas fa-solid fa-note-sticky"> Catatan Pembukaan</h4>
                        <p class="text-sm text-gray-700">{{ $beritaAcara->catatan_pembukaan ?? '-' }}</p>
                    </div>

                    <div class="bg-white p-4 rounded border-l-4 border-green-500">
                        <h4 class=" text-green-700 mb-2"><i class="fas fa-solid fa-clock"> Catatan Pelaksanaan</h4>
                        <p class="text-sm text-gray-700">{{ $beritaAcara->catatan_pelaksanaan ?? '-' }}</p>
                    </div>

                    <div class="bg-white p-4 rounded border-l-4 border-purple-500">
                        <h4 class=" text-purple-700 mb-2"><i class="fas fa-solid fa-check"> Catatan Penutupan</h4>
                        <p class="text-sm text-gray-700">{{ $beritaAcara->catatan_penutupan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="page-break"></div>

            <!-- Daftar Hadir Siswa Title -->
            <div class="w-full flex items-center justify-center pt-12">
                <div
                    class="flex flex-col flex-none bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4 rounded-2xl shadow-xl max-w-xl items-center justify-center self-center">
                    <h2 class="text-xl font-bold text-white tracking-wide">
                        DAFTAR HADIR SISWA
                    </h2>
                    <p class="text-sm text-white">
                        {{ $beritaAcara->sesiRuangan->jadwalUjians->first() ? $beritaAcara->sesiRuangan->jadwalUjians->first()->tanggal->format('d F Y') : 'N/A' }}
                        -
                        {{ $beritaAcara->sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}
                        {{ $beritaAcara->sesiRuangan->nama_sesi ?? 'N/A' }}
                    </p>
                </div>
            </div>


            <!-- Data Kehadiran -->
            <div class="flex flex-col gap-6 text-md">
                <!-- Siswa Hadir -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-700 mb-4 flex items-center text-lg">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        {{ $beritaAcara->jumlah_peserta_hadir }} Siswa Hadir
                    </h3>

                    @php
                        $siswaHadir = $beritaAcara->sesiRuangan->sesiRuanganSiswa->where('status_kehadiran', 'hadir');
                    @endphp

                    @if ($siswaHadir->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-2 bg-white rounded-lg">
                            @foreach ($beritaAcara->sesiRuangan->sesiRuanganSiswa->where('status_kehadiran', 'hadir') as $index => $siswaSession)
                                <div class="px-3 py-1 rounded text-sm flex justify-between items-center">

                                    {{-- Nama + NIS --}}
                                    <p class="font-medium text-gray-900">
                                        {{ $siswaSession->siswa->nama ?? 'N/A' }}

                                    </p>

                                    {{-- Kelas --}}
                                    <span class="text-xs text-green-600 whitespace-nowrap">
                                        {{ $siswaSession->siswa->kelas->nama_kelas ?? 'N/A' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500 text-sm">
                            Tidak ada siswa hadir
                        </div>
                    @endif
                </div>


                <!-- Siswa Tidak Hadir -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-red-700 mb-4 flex items-center text-lg">
                        <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        {{ $beritaAcara->jumlah_peserta_tidak_hadir }} Siswa Tidak Hadir
                    </h3>

                    @php
                        $siswaTidakHadir = $beritaAcara->sesiRuangan->sesiRuanganSiswa->whereIn('status_kehadiran', [
                            'tidak_hadir',
                            'sakit',
                            'izin',
                        ]);
                    @endphp

                    @if ($siswaTidakHadir->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-2 bg-white rounded-lg">
                            @foreach ($beritaAcara->sesiRuangan->sesiRuanganSiswa->whereIn('status_kehadiran', ['tidak_hadir', 'sakit', 'izin']) as $index => $siswaSession)
                                <div class="px-3 py-1 rounded text-sm flex justify-between items-center">

                                    {{-- Nama + NIS --}}
                                    <p class="font-medium text-gray-900">
                                        {{ $siswaSession->siswa->nama ?? 'N/A' }}
                                        <span class="text-gray-500">
                                            - {{ $siswaSession->siswa->nis ?? '-' }}
                                        </span>
                                    </p>

                                    {{-- Kelas --}}
                                    <span class="text-xs text-red-600 whitespace-nowrap">
                                        {{ $siswaSession->siswa->kelas->nama_kelas ?? 'N/A' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500 text-sm">
                            Semua siswa hadir
                        </div>
                    @endif
                </div>

            </div>

            <!-- Tanda Tangan -->
            <div class="border-t pt-6 mt-8 signature-section">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-24">Pengawas</p>
                        <div class="border-t border-gray-300 pt-2">
                            <p class="">{{ $beritaAcara->pengawas->nama ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-24">Ketua Pelaksana</p>
                        <div class="border-t border-gray-300 pt-2">
                            <p class="">Akhmad Barizi, M.Kom</p>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

</body>

</html>
