<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h4 class="text-lg font-medium text-gray-900">{{ $pengawas->nama }}</h4>
            <p class="text-sm text-gray-600">{{ $pengawas->nip ?? 'N/A' }} •
                {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            {{ $sessions->count() }} sesi
        </span>
    </div>

    @if ($sessions->count() > 0)
        <div class="space-y-3">
            @foreach ($sessions as $session)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h5 class="font-medium text-gray-900">{{ $session->nama_sesi }}</h5>
                            <p class="text-sm text-gray-600">
                                <i class="fa-solid fa-door-open mr-1"></i>
                                {{ $session->ruangan->nama_ruangan ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $session->waktu_mulai }} - {{ $session->waktu_selesai }}
                            </p>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $session->status_label['class'] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </div>
                    </div>

                    @if ($session->sesiRuanganSiswa && $session->sesiRuanganSiswa->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                <i class="fa-solid fa-users mr-1"></i>
                                {{ $session->sesiRuanganSiswa->count() }} siswa terdaftar
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <i class="fa-solid fa-calendar-times text-gray-400 text-4xl mb-3"></i>
            <h5 class="text-lg font-medium text-gray-900 mb-1">Tidak Ada Jadwal</h5>
            <p class="text-gray-600">Pengawas tidak memiliki jadwal pada tanggal ini</p>
        </div>
    @endif
</div>
