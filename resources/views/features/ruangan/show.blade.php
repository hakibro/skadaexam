@extends('layouts.admin')

@section('title', 'Detail Ruangan: ' . $ruangan->nama_ruangan)
@section('page-title', 'Detail Ruangan')
@section('page-description', $ruangan->nama_ruangan . ' - ' . $ruangan->kode_ruangan)

@section('content')
    <div class="py-4">
        <!-- Actions -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('ruangan.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700">
                <i class="fa-solid fa-edit"></i>
                <span>Edit Ruangan</span>
            </a>
            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                <i class="fa-solid fa-calendar-alt"></i>
                <span>Kelola Sesi</span>
            </a>
            <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Sesi Baru</span>
            </a>
            <form action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST" class="inline"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini? Semua sesi terkait juga akan dihapus.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700">
                    <i class="fa-solid fa-trash"></i>
                    <span>Hapus Ruangan</span>
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Room Information -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fa-solid fa-info-circle text-blue-600 mr-2"></i>
                            Informasi Ruangan
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $ruangan->nama_ruangan }}</h2>
                                <div class="flex items-center mt-2">
                                    <span
                                        class="{{ $ruangan->status_badge_class }} px-3 py-1 text-xs font-medium rounded-full">
                                        {{ $ruangan->status_label['text'] }}
                                    </span>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <span class="text-sm text-gray-600">Kode: {{ $ruangan->kode_ruangan }}</span>
                                </div>
                            </div>
                            <div
                                class="bg-{{ $ruangan->status === 'aktif' ? 'green' : ($ruangan->status === 'nonaktif' ? 'red' : 'yellow') }}-100 p-3 rounded-full">
                                <i
                                    class="fa-solid fa-{{ $ruangan->status === 'aktif' ? 'check-circle' : ($ruangan->status === 'nonaktif' ? 'times-circle' : 'tools') }} text-{{ $ruangan->status === 'aktif' ? 'green' : ($ruangan->status === 'nonaktif' ? 'red' : 'yellow') }}-600 text-2xl"></i>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                                <div class="flex items-center">
                                    <div class="bg-blue-100 text-blue-600 p-2 rounded mr-3">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <span class="text-xl font-semibold">{{ $ruangan->kapasitas }} siswa</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <div class="flex items-center">
                                    <div class="bg-purple-100 text-purple-600 p-2 rounded mr-3">
                                        <i class="fa-solid fa-map-marker-alt"></i>
                                    </div>
                                    <span>{{ $ruangan->lokasi ?? 'Lokasi tidak disetel' }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Sesi</label>
                                <div class="flex items-center">
                                    <div class="bg-green-100 text-green-600 p-2 rounded mr-3">
                                        <i class="fa-solid fa-calendar-alt"></i>
                                    </div>
                                    <span>{{ $sesiCount }} sesi ruangan</span>
                                </div>
                            </div>

                            @if ($ruangan->catatan)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                        <p class="text-gray-700">{{ $ruangan->catatan }}</p>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Terakhir Diperbarui</label>
                                <div class="flex items-center">
                                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded mr-3">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                    <span>{{ $ruangan->updated_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($todaySessions->count() > 0)
                            <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                                <h4 class="text-green-800 font-semibold mb-2">
                                    <i class="fa-solid fa-calendar-day mr-1"></i>
                                    Sesi Hari Ini
                                </h4>
                                <div class="space-y-2">
                                    @foreach ($todaySessions as $session)
                                        <div
                                            class="flex items-center justify-between p-2 bg-white rounded border border-green-100">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $session->nama_sesi }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i') }}
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <span
                                                    class="{{ $session->status == 'belum_mulai' ? 'bg-blue-100 text-blue-800' : ($session->status == 'berlangsung' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }} px-2 py-1 text-xs rounded-full mr-2">
                                                    {{ $session->status_label }}
                                                </span>
                                                <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $session->id]) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status Change Widget -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fa-solid fa-exchange-alt text-blue-600 mr-2"></i>
                            Ubah Status Ruangan
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('ruangan.update-status', $ruangan->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <div class="flex flex-col space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="aktif"
                                            {{ $ruangan->status === 'aktif' ? 'checked' : '' }}
                                            class="form-radio h-5 w-5 text-green-600">
                                        <span class="ml-2 text-gray-700">
                                            <i class="fa-solid fa-check-circle text-green-600 mr-1"></i>
                                            Aktif
                                        </span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="nonaktif"
                                            {{ $ruangan->status === 'nonaktif' ? 'checked' : '' }}
                                            class="form-radio h-5 w-5 text-red-600">
                                        <span class="ml-2 text-gray-700">
                                            <i class="fa-solid fa-times-circle text-red-600 mr-1"></i>
                                            Nonaktif
                                        </span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="perbaikan"
                                            {{ $ruangan->status === 'perbaikan' ? 'checked' : '' }}
                                            class="form-radio h-5 w-5 text-yellow-600">
                                        <span class="ml-2 text-gray-700">
                                            <i class="fa-solid fa-tools text-yellow-600 mr-1"></i>
                                            Dalam Perbaikan
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan Status
                                    (Opsional)</label>
                                <textarea name="catatan" id="catatan" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $ruangan->catatan }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fa-solid fa-save mr-1"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sessions Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fa-solid fa-calendar-alt text-blue-600 mr-2"></i>
                            Daftar Sesi Ruangan
                        </h3>
                        <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-plus mr-1"></i>
                            Tambah Sesi
                        </a>
                    </div>

                    <!-- Session List -->
                    @if ($sesiRuangan->count() > 0)
                        <div class="px-6 py-4">
                            <div class="flex items-center mb-4">
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" id="searchSesi" placeholder="Cari sesi..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div class="ml-3">
                                    <select id="filterStatus"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="all">Semua Status</option>
                                        <option value="belum_mulai">Belum Mulai</option>
                                        <option value="berlangsung">Berlangsung</option>
                                        <option value="selesai">Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-4 max-h-[600px] overflow-y-auto" id="sesiList">
                                @foreach ($sesiRuangan as $sesi)
                                    <div class="sesi-item border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
                                        data-status="{{ $sesi->status }}" data-name="{{ strtolower($sesi->nama_sesi) }}"
                                        data-date="{{ $sesi->jadwalUjians->first() ? \Carbon\Carbon::parse($sesi->jadwalUjians->first()->tanggal)->format('Y-m-d') : now()->format('Y-m-d') }}">
                                        <div
                                            class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div
                                                    class="{{ $sesi->status == 'belum_mulai' ? 'bg-blue-100 text-blue-600' : ($sesi->status == 'berlangsung' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600') }} p-2 rounded-full mr-3">
                                                    <i
                                                        class="fa-solid {{ $sesi->status == 'belum_mulai' ? 'fa-hourglass-start' : ($sesi->status == 'berlangsung' ? 'fa-play-circle' : 'fa-check-circle') }}"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">{{ $sesi->nama_sesi }}</h4>
                                                    <span class="text-sm text-gray-600">{{ $sesi->kode_sesi }}</span>
                                                </div>
                                            </div>
                                            <span
                                                class="{{ $sesi->status == 'belum_mulai' ? 'bg-blue-100 text-blue-800' : ($sesi->status == 'berlangsung' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }} px-2 py-1 text-xs rounded-full">
                                                {{ $sesi->status_label }}
                                            </span>
                                        </div>
                                        <div class="p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                                <div>
                                                    <span class="text-xs text-gray-500">Tanggal</span>
                                                    <div class="text-gray-900">
                                                        <i class="fa-solid fa-calendar mr-1 text-gray-400"></i>
                                                        {{ $sesi->jadwalUjians->first() ? \Carbon\Carbon::parse($sesi->jadwalUjians->first()->tanggal)->format('d M Y') : now()->format('d M Y') }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-gray-500">Waktu</span>
                                                    <div class="text-gray-900">
                                                        <i class="fa-solid fa-clock mr-1 text-gray-400"></i>
                                                        {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-gray-500">Pengawas</span>
                                                    <div class="text-gray-900">
                                                        <i class="fa-solid fa-user mr-1 text-gray-400"></i>
                                                        {{ $sesi->pengawas->nama ?? 'Belum ditentukan' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                                <div class="bg-blue-50 p-2 rounded flex items-center">
                                                    <div class="bg-blue-100 text-blue-600 p-1 rounded mr-2">
                                                        <i class="fa-solid fa-users"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $sesi->sesiRuanganSiswa->count() }} /
                                                            {{ $ruangan->kapasitas }}</div>
                                                        <div class="text-xs text-gray-600">Siswa</div>
                                                    </div>
                                                </div>

                                                <div class="bg-green-50 p-2 rounded flex items-center">
                                                    <div class="bg-green-100 text-green-600 p-1 rounded mr-2">
                                                        <i class="fa-solid fa-file-alt"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $sesi->jadwalUjians->count() }}</div>
                                                        <div class="text-xs text-gray-600">Jadwal Ujian</div>
                                                    </div>
                                                </div>

                                                <div class="bg-purple-50 p-2 rounded flex items-center">
                                                    <div class="bg-purple-100 text-purple-600 p-1 rounded mr-2">
                                                        <i class="fa-solid fa-graduation-cap"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $sesi->jadwalUjians->map(function ($jadwal) {
                                                                    return $jadwal->mapel->jurusan;
                                                                })->filter()->unique()->count() }}
                                                        </div>
                                                        <div class="text-xs text-gray-600">Jurusan</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="mt-2 pt-3 border-t border-gray-100 flex items-center justify-between">
                                                <div>
                                                    @if ($sesi->jadwalUjians->count() > 0)
                                                        <div class="text-xs text-gray-600 mb-1">Mapel:
                                                            @foreach ($sesi->jadwalUjians->take(2) as $index => $jadwal)
                                                                {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}{{ $index < min(1, $sesi->jadwalUjians->count() - 1) ? ', ' : '' }}
                                                            @endforeach
                                                            @if ($sesi->jadwalUjians->count() > 2)
                                                                <span>+{{ $sesi->jadwalUjians->count() - 2 }}
                                                                    lainnya</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                                                        class="text-blue-600 hover:text-blue-800 p-1"
                                                        title="Lihat Detail">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('ruangan.sesi.edit', [$ruangan->id, $sesi->id]) }}"
                                                        class="text-yellow-600 hover:text-yellow-800 p-1"
                                                        title="Edit Sesi">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('ruangan.sesi.siswa.index', [$ruangan->id, $sesi->id]) }}"
                                                        class="text-green-600 hover:text-green-800 p-1"
                                                        title="Kelola Siswa">
                                                        <i class="fa-solid fa-users"></i>
                                                    </a>
                                                    <a href="{{ route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id]) }}"
                                                        class="text-indigo-600 hover:text-indigo-800 p-1"
                                                        title="Kelola Jadwal Ujian">
                                                        <i class="fa-solid fa-calendar-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <div class="text-gray-400 mb-4">
                                <i class="fa-solid fa-calendar-alt text-5xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada sesi untuk ruangan ini</h3>
                            <p class="text-gray-500 mb-6">Mulai dengan membuat sesi ruangan baru</p>
                            <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Buat Sesi Ruangan
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Room Schedule Calendar -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fa-solid fa-calendar-week text-blue-600 mr-2"></i>
                            Jadwal Ruangan ({{ now()->format('F Y') }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-7 gap-1 text-center">
                            <div class="text-xs font-medium text-gray-500">Min</div>
                            <div class="text-xs font-medium text-gray-500">Sen</div>
                            <div class="text-xs font-medium text-gray-500">Sel</div>
                            <div class="text-xs font-medium text-gray-500">Rab</div>
                            <div class="text-xs font-medium text-gray-500">Kam</div>
                            <div class="text-xs font-medium text-gray-500">Jum</div>
                            <div class="text-xs font-medium text-gray-500">Sab</div>
                        </div>

                        <div class="grid grid-cols-7 gap-1" id="calendar">
                            @foreach ($calendarDays as $day)
                                <div class="aspect-square p-1 border rounded {{ $day['isCurrentMonth'] ? '' : 'bg-gray-50' }} 
                                           {{ $day['isToday'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}
                                           {{ $day['hasSessions'] ? 'cursor-pointer hover:bg-blue-50' : '' }}"
                                    {{ $day['hasSessions'] ? 'data-date="' . $day['date'] . '" onclick="showSessionsForDay(this)"' : '' }}>
                                    <div class="text-right">
                                        <span
                                            class="{{ $day['isToday'] ? 'bg-blue-500 text-white' : ($day['isCurrentMonth'] ? 'text-gray-700' : 'text-gray-400') }} inline-block rounded-full w-6 h-6 text-xs flex items-center justify-center">
                                            {{ $day['day'] }}
                                        </span>
                                    </div>
                                    @if ($day['hasSessions'])
                                        <div class="mt-1 space-y-1">
                                            @foreach ($day['sessionCounts'] as $status => $count)
                                                <div
                                                    class="bg-{{ $status == 'belum_mulai' ? 'blue' : ($status == 'berlangsung' ? 'green' : 'gray') }}-100 text-{{ $status == 'belum_mulai' ? 'blue' : ($status == 'berlangsung' ? 'green' : 'gray') }}-800 text-xs rounded-sm px-1 py-0.5 text-center">
                                                    {{ $count }} sesi
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Session Modal -->
                        <div id="sessionModal"
                            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[80vh] overflow-y-auto">
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <i class="fa-solid fa-calendar-day text-blue-600 mr-2"></i>
                                        <span id="modalDate">Sesi Tanggal</span>
                                    </h3>
                                    <button type="button" onclick="closeModal()"
                                        class="text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                                <div class="p-6" id="modalContent">
                                    <!-- Content will be loaded here -->
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right">
                                    <button type="button" onclick="closeModal()"
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Search functionality
        document.getElementById('searchSesi').addEventListener('input', function(e) {
            filterSessions();
        });

        // Filter functionality
        document.getElementById('filterStatus').addEventListener('change', function(e) {
            filterSessions();
        });

        function filterSessions() {
            const searchTerm = document.getElementById('searchSesi').value.toLowerCase();
            const filterStatus = document.getElementById('filterStatus').value;
            const sessions = document.querySelectorAll('#sesiList .sesi-item');

            sessions.forEach(session => {
                const name = session.dataset.name;
                const status = session.dataset.status;
                const matchesSearch = name.includes(searchTerm);
                const matchesFilter = filterStatus === 'all' || status === filterStatus;

                if (matchesSearch && matchesFilter) {
                    session.style.display = 'block';
                } else {
                    session.style.display = 'none';
                }
            });
        }

        // Calendar modal functionality
        function showSessionsForDay(element) {
            const date = element.dataset.date;
            const modal = document.getElementById('sessionModal');
            const modalDate = document.getElementById('modalDate');
            const modalContent = document.getElementById('modalContent');

            // Format the date for display
            const formattedDate = new Date(date).toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            modalDate.textContent = `Sesi Tanggal ${formattedDate}`;

            // Get all sessions for this day
            const sessionsForDay = {!! json_encode($sessionsData) !!}[date] || [];

            if (sessionsForDay.length > 0) {
                let sessionsHtml = '<div class="space-y-3">';

                sessionsForDay.forEach(session => {
                    const statusClass = session.status === 'belum_mulai' ? 'bg-blue-100 text-blue-800' :
                        (session.status === 'berlangsung' ? 'bg-green-100 text-green-800' :
                            'bg-gray-100 text-gray-800');

                    sessionsHtml += `
                        <div class="border rounded-lg overflow-hidden hover:shadow-sm">
                            <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                                <div class="flex items-center">
                                    <div class="${session.status === 'belum_mulai' ? 'bg-blue-100 text-blue-600' : 
                                              (session.status === 'berlangsung' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600')} 
                                                p-2 rounded-full mr-3">
                                        <i class="fa-solid ${session.status === 'belum_mulai' ? 'fa-hourglass-start' : 
                                                          (session.status === 'berlangsung' ? 'fa-play-circle' : 'fa-check-circle')}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">${session.nama_sesi}</h4>
                                        <span class="text-sm text-gray-600">${session.kode_sesi}</span>
                                    </div>
                                </div>
                                <span class="${statusClass} px-2 py-1 text-xs rounded-full">
                                    ${session.status_label}
                                </span>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <span class="text-xs text-gray-500">Waktu</span>
                                        <div class="text-gray-900">
                                            <i class="fa-solid fa-clock mr-1 text-gray-400"></i>
                                            ${session.waktu_mulai} - ${session.waktu_selesai}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Pengawas</span>
                                        <div class="text-gray-900">
                                            <i class="fa-solid fa-user mr-1 text-gray-400"></i>
                                            ${session.pengawas || 'Belum ditentukan'}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-4">
                                        <div class="text-xs">
                                            <span class="text-gray-500">Siswa:</span>
                                            <span class="font-medium">${session.siswa_count} / ${session.kapasitas}</span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-500">Jadwal:</span>
                                            <span class="font-medium">${session.jadwal_count}</span>
                                        </div>
                                    </div>
                                    <a href="/ruangan/${session.ruangan_id}/sesi/${session.id}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-eye mr-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });

                sessionsHtml += '</div>';
                modalContent.innerHTML = sessionsHtml;
            } else {
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-2">
                            <i class="fa-solid fa-calendar-times text-3xl"></i>
                        </div>
                        <p class="text-gray-500">Tidak ada sesi pada tanggal ini</p>
                    </div>
                `;
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('sessionModal').classList.add('hidden');
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('sessionModal');
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
@endsection
