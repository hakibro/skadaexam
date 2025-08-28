<!-- filepath: resources\views\features\data\siswa\show.blade.php -->

@extends('layouts.admin')

@section('title', 'View Siswa: ' . ($siswa->nama ?: $siswa->idyayasan))
@section('page-title', 'View Siswa')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Student Details: {{ $siswa->nama ?: $siswa->idyayasan }}
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('data.siswa.edit', $siswa) }}"
                            class="bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700">
                            <i class="fa-solid fa-edit mr-1"></i>Edit
                        </a>
                        <a href="{{ route('data.siswa.index') }}"
                            class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                            <i class="fa-solid fa-arrow-left mr-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-6">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID Yayasan</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $siswa->idyayasan }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->nama ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($siswa->email)
                                <a href="mailto:{{ $siswa->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $siswa->email }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->kelas ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rekomendasi</dt>
                        <dd class="mt-1">
                            @if ($siswa->rekomendasi === 'ya')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check-circle mr-1"></i>Ya
                                </span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fa-solid fa-times-circle mr-1"></i>Tidak
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Pembayaran</dt>
                        <dd class="mt-1">
                            @php
                                $statusClass = match ($siswa->status_pembayaran) {
                                    'Lunas' => 'bg-green-100 text-green-800',
                                    'Belum Lunas' => 'bg-red-100 text-red-800',
                                    'Cicilan' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ $siswa->status_pembayaran ?: 'Unknown' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->created_at->format('d M Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
