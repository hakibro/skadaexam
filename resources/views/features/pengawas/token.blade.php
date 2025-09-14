@extends('layouts.admin')

@section('title', 'Generate Token')
@section('page-title', 'Generate Token Ujian')
@section('page-description', 'Buat token untuk login siswa dalam ujian')

@section('styles')
    <style>
        .token-display {
            font-family: monospace;
            font-size: 2.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            padding: 1.5rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
            border: 2px dashed #d1d5db;
        }

        .token-expired {
            background-color: #fee2e2;
            border: 2px dashed #ef4444;
        }

        .token-active {
            background-color: #dcfce7;
            border: 2px dashed #22c55e;
        }

        .token-pending {
            background-color: #ffedd5;
            border: 2px dashed #f97316;
        }

        .countdown {
            font-size: 1rem;
            letter-spacing: normal;
            margin-top: 0.5rem;
            display: block;
        }
    </style>
@endsection

@section('content')
    <div>
        <div class="mb-6">
            <a href="{{ route('pengawas.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-green-700">
                    <i class="fa-solid fa-key mr-2"></i>
                    Generate Token Ujian
                </h2>
                <p class="text-gray-600 mt-1">Generate token untuk siswa login ke sistem ujian.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Informasi Ujian</h3>
                    <table class="w-full">
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Mata Pelajaran</td>
                            <td class="py-1 font-bold">
                                @if ($sesiRuangan->jadwalUjians->count() > 1)
                                    @php
                                        $mapelNames = $sesiRuangan->jadwalUjians
                                            ->filter(function ($jadwal) {
                                                return $jadwal->mapel !== null;
                                            })
                                            ->map(function ($jadwal) {
                                                return $jadwal->mapel->nama_mapel;
                                            })
                                            ->unique();
                                    @endphp
                                    @if ($mapelNames->count() > 0)
                                        {{ $mapelNames->implode(' + ') }}
                                        @if ($mapelNames->count() != $sesiRuangan->jadwalUjians->count())
                                            <span class="text-sm text-gray-500">({{ $mapelNames->count() }} dari
                                                {{ $sesiRuangan->jadwalUjians->count() }} mapel)</span>
                                        @else
                                            <span class="text-sm text-gray-500">({{ $mapelNames->count() }} mapel)</span>
                                        @endif
                                    @else
                                        <span class="text-red-500">Tidak ada mapel tersedia</span>
                                        <span class="text-sm text-gray-500">({{ $sesiRuangan->jadwalUjians->count() }}
                                            jadwal)</span>
                                    @endif
                                @elseif($sesiRuangan->jadwalUjians->count() == 1)
                                    @php
                                        $jadwal = $sesiRuangan->jadwalUjians->first();
                                    @endphp
                                    @if ($jadwal->mapel)
                                        {{ $jadwal->mapel->nama_mapel }}
                                    @else
                                        <span class="text-red-500">Mapel tidak tersedia</span>
                                        <span class="text-sm text-gray-500">(ID: {{ $jadwal->id }}, Mapel ID:
                                            {{ $jadwal->mapel_id ?? 'NULL' }})</span>
                                    @endif
                                @else
                                    <span class="text-red-500">Tidak ada jadwal</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Ruangan</td>
                            <td class="py-1 font-bold">
                                {{ $sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Sesi</td>
                            <td class="py-1 font-bold">{{ $sesiRuangan->nama_sesi }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Waktu</td>
                            <td class="py-1 font-bold">{{ $sesiRuangan->waktu_mulai }} - {{ $sesiRuangan->waktu_selesai }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Jumlah Siswa</td>
                            <td class="py-1 font-bold">{{ $sesiRuangan->sesiRuanganSiswa->count() }} siswa</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-600 font-medium">Status Sesi</td>
                            <td class="py-1">
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesiRuangan->status_badge_class }}">
                                    {{ $sesiRuangan->status_label['text'] }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Status Token</h3>

                    @if ($sesiRuangan->token_ujian && $sesiRuangan->token_expired_at && now()->lt($sesiRuangan->token_expired_at))
                        <div class="text-center">
                            <div class="token-display token-active">
                                {{ $sesiRuangan->token_ujian }}
                                <span class="countdown" id="token-countdown"
                                    data-expires="{{ $sesiRuangan->token_expired_at }}">
                                    Token berlaku hingga {{ $sesiRuangan->token_expired_at->format('H:i') }}
                                </span>
                            </div>

                            <div class="mt-4 text-sm text-gray-500">
                                <div class="flex items-center justify-center mb-2">
                                    <i class="fa-solid fa-circle-info text-blue-500 mr-2"></i>
                                    <span>Token aktif dan dapat digunakan siswa untuk login</span>
                                </div>

                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-clock text-blue-500 mr-2"></i>
                                    <span>Token dibuat pada
                                        @if ($sesiRuangan->token_expired_at)
                                            {{ $sesiRuangan->token_expired_at->copy()->subHours(4)->format('H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @elseif ($sesiRuangan->token_ujian && $sesiRuangan->token_expired_at)
                        <div class="text-center">
                            <div class="token-display token-expired">
                                {{ $sesiRuangan->token_ujian }}
                                <span class="countdown">
                                    Token sudah kadaluarsa
                                </span>
                            </div>

                            <div class="mt-4 text-sm text-gray-500">
                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-clock text-red-500 mr-2"></i>
                                    <span>Token kadaluarsa pada
                                        @if ($sesiRuangan->token_expired_at)
                                            {{ $sesiRuangan->token_expired_at->format('d M Y H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-2">Generate token baru untuk melanjutkan ujian.</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="token-display token-pending">
                                ------
                                <span class="countdown">
                                    Token belum dibuat
                                </span>
                            </div>

                            <div class="mt-4 text-sm text-gray-500">
                                <p>Generate token untuk memulai ujian.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Generate Token Baru</h3>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-800">
                                Token akan berlaku selama waktu yang ditentukan. Pastikan semua siswa telah login sebelum
                                token kadaluarsa.
                                Token yang sudah kadaluarsa tidak dapat digunakan dan perlu di-generate ulang.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('pengawas.store-token', $sesiRuangan->id) }}" method="POST"
                    class="flex items-end gap-4">
                    @csrf
                    <div class="flex-grow">
                        <label for="expiry_minutes" class="block text-sm font-medium text-gray-700 mb-1">
                            Masa Berlaku Token
                        </label>
                        <select id="expiry_minutes" name="expiry_minutes"
                            class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="3">3 menit</option>
                            <option value="5" selected>5 menit</option>
                            <option value="10">10 menit</option>
                            <option value="15">15 menit</option>
                            <option value="20">20 menit</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fa-solid fa-key mr-2"></i>
                            Generate Token
                        </button>
                    </div>
                </form>

                @if ($sesiRuangan->token_ujian)
                    <div class="mt-6">
                        <button id="copyTokenBtn"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fa-solid fa-copy mr-2"></i>
                            Salin Token
                        </button>
                        <span id="copyMessage" class="ml-2 text-green-600 hidden">Token disalin!</span>
                    </div>
                @endif
            </div>

            <!-- Information Box -->
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Petunjuk Penggunaan Token</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Token ini digunakan untuk login siswa ke sistem ujian</li>
                                <li>Bagikan token kepada siswa saat ujian akan dimulai</li>
                                <li>Pastikan siswa memasukkan token dengan benar</li>
                                <li>Token bersifat case-sensitive (huruf besar/kecil berpengaruh)</li>
                                <li>Jika token kadaluarsa, generate token baru</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Copy token functionality
            const copyTokenBtn = document.getElementById('copyTokenBtn');
            if (copyTokenBtn) {
                const copyMessage = document.getElementById('copyMessage');

                copyTokenBtn.addEventListener('click', function() {
                    const tokenDisplay = document.querySelector('.token-display');
                    const tokenText = tokenDisplay.innerText.split('\n')[0].trim();

                    // Create temporary textarea to copy from
                    const textarea = document.createElement('textarea');
                    textarea.value = tokenText;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);

                    // Show copy message
                    copyMessage.classList.remove('hidden');
                    setTimeout(() => {
                        copyMessage.classList.add('hidden');
                    }, 2000);
                });
            }

            // Countdown timer for token
            const tokenCountdown = document.getElementById('token-countdown');
            if (tokenCountdown) {
                const expiresAt = new Date(tokenCountdown.dataset.expires);

                function updateCountdown() {
                    const now = new Date();
                    const diffMs = expiresAt - now;

                    if (diffMs <= 0) {
                        tokenCountdown.innerText = 'Token sudah kadaluarsa';
                        document.querySelector('.token-display').classList.remove('token-active');
                        document.querySelector('.token-display').classList.add('token-expired');
                        return;
                    }

                    // Calculate hours, minutes, seconds
                    const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                    const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);

                    // Format with leading zeros
                    const hours = String(diffHrs).padStart(2, '0');
                    const minutes = String(diffMins).padStart(2, '0');
                    const seconds = String(diffSecs).padStart(2, '0');

                    // Update the countdown text
                    tokenCountdown.innerText = `Berlaku selama: ${minutes}:${seconds}`;

                    // Schedule next update
                    setTimeout(updateCountdown, 1000);
                }

                // Start the countdown
                updateCountdown();
            }
        });
    </script>
@endsection
