@extends('layouts.admin')

@section('title', 'Preview Soal')
@section('page-title', 'Preview Soal')
@section('page-description', 'Pratinjau tampilan soal')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 text-white">
                <h2 class="text-xl font-semibold">Preview Soal</h2>
                <p class="text-blue-100">Tampilan seperti yang akan dilihat siswa</p>
            </div>

            <div class="p-6">
                <x-soal-card :soal="$soal" />
            </div>
        </div>
    </div>
@endsection
