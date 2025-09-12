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
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-indigo-500 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-brain text-indigo-500 mr-2"></i>
                        {{ $examData['title'] ?? 'Ujian' }}
                    </h1>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user mr-1"></i>
                        {{ $siswa->nama }}
                    </div>
                </div>

                <!-- Timer & Progress -->
                <div class="flex items-center space-x-6">
                    <!-- Progress Circle -->
                    <div class="relative w-12 h-12">
                        <svg class="w-12 h-12 transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="#e5e7eb" stroke-width="3"
                                fill="none" />
                            <circle cx="24" cy="24" r="20" stroke="#3b82f6" stroke-width="3"
                                fill="none" class="progress-ring"
                                stroke-dasharray="{{ $examData['totalQuestions'] > 0 ? ($examData['answeredCount'] / $examData['totalQuestions']) * 126 : 0 }} 126"
                                stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span
                                class="text-xs font-bold text-gray-700">{{ $examData['answeredCount'] ?? 0 }}/{{ $examData['totalQuestions'] ?? 0 }}</span>
                        </div>
                    </div>

                    <!-- Timer -->
                    @if (isset($examData['timeLimit']) && $examData['timeLimit'] > 0)
                        <div
                            class="bg-gradient-to-r from-orange-400 to-red-500 text-white px-4 py-2 rounded-lg shadow-md">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-clock"></i>
                                <span id="timer" class="font-mono font-bold text-lg">
                                    {{ gmdate('H:i:s', $examData['remainingTime'] ?? 0) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <button id="submitExam" onclick="submitExam()"
                        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 
                                   text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 
                                   transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (!isset($examData['questions']) || count($examData['questions']) == 0)
            <!-- No Questions Available -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-question-circle text-6xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-600 mb-2">Ujian Belum Tersedia</h3>
                <p class="text-gray-500 mb-6">Belum ada soal yang tersedia untuk ujian ini atau ujian belum dimulai.</p>

                <div class="space-y-3">
                    <a href="{{ route('siswa.portal.dashboard') }}"
                        class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-12 gap-6">

                <!-- Question Navigation Sidebar -->
                <div class="col-span-3">
                    <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-list-ol text-indigo-500 mr-2"></i>
                            Navigasi Soal
                        </h3>

                        <!-- Question Grid -->
                        <div class="grid grid-cols-5 gap-2 mb-4">
                            @foreach ($examData['questions'] as $index => $question)
                                <button
                                    class="question-nav-btn w-10 h-10 rounded-lg border-2 font-bold text-sm transition-all duration-300 
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
                        <div class="text-xs space-y-2">
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
                <div class="col-span-9">
                    <div id="questionContainer" class="bg-white rounded-2xl shadow-xl p-8">
                        <!-- Question Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg px-4 py-2">
                                    <span class="font-bold text-lg">Soal
                                        {{ $examData['currentQuestionIndex'] + 1 }}</span>
                                </div>
                                @if (isset($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan']))
                                    <div
                                        class="px-3 py-1 rounded-full text-xs font-medium
                                            {{ $examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan'] == 'mudah'
                                                ? 'bg-green-100 text-green-800'
                                                : ($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan'] == 'sedang'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($examData['questions'][$examData['currentQuestionIndex']]['tingkat_kesulitan']) }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $examData['currentQuestionIndex'] + 1 }} dari {{ count($examData['questions']) }}
                                soal
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-8">
                            <div class="text-lg font-medium text-gray-800 leading-relaxed">
                                {!! $examData['questions'][$examData['currentQuestionIndex']]['soal'] !!}
                            </div>

                            @if (isset($examData['questions'][$examData['currentQuestionIndex']]['gambar_soal']) &&
                                    $examData['questions'][$examData['currentQuestionIndex']]['gambar_soal']
                            )
                                <div class="mt-4">
                                    <img src="{{ Storage::url($examData['questions'][$examData['currentQuestionIndex']]['gambar_soal']) }}"
                                        alt="Gambar soal" class="max-w-md mx-auto rounded-lg shadow-md">
                                </div>
                            @endif
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-4 mb-8">
                            @php
                                $currentQuestion = $examData['questions'][$examData['currentQuestionIndex']];
                                // Use options directly from controller
                                $options = $currentQuestion['options'] ?? [];
                            @endphp

                            @foreach ($options as $key => $option)
                                <button
                                    class="option-card w-full p-6 rounded-xl border-2 border-gray-200 text-left 
                                           hover:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-100
                                           {{ isset($examData['answers'][$currentQuestion['id']]) && $examData['answers'][$currentQuestion['id']] == $key ? 'option-selected border-indigo-500' : '' }}"
                                    data-option="{{ $key }}"
                                    onclick="selectAnswer('{{ $currentQuestion['id'] }}', '{{ $key }}')">
                                    <div class="flex items-start space-x-4">
                                        <div
                                            class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 
                                               flex items-center justify-center text-white font-bold">
                                            {{ strtoupper($key) }}
                                        </div>
                                        <div class="flex-1 text-gray-700 leading-relaxed">
                                            {!! $option !!}
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between items-center pt-6 border-t">
                            <button id="prevBtn"
                                class="flex items-center space-x-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 
                                           text-gray-700 rounded-lg font-medium transition-colors
                                           {{ $examData['currentQuestionIndex'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $examData['currentQuestionIndex'] == 0 ? 'disabled' : '' }}
                                onclick="navigateQuestion('prev')">
                                <i class="fas fa-chevron-left"></i>
                                <span>Sebelumnya</span>
                            </button>

                            <div class="flex space-x-3">
                                <button
                                    class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors"
                                    onclick="skipQuestion()">
                                    <i class="fas fa-forward mr-2"></i>
                                    Lewati
                                </button>

                                <button
                                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
                                    onclick="saveAndNext()">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan & Lanjut
                                </button>
                            </div>

                            <button id="nextBtn"
                                class="flex items-center space-x-2 px-6 py-3 bg-indigo-500 hover:bg-indigo-600 
                                           text-white rounded-lg font-medium transition-colors
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
    </div>

    <!-- Modals and JavaScript -->
    <script>
        // Global variables
        let currentQuestionIndex = {{ $examData['currentQuestionIndex'] ?? 0 }};
        let questions = @json($examData['questions'] ?? []);
        let answers = @json($examData['answers'] ?? []);
        let flaggedQuestions = @json($examData['flaggedQuestions'] ?? []);
        let hasilUjianId = {{ $examData['hasilUjianId'] ?? 0 }};
        let timeLimit = {{ $examData['timeLimit'] ?? 0 }};
        let remainingTime = {{ $examData['remainingTime'] ?? 0 }};
        let examSettings = @json($examData['examSettings'] ?? []);

        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function navigateToQuestion(index) {
            if (index >= 0 && index < questions.length) {
                saveCurrentAnswer().then(() => {
                    window.location.href = `{{ route('siswa.portal.dashboard') }}?question=${index}`;
                }).catch(() => {
                    window.location.href = `{{ route('siswa.portal.dashboard') }}?question=${index}`;
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
                const response = await fetch('{{ route('siswa.portal.exam.save-answer') }}', {
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
                console.error('Error saving answer:', error);
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
                    alert('Semua soal sudah dijawab!');
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
                    alert('Ini adalah soal terakhir. Gunakan tombol "Selesai" untuk mengumpulkan ujian.');
                }
            }).catch((error) => {
                console.error('Save failed:', error);
                // Even if save fails, still proceed to next question
                if (currentQuestionIndex < questions.length - 1) {
                    navigateQuestion('next');
                } else {
                    alert('Ini adalah soal terakhir. Gunakan tombol "Selesai" untuk mengumpulkan ujian.');
                }
            });
        }

        function updateProgress() {
            const answeredCount = Object.keys(answers).length;
            const totalQuestions = questions.length;
            const progress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 126 : 0;

            const progressRing = document.querySelector('.progress-ring');
            if (progressRing) {
                progressRing.style.strokeDasharray = `${progress} 126`;
            }

            // Update question count
            const countElement = document.querySelector('.text-xs.font-bold.text-gray-700');
            if (countElement) {
                countElement.textContent = `${answeredCount}/${totalQuestions}`;
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
                        'question-nav-btn w-10 h-10 rounded-lg border-2 font-bold text-sm transition-all duration-300 bg-indigo-500 text-white border-indigo-500';
                } else if (answers[question.id]) {
                    // Answered question - green
                    btn.className =
                        'question-nav-btn w-10 h-10 rounded-lg border-2 font-bold text-sm transition-all duration-300 bg-green-500 text-white border-green-500';
                } else if (flaggedQuestions.includes(question.id)) {
                    // Flagged question - yellow  
                    btn.className =
                        'question-nav-btn w-10 h-10 rounded-lg border-2 font-bold text-sm transition-all duration-300 bg-yellow-500 text-white border-yellow-500';
                } else {
                    // Unanswered question - gray
                    btn.className =
                        'question-nav-btn w-10 h-10 rounded-lg border-2 font-bold text-sm transition-all duration-300 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200';
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
                        alert('⚠️ Perhatian: Waktu ujian tersisa 5 menit lagi!');
                    }

                    // Warning when 1 minute left
                    if (remainingTime === 60) {
                        alert('⚠️ Perhatian: Waktu ujian tersisa 1 menit lagi!');
                    }

                } else {
                    // Time's up!
                    clearInterval(timerInterval);
                    alert('⏰ Waktu ujian habis! Ujian akan dikumpulkan otomatis.');
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
            if (timerElement) {
                timerElement.textContent = timeString;

                // Change color when time is running low
                const timerParent = timerElement.parentElement.parentElement;
                if (remainingTime <= 300) { // 5 minutes
                    timerParent.className =
                        'bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg shadow-md pulse';
                } else if (remainingTime <= 600) { // 10 minutes  
                    timerParent.className =
                        'bg-gradient-to-r from-orange-400 to-orange-500 text-white px-4 py-2 rounded-lg shadow-md';
                } else {
                    timerParent.className =
                        'bg-gradient-to-r from-orange-400 to-red-500 text-white px-4 py-2 rounded-lg shadow-md';
                }
            }
        }

        // Auto-submit when time runs out
        function autoSubmitExam() {
            // Save current answer before submitting
            saveCurrentAnswer().then(() => {
                // Redirect to submit page or trigger submit
                window.location.href = '{{ route('siswa.portal.dashboard') }}';
            }).catch(() => {
                // Even if save fails, still redirect
                window.location.href = '{{ route('siswa.portal.dashboard') }}';
            });
        }

        // Toggle flag for current question
        function toggleFlag() {
            const currentQuestion = questions[currentQuestionIndex];
            if (!currentQuestion) return;

            fetch('{{ route('siswa.portal.exam.flag') }}', {
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
                    console.error('Error toggling flag:', error);
                    alert('Gagal mengubah status tandai soal');
                });
        }

        // Submit exam
        function submitExam() {
            if (confirm(
                    'Apakah Anda yakin ingin mengumpulkan ujian? Ujian yang sudah dikumpulkan tidak dapat diubah lagi.')) {
                // Save current answer first
                saveCurrentAnswer().then(() => {
                    // Submit exam
                    fetch('{{ route('siswa.portal.exam.submit') }}', {
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Ujian berhasil dikumpulkan!');
                                window.location.href = '{{ route('siswa.portal.dashboard') }}';
                            } else {
                                alert('Gagal mengumpulkan ujian: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting exam:', error);
                            alert('Terjadi kesalahan saat mengumpulkan ujian');
                        });
                }).catch(error => {
                    console.error('Error saving answer before submit:', error);
                    // Still try to submit even if save fails
                    if (confirm('Gagal menyimpan jawaban terakhir. Tetap lanjutkan mengumpulkan ujian?')) {
                        window.location.href = '{{ route('siswa.portal.dashboard') }}';
                    }
                });
            }
        }
    </script>
</body>

</html>
