@extends('layouts.admin')

@section('title', 'Analisis Hasil Ujian')
@section('page-title', 'Analisis Hasil Ujian')
@section('page-description', 'Statistik dan analisis komprehensif hasil ujian')

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex flex-wrap justify-between items-center">
            <div class="flex space-x-2 mb-2 sm:mb-0">
                <a href="{{ route('naskah.hasil.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Hasil Ujian
                </a>
            </div>

            <!-- Filter Form -->
            <div class="flex-1 sm:flex-none">
                <form action="{{ route('naskah.hasil.analisis') }}" method="GET" class="flex flex-wrap gap-2 sm:justify-end">
                    <select name="jadwal_id"
                        class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">-- Semua Jadwal --</option>
                        @foreach ($jadwalUjians as $jadwal)
                            <option value="{{ $jadwal->id }}" {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                {{ $jadwal->judul }} ({{ $jadwal->tanggal->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>

                    <select name="kelas_id"
                        class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fa-solid fa-filter mr-2"></i> Filter
                    </button>

                    @if (request('jadwal_id') || request('kelas_id'))
                        <a href="{{ route('naskah.hasil.analisis') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fa-solid fa-times mr-2"></i> Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Participants Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Peserta</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalSiswa }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">{{ $completedCount }} Selesai
                        ({{ number_format($completionRate, 1) }}%)</dd>
                </div>
            </div>

            <!-- Average Score Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Nilai Rata-rata</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $averageScore }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">Min: {{ $minScore }} | Max: {{ $maxScore }}</dd>
                </div>
            </div>

            <!-- Pass Rate Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Tingkat Kelulusan</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $passRate }}%</dd>
                    <dd class="mt-1 text-sm text-gray-500">{{ $passCount }} dari {{ $completedCount }} siswa lulus KKM
                    </dd>
                </div>
            </div>

            <!-- Average Duration Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Waktu Rata-rata</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $averageTime }} mnt</dd>
                    <dd class="mt-1 text-sm text-gray-500">Min: {{ $minTime }} | Max: {{ $maxTime }} menit</dd>
                </div>
            </div>
        </div>

        <!-- Score Distribution Chart -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Nilai</h3>
                <p class="mt-1 text-sm text-gray-500">Sebaran nilai siswa dalam rentang nilai</p>
            </div>
            <div class="p-4">
                <div class="h-64">
                    <canvas id="scoreDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Class Comparison -->
        @if (count($kelasComparison) > 1)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Perbandingan Kelas</h3>
                    <p class="mt-1 text-sm text-gray-500">Perbandingan nilai rata-rata antar kelas</p>
                </div>
                <div class="p-4">
                    <div class="h-64">
                        <canvas id="classComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- Question Analysis -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Analisis Soal</h3>
                <p class="mt-1 text-sm text-gray-500">Tingkat kesulitan dan akurasi jawaban per soal</p>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <!-- Question Difficulty Chart -->
                <div class="mb-8">
                    <h4 class="text-md font-medium text-gray-700 mb-4">Tingkat Kesulitan Soal</h4>
                    <div class="h-64">
                        <canvas id="questionDifficultyChart"></canvas>
                    </div>
                </div>

                <!-- Table of Questions -->
                <h4 class="text-md font-medium text-gray-700 mb-4">Detail Analisis Per Soal</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Soal</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Benar</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Salah</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tingkat Kesulitan</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Daya Beda</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($questionAnalysis as $index => $question)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">{{ $question['text'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $question['category'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 font-medium">
                                        {{ $question['correct'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 font-medium">
                                        {{ $question['incorrect'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $question['difficulty'] }}%</div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $question['difficulty'] }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $question['discrimination'] >= 0.4 ? 'bg-green-100 text-green-800' : ($question['discrimination'] >= 0.2 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($question['discrimination'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Category Analysis -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Analisis Kategori</h3>
                <p class="mt-1 text-sm text-gray-500">Persentase keberhasilan berdasarkan kategori soal</p>
            </div>
            <div class="p-4">
                <div class="h-80">
                    <canvas id="categoryAnalysisChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Students Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">10 Siswa Terbaik</h3>
                <p class="mt-1 text-sm text-gray-500">Siswa dengan nilai tertinggi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Peringkat</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kelas</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nilai</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Benar</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($topStudents as $index => $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($index < 3)
                                        <span
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index == 0 ? 'bg-yellow-100 text-yellow-800' : ($index == 1 ? 'bg-gray-100 text-gray-800' : 'bg-yellow-600 text-white') }}">
                                            {{ $index + 1 }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $student->siswa->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->siswa->nis }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->siswa->kelas->nama_kelas }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div
                                        class="text-sm font-bold {{ $student->nilai >= ($student->jadwalUjian->sesiRuangan->bankSoal->mapel->kkm ?? 75) ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($student->nilai, 2) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->jawaban_benar }}/{{ $student->total_soal }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->waktu_pengerjaan ?? '-' }} menit
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('naskah.hasil.show', $student->id) }}"
                                        class="text-blue-600 hover:text-blue-900">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Report -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Laporan Analisis</h3>
                <p class="mt-1 text-sm text-gray-500">Ekspor laporan analisis lengkap</p>
            </div>
            <div class="px-4 py-5 sm:p-6 flex flex-wrap gap-3">
                <a href="{{ route('naskah.hasil.analisis.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Laporan PDF
                </a>
                <a href="{{ route('naskah.hasil.analisis.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <i class="fa-solid fa-file-excel mr-2"></i> Laporan Excel
                </a>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Score Distribution Chart
            const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
            new Chart(scoreCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($scoreDistribution)) !!},
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: {!! json_encode(array_values($scoreDistribution)) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Siswa'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Rentang Nilai'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            @if (count($kelasComparison) > 1)
                // Class Comparison Chart
                const classCtx = document.getElementById('classComparisonChart').getContext('2d');
                new Chart(classCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($kelasComparison)) !!},
                        datasets: [{
                            label: 'Rata-rata Nilai',
                            data: {!! json_encode(array_values($kelasComparison)) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Rata-rata Nilai'
                                },
                                suggestedMax: 100
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Kelas'
                                }
                            }
                        }
                    }
                });
            @endif

            // Question Difficulty Chart
            const difficultyCtx = document.getElementById('questionDifficultyChart').getContext('2d');
            new Chart(difficultyCtx, {
                type: 'horizontalBar',
                data: {
                    labels: {!! json_encode(
                        array_map(
                            function ($q, $i) {
                                return 'Soal ' . ($i + 1);
                            },
                            $questionAnalysis,
                            array_keys($questionAnalysis),
                        ),
                    ) !!},
                    datasets: [{
                        label: 'Persentase Jawaban Benar',
                        data: {!! json_encode(
                            array_map(function ($q) {
                                return $q['difficulty'];
                            }, $questionAnalysis),
                        ) !!},
                        backgroundColor: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            return value >= 70 ? 'rgba(75, 192, 192, 0.5)' :
                                value >= 40 ? 'rgba(255, 206, 86, 0.5)' :
                                'rgba(255, 99, 132, 0.5)';
                        },
                        borderColor: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            return value >= 70 ? 'rgba(75, 192, 192, 1)' :
                                value >= 40 ? 'rgba(255, 206, 86, 1)' :
                                'rgba(255, 99, 132, 1)';
                        },
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Persentase Jawaban Benar (%)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Category Analysis Chart
            const categoryCtx = document.getElementById('categoryAnalysisChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'radar',
                data: {
                    labels: {!! json_encode(array_keys($categoryAnalysis)) !!},
                    datasets: [{
                        label: 'Persentase Jawaban Benar',
                        data: {!! json_encode(array_values($categoryAnalysis)) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0,
                            suggestedMax: 100
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.2
                        }
                    }
                }
            });
        });
    </script>
@endsection
