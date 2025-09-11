@extends('layouts.admin')

@section('title', 'Jadwal Pengawas')
@section('page-title', 'Jadwal Pengawas')
@section('page-description', 'Detail jadwal sesi pengawasan untuk pengawas')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $pengawas->nama }}</h2>
                    <p class="text-gray-600">NIP: {{ $pengawas->nip ?? 'N/A' }}</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="text-gray-700">Tanggal:
                        <strong>{{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}</strong></span>
                </div>
            </div>

            @if ($sessions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi
                                </th>
                                <th
                                    class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruangan
                                </th>
                                <th
                                    class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th
                                    class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th
                                    class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sessions as $session)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $session['nama_sesi'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $session['ruangan'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ substr($session['waktu_mulai'], 0, 5) }} -
                                        {{ substr($session['waktu_selesai'], 0, 5) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $session['mapel'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php
                                            $statusColor = 'gray';
                                            switch ($session['status']) {
                                                case 'belum_mulai':
                                                    $statusColor = 'blue';
                                                    $statusText = 'Belum Mulai';
                                                    break;
                                                case 'berlangsung':
                                                    $statusColor = 'green';
                                                    $statusText = 'Berlangsung';
                                                    break;
                                                case 'selesai':
                                                    $statusColor = 'gray';
                                                    $statusText = 'Selesai';
                                                    break;
                                                case 'dibatalkan':
                                                    $statusColor = 'red';
                                                    $statusText = 'Dibatalkan';
                                                    break;
                                                default:
                                                    $statusText = 'Unknown';
                                            }
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-between items-center">
                    <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>

                    <button id="printButton"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-blue transition ease-in-out duration-150">
                        <i class="fa-solid fa-print mr-2"></i> Cetak Jadwal
                    </button>
                </div>
            @else
                <div class="bg-yellow-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Tidak ada jadwal pengawasan untuk pengawas ini pada tanggal tersebut.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($sessions->count() > 0)
    @section('scripts')
        <script>
            document.getElementById('printButton').addEventListener('click', function() {
                window.print();
            });
        </script>
    @endsection
@endif
