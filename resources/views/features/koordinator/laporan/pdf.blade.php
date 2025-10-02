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
                                <!-- Logo Section - Left Column -->
                                <div class="flex justify-center">
                                    <div
                                        class="grid grid-cols-2 gap-3 bg-white rounded-xl p-4 shadow-xl ring-1 ring-slate-200">
                                        <!-- Logo Sekolah -->
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 ring-1 ring-blue-100">
                                            <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z" />
                                            </svg>
                                        </div>

                                        <!-- Logo SMK-PK -->
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50 ring-1 ring-orange-100">
                                            <svg class="h-8 w-8 text-orange-500" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z" />
                                            </svg>
                                        </div>

                                        <!-- Logo Pusmendik -->
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50 ring-1 ring-green-100">
                                            <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                            </svg>
                                        </div>

                                        <!-- Logo Indonesia -->
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50 ring-1 ring-red-100 overflow-hidden">
                                            <div class="h-6 w-9 flex flex-col rounded">
                                                <div class="h-1/2 w-full bg-red-500"></div>
                                                <div class="h-1/2 w-full bg-white"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Text Section - Right Column -->
                                <div class="text-left">
                                    <p class="text-lg font-semibold leading-7 text-blue-600 mb-3">YAYASAN DARUT TAQWA
                                    </p>
                                    <h1
                                        class="text-3xl font-bold tracking-tight text-slate-900 lg:text-4xl xl:text-5xl mb-3">
                                        <span class="block text-blue-600">SMK DARUT TAQWA</span>
                                    </h1>
                                    <p class="text-lg leading-8 text-slate-600 ">SENGONAGUNG PURWOSARI
                                        PASURUAN</p>
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
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm  bg-green-100 text-green-800">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                Selesai Normal
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
                        <h4 class=" text-blue-700 mb-2">📋 Catatan Pembukaan</h4>
                        <p class="text-sm text-gray-700">Ujian dimulai tepat waktu pukul 08:00 WIB. Semua peserta telah
                            diperiksa identitasnya dan ditempatkan sesuai nomor urut. Pengawas memberikan penjelasan
                            tata tertib ujian dan membagikan lembar soal.</p>
                    </div>

                    <div class="bg-white p-4 rounded border-l-4 border-green-500">
                        <h4 class=" text-green-700 mb-2">⏱️ Catatan Pelaksanaan</h4>
                        <p class="text-sm text-gray-700">Ujian berlangsung lancar tanpa kendala berarti. Tidak ada
                            peserta yang melakukan kecurangan. Pada menit ke-90, ada 1 peserta yang mengalami sakit
                            ringan namun dapat melanjutkan ujian hingga selesai.</p>
                    </div>

                    <div class="bg-white p-4 rounded border-l-4 border-purple-500">
                        <h4 class=" text-purple-700 mb-2">✅ Catatan Penutupan</h4>
                        <p class="text-sm text-gray-700">Ujian berakhir pukul 10:00 WIB. Semua lembar jawaban telah
                            dikumpulkan dan dihitung sesuai jumlah peserta hadir. Ruangan dikembalikan dalam kondisi
                            bersih dan rapi.</p>
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
                    <p class="text-sm text-white">Ujian Tanggal 15 Maret 2024 - Ruang R.12A</p>
                </div>
            </div>


            <!-- Data Kehadiran -->
            <div class="flex flex-col gap-6 text-md">
                <!-- Siswa Hadir -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold  text-green-700 mb-4 flex items-center">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        Siswa Hadir (30 orang)
                    </h3>
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 bg-white gap-2 p-2 rounded-lg">
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <p>ANGGIES ANGGRAENI CAHYA BUNGA MAYCANTIQA - 245035
                                </p>
                                <span class="text-green-600 text-xs whitespace-nowrap">X BD 1</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>001 - Ahmad Rizki</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>001 - Ahmad Rizki</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>001 - Ahmad Rizki</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>001 - Ahmad Rizki</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>001 - Ahmad Rizki</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>002 - Siti Nurhaliza</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>003 - Budi Santoso</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>004 - Dewi Sartika</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>
                            <div class="px-2 rounded text-sm flex justify-between items-center">
                                <span>005 - Eko Prasetyo</span>
                                <span class="text-green-600 text-xs">✓</span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Siswa Tidak Hadir -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-red-700 mb-4 flex items-center">
                        <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        Siswa Tidak Hadir (2 orang)
                    </h3>
                    <div class="flex-none bg-white space-y-2 p-2 rounded-lg">
                        <div class="px-2 rounded text-sm flex justify-between items-center">
                            <span>015 - Rina Wati</span>
                            <span class="text-red-600 text-xs">✗ Sakit</span>
                        </div>
                        <div class="px-2 rounded text-sm flex justify-between items-center">
                            <span>028 - Joko Widodo</span>
                            <span class="text-red-600 text-xs">✗ Izin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="border-t pt-6 mt-8 signature-section">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-24">Pengawas</p>
                        <div class="border-t border-gray-300 pt-2">
                            <p class="">Dra. Sri Mulyani, M.Pd</p>
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
