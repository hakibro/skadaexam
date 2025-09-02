@extends('layouts.app')

@section('title', 'Detail Enrollment')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Detail Enrollment - {{ $jadwalUjian->nama }}</h1>
                <a href="{{ route('enrollment.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md">Kembali</a>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-blue-700">Mata Pelajaran: <span
                                class="font-bold">{{ $jadwalUjian->mapel->nama ?? 'Tidak ada' }}</span></p>
                        <p class="text-sm text-blue-700">Status: <span
                                class="font-bold">{{ ucfirst($jadwalUjian->status) }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700">Tanggal: <span
                                class="font-bold">{{ $jadwalUjian->tanggal_mulai->format('d M Y') }} -
                                {{ $jadwalUjian->tanggal_selesai->format('d M Y') }}</span></p>
                        <p class="text-sm text-blue-700">Durasi: <span class="font-bold">{{ $jadwalUjian->durasi }}
                                menit</span></p>
                    </div>
                </div>
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

            <!-- Enrollment Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 rounded-md p-4 text-center">
                    <p class="text-gray-500 text-sm">Total Terdaftar</p>
                    <p class="text-2xl font-bold">{{ $jadwalUjian->enrollmentUjian->count() }}</p>
                </div>

                <div class="bg-green-50 rounded-md p-4 text-center">
                    <p class="text-green-600 text-sm">Sudah Login</p>
                    <p class="text-2xl font-bold text-green-700">
                        {{ $jadwalUjian->enrollmentUjian->whereNotNull('token_used_at')->count() }}
                    </p>
                </div>

                <div class="bg-blue-50 rounded-md p-4 text-center">
                    <p class="text-blue-600 text-sm">Sudah Selesai</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ $jadwalUjian->hasilUjian->where('is_final', true)->count() }}
                    </p>
                </div>

                <div class="bg-yellow-50 rounded-md p-4 text-center">
                    <p class="text-yellow-600 text-sm">Token Aktif</p>
                    <p class="text-2xl font-bold text-yellow-700">
                        {{ $jadwalUjian->enrollmentUjian->whereNotNull('token')->whereNull('token_used_at')->where('token_expires_at', '>', now())->count() }}
                    </p>
                </div>
            </div>

            <!-- Token Generation -->
            <div class="mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-bold mb-4">Generate Token untuk Sesi</h2>

                    <form action="{{ route('enrollment.generate-tokens') }}" method="POST">
                        @csrf
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label for="sesi_ujian_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih
                                    Sesi</label>
                                <select id="sesi_ujian_id" name="sesi_ujian_id" required
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Sesi --</option>
                                    @foreach ($jadwalUjian->sesiUjian as $sesi)
                                        <option value="{{ $sesi->id }}">
                                            {{ $sesi->nama }} - {{ $sesi->tanggal->format('d M Y') }}
                                            ({{ $sesi->waktu_mulai }} - {{ $sesi->waktu_selesai }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                                    Generate Token
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrolled Students -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Daftar Siswa Terdaftar</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIS</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruangan</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Token</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jadwalUjian->enrollmentUjian as $enrollment)
                                <tr>
                                    <td class="py-3 px-4">{{ $enrollment->siswa->nis }}</td>
                                    <td class="py-3 px-4">{{ $enrollment->siswa->nama }}</td>
                                    <td class="py-3 px-4">{{ $enrollment->siswa->kelas->nama ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $enrollment->sesiUjian->nama ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $enrollment->ruangan->nama ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        @if ($enrollment->token && $enrollment->token_expires_at > now() && !$enrollment->token_used_at)
                                            <span
                                                class="text-green-600 font-mono font-bold">{{ $enrollment->token }}</span>
                                        @elseif($enrollment->token_used_at)
                                            <span class="text-gray-500">Digunakan</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($enrollment->token_used_at)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Login
                                            </span>
                                        @elseif($enrollment->token && $enrollment->token_expires_at > now())
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Token Aktif
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Terdaftar
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 px-4 text-center text-gray-500">
                                        Belum ada siswa yang terdaftar
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
