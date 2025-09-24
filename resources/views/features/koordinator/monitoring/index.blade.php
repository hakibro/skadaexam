@extends('layouts.admin')

@section('title', 'Monitoring Ujian')
@section('page-title', 'Monitoring Ujian Live')
@section('page-description', 'Pantau jalannya ujian secara real-time')
@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-semibold mb-4">Monitoring Ujian</h1>

        <!-- Filters Section -->
        <div class="flex justify-between mb-6">
            <form method="GET" action="{{ route('koordinator.monitoring.index') }}" class="flex space-x-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" name="date" id="date" value="{{ $selectedDate }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $selectedStatus == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="finalized" {{ $selectedStatus == 'finalized' ? 'selected' : '' }}>Finalized</option>
                        <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div>
                    <label for="ruangan_id" class="block text-sm font-medium text-gray-700">Ruangan</label>
                    <select name="ruangan_id" id="ruangan_id"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $selectedRuangan == 'all' ? 'selected' : '' }}>Semua</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ $selectedRuangan == $room->id ? 'selected' : '' }}>
                                {{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-indigo-700">Filter</button>
            </form>
        </div>

        <!-- Sessions Section -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Sesi Ujian</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($sessions as $session)
                    <div class="bg-white shadow-lg rounded-lg p-4">
                        <h3 class="text-lg font-semibold">{{ $session->ruangan->name }}</h3>
                        <p class="text-sm text-gray-600">Tanggal Ujian:
                            {{ $session->jadwalUjians->first()->tanggal_ujian }}</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($session->sesiRuanganSiswa as $siswa)
                                <li class="text-sm text-gray-800">{{ $siswa->siswa->name }} - {{ $siswa->status }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Violations Section -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Pelanggaran Ujian</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($violations as $violation)
                    <div class="bg-white shadow-lg rounded-lg p-4">
                        <h3 class="text-lg font-semibold">{{ $violation->siswa->name }}</h3>
                        <p class="text-sm text-gray-600">Jenis Pelanggaran: {{ $violation->jenis_pelanggaran }}</p>
                        <p class="text-sm text-gray-600">Waktu: {{ $violation->waktu_pelanggaran }}</p>
                        <p class="text-sm text-gray-600">Tindakan: {{ $violation->tindakan }}</p>
                        <p class="text-sm text-gray-600">Deskripsi: {{ $violation->deskripsi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
