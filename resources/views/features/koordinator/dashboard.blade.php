@extends('layouts.admin')

@section('title', 'Koordinator Dashboard')
@section('page-title', 'Dashboard Koordinator')
@section('page-description', 'Kelola penugasan pengawas dan monitoring ujian')

@section('content')
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-user-tie text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Pengawas</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_pengawas'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                            class="font-medium text-purple-700 hover:text-purple-900">
                            Kelola Penugasan Baru
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-calendar-check text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Sesi Hari Ini</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['sessions_today'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('koordinator.monitoring.index') }}"
                            class="font-medium text-blue-700 hover:text-blue-900">
                            Lihat Monitoring
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Belum Ditugaskan</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['unassigned_sessions'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                            class="font-medium text-yellow-700 hover:text-yellow-900">
                            Tugaskan Pengawas
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-file-alt text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Berita Acara Draft</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['draft_berita_acara'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('koordinator.laporan.index', ['status' => 'draft']) }}"
                            class="font-medium text-green-700 hover:text-green-900">
                            Kelola Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 rounded-lg p-3">
                        <i class="fa-solid fa-user-plus text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Penugasan Pengawas</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Tugaskan pengawas ke sesi ruangan ujian</p>
                <div class="flex flex-col space-y-2">
                    <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                        <i class="fa-solid fa-user-check mr-2"></i>
                        Penugasan Pengawas
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 rounded-lg p-3">
                        <i class="fa-solid fa-eye text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Live Monitoring</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Monitor jalannya ujian secara real-time</p>
                <a href="{{ route('koordinator.monitoring.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fa-solid fa-desktop mr-2"></i>
                    Akses Monitoring
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <i class="fa-solid fa-file-export text-green-600 text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Laporan Koordinasi</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Buat dan kelola laporan koordinasi ujian</p>
                <a href="{{ route('koordinator.laporan.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    <i class="fa-solid fa-file-download mr-2"></i>
                    Unduh Laporan
                </a>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <i class="fa-solid fa-file-export text-green-600 text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Upload Tata Tertib</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Buat dan kelola tata tertib ujian</p>
                <a href="{{ route('koordinator.upload-form') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    <i class="fa-solid fa-file-download mr-2"></i>
                    Upload Tata Tertib
                </a>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-yellow-100 rounded-lg p-3">
                        📢
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Buat Pengumuman Ujian</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Buat dan kelola pengumuman ujian</p>
                <a href="{{ route('koordinator.pengumuman.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-800 bg-yellow-100 hover:bg-yellow-300">
                    📢 Buat Pengumuman Ujian
                </a>
            </div>
        </div>

        <!-- Recent Activities & Quick Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
                    <a href="{{ route('koordinator.monitoring.index') }}"
                        class="text-sm text-purple-600 hover:text-purple-800">
                        Lihat Semua
                    </a>
                </div>
                @if ($recentActivities && count($recentActivities) > 0)
                    <div class="space-y-3">
                        @foreach (array_slice($recentActivities, 0, 5) as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 rounded-full {{ $activity['icon_bg'] ?? 'bg-gray-100' }} flex items-center justify-center">
                                        <i
                                            class="fa-solid {{ $activity['icon'] ?? 'fa-bell' }} text-sm {{ $activity['icon_color'] ?? 'text-gray-600' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">{{ $activity['message'] ?? 'Aktivitas sistem' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['time'] ?? 'Baru saja' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fa-solid fa-history text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500">Belum ada aktivitas terbaru</p>
                    </div>
                @endif
            </div>

            <!-- Pending Assignments -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Penugasan Menunggu</h3>
                    <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                        class="text-sm text-purple-600 hover:text-purple-800">
                        Kelola
                    </a>
                </div>
                @if ($pendingAssignments && count($pendingAssignments) > 0)
                    <div class="space-y-3">
                        @foreach (array_slice($pendingAssignments, 0, 5) as $assignment)
                            <div
                                class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $assignment['session_name'] ?? 'Sesi Ujian' }}</p>
                                    <p class="text-xs text-gray-600">{{ $assignment['room_name'] ?? 'Ruangan' }} •
                                        {{ $assignment['date'] ?? 'Hari ini' }}</p>
                                </div>
                                <div class="flex items-center">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Belum Ditugaskan
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fa-solid fa-check-circle text-green-400 text-3xl mb-2"></i>
                        <p class="text-gray-500">Semua sesi sudah memiliki pengawas</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monitoring Overview -->
        @if ($activeSessions && count($activeSessions) > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Sesi Berlangsung</h3>
                        <a href="{{ route('koordinator.monitoring.index') }}"
                            class="text-sm text-purple-600 hover:text-purple-800">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach (array_slice($activeSessions, 0, 6) as $session)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $session['name'] ?? 'Sesi Ujian' }}
                                    </h4>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-1"></span>
                                        Live
                                    </span>
                                </div>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Ruangan:</span>
                                        <span>{{ $session['room'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Siswa Hadir:</span>
                                        <span
                                            class="text-green-600 font-medium">{{ $session['students_present'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Progress:</span>
                                        <span>{{ $session['progress'] ?? '0' }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pengawas:</span>
                                        <span>{{ $session['supervisor'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <a href="{{ route('koordinator.monitoring.show', $session['id'] ?? '#') }}"
                                        class="text-xs text-purple-600 hover:text-purple-800">
                                        Monitor Detail →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
