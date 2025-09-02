@extends('layouts.admin')

@section('title', 'Detail Sesi Ujian')
@section('page-title', 'Detail Sesi Ujian')
@section('page-description', 'Sesi: ' . $sesi->nama_sesi)

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Jadwal
                </a>
                <a href="{{ route('naskah.sesi.edit', [$jadwal->id, $sesi->id]) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                    <i class="fa-solid fa-edit mr-2"></i> Edit Sesi
                </a>
            </div>

            <div class="flex space-x-2">
                <form action="{{ route('naskah.sesi.status', [$jadwal->id, $sesi->id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    @if ($sesi->status == 'aktif')
                        <input type="hidden" name="status" value="penuh">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                            <i class="fa-solid fa-user-slash mr-2"></i> Tandai Penuh
                        </button>
                    @elseif($sesi->status == 'penuh')
                        <input type="hidden" name="status" value="aktif">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fa-solid fa-user-check mr-2"></i> Tandai Aktif
                        </button>
                    @endif
                    @if ($sesi->status != 'selesai')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-flag-checkered mr-2"></i> Tandai Selesai
                        </button>
                    @endif
                </form>

                <form action="{{ route('naskah.sesi.destroy', [$jadwal->id, $sesi->id]) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus sesi ujian ini? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.')">
                        <i class="fa-solid fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Sesi Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2 space-y-6">
                <!-- Sesi Info Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Sesi Ujian</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Detail lengkap sesi ujian ini.</p>
                            </div>
                            <div>
                                @switch($sesi->status)
                                    @case('aktif')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                    @break

                                    @case('penuh')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Penuh</span>
                                    @break

                                    @case('selesai')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                                    @break

                                    @default
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $sesi->status }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Nama Sesi</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $sesi->nama_sesi }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Jenis Sesi</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    @switch($sesi->jenis_sesi)
                                        @case('reguler')
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Reguler</span>
                                        @break

                                        @case('susulan')
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Susulan</span>
                                        @break

                                        @default
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $sesi->jenis_sesi }}</span>
                                    @endswitch
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $sesi->waktu_mulai->format('H:i') }} - {{ $sesi->waktu_selesai->format('H:i') }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Ruangan</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $sesi->ruangan ?: 'Belum ditentukan' }}</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                                <dt class="text-sm font-medium text-gray-500">Pengawas</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $sesi->pengawas ?: 'Belum ditentukan' }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Kapasitas</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <span>{{ $sesi->peserta_terdaftar }} / {{ $sesi->kapasitas_maksimal }}
                                            peserta</span>
                                        <div class="ml-4 w-32 bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ ($sesi->peserta_terdaftar / $sesi->kapasitas_maksimal) * 100 }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="ml-2 text-xs text-gray-500">{{ round(($sesi->peserta_terdaftar / $sesi->kapasitas_maksimal) * 100) }}%</span>
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Peserta Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b flex justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Peserta</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Peserta yang terdaftar pada sesi ini.</p>
                        </div>
                        <div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ count($sesi->hasilUjians) }} Peserta
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        @if (count($sesi->hasilUjians) > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Siswa</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kelas</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nilai</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($sesi->hasilUjians as $index => $hasil)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $hasil->siswa->nama ?? 'Siswa #' . $hasil->siswa_id }}</div>
                                                <div class="text-sm text-gray-500">{{ $hasil->siswa->nis ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $hasil->siswa->kelas->nama ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @switch($hasil->status)
                                                    @case('belum_mulai')
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Belum
                                                            Mulai</span>
                                                    @break

                                                    @case('sedang_ujian')
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Sedang
                                                            Ujian</span>
                                                    @break

                                                    @case('selesai')
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $hasil->status }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($hasil->nilai !== null)
                                                    <span
                                                        class="font-medium {{ $hasil->nilai >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $hasil->nilai }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="#" class="text-blue-600 hover:text-blue-900">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-8">
                                <i class="fa-solid fa-users text-gray-300 text-4xl mb-2"></i>
                                <p class="text-gray-500">Belum ada peserta terdaftar</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Jadwal Info Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Jadwal</h3>
                    </div>

                    <div class="p-4 space-y-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Jadwal Ujian</h4>
                            <p class="text-sm font-medium">{{ $jadwal->nama_ujian }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Kode Ujian</h4>
                            <p class="text-sm font-medium">{{ $jadwal->kode_ujian }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Mata Pelajaran</h4>
                            <p class="text-sm font-medium">{{ $jadwal->mapel->nama ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Tanggal Ujian</h4>
                            <p class="text-sm font-medium">{{ $jadwal->tanggal_ujian->format('d M Y') }}</p>
                        </div>
                        <div class="pt-3">
                            <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-900">
                                <i class="fa-solid fa-calendar-alt mr-1"></i> Lihat Detail Jadwal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-gray-50 shadow-md rounded-lg overflow-hidden border border-gray-200">
                    <div class="p-4">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Aksi</h3>
                        <div class="space-y-2">
                            <a href="{{ route('naskah.sesi.edit', [$jadwal->id, $sesi->id]) }}"
                                class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                <i class="fa-solid fa-edit w-5 h-5 mr-3 text-gray-400"></i>
                                Edit Sesi Ujian
                            </a>
                            <form action="{{ route('naskah.sesi.destroy', [$jadwal->id, $sesi->id]) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex items-center w-full p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus sesi ujian ini? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="fa-solid fa-trash w-5 h-5 mr-3 text-red-400"></i>
                                    Hapus Sesi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
