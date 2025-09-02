@extends('layouts.app')

@section('title', 'Enrollment Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Enrollment Management</h1>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                    <p>{{ session('warning') }}</p>
                </div>
            @endif

            @if (session('enrollmentResult'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                    <h3 class="font-bold">Hasil Pendaftaran:</h3>
                    <p>Berhasil: {{ session('enrollmentResult')['success'] }}</p>
                    <p>Gagal: {{ session('enrollmentResult')['failed'] }}</p>
                    <p>Sudah terdaftar: {{ session('enrollmentResult')['already_enrolled'] }}</p>
                    @if (!empty(session('enrollmentResult')['errors']))
                        <p class="mt-2"><strong>Errors:</strong></p>
                        <ul class="list-disc ml-5">
                            @foreach (session('enrollmentResult')['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jadwal Ujian</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata
                                Pelajaran</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa
                                Terdaftar</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($jadwalUjianList as $jadwal)
                            <tr>
                                <td class="py-3 px-4">{{ $jadwal->nama }}</td>
                                <td class="py-3 px-4">{{ $jadwal->mapel->nama ?? '-' }}</td>
                                <td class="py-3 px-4">{{ $jadwal->tanggal_mulai->format('d M Y') }} -
                                    {{ $jadwal->tanggal_selesai->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $jadwal->status == 'open' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($jadwal->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $jadwal->enrollmentUjian->count() }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('enrollment.create', $jadwal->id) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <span>Enrollment</span>
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('enrollment.show', $jadwal->id) }}"
                                            class="text-green-600 hover:text-green-900">
                                            <span>Detail</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">Tidak ada jadwal ujian yang
                                    tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
