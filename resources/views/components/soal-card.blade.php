<div class="soal-card bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6">
        <div class="flex items-start mb-4">
            <span
                class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-800 font-semibold text-sm mr-3">
                {{ $soal->nomor_soal }}
            </span>
            <div class="flex-1">
                <div class="prose max-w-none">
                    {!! nl2br($soal->pertanyaan_html) !!}
                </div>
                @if ($soal->gambar_pertanyaan)
                    <div class="mt-3 border rounded-lg overflow-hidden">
                        <img src="{{ $soal->gambar_pertanyaan_url }}" alt="Gambar Pertanyaan" class="max-w-full h-auto">
                    </div>
                @endif
            </div>
        </div>

        <div class="ml-11">
            @if ($soal->tipe_soal == 'pilihan_ganda')
                <div class="grid grid-cols-1 gap-3 mb-4">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opsi)
                        @php
                            $opsiLower = strtolower($opsi);
                            $pilihanText = $soal->{"pilihan_{$opsiLower}_teks"};
                            $pilihanGambar = $soal->{"pilihan_{$opsiLower}_gambar"};
                            $pilihanTipe = $soal->{"pilihan_{$opsiLower}_tipe"} ?? 'teks';
                            $isCorrect = strtoupper($soal->kunci_jawaban) == $opsi;
                        @endphp

                        @if ($pilihanText || $pilihanGambar)
                            <div class="flex items-start">
                                <div
                                    class="inline-flex items-center justify-center h-6 w-6 rounded-full {{ $isCorrect ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} font-semibold text-sm mr-3">
                                    {{ $opsi }}
                                </div>
                                <div class="flex-1">
                                    @if ($pilihanText)
                                        <div
                                            class="prose max-w-none {{ $isCorrect ? 'text-green-800 font-medium' : '' }}">
                                            {!! nl2br($soal->{"pilihan_{$opsiLower}_teks_html"}) !!}
                                        </div>
                                    @endif

                                    @if ($pilihanGambar)
                                        <div
                                            class="mt-1 {{ $pilihanText ? 'mt-2' : '' }} border {{ $isCorrect ? 'border-green-300' : 'border-gray-200' }} rounded-lg overflow-hidden">
                                            <img src="{{ $soal->{"pilihan_{$opsiLower}_gambar_url"} }}"
                                                alt="Pilihan {{ $opsi }}" class="max-w-full h-auto max-h-40">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($soal->pembahasan_teks || $soal->pembahasan_gambar)
                <div class="mt-5 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Pembahasan:</h4>
                    @if ($soal->pembahasan_teks)
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br($soal->pembahasan_teks_html) !!}
                        </div>
                    @endif

                    @if ($soal->pembahasan_gambar)
                        <div class="mt-3 border border-gray-200 rounded-lg overflow-hidden">
                            <img src="{{ $soal->pembahasan_gambar_url }}" alt="Gambar Pembahasan"
                                class="max-w-full h-auto">
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
