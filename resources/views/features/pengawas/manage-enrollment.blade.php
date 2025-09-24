@extends('layouts.admin')

@section('title', 'Pengawas Dashboard')
@section('page-title', 'Pengawas Dashboard')
@section('page-description', 'Atur Enrollment Ujian Siswa')

@section('content')
    <div class="container mx-auto p-4">
        <div class="mb-6">
            <a href="{{ route('pengawas.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
        <h1 class="text-2xl font-bold mb-4">Kelola Siswa Dibatalkan / Dihapus</h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-b">No</th>
                        <th class="py-2 px-4 border-b">Nama Siswa</th>
                        <th class="py-2 px-4 border-b">Jadwal Ujian</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach ($siswaCancelled as $enrollment)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">{{ $no++ }}</td>
                            <td class="py-2 px-4">{{ $enrollment->siswa->nama }}</td>
                            <td class="py-2 px-4">{{ $enrollment->jadwalUjian->judul ?? '-' }}</td>
                            <td class="py-2 px-4 text-red-600 font-semibold">{{ $enrollment->status_enrollment }}</td>
                            <td class="py-2 px-4">
                                <form action="{{ route('pengawas.manage-enrollment.restore', $enrollment->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Aktifkan Kembali
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @foreach ($siswaDeleted as $enrollment)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">{{ $no++ }}</td>
                            <td class="py-2 px-4">{{ $enrollment->siswa->name ?? 'Data Siswa Dihapus' }}</td>
                            <td class="py-2 px-4">{{ $enrollment->siswa->email ?? '-' }}</td>
                            <td class="py-2 px-4 text-red-600 font-semibold">Dihapus</td>
                            <td class="py-2 px-4">
                                <form action="{{ route('pengawas.manage-enrollment.restore', $enrollment->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Aktifkan Kembali
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection
