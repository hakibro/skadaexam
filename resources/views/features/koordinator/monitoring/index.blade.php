@extends('layouts.admin')

@section('title', 'Monitoring Ujian')
@section('page-title', 'Monitoring Ujian Live')
@section('page-description', 'Pantau jalannya ujian secara real-time')

@section('content')
    <div class="space-y-6">
        <!-- Live Status Bar -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Live Monitoring Dashboard</h2>
                    <p class="opacity-90 mt-1">Status ujian diperbarui setiap 30 detik</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold" id="live-time"></div>
                        <div class="text-sm opacity-90">Waktu Sekarang</div>
                    </div>
                    <div class="h-12 w-px bg-white opacity-30"></div>
                    <div class="text-center">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse mr-2"></div>
                            <span class="font-medium">LIVE</span>
                        </div>
                        <div class="text-sm opacity-90">Status</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-play text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="active-sessions">
                            {{ is_numeric($stats['active_sessions'] ?? null) ? $stats['active_sessions'] : 0 }}
                        </div>
                        <div class="text-gray-600 font-medium">Sesi Berlangsung</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="active-students">
                            {{ is_numeric($stats['active_students'] ?? null) ? $stats['active_students'] : 0 }}
                        </div>
                        <div class="text-gray-600 font-medium">Siswa Online</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="issues-count">
                            {{ is_numeric($stats['issues'] ?? null) ? $stats['issues'] : 0 }}
                        </div>
                        <div class="text-gray-600 font-medium">Permasalahan</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="bg-purple-100 text-purple-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-user-tie text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="online-proctors">
                            {{ is_numeric($stats['online_proctors'] ?? null) ? $stats['online_proctors'] : 0 }}
                        </div>
                        <div class="text-gray-600 font-medium">Pengawas Online</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Controls -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div>
                        <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="filter-status"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Semua Status</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="belum_mulai">Belum Mulai</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-room" class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                        <select id="filter-room"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Semua Ruangan</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="refresh-interval" class="block text-sm font-medium text-gray-700 mb-1">Auto
                            Refresh</label>
                        <select id="refresh-interval"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="30">30 detik</option>
                            <option value="60">1 menit</option>
                            <option value="300">5 menit</option>
                            <option value="0">Manual</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="refreshData()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fa-solid fa-sync-alt mr-2"></i>
                        Refresh Manual
                    </button>
                    <button onclick="exportReport()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                        <i class="fa-solid fa-download mr-2"></i>
                        Export Laporan
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Sessions Grid -->
        <div id="sessions-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach ($activeSessions as $session)
                <div class="bg-white rounded-lg shadow-lg border-l-4 {{ $session->status_border_class }}"
                    id="session-{{ $session->id }}">
                    <div class="p-6">
                        <!-- Session Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $session->ruangan->nama_ruangan ?? 'Ruangan tidak ditemukan' }}</h3>
                                <div class="flex items-center text-sm text-gray-600 mt-1">
                                    <i class="fa-solid fa-door-open mr-1"></i>
                                    <span>{{ $session->nama_sesi ?? 'Sesi tidak ditemukan' }}</span>
                                    <span class="mx-2">•</span>
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    <span>{{ $session->waktu_mulai }} - {{ $session->waktu_selesai }}</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <!-- Session status badge -->
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ is_string($session->status_badge_class) ? $session->status_badge_class : '' }}">
                                    {{ is_string($session->status_label) ? $session->status_label : (is_array($session->status_label) ? $session->status_label['label'] ?? '' : '') }}
                                </span>
                                <button onclick="toggleSessionDetails({{ $session->id }})"
                                    class="text-gray-400 hover:text-gray-600" id="toggle-btn-{{ $session->id }}">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-800" id="session-{{ $session->id }}-students">
                                    {{ $session->sesiRuanganSiswa->count() }}
                                </div>
                                <div class="text-xs text-gray-600">Total Siswa</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600" id="session-{{ $session->id }}-active">
                                    {{ $session->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count() }}
                                </div>
                                <div class="text-xs text-gray-600">Hadir</div>
                            </div>
                            <div class="text-center p-3 bg-red-50 rounded-lg">
                                <div class="text-2xl font-bold text-red-600" id="session-{{ $session->id }}-issues">
                                    0{{-- Logout functionality removed --}}
                                </div>
                                <div class="text-xs text-gray-600">Logout</div>
                            </div>
                        </div>

                        <!-- Supervisor Info -->
                        @if ($session->pengawas)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <div class="bg-purple-100 text-purple-600 p-2 rounded-full mr-3">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        @if ($session->pengawas)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ optional($session->pengawas)->nama ?? '-' }}</div>
                                        @endif

                                        <div class="text-xs text-gray-600">Pengawas</div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                    <span class="text-xs text-gray-600">Online</span>
                                </div>
                            </div>
                        @endif

                        <!-- Session Details (Collapsible) -->
                        <div id="session-details-{{ $session->id }}" class="hidden">
                            <div class="border-t border-gray-200 pt-4">
                                <!-- Progress Bar -->
                                @if ($session->status === 'berlangsung')
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                                            <span>Progress Ujian</span>
                                            <span
                                                id="session-{{ $session->id }}-progress">{{ $session->progress_percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $session->progress_percentage }}%"
                                                id="session-{{ $session->id }}-progress-bar"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Sisa waktu: <span
                                                id="session-{{ $session->id }}-remaining">{{ $session->remaining_time }}</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Student List -->
                                <div class="mb-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Status Siswa</h5>
                                    <div class="max-h-48 overflow-y-auto">
                                        <div class="space-y-2" id="session-{{ $session->id }}-students-list">
                                            @foreach ($session->sesiRuanganSiswa as $siswaSession)
                                                <div
                                                    class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                                    <div class="flex items-center">
                                                        <!-- Student status dot -->
                                                        <span
                                                            class="w-2 h-2 rounded-full mr-2 {{ is_string($siswaSession->status_dot_class) ? $siswaSession->status_dot_class : (is_array($siswaSession->status_dot_class) ? $siswaSession->status_dot_class['class'] ?? '' : '') }}"></span>

                                                        <span
                                                            class="font-medium">{{ optional($siswaSession->siswa)->nama ?? 'Tidak diketahui' }}</span>

                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <!-- Student badge -->
                                                        <span
                                                            class="text-xs px-2 py-1 rounded {{ is_string($siswaSession->status_badge_class) ? $siswaSession->status_badge_class : (is_array($siswaSession->status_badge_class) ? $siswaSession->status_badge_class['class'] ?? '' : '') }}">
                                                            {{ is_string($siswaSession->status_kehadiran) ? ucfirst($siswaSession->status_kehadiran) : '-' }}
                                                        </span>
                                                        @if (optional($siswaSession->last_activity))
                                                            <span class="text-xs text-gray-500">
                                                                {{ optional($siswaSession->last_activity)->diffForHumans() ?? '-' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>


                                <!-- Quick Actions -->
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('koordinator.monitoring.show', $session->id) }}"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </a>
                                    @if ($session->status === 'berlangsung')
                                        <button onclick="sendMessage({{ $session->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fa-solid fa-comment mr-1"></i>
                                            Pesan
                                        </button>
                                    @endif
                                    @if ($session->beritaAcaraUjian)
                                        <a href="{{ route('koordinator.laporan.show', $session->beritaAcaraUjian->id) }}"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700">
                                            <i class="fa-solid fa-file-alt mr-1"></i>
                                            Laporan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($activeSessions->isEmpty())
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fa-solid fa-calendar-times text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Tidak Ada Sesi Aktif</h3>
                <p class="text-gray-600">Saat ini tidak ada sesi ujian yang sedang berlangsung.</p>
                <a href="{{ route('koordinator.assignment.index') }}"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Lihat Penjadwalan
                </a>
            </div>
        @endif
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Kirim Pesan ke Pengawas</h3>
                </div>
                <div class="px-6 py-4">
                    <form id="messageForm">
                        <input type="hidden" id="messageSessionId" name="session_id">
                        <div class="mb-4">
                            <label for="messageType" class="block text-sm font-medium text-gray-700 mb-2">Jenis
                                Pesan</label>
                            <select id="messageType" name="type"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="info">Informasi</option>
                                <option value="warning">Peringatan</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="messageContent" class="block text-sm font-medium text-gray-700 mb-2">Pesan</label>
                            <textarea id="messageContent" name="message" rows="4" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Tulis pesan untuk pengawas..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeMessageModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="button" onclick="submitMessage()"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                        Kirim Pesan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

    <script>
        let refreshInterval;
        let refreshTimer = 30; // Default 30 seconds

        document.addEventListener('DOMContentLoaded', function() {
            updateLiveTime();
            setInterval(updateLiveTime, 1000);

            // Set up auto refresh
            setupAutoRefresh();

            // Filter event listeners
            document.getElementById('filter-status').addEventListener('change', applyFilters);
            document.getElementById('filter-room').addEventListener('change', applyFilters);
            document.getElementById('refresh-interval').addEventListener('change', function() {
                refreshTimer = parseInt(this.value);
                setupAutoRefresh();
            });
        });

        function updateLiveTime() {
            const now = new Date();
            document.getElementById('live-time').textContent =
                now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
        }

        function setupAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

            if (refreshTimer > 0) {
                refreshInterval = setInterval(refreshData, refreshTimer * 1000);
            }
        }

        function refreshData() {
            fetch(window.location.href + '?ajax=1', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    updateStats(data.stats);
                    updateSessions(data.sessions);
                    showToast('Data berhasil diperbarui', 'success');
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    showToast('Gagal memperbarui data', 'error');
                });
        }

        function updateStats(stats) {
            document.getElementById('active-sessions').textContent = stats.active_sessions;
            document.getElementById('active-students').textContent = stats.active_students;
            document.getElementById('issues-count').textContent = stats.issues;
            document.getElementById('online-proctors').textContent = stats.online_proctors;
        }

        function updateSessions(sessions) {
            // Update each session card with new data
            sessions.forEach(session => {
                const studentCountEl = document.getElementById(`session-${session.id}-students`);
                const activeCountEl = document.getElementById(`session-${session.id}-active`);
                const issuesCountEl = document.getElementById(`session-${session.id}-issues`);

                if (studentCountEl) studentCountEl.textContent = session.total_students;
                if (activeCountEl) activeCountEl.textContent = session.active_students;
                if (issuesCountEl) issuesCountEl.textContent = session.issues_count;

                // Update progress if session is ongoing
                if (session.status === 'berlangsung') {
                    const progressEl = document.getElementById(`session-${session.id}-progress`);
                    const progressBarEl = document.getElementById(`session-${session.id}-progress-bar`);
                    const remainingEl = document.getElementById(`session-${session.id}-remaining`);

                    if (progressEl) progressEl.textContent = session.progress_percentage + '%';
                    if (progressBarEl) progressBarEl.style.width = session.progress_percentage + '%';
                    if (remainingEl) remainingEl.textContent = session.remaining_time;
                }
            });
        }

        function toggleSessionDetails(sessionId) {
            const details = document.getElementById(`session-details-${sessionId}`);
            const toggleBtn = document.getElementById(`toggle-btn-${sessionId}`);

            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
            } else {
                details.classList.add('hidden');
                toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
            }
        }

        function applyFilters() {
            const status = document.getElementById('filter-status').value;
            const room = document.getElementById('filter-room').value;

            const sessions = document.querySelectorAll('[id^="session-"]');
            sessions.forEach(session => {
                let show = true;

                // Apply status filter
                if (status) {
                    const sessionStatusEl = session.querySelector('.status-badge');
                    if (sessionStatusEl && !sessionStatusEl.textContent.toLowerCase().includes(status)) {
                        show = false;
                    }
                }

                // Apply room filter
                if (room && show) {
                    // This would need to be implemented based on session data
                    // For now, we'll skip this implementation
                }

                if (show) {
                    session.closest('.lg\\:w-1\\/2, .w-full').classList.remove('hidden');
                } else {
                    session.closest('.lg\\:w-1\\/2, .w-full').classList.add('hidden');
                }
            });
        }

        function sendMessage(sessionId) {
            document.getElementById('messageSessionId').value = sessionId;
            document.getElementById('messageModal').classList.remove('hidden');
        }

        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageForm').reset();
        }

        function submitMessage() {
            const form = document.getElementById('messageForm');
            const formData = new FormData(form);

            fetch('{{ route('koordinator.monitoring.message') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        closeMessageModal();
                    } else {
                        showToast(data.message || 'Terjadi kesalahan', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat mengirim pesan', 'error');
                });
        }

        function exportReport() {
            const filters = {
                status: document.getElementById('filter-status').value,
                room: document.getElementById('filter-room').value,
                date: new Date().toISOString().split('T')[0]
            };

            const queryString = new URLSearchParams(filters).toString();
            window.open(`{{ route('koordinator.monitoring.export') }}?${queryString}`, '_blank');
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>

@endsection
