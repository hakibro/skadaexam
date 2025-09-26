@extends('layouts.admin')

@section('title', 'Koordinator Dashboard')
@section('page-title', 'Dashboard Koordinator')
@section('page-description', 'Upload Tata Tertib Ujian')

@section('content')
    <div class="max-w-xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Upload Tata Tertib Ujian</h2>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('koordinator.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">Pilih File PDF</label>
                <input type="file" name="file" accept="application/pdf" class="mt-1 block w-full border rounded p-2">
                @error('file')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Upload
            </button>
        </form>
    </div>
@endsection
