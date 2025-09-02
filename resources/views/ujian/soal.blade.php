@extends('layouts.app')

@section('title', 'Ujian - Soal ' . ($soalIndex + 1))

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left side - Question area -->
            <div class="lg:w-3/4 bg-white rounded-lg shadow-md p-6">
                <!-- Progress bar -->
                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm">Soal {{ $progress['current'] }} dari {{ $progress['total'] }}</span>
                        <span class="text-sm">
                            <span class="text-green-600">{{ $progress['terjawab'] }} terjawab</span> |
                            <span class="text-red-600">{{ $progress['belum_terjawab'] }} belum terjawab</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress['percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Timer -->
                <div class="bg-gray-100 rounded-md p-4 mb-6 text-center">
                    <span class="font-medium">Sisa Waktu:</span>
                    <span id="countdown" class="font-bold text-lg ml-2">--:--:--</span>
                </div>

                <!-- Question -->
                <div class="mb-8">
                    <h2 class="font-bold text-lg mb-4">Soal {{ $soalIndex + 1 }}</h2>
                    <div class="prose max-w-none mb-6">
                        {!! $soal->pertanyaan_html !!}
                    </div>

                    <!-- Answer form -->
                    <form id="answerForm" method="POST" action="{{ route('ujian.jawaban') }}">
                        @csrf
                        <input type="hidden" name="soal_id" value="{{ $soal->id }}">
                        <input type="hidden" name="soal_index" value="{{ $soalIndex }}">

                        <div class="space-y-4">
                            @foreach (json_decode($soal->pilihan) as $key => $pilihan)
                                <div class="flex items-start">
                                    <div class="flex items-center h-6">
                                        <input id="option{{ $key }}" name="jawaban" type="radio"
                                            value="{{ $key }}"
                                            class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                                            {{ $currentSoal['jawaban'] == $key ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3">
                                        <label for="option{{ $key }}" class="text-gray-700 select-none">
                                            <span class="font-medium mr-2">{{ strtoupper($key) }}.</span>
                                            {!! $pilihan !!}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Navigation buttons -->
                        <div class="flex justify-between mt-8">
                            <button type="submit" name="action" value="save_prev"
                                class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md"
                                {{ $soalIndex == 0 ? 'disabled' : '' }}>
                                ← Soal Sebelumnya
                            </button>

                            <button type="submit" name="action" value="save"
                                class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                                Simpan
                            </button>

                            <button type="submit" name="action" value="save_next"
                                class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md"
                                {{ $soalIndex == $progress['total'] - 1 ? 'disabled' : '' }}>
                                Soal Berikutnya →
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right side - Question navigator -->
            <div class="lg:w-1/4 bg-white rounded-lg shadow-md p-6">
                <h3 class="font-bold text-lg mb-4">Navigasi Soal</h3>

                <div class="grid grid-cols-5 gap-2">
                    @for ($i = 0; $i < count($hasilUjian->jawaban_siswa); $i++)
                        @php
                            $jawaban = $hasilUjian->jawaban_siswa[$i];
                            $buttonClass = 'w-10 h-10 flex items-center justify-center rounded-md ';

                            if ($i == $soalIndex) {
                                $buttonClass .= 'bg-blue-600 text-white border-2 border-blue-600';
                            } elseif (!is_null($jawaban['jawaban'])) {
                                $buttonClass .= 'bg-green-100 text-green-800 border border-green-300';
                            } else {
                                $buttonClass .= 'bg-gray-100 text-gray-800 border border-gray-300';
                            }
                        @endphp

                        <a href="{{ route('ujian.soal', $i) }}" class="{{ $buttonClass }}"
                            title="Soal {{ $i + 1 }} {{ !is_null($jawaban['jawaban']) ? '(Sudah Dijawab)' : '(Belum Dijawab)' }}">
                            {{ $i + 1 }}
                        </a>
                    @endfor
                </div>

                <div class="mt-8">
                    <div class="flex items-center mb-2">
                        <div class="w-4 h-4 bg-green-100 border border-green-300 rounded-sm mr-2"></div>
                        <span class="text-sm">Sudah dijawab</span>
                    </div>
                    <div class="flex items-center mb-2">
                        <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded-sm mr-2"></div>
                        <span class="text-sm">Belum dijawab</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-600 rounded-sm mr-2"></div>
                        <span class="text-sm">Soal aktif</span>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-md"
                        onclick="confirmFinish()">
                        Selesai Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Countdown timer
        let remainingTime = {{ $sisaWaktu }};

        function updateCountdown() {
            const hours = Math.floor(remainingTime / 3600);
            const minutes = Math.floor((remainingTime % 3600) / 60);
            const seconds = remainingTime % 60;

            document.getElementById('countdown').textContent =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (remainingTime <= 0) {
                // Time's up, submit the form
                window.location.href = "{{ route('ujian.finish') }}";
                return;
            }

            remainingTime--;
        }

        // Update the countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Confirm finish exam
        function confirmFinish() {
            if (confirm('Apakah Anda yakin ingin menyelesaikan ujian? Pastikan semua jawaban sudah terisi.')) {
                // Add hidden input to form
                const form = document.getElementById('answerForm');
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'finish';
                form.appendChild(actionInput);
                form.submit();
            }
        }
    </script>
@endpush
