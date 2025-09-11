@extends('layouts.admin')

@section('title', 'Jadwal Lengkap Pengawas')
@section('page-title', 'Jadwal Lengkap Pengawas')
@section('page-description', 'Lihat semua jadwal penugasan pengawas')

@section('styles')
    <style>
        @media print {
            body {
                font-size: 12pt;
            }

            .no-print {
                display: none !important;
            }

            .print-full-width {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .page-break {
                page-break-after: always;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-belum_mulai {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-berlangsung {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-selesai {
            background-color: #f3f4f6;
            color: #374151;
        }

        .status-dibatalkan {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .mapel-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="flex flex-wrap items-center justify-between mb-6">
        <div>
            <a href="{{ route('koordinator.pengawas-assignment.calendar') }}"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 no-print">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Kembali ke Kalender
            </a>
        </div>
        <div class="no-print">
            <button onclick="window.print()"
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6 print-full-width">
        <div class="flex flex-wrap items-center gap-4">
            <div class="bg-indigo-100 rounded-full p-2.5">
                <i class="fa-solid fa-user-tie text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $pengawas->nama }}</h1>
                <p class="text-gray-600">
                    {{ $pengawas->nip ?? 'Tanpa NIP' }} |
                    <span class="text-gray-500">{{ $pengawas->email ?? 'Tanpa Email' }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 print-full-width">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Jadwal Penugasan Pengawas</h2>

        @if (count($groupedSessions) == 0)
            <div class="bg-yellow-50 p-4 rounded-md border border-yellow-100 text-center">
                <i class="fa-solid fa-exclamation-circle text-yellow-500 text-xl mb-2"></i>
                <p class="text-yellow-700 font-medium">Tidak ada jadwal penugasan yang ditemukan.</p>
                <p class="text-yellow-600 text-sm mt-1">Pengawas belum memiliki jadwal atau semua jadwal telah selesai.</p>
            </div>
        @else
            {{-- Dates are already sorted in the controller --}}
            @foreach ($groupedSessions as $date => $sessions)
                <div class="mb-8 @if (!$loop->last) page-break @endif">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fa-solid fa-calendar-day text-indigo-500 mr-2"></i>
                        {{ $date }}
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                        No
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                        Sesi
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                        Waktu
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-indigo-600 uppercase tracking-wider border-r">
                                        Mata Pelajaran
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                        Ruangan
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($sessions as $index => $session)
                                    <tr @if ($index % 2 != 0) class="bg-gray-50" @endif>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r">
                                            {{ $session['nama_sesi'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r">
                                            {{ substr($session['waktu_mulai'], 0, 5) }} -
                                            {{ substr($session['waktu_selesai'], 0, 5) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 border-r font-medium bg-indigo-50 mapel-cell"
                                            title="{{ $session['mapel'] }}">
                                            {{ $session['mapel'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 border-r">
                                            {{ $session['ruangan'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span class="status-badge status-{{ $session['status'] }}">
                                                @switch($session['status'])
                                                    @case('belum_mulai')
                                                        Belum Mulai
                                                    @break

                                                    @case('berlangsung')
                                                        Sedang Berlangsung
                                                    @break

                                                    @case('selesai')
                                                        Selesai
                                                    @break

                                                    @case('dibatalkan')
                                                        Dibatalkan
                                                    @break

                                                    @default
                                                        {{ ucfirst($session['status']) }}
                                                @endswitch
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="mt-8 print-full-width">
        <div class="text-sm text-gray-500 text-center">
            <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto print when the page loads if ?print=true parameter is present
            if (window.location.search.includes('print=true')) {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        });
    </script>
@endsection
