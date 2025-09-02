@extends('layouts.admin')

@section('title', 'Detail Monitoring - ' . $sesiRuangan->nama_sesi)
@section('page-title', 'Detail Monitoring Sesi')
@section('page-description', 'Monitoring detail sesi ujian: ' . $sesiRuangan->nama_sesi)

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div class="flex items-center">
            <a href="{{ route('koordinator.monitoring.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Kembali ke Monitoring
            </a>
        </div>

        <!-- Session Header -->
        <div class="bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ $sesiRuangan->nama_sesi }}</h1>
                    <div class="flex items-center mt-2 space-x-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-door-open mr-2"></i>
                            <span>{{ $sesiRuangan->ruangan->nama }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-calendar mr-2"></i>
                            <span>{{ $sesiRuangan->tanggal->format('d F Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-clock mr-2"></i>
                            <span>{{ $sesiRuangan->waktu_mulai }} - {{ $sesiRuangan->waktu_selesai }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $sesiRuangan->status_badge_class }}">
                        {{ $sesiRuangan->status_label }}
                    </span>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="live-time"></div>
                        <div class="text-sm opacity-90">Live</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="total-students">
                            {{ $sesiRuangan->sesiRuanganSiswa->count() }}
                        </div>
                        <div class="text-gray-600 font-medium">Total Siswa</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-user-check text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="present-students">
                            {{ $sesiRuangan->sesiRuanganSiswa->where('status', 'hadir')->count() }}
                        </div>
                        <div class="text-gray-600 font-medium">Hadir</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-user-times text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="logout-students">
                            {{ $sesiRuangan->sesiRuanganSiswa->where('status', 'logout')->count() }}
                        </div>
                        <div class="text-gray-600 font-medium">Logout</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full mr-4">
                        <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800" id="absent-students">
                            {{ $sesiRuangan->sesiRuanganSiswa->where('status', 'tidak_hadir')->count() }}
                        </div>
                        <div class="text-gray-600 font-medium">Tidak Hadir</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Progress -->
        @if ($sesiRuangan->status === 'berlangsung')
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Ujian</h3>
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Waktu Berlangsung</span>
                        <span id="elapsed-time">{{ $sesiRuangan->elapsed_time }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                            style="width: {{ $sesiRuangan->progress_percentage }}%" id="progress-bar"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500 mt-2">
                        <span>Mulai: {{ $sesiRuangan->waktu_mulai }}</span>
                        <span>Sisa: <span id="remaining-time">{{ $sesiRuangan->remaining_time }}</span></span>
                        <span>Selesai: {{ $sesiRuangan->waktu_selesai }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Supervisor Info -->
        @if ($sesiRuangan->pengawas)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengawas</h3>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-purple-100 text-purple-600 p-4 rounded-full mr-4">
                            <i class="fa-solid fa-user-tie text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-medium text-gray-900">{{ $sesiRuangan->pengawas->nama }}</h4>
                            <p class="text-gray-600">NIP: {{ $sesiRuangan->pengawas->nip ?? 'N/A' }}</p>
                            <p class="text-gray-600">Email: {{ $sesiRuangan->pengawas->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center justify-end mb-2">
                            <span class="w-3 h-3 bg-green-400 rounded-full animate-pulse mr-2"></span>
                            <span class="text-sm font-medium text-gray-900">Online</span>
                        </div>
                        <div class="space-x-2">
                            <button onclick="sendMessage()"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fa-solid fa-comment mr-1"></i>
                                Kirim Pesan
                            </button>
                            @if ($sesiRuangan->beritaAcaraUjian)
                                <a href="{{ route('koordinator.laporan.show', $sesiRuangan->beritaAcaraUjian->id) }}"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fa-solid fa-file-alt mr-1"></i>
                                    Lihat Laporan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Students Monitoring -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Monitoring Siswa</h3>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <label for="status-filter" class="text-sm font-medium text-gray-700 mr-2">Filter:</label>
                            <select id="status-filter"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm">
                                <option value="">Semua Status</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                                <option value="logout">Logout</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="auto-refresh"
                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-2" checked>
                            <label for="auto-refresh" class="text-sm text-gray-700">Auto Refresh (30s)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div id="students-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($sesiRuangan->sesiRuanganSiswa as $siswaSession)
                        <div class="border rounded-lg p-4 {{ $siswaSession->status_border_class }}"
                            id="student-{{ $siswaSession->id }}" data-status="{{ $siswaSession->status }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $siswaSession->siswa->nama }}</h4>
                                    <p class="text-sm text-gray-600">{{ $siswaSession->siswa->nisn ?? 'N/A' }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full {{ $siswaSession->status_dot_class }}"></span>
                                    <span class="text-xs px-2 py-1 rounded {{ $siswaSession->status_badge_class }}">
                                        {{ ucfirst($siswaSession->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-gray-600">
                                @if ($siswaSession->waktu_masuk)
                                    <div class="flex justify-between">
                                        <span>Masuk:</span>
                                        <span>{{ $siswaSession->waktu_masuk->format('H:i') }}</span>
                                    </div>
                                @endif
                                @if ($siswaSession->waktu_keluar)
                                    <div class="flex justify-between">
                                        <span>Keluar:</span>
                                        <span>{{ $siswaSession->waktu_keluar->format('H:i') }}</span>
                                    </div>
                                @endif
                                @if ($siswaSession->last_activity)
                                    <div class="flex justify-between">
                                        <span>Aktivitas:</span>
                                        <span>{{ $siswaSession->last_activity->diffForHumans() }}</span>
                                    </div>
                                @endif
                                @if ($siswaSession->durasi_aktif)
                                    <div class="flex justify-between">
                                        <span>Durasi:</span>
                                        <span>{{ $siswaSession->durasi_aktif }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Student Actions -->
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <div class="flex justify-end space-x-2">
                                    @if ($siswaSession->status === 'logout')
                                        <button onclick="allowReentry({{ $siswaSession->id }})"
                                            class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                            <i class="fa-solid fa-sign-in-alt mr-1"></i>
                                            Izinkan Masuk
                                        </button>
                                    @endif
                                    <button onclick="viewDetails({{ $siswaSession->id }})"
                                        class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Log Aktivitas Real-time</h3>
            </div>
            <div class="p-6">
                <div id="activity-log" class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full {{ $activity->icon_bg_class }} flex items-center justify-center">
                                    <i class="fa-solid {{ $activity->icon }} text-sm {{ $activity->icon_color }}"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->message }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fa-solid fa-history text-gray-400 text-4xl mb-2"></i>
                            <p class="text-gray-500">Belum ada aktivitas untuk ditampilkan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
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
                        <input type="hidden" name="session_id" value="{{ $sesiRuangan->id }}">
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

    <!-- Student Detail Modal -->
    <div id="studentDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Detail Siswa</h3>
                </div>
                <div class="px-6 py-4" id="studentDetailContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end">
                    <button type="button" onclick="closeStudentDetailModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let refreshInterval;
            let autoRefresh = true;

            document.addEventListener('DOMContentLoaded', function() {
                updateLiveTime();
                setInterval(updateLiveTime, 1000);

                setupAutoRefresh();

                // Filter functionality
                document.getElementById('status-filter').addEventListener('change', applyStatusFilter);
                document.getElementById('auto-refresh').addEventListener('change', function() {
                    autoRefresh = this.checked;
                    setupAutoRefresh();
                });
            });

            function updateLiveTime() {
                const now = new Date();
                document.getElementById('live-time').textContent =
                    now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
            }

            function setupAutoRefresh() {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }

                if (autoRefresh) {
                    refreshInterval = setInterval(refreshData, 30000); // 30 seconds
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
                        updateStudents(data.students);
                        updateProgress(data.progress);
                        updateActivityLog(data.activities);
                        console.log('Data refreshed successfully');
                    })
                    .catch(error => {
                        console.error('Error refreshing data:', error);
                    });
            }

            function updateStats(stats) {
                document.getElementById('total-students').textContent = stats.total;
                document.getElementById('present-students').textContent = stats.present;
                document.getElementById('logout-students').textContent = stats.logout;
                document.getElementById('absent-students').textContent = stats.absent;
            }

            function updateStudents(students) {
                students.forEach(student => {
                    const studentCard = document.getElementById(`student-${student.id}`);
                    if (studentCard) {
                        // Update status badge and other dynamic content
                        studentCard.setAttribute('data-status', student.status);
                        // Update other student details as needed
                    }
                });
            }

            function updateProgress(progress) {
                if (progress && progress.percentage !== undefined) {
                    const progressBar = document.getElementById('progress-bar');
                    const remainingTime = document.getElementById('remaining-time');
                    const elapsedTime = document.getElementById('elapsed-time');

                    if (progressBar) progressBar.style.width = progress.percentage + '%';
                    if (remainingTime) remainingTime.textContent = progress.remaining;
                    if (elapsedTime) elapsedTime.textContent = progress.elapsed;
                }
            }

            function updateActivityLog(activities) {
                const activityLog = document.getElementById('activity-log');
                if (activities && activities.length > 0) {
                    // Add new activities to the top of the log
                    activities.forEach(activity => {
                        const activityElement = createActivityElement(activity);
                        activityLog.insertBefore(activityElement, activityLog.firstChild);
                    });

                    // Keep only last 20 activities
                    const allActivities = activityLog.children;
                    while (allActivities.length > 20) {
                        activityLog.removeChild(allActivities[allActivities.length - 1]);
                    }
                }
            }

            function createActivityElement(activity) {
                const div = document.createElement('div');
                div.className = 'flex items-start space-x-3 p-3 bg-gray-50 rounded-lg';
                div.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full ${activity.icon_bg_class} flex items-center justify-center">
                        <i class="fa-solid ${activity.icon} text-sm ${activity.icon_color}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${activity.message}</p>
                    <p class="text-xs text-gray-500">${activity.time}</p>
                </div>
            `;
                return div;
            }

            function applyStatusFilter() {
                const filterValue = document.getElementById('status-filter').value;
                const studentCards = document.querySelectorAll('[id^="student-"]');

                studentCards.forEach(card => {
                    if (!filterValue || card.getAttribute('data-status') === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            function sendMessage() {
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
                            showToast('Pesan berhasil dikirim', 'success');
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

            function allowReentry(studentSessionId) {
                if (confirm('Izinkan siswa ini untuk masuk kembali ke ujian?')) {
                    fetch('{{ route('koordinator.monitoring.allow-reentry') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                student_session_id: studentSessionId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Siswa berhasil diizinkan masuk kembali', 'success');
                                refreshData();
                            } else {
                                showToast(data.message || 'Terjadi kesalahan', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Terjadi kesalahan', 'error');
                        });
                }
            }

            function viewDetails(studentSessionId) {
                fetch(`{{ route('koordinator.monitoring.student-detail', ':id') }}`.replace(':id', studentSessionId))
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('studentDetailContent').innerHTML = html;
                        document.getElementById('studentDetailModal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Gagal memuat detail siswa', 'error');
                    });
            }

            function closeStudentDetailModal() {
                document.getElementById('studentDetailModal').classList.add('hidden');
            }

            // Toast notification
            function showToast(message, type = 'info') {
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
    @endpush
@endsection
