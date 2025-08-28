<!-- filepath: c:\laragon\www\skadaexam\resources\views\admin\kelas\index.blade.php -->
@extends('layouts.dashboard')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Daftar Kelas</h1>
            <a href="{{ route('admin.kelas.create') }}"
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fa fa-plus mr-2"></i>Tambah Kelas
            </a>
        </div>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded shadow">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Nama Kelas</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kelas as $k)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $k->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $k->name }}</td>
                            <td class="py-2 px-4 border-b">
                                <a href="{{ route('admin.kelas.edit', $k) }}"
                                    class="bg-yellow-400 text-white px-3 py-1 rounded hover:bg-yellow-500 mr-2">Edit</a>
                                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                        onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                                <a href="{{ route('admin.kelas.show', $k) }}"
                                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 ml-2">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
