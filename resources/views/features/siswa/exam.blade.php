<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .option-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        .option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .option-selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(0.98);
        }

        .option-correct {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .option-incorrect {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .question-nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .nav-answered {
            background-color: #10b981;
        }

        .nav-current {
            background-color: #3b82f6;
            transform: scale(1.5);
        }

        .nav-unanswered {
            background-color: #d1d5db;
        }

        .nav-flagged {
            background-color: #f59e0b;
        }

        .progress-ring {
            transition: stroke-dasharray 0.3s ease;
        }

        .floating-nav {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Responsive Additions */
        .mobile-nav-toggle {
            display: none;
        }

        @media (max-width: 1023px) {
            .mobile-nav-toggle {
                display: block;
            }

            .question-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                width: 85%;
                max-width: 320px;
                z-index: 40;
                transition: left 0.3s ease;
                padding-top: 4rem;
                overflow-y: auto;
            }

            .question-sidebar.active {
                left: 0;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 39;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-indigo-500 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-between items-center py-4">
                <div class="flex flex-wrap items-center space-x-2 md:space-x-4 mb-2 sm:mb-0">
                    <!-- Mobile Sidebar Toggle -->
                    <button id="mobile-nav-toggle" class="mobile-nav-toggle mr-2 lg:hidden text-indigo-500">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <h1 class="text-lg sm:text-xl uppercase font-bold text-gray-800">
                        <span class="xs:inline">{{ $examData['title'] ?? 'Ujian' }}</span>
                        {{-- <span class="xs:hidden">Ujian</span> --}}
                    </h1>
                    <div class="text-xs sm:text-sm text-gray-600 truncate max-w-[120px] sm:max-w-none">
                        <i class="fas fa-user mr-1"></i>
                        {{ $siswa->nama }}
                    </div>
                    <!-- Violation Counter -->
                    {{-- <div class="flex items-center">
                        <span id="violation-count"
                            class="ml-2 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full {{ isset($examData['violations_count']) && $examData['violations_count'] > 0 ? '' : 'hidden' }}">
                            {{ $examData['violations_count'] ?? 0 }}
                        </span>
                        <span class="ml-1 text-xs text-red-500">Pelanggaran</span>
                    </div> --}}
                </div>

                <!-- Timer & Progress -->
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 lg:gap-6">
                    <!-- Progress Circle -->
                    <div class="relative w-10 h-10 sm:w-12 sm:h-12">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 transform -rotate-90">
                            <circle cx="20" cy="20" r="16" stroke="#e5e7eb" stroke-width="3"
                                fill="none" class="sm:hidden" />
                            <circle cx="20" cy="20" r="16" stroke="#3b82f6" stroke-width="3"
                                fill="none" class="progress-ring sm:hidden"
                                stroke-dasharray="{{ $examData['totalQuestions'] > 0 ? ($examData['answeredCount'] / $examData['totalQuestions']) * 100 : 0 }} 100"
                                stroke-linecap="round" />

                            <circle cx="24" cy="24" r="20" stroke="#e5e7eb" stroke-width="3"
                                fill="none" class="hidden sm:block" />
                            <circle cx="24" cy="24" r="20" stroke="#3b82f6" stroke-width="3"
                                fill="none" class="progress-ring hidden sm:block"
                                stroke-dasharray="{{ $examData['totalQuestions'] > 0 ? ($examData['answeredCount'] / $examData['totalQuestions']) * 126 : 0 }} 126"
                                stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span
                                class="text-xs font-bold text-gray-700">{{ $examData['answeredCount'] ?? 0 }}/{{ $examData['totalQuestions'] ?? 0 }}</span>
                        </div>
                    </div>

                    <!-- Timer -->
                    <div
                        class="bg-gradient-to-r from-orange-400 to-red-500 text-white px-2 sm:px-4 py-2 rounded-lg shadow-md">
                        <div class="flex items-center space-x-1 sm:space-x-2">
                            <i class="fas fa-clock"></i>
                            <span id="timer" class="font-mono font-bold text-sm sm:text-lg">

                            </span>
                        </div>
                    </div>

                    {{-- Old method display time limit, variable still there --}}
                    {{-- @if (isset($examData['timeLimit']) && $examData['timeLimit'] > 0)
                        <div
                            class="bg-gradient-to-r from-orange-400 to-red-500 text-white px-2 sm:px-4 py-2 rounded-lg shadow-md">
                            <div class="flex items-center space-x-1 sm:space-x-2">
                                <i class="fas fa-clock"></i>
                                <span id="timer" class="font-mono font-bold text-sm sm:text-lg">
                                    {{ gmdate('H:i:s', $examData['remainingTime'] ?? 0) }}
                                </span>
                            </div>
                        </div>
                    @endif --}}

                    <!-- Submit Button -->
                    <button id="submitExam"
                        class="hidden bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 
           text-white px-3 sm:px-6 py-2 rounded-lg font-semibold transition-all duration-300 
           transform hover:scale-105 shadow-lg hover:shadow-xl text-sm sm:text-base">
                        <i class="fas fa-check mr-1 sm:mr-2"></i>
                        <span class="hidden xs:inline">Selesai</span>
                        <span class="xs:hidden">Selesai</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <!-- Submit Exam Modal -->
    <div id="submitExamModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-11/12 max-w-md sm:max-w-md p-6 relative">
            <!-- Close Button -->
            <button id="closeModal"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full transition">
            </button>

            <!-- Modal Content -->
            <h3 class="text-lg font-semibold mb-4 text-center">Konfirmasi Pengumpulan</h3>
            <p class="mb-6 text-sm text-gray-700 text-center">
                Apakah Anda yakin ingin mengumpulkan ujian? Ujian yang sudah dikumpulkan tidak dapat diubah lagi.
            </p>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-3">
                <button id="cancelSubmitExam"
                    class="px-4 py-2 text-red-600 bg-red-100 hover:bg-red-300 rounded-lg transition items-center justify-center">
                    <i class="fas fa-times text-lg"></i>
                    Batal
                </button>
                <button id="confirmSubmitExam"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    Ya, Kumpulkan
                </button>
            </div>
        </div>
    </div>


    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="sidebar-overlay z-50"></div>

    <!-- Violation Warning Panel -->
    <div id="violation-warning"
        class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="font-medium">Peringatan!</p>
                <p class="text-sm" id="violation-message">Terdeteksi pelanggaran ujian. Pengawas telah diberi tahu.</p>
            </div>
        </div>
    </div>

    <!-- Violation Modal - Uncloseable -->
    <div id="violation-modal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden backdrop-blur-sm">
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-md w-full border-4 border-red-500 animate-pulse">
                <div class="p-4 sm:p-6">
                    <!-- Header with warning icon -->
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-red-100 rounded-full p-4 animate-bounce">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl sm:text-3xl"></i>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-4 animate-pulse">
                        ⚠️ PELANGGARAN TERDETEKSI
                    </h3>

                    <!-- Message -->
                    <div id="violation-modal-message" class="text-gray-700 text-center mb-6 space-y-2">
                        <p class="font-semibold text-red-600 text-sm sm:text-base">Anda telah berpindah tab atau
                            meminimalkan browser!</p>
                        <p class="text-xs sm:text-sm">Pelanggaran ini telah dicatat dan dilaporkan ke pengawas ujian.
                        </p>
                        <p class="text-xs sm:text-sm font-medium">Harap tetap fokus pada halaman ujian untuk
                            menghindari
                            pelanggaran lebih lanjut.</p>
                    </div>

                    <!-- Violation count display -->
                    <div id="violation-count-display" class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                        <div class="flex items-center justify-center">
                            <span class="text-sm text-gray-600">Total Pelanggaran: </span>
                            <span id="modal-violation-count"
                                class="ml-2 px-3 py-1 bg-red-500 text-white rounded-full text-sm font-bold animate-pulse">0</span>
                        </div>
                    </div>

                    <!-- Continue button -->
                    <div class="text-center">
                        <button id="continue-exam-btn"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 sm:py-3 px-4 sm:px-6 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-arrow-right mr-2"></i>
                            Lanjutkan Ujian
                        </button>
                    </div>

                    <!-- Warning footer -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 text-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pelanggaran berulang dapat menyebabkan diskualifikasi ujian
                        </p>
                        <p class="text-xs text-red-500 text-center mt-2 font-medium">
                            <i class="fas fa-lock mr-1"></i>
                            Modal ini tidak dapat ditutup sampai Anda menekan "Lanjutkan Ujian"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (!isset($examData['questions']) || count($examData['questions']) == 0)
            <!-- No Questions Available -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-question-circle text-6xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-600 mb-2">Ujian Belum Tersedia</h3>
                <p class="text-gray-500 mb-6">Belum ada soal yang tersedia untuk ujian ini atau ujian belum dimulai.
                </p>

                <div class="space-y-3">
                    <a href="{{ route('siswa.dashboard') }}"
                        class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="lg:grid lg:grid-cols-12 lg:gap-6 flex flex-col-reverse">
                <!-- Question Navigation Sidebar -->
                <div class="lg:col-span-3 mt-6 lg:mt-0">
                    <div id="question-sidebar"
                        class="question-sidebar bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:sticky lg:top-24">
                        <!-- Mobile Close Button -->
                        <button id="close-sidebar"
                            class="absolute top-2 right-2 lg:hidden text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>

                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-list-ol text-indigo-500 mr-2"></i>
                            Navigasi Soal
                        </h3>

                        <!-- Question Grid -->
                        <div
                            class="grid grid-cols-4 xs:grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-5 gap-2 mb-4">
                            @foreach ($examData['questions'] as $index => $question)
                                <button
                                    class="question-nav-btn w-8 h-8 sm:w-10 sm:h-10 rounded-lg border-2 font-bold text-xs sm:text-sm transition-all duration-300 
                                           {{ $examData['currentQuestionIndex'] == $index
                                               ? 'bg-indigo-500 text-white border-indigo-500'
                                               : (isset($examData['answers'][$question['id']])
                                                   ? 'bg-green-500 text-white border-green-500'
                                                   : (in_array($question['id'], $examData['flaggedQuestions'] ?? [])
                                                       ? 'bg-yellow-500 text-white border-yellow-500'
                                                       : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200')) }}"
                                    data-question-index="{{ $index }}"
                                    onclick="navigateToQuestion({{ $index }})">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <div class="text-xs grid grid-cols-2 gap-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-indigo-500 rounded"></div>
                                <span>Soal saat ini</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded"></div>
                                <span>Sudah dijawab</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                                <span>Ditandai</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-gray-300 rounded"></div>
                                <span>Belum dijawab</span>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-6 pt-4 border-t space-y-3">
                            <button id="flagQuestion" onclick="toggleFlag()"
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-flag mr-2"></i>
                                <span
                                    id="flagText">{{ in_array($examData['questions'][$examData['currentQuestionIndex']]['id'], $examData['flaggedQuestions'] ?? []) ? 'Lepas Tanda' : 'Tandai Soal' }}</span>
                            </button>

                            <button id="reviewAnswers"
                                class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-eye mr-2"></i>
                                Review Jawaban
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Question Content -->
                <div class="lg:col-span-9">
                    <div id="questionContainer" class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 md:p-8">
                        <!-- Question Header -->
                        <div class="flex flex-wrap justify-between items-start mb-6 gap-2">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <div
                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg px-3 sm:px-4 py-1 sm:py-2">
                                    <span class="font-bold text-base sm:text-lg">Soal
                                        {{ $examData['currentQuestionIndex'] + 1 }}</span>
                                </div>
                                @if (isset($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan']))
                                    <div
                                        class="px-2 sm:px-3 py-1 rounded-full text-xs font-medium
                                            {{ $examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan'] == 'mudah'
                                                ? 'bg-green-100 text-green-800'
                                                : ($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan'] == 'sedang'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan']) }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-xs sm:text-sm text-gray-500">
                                {{ $examData['currentQuestionIndex'] + 1 }} dari {{ count($examData['questions']) }}
                                soal
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-6 sm:mb-8">
                            <div class="text-base sm:text-lg font-medium text-gray-800 leading-relaxed">
                                {!! $examData['questions'][$examData['currentQuestionIndex']]['soal'] !!}
                            </div>

                            @php
                                $currentQuestionData = $examData['questions'][$examData['currentQuestionIndex']];
                                $gambarSoal = $currentQuestionData['gambar_soal'];
                            @endphp

                            @if (!empty($gambarSoal))
                                <div class="mt-4 text-center">
                                    <img src="{{ asset('storage/soal/pertanyaan/' . $gambarSoal) }}"
                                        alt="Gambar soal" class="max-w-full sm:max-w-md mx-auto rounded-lg shadow-md">
                                </div>
                            @endif
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-3 sm:space-y-4 mb-6 sm:mb-8">
                            @php
                                $currentQuestion = $examData['questions'][$examData['currentQuestionIndex']];
                                // Use options directly from controller
                                $options = $currentQuestion['options'] ?? [];
                            @endphp

                            @foreach ($options as $key => $option)
                                <button
                                    class="option-card w-full p-3 sm:p-6 rounded-xl border-2 border-gray-200 text-left 
                                           hover:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-100
                                           {{ isset($examData['answers'][$currentQuestion['id']]) && $examData['answers'][$currentQuestion['id']] == $key ? 'option-selected border-indigo-500' : '' }}"
                                    data-option="{{ $key }}"
                                    onclick="selectAnswer('{{ $currentQuestion['id'] }}', '{{ $key }}')">
                                    <div class="flex items-start space-x-3 sm:space-x-4">
                                        <div
                                            class="flex-shrink-0 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 
                                               flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                                            {{ strtoupper($key) }}
                                        </div>
                                        <div class="flex-1 text-sm sm:text-base text-gray-700 leading-relaxed">
                                            @if (is_array($option))
                                                @if ($option['tipe'] == 'gambar' && $option['gambar'])
                                                    <img src="{{ asset('storage/soal/pilihan/' . $option['gambar']) }}"
                                                        alt="Pilihan {{ $key }}"
                                                        class="max-w-full sm:max-w-sm mx-auto rounded-lg shadow-md">
                                                    @if ($option['teks'])
                                                        <div class="mt-2">{!! $option['teks'] !!}</div>
                                                    @endif
                                                @else
                                                    {!! $option['teks'] !!}
                                                @endif
                                            @else
                                                {!! $option !!}
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex flex-wrap justify-between items-center gap-2 pt-4 sm:pt-6 border-t">
                            <button id="prevBtn"
                                class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-6 py-2 sm:py-3 bg-gray-200 hover:bg-gray-300 
                                       text-gray-700 rounded-lg font-medium transition-colors text-sm sm:text-base
                                       {{ $examData['currentQuestionIndex'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $examData['currentQuestionIndex'] == 0 ? 'disabled' : '' }}
                                onclick="navigateQuestion('prev')">
                                <i class="fas fa-chevron-left"></i>
                                <span>Sebelumnya</span>
                            </button>

                            {{-- <div class="flex space-x-2 sm:space-x-3">
                                <button
                                    class="px-3 sm:px-6 py-2 sm:py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors text-xs sm:text-base"
                                    onclick="skipQuestion()">
                                    <i class="fas fa-forward mr-1 sm:mr-2"></i>
                                    <span class="hidden xs:inline">Lewati</span>
                                    <span class="xs:hidden">Lewat</span>
                                </button>

                                <button
                                    class="px-3 sm:px-6 py-2 sm:py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors text-xs sm:text-base"
                                    onclick="saveAndNext()">
                                    <i class="fas fa-save mr-1 sm:mr-2"></i>
                                    <span class="hidden xs:inline">Simpan & Lanjut</span>
                                    <span class="xs:hidden">Simpan</span>
                                </button>
                            </div> --}}

                            <button id="nextBtn"
                                class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-6 py-2 sm:py-3 bg-indigo-500 hover:bg-indigo-600 
                                       text-white rounded-lg font-medium transition-colors text-sm sm:text-base
                                       {{ $examData['currentQuestionIndex'] >= count($examData['questions']) - 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $examData['currentQuestionIndex'] >= count($examData['questions']) - 1 ? 'disabled' : '' }}
                                onclick="navigateQuestion('next')">
                                <span>Selanjutnya</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Mobile Bottom Navigation Bar -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t lg:hidden flex justify-between items-center px-4 py-2 z-30">
            <button onclick="navigateQuestion('prev')"
                class="{{ $examData['currentQuestionIndex'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $examData['currentQuestionIndex'] == 0 ? 'disabled' : '' }}>
                <i class="fas fa-chevron-left text-lg text-indigo-500"></i>
            </button>

            <button id="mobile-nav-show" class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm">
                <i class="fas fa-th-large mr-1"></i>
                <span>Soal</span>
            </button>

            <div class="text-xs font-bold text-center">
                <span class="text-green-600">{{ count($examData['answers'] ?? []) }}</span>
                <span class="text-gray-500">/{{ count($examData['questions'] ?? []) }}</span>
            </div>

            <button onclick="saveAndNext()" class="px-3 py-1 rounded-full bg-blue-500 text-white text-sm">
                <i class="fas fa-save mr-1"></i>
                <span>Simpan</span>
            </button>

            <button onclick="navigateQuestion('next')"
                class="{{ $examData['currentQuestionIndex'] >= count($examData['questions']) - 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $examData['currentQuestionIndex'] >= count($examData['questions']) - 1 ? 'disabled' : '' }}>
                <i class="fas fa-chevron-right text-lg text-indigo-500"></i>
            </button>
        </div>
    </div>

    <!-- Modals and JavaScript -->
    <script>
        // Global variables
        let currentQuestionIndex = {{ $examData['currentQuestionIndex'] ?? 0 }};
        let questions = @json($examData['questions'] ?? []);
        let answers = @json($examData['answers'] ?? []);
        let flaggedQuestions = @json($examData['flaggedQuestions'] ?? []);
        let hasilUjianId = {{ $examData['hasilUjianId'] ?? 0 }};
        let examSettings = @json($examData['examSettings'] ?? []);

        // Update time limit to realtime
        let timeLimit = {{ $examData['examEndTime'] ?? '' }};
        let remainingTime = Math.floor((timeLimit - Date.now()) / 1000);;
        console.log(timeLimit, remainingTime);
        // let timeLimit = {{ $examData['timeLimit'] ?? 0 }};
        // let remainingTime = {{ $examData['remainingTime'] ?? 0 }};

        // Submit Exam
        const submitExamBtn = document.getElementById('submitExam');
        const modal = document.getElementById('submitExamModal');
        const cancelBtn = document.getElementById('cancelSubmitExam');
        const confirmBtn = document.getElementById('confirmSubmitExam');
        const closeBtn = document.getElementById('closeModal');

        // Tampilkan modal saat klik tombol
        submitExamBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        // Tutup modal saat klik batal atau X
        cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        // Konfirmasi submit
        confirmBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            submitExam(); // panggil fungsi submit
        });



        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


        // Safe system notification function (doesn't trigger violation detection)
        function showSystemNotification(message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 text-sm';

            // Set color based on type
            switch (type) {
                case 'success':
                    notification.className += ' bg-green-500 text-white';
                    break;
                case 'warning':
                    notification.className += ' bg-yellow-500 text-white';
                    break;
                case 'error':
                    notification.className += ' bg-red-500 text-white';
                    break;
                default:
                    notification.className += ' bg-blue-500 text-white';
            }

            notification.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
            document.body.appendChild(notification);

            // Auto remove after duration
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }

        // Mobile sidebar functionality
        const mobileSidebar = document.getElementById('question-sidebar');
        const mobileNavToggle = document.getElementById('mobile-nav-toggle');
        const mobileNavShow = document.getElementById('mobile-nav-show');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            mobileSidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.classList.toggle('overflow-hidden');
        }

        if (mobileNavToggle) {
            mobileNavToggle.addEventListener('click', toggleSidebar);
        }

        if (mobileNavShow) {
            mobileNavShow.addEventListener('click', toggleSidebar);
        }

        if (closeSidebar) {
            closeSidebar.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        function navigateToQuestion(index) {
            if (index >= 0 && index < questions.length) {
                saveCurrentAnswer().then(() => {
                    window.location.href =
                        `{{ route('ujian.exam', ['jadwal_id' => $examData['jadwalUjianId'] ?? 0]) }}?question=${index}`;
                }).catch(() => {
                    window.location.href =
                        `{{ route('ujian.exam', ['jadwal_id' => $examData['jadwalUjianId'] ?? 0]) }}?question=${index}`;
                });
            }
        }

        function restoreSelectedAnswer() {
            // Clear all option selections first
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('option-selected', 'border-indigo-500');
                card.classList.add('border-gray-200');
            });

            // If there's a saved answer for current question, highlight it
            if (questions[currentQuestionIndex]) {
                const currentQuestion = questions[currentQuestionIndex];
                const savedAnswer = answers[currentQuestion.id];

                if (savedAnswer) {
                    const selectedOption = document.querySelector(`[onclick*="'${savedAnswer}'"]`);
                    if (selectedOption) {
                        selectedOption.classList.add('option-selected', 'border-indigo-500');
                        selectedOption.classList.remove('border-gray-200');
                    }
                }
            }
        }

        function navigateQuestion(direction) {
            const currentIndex = currentQuestionIndex;
            let newIndex = currentIndex;

            if (direction === 'prev' && currentIndex > 0) {
                newIndex = currentIndex - 1;
            } else if (direction === 'next' && currentIndex < questions.length - 1) {
                newIndex = currentIndex + 1;
            }

            if (newIndex !== currentIndex) {
                navigateToQuestion(newIndex);
            }
        }

        function selectAnswer(questionId, option) {
            // Update UI - remove selection from all options
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('option-selected', 'border-indigo-500');
                card.classList.add('border-gray-200');
            });

            // Add selection to clicked option
            event.currentTarget.classList.add('option-selected', 'border-indigo-500');
            event.currentTarget.classList.remove('border-gray-200');

            // Update answers object
            answers[questionId] = option;

            // Update progress and navigation
            updateProgress();

            // Auto-save after short delay
            setTimeout(() => saveCurrentAnswer(), 500);
        }

        // Save current answer
        async function saveCurrentAnswer() {
            if (!questions[currentQuestionIndex]) return Promise.resolve();

            const currentQuestion = questions[currentQuestionIndex];
            const selectedAnswer = answers[currentQuestion.id];

            if (!selectedAnswer) return Promise.resolve();

            try {
                const response = await fetch('{{ route('ujian.save-answer') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        hasil_ujian_id: hasilUjianId,
                        soal_ujian_id: currentQuestion.id,
                        jawaban: selectedAnswer
                    })
                });

                if (!response.ok) throw new Error('Failed to save answer');

                const result = await response.json();
                return result;
            } catch (error) {
                // Error saving answer - show system notification
                showSystemNotification('Gagal menyimpan jawaban', 'error');
                return Promise.reject(error);
            }
        }

        function skipQuestion() {
            // Find next unanswered question
            let nextIndex = -1;
            for (let i = currentQuestionIndex + 1; i < questions.length; i++) {
                if (!answers[questions[i].id]) {
                    nextIndex = i;
                    break;
                }
            }

            // If no unanswered found after current, look from beginning
            if (nextIndex === -1) {
                for (let i = 0; i < currentQuestionIndex; i++) {
                    if (!answers[questions[i].id]) {
                        nextIndex = i;
                        break;
                    }
                }
            }

            if (nextIndex !== -1) {
                navigateToQuestion(nextIndex);
            } else {
                // All questions answered, go to next question or show completion
                if (currentQuestionIndex < questions.length - 1) {
                    navigateQuestion('next');
                } else {
                    showSystemNotification('Semua soal sudah dijawab!');
                }
            }
        }

        function saveAndNext() {
            // Always try to save current answer (if any)
            saveCurrentAnswer().then(() => {
                if (currentQuestionIndex < questions.length - 1) {
                    navigateQuestion('next');
                } else {
                    // Last question, could show submit confirmation
                    showSystemNotification(
                        'Ini adalah soal terakhir. Gunakan tombol "Selesai" untuk mengumpulkan ujian.');
                }
            }).catch((error) => {
                // Error in save operation
                showSystemNotification('Gagal menyimpan data', 'error');
                // Even if save fails, still proceed to next question
                if (currentQuestionIndex < questions.length - 1) {
                    navigateQuestion('next');
                } else {
                    showSystemNotification(
                        'Ini adalah soal terakhir. Gunakan tombol "Selesai" untuk mengumpulkan ujian.');
                }
            });
        }

        function updateProgress() {
            const answeredCount = Object.keys(answers).length;
            const totalQuestions = questions.length;

            // Update both progress rings
            const mobileSVGProgressRing = document.querySelector('.progress-ring.sm\\:hidden');
            const desktopSVGProgressRing = document.querySelector('.progress-ring.hidden.sm\\:block');

            if (mobileSVGProgressRing) {
                const mobileProgress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
                mobileSVGProgressRing.style.strokeDasharray = `${mobileProgress} 100`;
            }

            if (desktopSVGProgressRing) {
                const desktopProgress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 126 : 0;
                desktopSVGProgressRing.style.strokeDasharray = `${desktopProgress} 126`;
            }

            // Update question count
            const countElements = document.querySelectorAll('.text-xs.font-bold.text-gray-700 span');
            countElements.forEach(el => {
                el.textContent = `${answeredCount}/${totalQuestions}`;
            });

            // Update mobile bottom bar count
            const mobileCount = document.querySelector('.fixed.bottom-0 .text-xs.font-bold span.text-green-600');
            if (mobileCount) {
                mobileCount.textContent = answeredCount;
            }

            // Update navigation button colors
            updateNavigationButtons();
        }

        function updateNavigationButtons() {
            // Update each navigation button based on answer status
            questions.forEach((question, index) => {
                const btn = document.querySelector(`[data-question-index="${index}"]`);
                if (!btn) return;

                // Remove all answer-related classes
                btn.classList.remove('bg-green-500', 'text-white', 'border-green-500', 'bg-gray-100',
                    'text-gray-700', 'border-gray-300');

                if (index === currentQuestionIndex) {
                    // Current question - keep current styling (blue)
                    btn.className =
                        'question-nav-btn w-8 h-8 sm:w-10 sm:h-10 rounded-lg border-2 font-bold text-xs sm:text-sm transition-all duration-300 bg-indigo-500 text-white border-indigo-500';
                } else if (answers[question.id]) {
                    // Answered question - green
                    btn.className =
                        'question-nav-btn w-8 h-8 sm:w-10 sm:h-10 rounded-lg border-2 font-bold text-xs sm:text-sm transition-all duration-300 bg-green-500 text-white border-green-500';
                } else if (flaggedQuestions.includes(question.id)) {
                    // Flagged question - yellow  
                    btn.className =
                        'question-nav-btn w-8 h-8 sm:w-10 sm:h-10 rounded-lg border-2 font-bold text-xs sm:text-sm transition-all duration-300 bg-yellow-500 text-white border-yellow-500';
                } else {
                    // Unanswered question - gray
                    btn.className =
                        'question-nav-btn w-8 h-8 sm:w-10 sm:h-10 rounded-lg border-2 font-bold text-xs sm:text-sm transition-all duration-300 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200';
                }
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            restoreSelectedAnswer(); // Restore the selected answer for current question

            // Start timer if time limit is set
            if (timeLimit > 0 && remainingTime > 0) {
                startTimer();
            }

            // Auto-save every 30 seconds
            setInterval(() => {
                if (questions[currentQuestionIndex]) {
                    saveCurrentAnswer();
                }
            }, 30000);

            // Set up visibility change detection
            setupVisibilityChangeDetection();
        });

        // Countdown timer function
        function startTimer() {
            if (remainingTime <= 0) return;

            // Update timer display immediately
            updateTimerDisplay();

            const timerInterval = setInterval(() => {
                if (remainingTime > 0) {
                    remainingTime--;
                    updateTimerDisplay();

                    // Warning when 5 minutes left
                    if (remainingTime === 300) {
                        showSystemNotification('⚠️ Perhatian: Waktu ujian tersisa 5 menit lagi!');
                    }

                    // Warning when 1 minute left
                    if (remainingTime === 60) {
                        showSystemNotification('⚠️ Perhatian: Waktu ujian tersisa 1 menit lagi!');
                    }

                } else {
                    // Time's up!
                    clearInterval(timerInterval);
                    showSystemNotification('⏰ Waktu ujian habis! Ujian akan dikumpulkan otomatis.');
                    console.log('Waktu habis, mengumpulkan ujian...');
                    autoSubmitExam();
                }
            }, 1000);
        }

        // Update timer display
        function updateTimerDisplay() {

            const hours = Math.floor(remainingTime / 3600);
            const minutes = Math.floor((remainingTime % 3600) / 60);
            const seconds = remainingTime % 60;

            const timeString =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            const timerElement = document.getElementById('timer');
            // const submitBtn = document.getElementById('submitExamBtn');
            if (timerElement) {
                timerElement.textContent = timeString;

                // Change color when time is running low
                const timerParent = timerElement.parentElement.parentElement;
                if (remainingTime <= 300) { // 5 minutes
                    timerParent.className =
                        'bg-gradient-to-r from-red-500 to-red-600 text-white px-2 sm:px-4 py-2 rounded-lg shadow-md pulse';
                    if (submitExamBtn) submitExamBtn.classList.remove('hidden'); // tampilkan tombol
                } else if (remainingTime <= 600) { // 10 minutes  
                    timerParent.className =
                        'bg-gradient-to-r from-orange-400 to-orange-500 text-white px-2 sm:px-4 py-2 rounded-lg shadow-md';
                } else {
                    timerParent.className =
                        'bg-gradient-to-r from-orange-400 to-red-500 text-white px-2 sm:px-4 py-2 rounded-lg shadow-md';
                }
            }
        }

        // Auto-submit when time runs out
        function autoSubmitExam() {
            // Save current answer before submitting
            saveCurrentAnswer().then(() => {
                // Submit exam formally via the submit API endpoint
                fetch('{{ route('ujian.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            hasil_ujian_id: hasilUjianId,
                            is_auto_submit: true
                        })
                    })
                    .then(() => {
                        // Redirect to dashboard after submission
                        console.log('Ujian berhasil disubmit, mengarahkan ke Dashboard...');
                        window.location.href = '{{ route('siswa.dashboard') }}';
                    })
                    .catch(() => {
                        // Even if submission fails, redirect to dashboard
                        window.location.href = '{{ route('siswa.dashboard') }}';
                    });
            }).catch(() => {
                // Even if save fails, still redirect
                window.location.href = '{{ route('siswa.dashboard') }}';
            });
        }



        // Fungsi submit exam (tetap pakai fetch)
        function submitExam() {
            saveCurrentAnswer().then(() => {
                fetch('{{ route('ujian.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            hasil_ujian_id: hasilUjianId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showSystemNotification('Ujian berhasil dikumpulkan!');
                            window.location.href = '{{ route('siswa.dashboard') }}';
                        } else {
                            showSystemNotification('Gagal mengumpulkan ujian: ' + (data.message || data.error ||
                                'Unknown error'));
                        }
                    })
                    .catch(err => {
                        showSystemNotification('Terjadi kesalahan saat mengumpulkan ujian', 'error');
                    });
            }).catch(err => {
                showSystemNotification('Gagal menyimpan jawaban terakhir', 'error');
                // Optional: tanya user tetap submit
                if (confirm('Gagal menyimpan jawaban terakhir. Tetap lanjutkan mengumpulkan ujian?')) {
                    window.location.href = '{{ route('siswa.dashboard') }}';
                }
            });
        }

        // Toggle flag for current question
        function toggleFlag() {
            const currentQuestion = questions[currentQuestionIndex];
            if (!currentQuestion) return;

            fetch('{{ route('ujian.flag-question') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        hasil_ujian_id: hasilUjianId,
                        soal_ujian_id: currentQuestion.id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        const flagText = document.getElementById('flagText');
                        const questionNavBtn = document.querySelector(
                            `button[onclick="navigateToQuestion(${currentQuestionIndex})"]`);

                        if (data.is_flagged) {
                            flagText.textContent = 'Lepas Tanda';
                            if (questionNavBtn) {
                                questionNavBtn.classList.remove('bg-gray-100', 'bg-green-500');
                                questionNavBtn.classList.add('bg-yellow-500', 'text-white');
                            }
                        } else {
                            flagText.textContent = 'Tandai Soal';
                            if (questionNavBtn) {
                                questionNavBtn.classList.remove('bg-yellow-500');
                                if (answers[currentQuestion.id]) {
                                    questionNavBtn.classList.add('bg-green-500', 'text-white');
                                } else {
                                    questionNavBtn.classList.add('bg-gray-100');
                                    questionNavBtn.classList.remove('text-white');
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    // Error toggling flag
                    showSystemNotification('Gagal mengubah flag', 'error');
                    showSystemNotification('Gagal mengubah status tandai soal');
                });
        }

        // Set up detection for browser tab switching or minimizing
        function setupVisibilityChangeDetection() {
            // Check if auto-logout is enabled in exam settings
            const autoLogoutEnabled = {{ $examData['examSettings']['aktifkan_auto_logout'] ? 'true' : 'false' }};

            // If auto-logout is disabled, don't set up the detection
            if (!autoLogoutEnabled) {
                // Show notification that monitoring is disabled
                showSystemNotification('Monitoring pelanggaran ujian dinonaktifkan oleh pengawas', 'info');
                return;
            }

            let visibilityWarnings = 0;
            const maxWarnings = 3; // Number of warnings before automatic logout
            let lastFocusTime = Date.now();
            let isDetectionActive = false;
            let debounceTimer = null;

            // Grace period: Don't start detection immediately (3 seconds after page load)
            const gracePeriod = 2000; // 2 seconds
            showSystemNotification('Sistem monitoring akan aktif dalam 2 detik...', 'info');

            setTimeout(() => {
                isDetectionActive = true;
                showSystemNotification('Sistem monitoring ujian telah aktif', 'success');

                // Show one-time notification to student
                const notification = document.createElement('div');
                notification.className =
                    'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm';
                notification.innerHTML =
                    '<i class="fas fa-shield-alt mr-2"></i>Sistem monitoring ujian telah aktif';
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }, gracePeriod);

            // Add event listeners for visibility change - but only process if detection is active
            document.addEventListener('visibilitychange', handleVisibilityChange);
            window.addEventListener('blur', handleWindowBlur);
            window.addEventListener('focus', handleWindowFocus);

            function handleVisibilityChange() {
                if (!isDetectionActive) return; // Skip during grace period

                if (document.visibilityState === 'hidden') {
                    handleUserLeftPage();
                } else if (document.visibilityState === 'visible') {
                    handleUserReturnedToPage();
                }
            }

            function handleWindowBlur() {
                if (!isDetectionActive) return; // Skip during grace period

                // Add debouncing to prevent false positives from quick focus changes
                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                debounceTimer = setTimeout(() => {
                    // Only trigger if the page is still not visible after a short delay
                    if (document.visibilityState === 'hidden') {
                        handleUserLeftPage();
                    }
                }, 1000); // 1 second debounce
            }

            function handleWindowFocus() {
                if (!isDetectionActive) return; // Skip during grace period

                // Clear the debounce timer as user is back
                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                    debounceTimer = null;
                }

                handleUserReturnedToPage();
            }

            function handleUserLeftPage() {
                if (!isDetectionActive) return; // Skip during grace period

                // System log: User left page detected (using internal logging)
                lastFocusTime = Date.now();

                // Only increment warnings for actual visibility changes (not just focus changes)
                if (document.visibilityState === 'hidden') {
                    visibilityWarnings++;
                    // Internal tracking: violation count increased
                    localStorage.setItem('violationCount', visibilityWarnings);
                    if (visibilityWarnings > maxWarnings) {
                        // Automatically logout
                        logoutDueToCheating();
                    } else {
                        // Store the time when user left the page
                        localStorage.setItem('examLeftPageTime', Date.now());
                    }
                }
            }

            // function handleUserReturnedToPage() {
            //     if (!isDetectionActive) return; // Skip during grace period

            //     const leftTime = localStorage.getItem('examLeftPageTime');
            //     if (leftTime) {
            //         const timeAway = Date.now() - parseInt(leftTime);

            //         // Only show warning if away for more than 5 seconds (increased threshold)
            //         if (timeAway > 5000) {
            //             if (visibilityWarnings >= maxWarnings) {
            //                 logoutDueToCheating();
            //             } else {
            //                 const violationCount = parseInt(localStorage.getItem('violationCount') || '0');
            //                 // Show violation modal instead of alert
            //                 showViolationModal(
            //                     `Anda telah berpindah dari halaman ujian selama ${Math.floor(timeAway/1000)} detik!`,
            //                     `Peringatan ${violationCount} dari ${maxWarnings}`,
            //                     violationCount
            //                 );
            //             }
            //         } else {
            //             // Internal tracking: Quick focus change detected, ignoring
            //         }

            //         // Remove stored time
            //         localStorage.removeItem('examLeftPageTime');
            //     }
            // }
            function handleUserReturnedToPage() {
                if (!isDetectionActive) return;

                const leftTime = localStorage.getItem('examLeftPageTime');
                if (leftTime) {
                    const timeAway = Date.now() - parseInt(leftTime);

                    if (timeAway > 5000) {
                        // Catat ke server
                        fetch('{{ route('ujian.record-violation') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    hasil_ujian_id: hasilUjianId,
                                    reason: 'tab_switching'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (data.force_logout) {
                                        logoutDueToCheating();
                                    } else {
                                        showViolationModal(
                                            `Anda telah berpindah dari halaman ujian selama ${Math.floor(timeAway/1000)} detik!`,
                                            `Peringatan ${data.violations_count} dari ${maxWarnings}. Pelanggaran ini telah dicatat dan akan dilaporkan ke pengawas.`,
                                            data.violations_count
                                        );
                                    }
                                }
                            })
                            .catch(() => {
                                showViolationModal(
                                    'KESALAHAN SISTEM',
                                    'Gagal merekam pelanggaran. Silakan lanjutkan ujian, laporkan ke pengawas.',
                                    0
                                );
                            });
                    }

                    localStorage.removeItem('examLeftPageTime');
                }
            }


            function logoutDueToCheating() {
                // Save any answers
                saveCurrentAnswer().catch(() => {});

                let violationCount = parseInt(localStorage.getItem('violationCount') || '0');
                violationCount++;
                localStorage.setItem('violationCount', violationCount);

                // Mark this exam attempt as a violation
                fetch('{{ route('ujian.logout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            hasil_ujian_id: hasilUjianId,
                            reason: 'tab_switching'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.continue_exam) {
                            // Show violation modal instead of alert
                            showViolationModal(
                                'Anda telah berpindah tab atau meminimalkan browser!',
                                'Pelanggaran ini telah dicatat dan akan dilaporkan ke pengawas. Anda dapat melanjutkan ujian.',
                                data.violations_count
                            );

                            // Update UI to show violation count if needed
                            if (data.violations_count) {
                                const violationBadge = document.getElementById('violation-count');
                                if (violationBadge) {
                                    violationBadge.textContent = data.violations_count;
                                    violationBadge.classList.remove('hidden');
                                }

                                // Show violation warning panel
                                const warningPanel = document.getElementById('violation-warning');
                                const warningMessage = document.getElementById('violation-message');

                                if (warningPanel && warningMessage) {
                                    warningPanel.classList.remove('hidden');
                                    warningMessage.textContent =
                                        'Terdeteksi perpindahan tab. Pelanggaran telah dicatat dan dilaporkan ke pengawas.';

                                    // Auto-hide the warning after 10 seconds
                                    setTimeout(() => {
                                        warningPanel.classList.add('hidden');
                                    }, 10000);
                                }
                            }
                        } else {
                            // If server decides to log student out anyway
                            showSystemNotification(
                                'Anda telah melakukan pelanggaran berulang kali. Sistem akan logout otomatis.'
                            );

                            // Logout if server requests it
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route('siswa.logout') }}';
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = csrfToken;
                            form.appendChild(csrfInput);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    })
                    .catch(error => {
                        // Error recording violation - show user-friendly modal
                        showViolationModal(
                            'KESALAHAN SISTEM',
                            'Terjadi kesalahan saat merekam pelanggaran. Silakan lanjutkan ujian dan laporkan ke pengawas jika masalah berlanjut.',
                            0
                        );
                    });
            }
        }



        // Function to show violation modal (uncloseable)
        function showViolationModal(title, message, violationCount = 0) {
            const modal = document.getElementById('violation-modal');
            const modalTitle = modal.querySelector('h3');
            const modalMessage = modal.querySelector('#violation-modal-message');
            const modalViolationCount = document.getElementById('modal-violation-count');
            const continueBtn = document.getElementById('continue-exam-btn');

            let count = violationCount !== null ? violationCount : parseInt(localStorage.getItem('violationCount') || '0');
            modalViolationCount.textContent = count;

            // Update modal content
            if (title.includes('Peringatan') || title.includes('Pelanggaran')) {
                modalTitle.innerHTML = `⚠️ ${title}`;
            } else {
                modalTitle.innerHTML = '⚠️ PELANGGARAN TERDETEKSI';
            }
            // Update message
            const messageLines = message.split('. ');
            modalMessage.innerHTML = '';
            messageLines.forEach(line => {
                if (line.trim()) {
                    const p = document.createElement('p');
                    p.className = 'text-sm mb-2';
                    if (line.includes('dicatat')) {
                        p.className += ' font-semibold text-red-600';
                    }
                    p.textContent = line.trim() + '.';
                    modalMessage.appendChild(p);
                }
            });

            // Update violation count
            modalViolationCount.textContent = violationCount || '0';

            // Show modal
            modal.classList.remove('hidden');

            // Disable page scrolling
            document.body.style.overflow = 'hidden';

            // Focus on continue button
            continueBtn.focus();

            // Prevent ESC key from closing modal and other keyboard shortcuts
            const handleKeyDown = (e) => {
                // Prevent ESC, Alt+Tab, Ctrl+Tab, F5, Ctrl+R, etc.
                if (e.key === 'Escape' ||
                    (e.altKey && e.key === 'Tab') ||
                    (e.ctrlKey && e.key === 'Tab') ||
                    e.key === 'F5' ||
                    (e.ctrlKey && e.key === 'r')) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                // Allow Enter key to trigger continue button
                if (e.key === 'Enter') {
                    e.preventDefault();
                    continueBtn.click();
                }
            };

            document.addEventListener('keydown', handleKeyDown);

            // Handle continue button click
            const handleContinue = () => {
                // Hide modal
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';

                // Remove event listeners
                document.removeEventListener('keydown', handleKeyDown);
                continueBtn.removeEventListener('click', handleContinue);

                // Focus back on the exam content
                const currentQuestion = document.querySelector('[data-question-index="' + currentQuestionIndex + '"]');
                if (currentQuestion) {
                    currentQuestion.focus();
                }

                // Log that student acknowledged the violation (internal tracking)
                // Student acknowledged violation and continued exam
            };

            continueBtn.addEventListener('click', handleContinue);

            // Prevent clicking outside to close modal
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            // Add visual indication that modal is uncloseable
            const modalContent = modal.querySelector('.bg-white');
            modalContent.classList.add('shadow-2xl', 'ring-4', 'ring-red-500', 'ring-opacity-50');
        }
    </script>
</body>

</html>
