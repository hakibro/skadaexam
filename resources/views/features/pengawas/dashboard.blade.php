@extends('layouts.admin')

@section('title', 'Jadwal Pengawas')
@section('page-title', 'Jadwal Pengawas')
@section('page-description', 'Detail jadwal sesi pengawasan untuk pengawas')

@push('styles')
    <style>
        /* Smooth tab transitions */
        .tab-panel {
            transition: opacity 0.2s ease-in-out;
        }

        .tab-panel.hidden {
            display: none;
        }

        /* Custom scrollbar for violations */
        .violations-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .violations-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .violations-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 999px;
        }

        .violations-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        /* Animate fade-in for toast */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Tab bottom nav - glass morphism effect */
        .tab-nav-blur {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Active tab indicator pulse */
        @keyframes tabPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .tab-ongoing-dot {
            animation: tabPulse 2s ease-in-out infinite;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">

        {{-- Variables & Sorting --}}
        @php
            $user = auth()->user();
            $isAdmin = $user->isAdmin() || $user->isKoordinator();
            $sortedAssignments = $assignments->sortBy(function ($assignment) {
                $ruanganId = $assignment->ruangan->id ?? 0;
                $waktuMulai = $assignment->waktu_mulai ?? '00:00:00';
                return sprintf('%03d_%s', $ruanganId, $waktuMulai);
            });
            $groupedByRuangan = $sortedAssignments->groupBy(function ($assignment) {
                return $assignment->ruangan->id ?? 0;
            });
        @endphp

        @if (!$isAdmin)
            {{-- ======================= PENGAWAS VIEW ======================= --}}
            <div class="pb-28 md:pb-24 px-4 pt-4 md:px-6 md:pt-6 max-w-5xl mx-auto">
                @if ($groupedByRuangan->isNotEmpty())
                    @foreach ($groupedByRuangan as $ruanganId => $sesiList)
                        @php
                            $ruangan = $sesiList->first()->ruangan;
                            $ruanganNama = $ruangan->nama_ruangan ?? 'Tanpa Ruangan';
                            // Find the ongoing assignment to select its tab by default
                            $ongoingAssignment = $sesiList->first(fn($s) => $s->status === 'berlangsung');
                            $defaultTabId = $ongoingAssignment ? $ongoingAssignment->id : $sesiList->first()->id;
                        @endphp

                        {{-- Ruangan Header --}}
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md shadow-indigo-200">
                                <i class="fa-solid fa-door-open text-white text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ $ruanganNama }}</h2>
                                <p class="text-xs text-gray-500">{{ $sesiList->count() }} sesi</p>
                            </div>
                        </div>

                        {{-- Tab Content Area --}}
                        <div class="relative">
                            @foreach ($sesiList as $index => $assignment)
                                @php
                                    $tabId = 'tab-' . $assignment->id;
                                    $isOngoing = $assignment->status === 'berlangsung';
                                    $isDefault = $assignment->id === $defaultTabId;
                                    $jadwalUjians = $assignment->jadwalUjians->sortBy('kode_jadwal');
                                    $mapelNames = [];
                                    foreach ($jadwalUjians as $jadwal) {
                                        $mapelNames[] = $jadwal->mapel->nama_mapel ?? 'Tidak ada mapel';
                                    }
                                    $mapelDisplay =
                                        count($mapelNames) > 0 ? implode(' + ', $mapelNames) : 'Tidak ada jadwal';
                                    $totalSiswa = $assignment->sesiRuanganSiswa->count();
                                    $hadir = $assignment->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count();
                                    $tidakHadir = $assignment->sesiRuanganSiswa
                                        ->where('status_kehadiran', 'tidak_hadir')
                                        ->count();
                                    $belumAbsen = $totalSiswa - $hadir - $tidakHadir;
                                    $beritaAcara = $assignment->beritaAcara;
                                    $pelanggaranList = $assignment->pelanggaranUjian->sortByDesc('waktu_pelanggaran');
                                    $tindakanLabels = [
                                        'peringatan' => ['Peringatan', 'text-amber-600', 'fa-triangle-exclamation'],
                                        'hentikan_sementara' => [
                                            'Dihentikan Sementara',
                                            'text-orange-600',
                                            'fa-pause-circle',
                                        ],
                                        'keluarkan' => ['Dikeluarkan dari Ujian', 'text-red-600', 'fa-circle-xmark'],
                                    ];
                                @endphp

                                <div id="{{ $tabId }}-panel" class="tab-panel {{ $isDefault ? '' : 'hidden' }}">
                                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                                        {{-- Session Header --}}
                                        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 px-5 py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div
                                                        class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                                        <i class="fa-solid fa-clock text-white/80 text-sm"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h3 class="font-bold text-white text-base truncate">
                                                            {{ $assignment->nama_sesi }}</h3>
                                                        <p class="text-xs text-white/60 truncate">
                                                            {{ $mapelDisplay }}
                                                            <span class="mx-1.5">&middot;</span>
                                                            {{ substr($assignment->waktu_mulai, 0, 5) }} -
                                                            {{ substr($assignment->waktu_selesai, 0, 5) }}
                                                            <span class="mx-1.5">&middot;</span>
                                                            {{ $totalSiswa }} siswa
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($isOngoing)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 bg-green-400/20 text-green-300 text-xs px-2.5 py-1 rounded-full font-semibold border border-green-400/30 flex-shrink-0 ml-2">
                                                        <i class="fa-solid fa-circle text-[6px] tab-ongoing-dot"></i>
                                                        Berlangsung
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 bg-white/10 text-white/50 text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 ml-2">
                                                        <i class="fa-regular fa-circle-check"></i>
                                                        {{ ucfirst($assignment->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Session Content --}}
                                        <div class="p-4 md:p-5 space-y-4">
                                            {{-- TOKEN CARD --}}
                                            <div id="token-card-{{ $assignment->id }}"
                                                class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 p-4">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="fa-solid fa-key text-indigo-600 text-sm"></i>
                                                    <span
                                                        class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Token
                                                        Ujian</span>
                                                </div>
                                                <div id="token-info-{{ $assignment->id }}" class="space-y-2">
                                                    @if ($assignment->token_ujian)
                                                        @php
                                                            $now = now();
                                                            $isExpired =
                                                                $assignment->token_expired_at &&
                                                                $now->greaterThan($assignment->token_expired_at);
                                                            $expiresAt = $assignment->token_expired_at
                                                                ? $assignment->token_expired_at->format('Y-m-d H:i:s')
                                                                : '';
                                                        @endphp
                                                        <div
                                                            class="flex items-center gap-2 bg-white rounded-lg p-3 border {{ $isExpired ? 'border-red-200' : 'border-indigo-200' }}">
                                                            <code
                                                                class="flex-1 text-lg md:text-xl font-mono font-bold {{ $isExpired ? 'text-red-500' : 'text-indigo-700' }} tracking-widest select-all">{{ $assignment->token_ujian }}</code>
                                                            <button onclick="copyToken('{{ $assignment->token_ujian }}')"
                                                                class="{{ $isExpired ? 'text-red-400' : 'text-indigo-600' }} hover:text-gray-700 transition p-1.5 hover:bg-white/70 rounded-lg">
                                                                <i class="fa-regular fa-copy"></i>
                                                            </button>
                                                        </div>
                                                        @if ($assignment->token_expired_at)
                                                            <div id="token-status-{{ $assignment->id }}"
                                                                class="text-xs font-medium {{ $isExpired ? 'text-red-600' : 'text-indigo-600' }}">
                                                                <i
                                                                    class="fa-regular {{ $isExpired ? 'fa-circle-xmark' : 'fa-hourglass-end' }}"></i>
                                                                <span id="token-expiry-text-{{ $assignment->id }}">
                                                                    @if ($isExpired)
                                                                        Token telah kedaluwarsa
                                                                        {{ $assignment->token_expired_at->diffForHumans() }}
                                                                    @else
                                                                        Berlaku hingga
                                                                        {{ $assignment->token_expired_at->format('H:i') }}
                                                                    @endif
                                                                </span>
                                                                @if (!$isExpired)
                                                                    <span id="token-countdown-{{ $assignment->id }}"
                                                                        class="ml-2 font-bold text-indigo-800">
                                                                        (<span
                                                                            id="token-countdown-time-{{ $assignment->id }}"
                                                                            data-expires="{{ $expiresAt }}"></span>)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @else
                                                        <p class="text-sm text-gray-500 italic">Belum ada token</p>
                                                        <div id="token-status-{{ $assignment->id }}" class="hidden"></div>
                                                    @endif
                                                </div>
                                                <button onclick="generateTokenAjax({{ $assignment->id }}, this)"
                                                    class="mt-3 w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium transition duration-200 text-sm">
                                                    <i class="fa-solid fa-rotate"></i>
                                                    <span>Generate Token</span>
                                                </button>
                                            </div>

                                            {{-- ATTENDANCE CARD --}}
                                            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-users text-emerald-600 text-sm"></i>
                                                        <span
                                                            class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Kehadiran</span>
                                                    </div>
                                                    <button onclick="refreshKehadiran({{ $assignment->id }}, this)"
                                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg px-2.5 py-1 transition border border-emerald-200">
                                                        <i class="fa-solid fa-rotate"></i>
                                                        <span>Update</span>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 mb-3">
                                                    <div
                                                        class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-3 text-center border border-emerald-200">
                                                        <div class="text-lg font-bold text-emerald-700"
                                                            id="kehadiran-hadir-{{ $assignment->id }}">{{ $hadir }}
                                                        </div>
                                                        <div class="text-xs font-medium text-emerald-600">Hadir</div>
                                                    </div>
                                                    <div
                                                        class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-3 text-center border border-red-200">
                                                        <div class="text-lg font-bold text-red-700"
                                                            id="kehadiran-tidakhadir-{{ $assignment->id }}">
                                                            {{ $tidakHadir }}
                                                        </div>
                                                        <div class="text-xs font-medium text-red-600">Tidak Hadir</div>
                                                    </div>
                                                    <div
                                                        class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-3 text-center border border-gray-200">
                                                        <div class="text-lg font-bold text-gray-700"
                                                            id="kehadiran-belumabsen-{{ $assignment->id }}">
                                                            {{ $belumAbsen }}
                                                        </div>
                                                        <div class="text-xs font-medium text-gray-600">Belum Absen</div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('pengawas.assignment', $assignment->id) }}"
                                                    class="inline-flex items-center gap-2 text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg px-3 py-2 transition w-full justify-center border border-emerald-200">
                                                    <i class="fa-solid fa-arrow-right"></i>
                                                    <span>Detail Jadwal Pengawasan</span>
                                                </a>
                                            </div>

                                            {{-- BERITA ACARA CARD --}}
                                            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <i class="fa-regular fa-clipboard text-purple-600 text-sm"></i>
                                                    <span
                                                        class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Berita
                                                        Acara</span>
                                                </div>
                                                @php
                                                    $baExists = $beritaAcara && $beritaAcara->count() > 0;
                                                @endphp
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        @if ($baExists)
                                                            <span
                                                                class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 text-xs px-3 py-1.5 rounded-full font-semibold">
                                                                <i class="fa-solid fa-check-circle"></i> Sudah dibuat
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs px-3 py-1.5 rounded-full font-semibold">
                                                                <i class="fa-solid fa-clock"></i> Belum dibuat
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex gap-2">
                                                        @if ($baExists)
                                                            <a href="{{ route('pengawas.berita-acara.show', $assignment->id) }}"
                                                                class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg font-medium transition inline-flex items-center gap-1.5">
                                                                <i class="fa-solid fa-eye"></i>
                                                                <span class="hidden sm:inline">Lihat</span>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('pengawas.berita-acara.create', $assignment->id) }}"
                                                                class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg font-medium transition inline-flex items-center gap-1.5">
                                                                <i class="fa-solid fa-plus"></i>
                                                                <span class="hidden sm:inline">Buat</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- PELANGGARAN CARD --}}
                                            <div class="bg-white rounded-xl border border-red-100 p-4 shadow-sm">
                                                <div class="flex items-center justify-between gap-2 mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <i
                                                            class="fa-solid fa-triangle-exclamation text-red-600 text-sm"></i>
                                                        <span
                                                            class="text-xs font-semibold text-red-700 uppercase tracking-wider">Pelanggaran
                                                            Ujian
                                                            (<span
                                                                id="pelanggaran-count-{{ $assignment->id }}">{{ $pelanggaranList->count() }}</span>)</span>
                                                    </div>
                                                    <select id="pelanggaran-interval-{{ $assignment->id }}"
                                                        onchange="setPelanggaranInterval({{ $assignment->id }}, this.value)"
                                                        class="text-xs border-gray-200 rounded-md py-1 pl-1.5 pr-6 text-gray-500 focus:ring-red-400 focus:border-red-400">
                                                        <option value="0">Manual</option>
                                                        <option value="5000">Tiap 5 detik</option>
                                                        <option value="10000">Tiap 10 detik</option>
                                                        <option value="15000" selected>Tiap 15 detik</option>
                                                        <option value="30000">Tiap 30 detik</option>
                                                        <option value="60000">Tiap 60 detik</option>
                                                    </select>
                                                </div>
                                                <div class="violations-scroll space-y-2 pr-1"
                                                    id="pelanggaran-list-{{ $assignment->id }}"
                                                    data-sesi-id="{{ $assignment->id }}">
                                                    @forelse ($pelanggaranList as $pelanggaran)
                                                        @php
                                                            $isHandled =
                                                                $pelanggaran->is_dismissed ||
                                                                $pelanggaran->is_finalized;
                                                        @endphp
                                                        <div id="pelanggaran-item-{{ $pelanggaran->id }}"
                                                            class="border rounded-xl overflow-hidden {{ $isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-200 bg-red-50/40' }}">
                                                            <div class="w-full flex items-center justify-between gap-2 px-4 py-3 cursor-pointer"
                                                                onclick="togglePelanggaranDetail({{ $pelanggaran->id }})">
                                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                    <div id="pelanggaran-avatar-{{ $pelanggaran->id }}"
                                                                        class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 {{ $isHandled ? 'bg-gray-200' : 'bg-red-100' }}">
                                                                        <i
                                                                            class="fa-solid fa-user text-xs {{ $isHandled ? 'text-gray-500' : 'text-red-600' }}"></i>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <span
                                                                            class="text-sm font-semibold text-gray-900 block truncate">{{ $pelanggaran->siswa?->nama ?? 'Siswa #' . $pelanggaran->siswa_id }}</span>
                                                                        <span class="text-xs font-medium"
                                                                            id="pelanggaran-status-{{ $pelanggaran->id }}">
                                                                            @if ($pelanggaran->tindakan && isset($tindakanLabels[$pelanggaran->tindakan]))
                                                                                @php [$label, $color, $icon] = $tindakanLabels[$pelanggaran->tindakan]; @endphp
                                                                                <span class="{{ $color }}"><i
                                                                                        class="fa-solid {{ $icon }} mr-0.5"></i>{{ $label }}</span>
                                                                            @elseif ($pelanggaran->is_dismissed)
                                                                                <span class="text-emerald-600"><i
                                                                                        class="fa-solid fa-check-circle mr-0.5"></i>Diabaikan</span>
                                                                            @else
                                                                                <span class="text-amber-600"><i
                                                                                        class="fa-solid fa-clock mr-0.5"></i>Menunggu
                                                                                    Tindakan</span>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                                    <div id="pelanggaran-aksi-{{ $pelanggaran->id }}"
                                                                        class="flex-shrink-0 {{ $isHandled ? 'hidden' : '' }}">
                                                                        <button type="button"
                                                                            onclick="event.stopPropagation(); openPelanggaranModal({{ $pelanggaran->id }}, '{{ addslashes($pelanggaran->siswa?->nama ?? 'Siswa #' . $pelanggaran->siswa_id) }}')"
                                                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                                                            <i class="fa-solid fa-gavel"></i>
                                                                            <span class="hidden sm:inline">Tindak
                                                                                Lanjut</span>
                                                                        </button>
                                                                    </div>
                                                                    <i id="pelanggaran-chevron-{{ $pelanggaran->id }}"
                                                                        class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform"></i>
                                                                </div>
                                                            </div>
                                                            <div id="pelanggaran-detail-{{ $pelanggaran->id }}"
                                                                class="hidden px-4 py-3 text-sm text-gray-700 space-y-2 border-t {{ $isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-100 bg-red-50' }}">
                                                                <p><span class="font-semibold">Jenis:</span>
                                                                    {{ $pelanggaran->jenis_pelanggaran }}</p>
                                                                <p><span class="font-semibold">Waktu:</span>
                                                                    {{ $pelanggaran->waktu_pelanggaran ? $pelanggaran->waktu_pelanggaran->format('d M Y H:i:s') : '-' }}
                                                                </p>
                                                                <p><span class="font-semibold">Deskripsi:</span>
                                                                    {{ $pelanggaran->deskripsi ?? '-' }}</p>
                                                                <p id="pelanggaran-catatan-{{ $pelanggaran->id }}"
                                                                    @if (!$pelanggaran->catatan_pengawas) class="hidden" @endif>
                                                                    <span class="font-semibold">Catatan
                                                                        Pengawas:</span>
                                                                    <span
                                                                        id="pelanggaran-catatan-text-{{ $pelanggaran->id }}">{{ $pelanggaran->catatan_pengawas }}</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-center text-xs text-gray-400 py-4">
                                                            <i
                                                                class="fa-solid fa-shield-check text-emerald-500 text-base mb-1 block"></i>
                                                            Tidak ada pelanggaran
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Floating Tab Navigation (sticky bottom, pill-shaped) --}}
                            <div class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-auto max-w-[calc(100vw-2rem)]">
                                <div class="flex items-center gap-1 bg-gray-900/90 tab-nav-blur rounded-full px-2 py-1.5 shadow-2xl shadow-gray-900/30 border border-white/10 overflow-x-auto scrollbar-none"
                                    style="scrollbar-width:none;">
                                    @foreach ($sesiList as $index => $assignment)
                                        @php
                                            $isOngoing = $assignment->status === 'berlangsung';
                                            $isDefault = $assignment->id === $defaultTabId;
                                            $tabLabel = $assignment->nama_sesi;
                                            // Shorten label for mobile
                                            if (strlen($tabLabel) > 12) {
                                                $tabLabel = substr($tabLabel, 0, 10) . '..';
                                            }
                                        @endphp
                                        <button onclick="switchTab('{{ $assignment->id }}')"
                                            id="tab-btn-{{ $assignment->id }}"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200 whitespace-nowrap {{ $isDefault ? 'bg-white text-gray-900 shadow-md' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                                            @if ($isOngoing)
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 tab-ongoing-dot"></span>
                                            @endif
                                            {{ $tabLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-8 md:p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
                            <i class="fa-solid fa-calendar-check text-indigo-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Jadwal Pengawasan</h3>
                        <p class="text-gray-600">Anda tidak memiliki tugas pengawasan untuk hari ini.</p>
                    </div>
                @endif
            </div>
        @else
            {{-- ======================= ADMIN/KOORDINATOR VIEW ======================= --}}
            <div class="p-4 md:p-6 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Pantau Pengawasan</h1>
                        <p class="text-gray-600 mt-1">{{ $assignments->count() }} sesi aktif hari ini</p>
                    </div>
                    <button onclick="document.getElementById('tataTertibModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 bg-white hover:bg-red-50 text-red-600 border-2 border-red-200 rounded-lg px-4 py-2.5 font-medium shadow-sm transition duration-200">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Tata Tertib</span>
                    </button>
                </div>

                @if ($groupedByRuangan->isNotEmpty())
                    <div class="space-y-5">
                        @foreach ($groupedByRuangan as $ruanganId => $sesiList)
                            @php
                                $ruangan = $sesiList->first()->ruangan;
                                $ruanganNama = $ruangan->nama_ruangan ?? 'Tanpa Ruangan';
                                $hasOngoing = $sesiList->contains(fn($s) => $s->status === 'berlangsung');
                            @endphp

                            <div
                                class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition">
                                <div
                                    class="bg-gradient-to-r from-slate-700 to-slate-800 text-white px-5 py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-door-open text-lg"></i>
                                        <h3 class="font-bold text-lg">{{ $ruanganNama }}</h3>
                                        @if ($hasOngoing)
                                            <span
                                                class="inline-flex items-center gap-1.5 bg-green-400 text-green-900 text-xs px-2.5 py-1 rounded-full font-semibold">
                                                <i class="fa-solid fa-circle text-xs animate-pulse"></i>
                                                Berlangsung
                                            </span>
                                        @endif
                                    </div>
                                    <span
                                        class="text-sm bg-white/20 px-3 py-1 rounded-full font-medium">{{ $sesiList->count() }}
                                        sesi</span>
                                </div>
                                <div class="p-4 space-y-3">
                                    @foreach ($sesiList as $assignment)
                                        @php
                                            $isOngoing = $assignment->status === 'berlangsung';
                                            $collapseId = 'admin-sesi-' . $assignment->id;
                                            $jadwalUjians = $assignment->jadwalUjians->sortBy('kode_jadwal');
                                            $mapelNames = [];
                                            foreach ($jadwalUjians as $jadwal) {
                                                $mapelNames[] = $jadwal->mapel->nama_mapel ?? 'Tidak ada mapel';
                                            }
                                            $mapelDisplay =
                                                count($mapelNames) > 0
                                                    ? implode(' + ', $mapelNames)
                                                    : 'Tidak ada jadwal';
                                            $totalSiswa = $assignment->sesiRuanganSiswa->count();
                                            $hadir = $assignment->sesiRuanganSiswa
                                                ->where('status_kehadiran', 'hadir')
                                                ->count();
                                            $tidakHadir = $assignment->sesiRuanganSiswa
                                                ->where('status_kehadiran', 'tidak_hadir')
                                                ->count();
                                        @endphp
                                        <div
                                            class="border rounded-lg {{ $isOngoing ? 'border-green-300 bg-green-50/40' : 'border-gray-200' }} transition hover:border-gray-300">
                                            <div class="flex items-center justify-between p-3 cursor-pointer"
                                                onclick="toggleCollapse('{{ $collapseId }}')">
                                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                                    <i class="fa-solid fa-chevron-down text-gray-400 text-sm transition-transform"
                                                        id="{{ $collapseId }}-icon"></i>
                                                    <div
                                                        class="w-2 h-2 rounded-full flex-shrink-0 {{ $isOngoing ? 'bg-green-500' : ($assignment->status === 'selesai' ? 'bg-blue-500' : 'bg-gray-400') }}">
                                                    </div>
                                                    <span
                                                        class="text-sm font-bold text-gray-900 truncate">{{ $assignment->nama_sesi }}</span>
                                                    <span
                                                        class="text-xs text-gray-500 hidden sm:inline">{{ substr($assignment->waktu_mulai, 0, 5) }}
                                                        - {{ substr($assignment->waktu_selesai, 0, 5) }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0 ml-2"
                                                    onclick="event.stopPropagation()">
                                                    <!-- Toggle Submit Button -->
                                                    <form action="{{ route('pengawas.toggle-submit', $assignment->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" name="tampilkan"
                                                            value="{{ $assignment->tampilkan_tombol_submit ? 0 : 1 }}"
                                                            class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg border font-medium transition
                                                                {{ $assignment->tampilkan_tombol_submit ? 'bg-green-100 border-green-300 text-green-700 hover:bg-green-200' : 'bg-gray-100 border-gray-300 text-gray-600 hover:bg-gray-200' }}">
                                                            <i
                                                                class="fas {{ $assignment->tampilkan_tombol_submit ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                            <span
                                                                class="hidden md:inline">{{ $assignment->tampilkan_tombol_submit ? 'Submit' : 'Off' }}</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div id="{{ $collapseId }}" class="hidden border-t border-gray-100">
                                                <div class="p-4 space-y-4">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                        <div
                                                            class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                            <div class="text-lg font-bold text-gray-900">
                                                                {{ $totalSiswa }}</div>
                                                            <div class="text-xs text-gray-600 font-medium">Total</div>
                                                        </div>
                                                        <div
                                                            class="bg-emerald-50 rounded-lg p-3 border border-emerald-200 text-center">
                                                            <div class="text-lg font-bold text-emerald-700">
                                                                {{ $hadir }}</div>
                                                            <div class="text-xs text-emerald-600 font-medium">Hadir</div>
                                                        </div>
                                                        <div
                                                            class="bg-red-50 rounded-lg p-3 border border-red-200 text-center">
                                                            <div class="text-lg font-bold text-red-700">
                                                                {{ $tidakHadir }}</div>
                                                            <div class="text-xs text-red-600 font-medium">Tidak Hadir</div>
                                                        </div>
                                                        <div
                                                            class="bg-indigo-50 rounded-lg p-3 border border-indigo-200 text-center">
                                                            <div class="text-lg font-bold text-indigo-700">
                                                                {{ $assignment->pelanggaran_total ?? 0 }}</div>
                                                            <div class="text-xs text-indigo-600 font-medium">Pelanggaran
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="{{ route('pengawas.generate-token', $assignment->id) }}"
                                                            class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-medium transition">
                                                            <i class="fa-solid fa-key mr-1"></i> Token
                                                        </a>
                                                        <a href="{{ route('pengawas.assignment', $assignment->id) }}"
                                                            class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg font-medium transition">
                                                            <i class="fa-solid fa-users mr-1"></i> Kehadiran
                                                        </a>
                                                        <a href="{{ route('pengawas.manage-enrollment', $assignment->id) }}"
                                                            class="text-xs bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg font-medium transition">
                                                            <i class="fa-solid fa-user-cog mr-1"></i> Enrollment
                                                        </a>
                                                        <a href="{{ route('pengawas.berita-acara.show', $assignment->id) }}"
                                                            class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-medium transition">
                                                            <i class="fa-regular fa-clipboard mr-1"></i> Berita Acara
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-8 md:p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
                            <i class="fa-solid fa-calendar-times text-indigo-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Jadwal Pengawasan</h3>
                        <p class="text-gray-600">Tidak ada sesi ujian yang berlangsung hari ini.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ======================= MODAL TINDAK LANJUT PELANGGARAN ======================= --}}
    <div id="pelanggaran-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closePelanggaranModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold text-base">
                    <i class="fa-solid fa-gavel mr-2"></i>Tindak Lanjut Pelanggaran
                </h3>
                <button onclick="closePelanggaranModal()" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <p class="text-sm text-gray-600">
                    Siswa: <span id="pelanggaran-modal-siswa" class="font-semibold text-gray-900"></span>
                </p>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Pilih
                        Tindakan</label>

                    <label
                        class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="pelanggaran-action" value="dismiss" class="mt-1" checked>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Abaikan Pelanggaran</span>
                            <span class="block text-xs text-gray-500">Tidak ada pelanggaran berarti, siswa lanjut ujian
                                seperti biasa.</span>
                        </span>
                    </label>

                    <label
                        class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                        <input type="radio" name="pelanggaran-action" value="warning" class="mt-1">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Beri Peringatan</span>
                            <span class="block text-xs text-gray-500">Siswa diberi peringatan tertulis, ujian tetap
                                dilanjutkan.</span>
                        </span>
                    </label>

                    <label
                        class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
                        <input type="radio" name="pelanggaran-action" value="suspend" class="mt-1">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Hentikan Sementara</span>
                            <span class="block text-xs text-gray-500">Siswa di-logout dan enrollment dibatalkan sementara
                                untuk diperiksa lebih lanjut.</span>
                        </span>
                    </label>

                    <label
                        class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                        <input type="radio" name="pelanggaran-action" value="remove" class="mt-1">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Keluarkan dari Ujian</span>
                            <span class="block text-xs text-gray-500">Pelanggaran berat, enrollment dihapus dan ujian siswa
                                diakhiri (terminated).</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Pengawas
                        (opsional)</label>
                    <textarea id="pelanggaran-modal-catatan" rows="2"
                        class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                        placeholder="Tambahkan catatan mengenai tindakan ini..."></textarea>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button onclick="closePelanggaranModal()"
                    class="flex-1 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-lg transition">
                    Batal
                </button>
                <button id="pelanggaran-modal-submit" onclick="submitPelanggaranAction()"
                    class="flex-1 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 px-4 py-2.5 rounded-lg transition">
                    <i class="fa-solid fa-check mr-1"></i> Terapkan
                </button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(sesiId) {
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
            // Show selected panel
            document.getElementById(`tab-${sesiId}-panel`).classList.remove('hidden');

            // Update tab buttons
            document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
                btn.classList.remove('bg-white', 'text-gray-900', 'shadow-md');
                btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
            });
            const activeBtn = document.getElementById(`tab-btn-${sesiId}`);
            if (activeBtn) {
                activeBtn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                activeBtn.classList.add('bg-white', 'text-gray-900', 'shadow-md');
            }
        }

        function toggleCollapse(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (el) {
                el.classList.toggle('hidden');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        }

        let activePelanggaranId = null;

        function openPelanggaranModal(pelanggaranId, siswaNama) {
            activePelanggaranId = pelanggaranId;
            document.getElementById('pelanggaran-modal-siswa').textContent = siswaNama;
            document.getElementById('pelanggaran-modal-catatan').value = '';
            document.querySelector('input[name="pelanggaran-action"][value="dismiss"]').checked = true;
            document.getElementById('pelanggaran-modal').classList.remove('hidden');
            document.getElementById('pelanggaran-modal').classList.add('flex');
        }

        function closePelanggaranModal() {
            activePelanggaranId = null;
            document.getElementById('pelanggaran-modal').classList.add('hidden');
            document.getElementById('pelanggaran-modal').classList.remove('flex');
        }

        function submitPelanggaranAction() {
            if (!activePelanggaranId) return;

            const pelanggaranId = activePelanggaranId;
            const action = document.querySelector('input[name="pelanggaran-action"]:checked').value;
            const catatan = document.getElementById('pelanggaran-modal-catatan').value;
            const submitBtn = document.getElementById('pelanggaran-modal-submit');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses...';

            fetch(`/features/pengawas/process-violation/${pelanggaranId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        catatan_pengawas: catatan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusMap = {
                            dismiss: '<span class="text-emerald-600"><i class="fa-solid fa-check-circle mr-0.5"></i>Diabaikan</span>',
                            warning: '<span class="text-amber-600"><i class="fa-solid fa-triangle-exclamation mr-0.5"></i>Peringatan</span>',
                            suspend: '<span class="text-orange-600"><i class="fa-solid fa-pause-circle mr-0.5"></i>Dihentikan Sementara</span>',
                            remove: '<span class="text-red-600"><i class="fa-solid fa-circle-xmark mr-0.5"></i>Dikeluarkan dari Ujian</span>',
                        };

                        const statusEl = document.getElementById(`pelanggaran-status-${pelanggaranId}`);
                        if (statusEl) statusEl.innerHTML = statusMap[action] || statusEl.innerHTML;

                        const aksiEl = document.getElementById(`pelanggaran-aksi-${pelanggaranId}`);
                        if (aksiEl) aksiEl.classList.add('hidden');

                        // Update colors to "sudah ditindak" (resolved) state
                        const itemEl = document.getElementById(`pelanggaran-item-${pelanggaranId}`);
                        if (itemEl) {
                            itemEl.classList.remove('border-red-200', 'bg-red-50/40');
                            itemEl.classList.add('border-gray-200', 'bg-gray-50');
                        }

                        const avatarEl = document.getElementById(`pelanggaran-avatar-${pelanggaranId}`);
                        if (avatarEl) {
                            avatarEl.classList.remove('bg-red-100');
                            avatarEl.classList.add('bg-gray-200');
                            const avatarIcon = avatarEl.querySelector('i');
                            if (avatarIcon) {
                                avatarIcon.classList.remove('text-red-600');
                                avatarIcon.classList.add('text-gray-500');
                            }
                        }

                        const detailEl = document.getElementById(`pelanggaran-detail-${pelanggaranId}`);
                        if (detailEl) {
                            detailEl.classList.remove('border-red-100', 'bg-red-50');
                            detailEl.classList.add('border-gray-200', 'bg-gray-50');
                        }

                        if (catatan) {
                            const catatanWrap = document.getElementById(`pelanggaran-catatan-${pelanggaranId}`);
                            const catatanText = document.getElementById(`pelanggaran-catatan-text-${pelanggaranId}`);
                            if (catatanWrap && catatanText) {
                                catatanWrap.classList.remove('hidden');
                                catatanText.textContent = catatan;
                            }
                        }

                        closePelanggaranModal();

                        const toast = document.createElement('div');
                        toast.className =
                            'fixed bottom-24 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                        toast.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                        document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            toast.style.transition = 'opacity 0.3s ease-out';
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    } else {
                        alert(data.message || 'Gagal memproses pelanggaran');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses pelanggaran');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        function refreshKehadiran(sesiId, btn) {
            const icon = btn.querySelector('i');
            btn.disabled = true;
            icon.classList.add('fa-spin');

            fetch(`/features/pengawas/assignment/${sesiId}/attendance-summary`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`kehadiran-hadir-${sesiId}`).textContent = data.data.hadir;
                        document.getElementById(`kehadiran-tidakhadir-${sesiId}`).textContent = data.data.tidak_hadir;
                        document.getElementById(`kehadiran-belumabsen-${sesiId}`).textContent = data.data
                            .belum_absen;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    icon.classList.remove('fa-spin');
                    btn.disabled = false;
                });
        }

        // ===================== Pelanggaran auto-refresh =====================
        const pelanggaranTimers = {};
        const pelanggaranTindakanLabels = {
            peringatan: ['Peringatan', 'text-amber-600', 'fa-triangle-exclamation'],
            hentikan_sementara: ['Dihentikan Sementara', 'text-orange-600', 'fa-pause-circle'],
            keluarkan: ['Dikeluarkan dari Ujian', 'text-red-600', 'fa-circle-xmark'],
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function pelanggaranStatusBadge(p) {
            if (p.tindakan && pelanggaranTindakanLabels[p.tindakan]) {
                const [label, color, icon] = pelanggaranTindakanLabels[p.tindakan];
                return `<span class="${color}"><i class="fa-solid ${icon} mr-0.5"></i>${label}</span>`;
            }
            if (p.is_dismissed) {
                return '<span class="text-emerald-600"><i class="fa-solid fa-check-circle mr-0.5"></i>Diabaikan</span>';
            }
            return '<span class="text-amber-600"><i class="fa-solid fa-clock mr-0.5"></i>Menunggu Tindakan</span>';
        }

        function renderPelanggaranItem(p) {
            const isHandled = p.is_dismissed || p.is_finalized;
            const itemColor = isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-200 bg-red-50/40';
            const avatarColor = isHandled ? 'bg-gray-200' : 'bg-red-100';
            const avatarIconColor = isHandled ? 'text-gray-500' : 'text-red-600';
            const detailColor = isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-100 bg-red-50';
            const namaSiswa = p.siswa?.nama ?? `Siswa #${p.siswa_id}`;
            const waktu = p.waktu_pelanggaran ? new Date(p.waktu_pelanggaran).toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }) : '-';
            const catatanHidden = p.catatan_pengawas ? '' : 'hidden';

            return `
                <div id="pelanggaran-item-${p.id}" class="border rounded-xl overflow-hidden ${itemColor}">
                    <div class="w-full flex items-center justify-between gap-2 px-4 py-3 cursor-pointer" onclick="togglePelanggaranDetail(${p.id})">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div id="pelanggaran-avatar-${p.id}" class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 ${avatarColor}">
                                <i class="fa-solid fa-user text-xs ${avatarIconColor}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-semibold text-gray-900 block truncate">${escapeHtml(namaSiswa)}</span>
                                <span class="text-xs font-medium" id="pelanggaran-status-${p.id}">${pelanggaranStatusBadge(p)}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div id="pelanggaran-aksi-${p.id}" class="flex-shrink-0 ${isHandled ? 'hidden' : ''}">
                                <button type="button" onclick="event.stopPropagation(); openPelanggaranModal(${p.id}, '${escapeHtml(namaSiswa).replace(/'/g, "\\'")}')"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    <i class="fa-solid fa-gavel"></i>
                                    <span class="hidden sm:inline">Tindak Lanjut</span>
                                </button>
                            </div>
                            <i id="pelanggaran-chevron-${p.id}" class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform"></i>
                        </div>
                    </div>
                    <div id="pelanggaran-detail-${p.id}" class="hidden px-4 py-3 text-sm text-gray-700 space-y-2 border-t ${detailColor}">
                        <p><span class="font-semibold">Jenis:</span> ${escapeHtml(p.jenis_pelanggaran)}</p>
                        <p><span class="font-semibold">Waktu:</span> ${waktu}</p>
                        <p><span class="font-semibold">Deskripsi:</span> ${escapeHtml(p.deskripsi ?? '-')}</p>
                        <p id="pelanggaran-catatan-${p.id}" class="${catatanHidden}">
                            <span class="font-semibold">Catatan Pengawas:</span>
                            <span id="pelanggaran-catatan-text-${p.id}">${escapeHtml(p.catatan_pengawas ?? '')}</span>
                        </p>
                    </div>
                </div>
            `;
        }

        function togglePelanggaranDetail(id) {
            const detail = document.getElementById(`pelanggaran-detail-${id}`);
            const chevron = document.getElementById(`pelanggaran-chevron-${id}`);
            if (!detail) return;
            detail.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        function refreshPelanggaranList(sesiId) {
            fetch(`/features/pengawas/get-violations/${sesiId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) return;

                    const listEl = document.getElementById(`pelanggaran-list-${sesiId}`);
                    const countEl = document.getElementById(`pelanggaran-count-${sesiId}`);
                    if (!listEl) return;

                    if (countEl) countEl.textContent = data.violations.length;

                    if (data.violations.length === 0) {
                        listEl.innerHTML = `
                            <div class="text-center text-xs text-gray-400 py-4">
                                <i class="fa-solid fa-shield-check text-emerald-500 text-base mb-1 block"></i>
                                Tidak ada pelanggaran
                            </div>
                        `;
                        return;
                    }

                    const expandedIds = new Set();
                    listEl.querySelectorAll('[id^="pelanggaran-detail-"]').forEach(el => {
                        if (!el.classList.contains('hidden')) {
                            expandedIds.add(el.id.replace('pelanggaran-detail-', ''));
                        }
                    });

                    listEl.innerHTML = data.violations.map(renderPelanggaranItem).join('');

                    expandedIds.forEach(id => {
                        const detail = document.getElementById(`pelanggaran-detail-${id}`);
                        const chevron = document.getElementById(`pelanggaran-chevron-${id}`);
                        if (detail) detail.classList.remove('hidden');
                        if (chevron) chevron.classList.add('rotate-180');
                    });
                })
                .catch(error => console.error('Error refreshing pelanggaran:', error));
        }

        function setPelanggaranInterval(sesiId, ms) {
            ms = parseInt(ms, 10);

            if (pelanggaranTimers[sesiId]) {
                clearInterval(pelanggaranTimers[sesiId]);
                delete pelanggaranTimers[sesiId];
            }

            if (ms > 0) {
                pelanggaranTimers[sesiId] = setInterval(() => refreshPelanggaranList(sesiId), ms);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[id^="pelanggaran-interval-"]').forEach(select => {
                const sesiId = select.id.replace('pelanggaran-interval-', '');
                setPelanggaranInterval(sesiId, select.value);
            });
        });

        function copyToken(token) {
            navigator.clipboard.writeText(token).then(() => {
                const toast = document.createElement('div');
                toast.className =
                    'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                toast.innerHTML = '<i class="fa-solid fa-check-circle"></i> Token berhasil disalin!';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }).catch(() => {
                alert('Gagal menyalin token');
            });
        }

        function generateTokenAjax(sesiId, btn) {
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';

            fetch(`/features/pengawas/generate-token/${sesiId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tokenInfo = document.getElementById(`token-info-${sesiId}`);

                        tokenInfo.innerHTML = `
                            <div class="flex items-center gap-2 bg-white rounded-lg p-3 border border-indigo-200">
                                <code class="flex-1 text-lg md:text-xl font-mono font-bold text-indigo-700 tracking-widest select-all">${data.data.token}</code>
                                <button onclick="copyToken('${data.data.token}')" class="text-indigo-600 hover:text-gray-700 transition p-1.5 hover:bg-white/70 rounded-lg">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <div id="token-status-${sesiId}" class="text-xs font-medium text-indigo-600">
                                <i class="fa-regular fa-hourglass-end"></i>
                                <span id="token-expiry-text-${sesiId}">Berlaku hingga ${data.data.expires_at_formatted}</span>
                                <span id="token-countdown-${sesiId}" class="ml-2 font-bold text-indigo-800">
                                    (<span id="token-countdown-time-${sesiId}" data-expires="${data.data.expires_at}"></span>)
                                </span>
                            </div>
                        `;

                        startCountdownForToken(sesiId);

                        const toast = document.createElement('div');
                        toast.className =
                            'fixed bottom-24 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                        toast.innerHTML = '<i class="fa-solid fa-check-circle"></i> Token berhasil dibuat!';
                        document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            toast.style.transition = 'opacity 0.3s ease-out';
                            setTimeout(() => toast.remove(), 300);
                        }, 2000);

                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } else {
                        alert(data.message || 'Gagal membuat token');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membuat token');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        function startCountdownForToken(sesiId) {
            const countdownEl = document.getElementById(`token-countdown-time-${sesiId}`);
            if (!countdownEl) return;

            const expiresAt = countdownEl.getAttribute('data-expires');
            if (!expiresAt) return;

            const updateCountdown = () => {
                const now = new Date();
                const expiry = new Date(expiresAt);
                const diff = expiry - now;

                if (diff <= 0) {
                    countdownEl.textContent = '00:00';
                    const statusEl = document.getElementById(`token-status-${sesiId}`);
                    const tokenCodeEl = document.getElementById(`token-card-${sesiId}`).querySelector('code');
                    const tokenBorderEl = document.getElementById(`token-card-${sesiId}`).querySelector('.border');
                    const tokenCopyBtn = document.getElementById(`token-card-${sesiId}`).querySelector(
                        'button[onclick*="copyToken"]');
                    const iconEl = statusEl?.querySelector('i');
                    const textEl = document.getElementById(`token-expiry-text-${sesiId}`);
                    const countdownSpan = document.getElementById(`token-countdown-${sesiId}`);

                    if (statusEl) statusEl.className = 'text-xs font-medium text-red-600';
                    if (tokenCodeEl) tokenCodeEl.className =
                        'flex-1 text-xl font-mono font-bold text-red-500 tracking-widest select-all';
                    if (tokenBorderEl) tokenBorderEl.className = tokenBorderEl.className.replace('border-indigo-200',
                        'border-red-300');
                    if (tokenCopyBtn) tokenCopyBtn.className = tokenCopyBtn.className.replace('text-indigo-600',
                        'text-red-500');
                    if (iconEl) iconEl.className = 'fa-regular fa-circle-xmark';
                    if (textEl) textEl.textContent = 'Token telah kedaluwarsa';
                    if (countdownSpan) countdownSpan.remove();

                    return;
                }

                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                countdownEl.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                setTimeout(updateCountdown, 1000);
            };

            updateCountdown();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Start countdown timers for all existing tokens
            document.querySelectorAll('[id^="token-countdown-time-"]').forEach(el => {
                const sesiId = el.id.replace('token-countdown-time-', '');
                startCountdownForToken(sesiId);
            });
        });
    </script>
@endsection
