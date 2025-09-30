@extends('layouts.admin')

@section('title', 'Koordinator Dashboard')
@section('page-title', 'Dashboard Koordinator')
@section('page-description', 'Pengumuman Ujian')

@section('content')
    <div class="max-w-3xl mx-auto bg-white shadow p-6 rounded-lg space-y-6">
        <h1 class="text-xl font-bold">📢 Manajemen Pengumuman</h1>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        {{-- Jika sudah ada pengumuman --}}
        @if ($exists)
            <div>
                <h2 class="font-semibold text-lg mb-2">👀 Preview</h2>
                <div class="prose max-w-none border p-3 rounded bg-gray-50">{!! $html !!}</div>
            </div>

            {{-- Form Edit --}}
            <div>
                <h2 class="font-semibold text-lg mb-2">✏️ Edit Pengumuman</h2>
                <form action="{{ route('koordinator.pengumuman.update') }}" method="POST">
                    @csrf
                    <textarea name="konten" rows="10" class="w-full border rounded p-3">{{ $content }}</textarea>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded mt-3">💾 Simpan</button>
                </form>
            </div>

            {{-- Hapus --}}
            <form action="{{ route('koordinator.pengumuman.delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">🗑 Hapus Pengumuman</button>
            </form>
        @else
            {{-- Jika belum ada pengumuman --}}
            <p class="text-gray-500">Belum ada pengumuman. Buat sekarang:</p>
            <form action="{{ route('koordinator.pengumuman.update') }}" method="POST">
                @csrf
                <textarea name="konten" rows="10" class="w-full border rounded p-3" placeholder="Tulis pengumuman di sini..."></textarea>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded mt-3">➕ Simpan Pengumuman</button>
            </form>
        @endif
    </div>
@endsection
