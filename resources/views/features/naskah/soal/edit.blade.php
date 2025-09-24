<!-- filepath: resources\views\features\naskah\soal\edit.blade.php -->

@extends('layouts.admin')

@section('title', 'Edit Soal')
@section('page-title', 'Edit Soal')

@push('scripts')
    <script src="{{ asset('js/image-clipboard.js') }}"></script>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('naskah.soal.update', $soal) }}" method="POST" enctype="multipart/form-data" id="soal-form">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Header Info -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar Soal</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bank Soal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Soal <span class="text-red-500">*</span>
                            </label>
                            <select name="bank_soal_id" id="bank_soal_id" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Bank Soal</option>
                                @foreach ($bankSoals as $bank)
                                    <option value="{{ $bank->id }}"
                                        {{ old('bank_soal_id', $soal->bank_soal_id) == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->judul }} ({{ $bank->total_soal }} soal)
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_soal_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nomor Soal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Soal <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nomor_soal" value="{{ old('nomor_soal', $soal->nomor_soal) }}"
                                min="1" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('nomor_soal')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipe Soal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Soal <span class="text-red-500">*</span>
                            </label>
                            <select name="tipe_soal" id="tipe_soal" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pilihan_ganda"
                                    {{ old('tipe_soal', $soal->tipe_soal) == 'pilihan_ganda' ? 'selected' : '' }}>
                                    Pilihan Ganda
                                </option>
                                <option value="essay"
                                    {{ old('tipe_soal', $soal->tipe_soal) == 'essay' ? 'selected' : '' }}>
                                    Essay
                                </option>
                            </select>
                            @error('tipe_soal')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori (Opsional)</label>
                            <input type="text" name="kategori" value="{{ old('kategori', $soal->kategori) }}"
                                placeholder="C1-Pengetahuan, C2-Pemahaman, dll"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('kategori')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pertanyaan -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pertanyaan</h3>

                    <!-- Tipe Pertanyaan -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Media Pertanyaan <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            <label
                                class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="tipe_pertanyaan" value="teks"
                                    {{ old('tipe_pertanyaan', $soal->tipe_pertanyaan) == 'teks' ? 'checked' : '' }}
                                    class="mr-3 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Teks Saja</div>
                                    <div class="text-sm text-gray-500">Pertanyaan dalam bentuk teks</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="tipe_pertanyaan" value="gambar"
                                    {{ old('tipe_pertanyaan', $soal->tipe_pertanyaan) == 'gambar' ? 'checked' : '' }}
                                    class="mr-3 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Gambar Saja</div>
                                    <div class="text-sm text-gray-500">Pertanyaan dalam bentuk gambar</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="tipe_pertanyaan" value="teks_gambar"
                                    {{ old('tipe_pertanyaan', $soal->tipe_pertanyaan) == 'teks_gambar' ? 'checked' : '' }}
                                    class="mr-3 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Teks + Gambar</div>
                                    <div class="text-sm text-gray-500">Kombinasi teks dan gambar</div>
                                </div>
                            </label>
                        </div>
                        @error('tipe_pertanyaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pertanyaan Teks -->
                    <div id="pertanyaan-teks-section"
                        class="{{ in_array($soal->tipe_pertanyaan, ['teks', 'teks_gambar']) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pertanyaan <span class="text-red-500" id="pertanyaan-required">*</span>
                        </label>
                        <textarea name="pertanyaan" rows="4" placeholder="Masukkan pertanyaan di sini..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                        @error('pertanyaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pertanyaan Gambar -->
                    <div id="pertanyaan-gambar-section"
                        class="mt-6 {{ in_array($soal->tipe_pertanyaan, ['gambar', 'teks_gambar']) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Pertanyaan <span class="text-red-500" id="gambar-pertanyaan-required">*</span>
                        </label>
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" name="gambar_pertanyaan" id="gambar-pertanyaan" accept="image/*"
                                class="hidden">
                            <label for="gambar-pertanyaan" class="cursor-pointer">
                                <i class="fa-solid fa-cloud-upload-alt text-gray-400 text-4xl mb-4"></i>
                                <div class="text-gray-600">
                                    <span class="font-medium text-blue-600 hover:text-blue-500">Klik untuk upload</span>
                                    atau drag & drop gambar
                                </div>
                                <div class="text-sm text-gray-400 mt-2">PNG, JPG, GIF hingga 5MB</div>
                            </label>
                            <div id="gambar-pertanyaan-preview"
                                class="mt-4 {{ $soal->gambar_pertanyaan ? '' : 'hidden' }}">
                                <img id="gambar-pertanyaan-img"
                                    src="{{ $soal->gambar_pertanyaan ? asset('storage/soal/pertanyaan/' . $soal->gambar_pertanyaan) : '' }}"
                                    alt="Preview" class="max-w-full max-h-64 mx-auto rounded-lg shadow">
                                <button type="button" onclick="removeGambarPertanyaan()"
                                    class="mt-2 text-red-600 hover:text-red-800 text-sm">
                                    <i class="fa-solid fa-trash mr-1"></i>Hapus Gambar
                                </button>
                            </div>
                        </div>
                        @error('gambar_pertanyaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pilihan Jawaban (untuk pilihan ganda) -->
                <div id="pilihan-jawaban-section"
                    class="bg-white shadow rounded-lg p-6 {{ $soal->tipe_soal == 'pilihan_ganda' ? '' : 'hidden' }}">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pilihan Jawaban</h3>

                    @foreach (['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'] as $label => $value)
                        <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900">Pilihan {{ $label }}</h4>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="pilihan_{{ $value }}_tipe" value="teks"
                                            {{ old("pilihan_{$value}_tipe", $soal->{"pilihan_{$value}_tipe"} ?? 'teks') == 'teks' ? 'checked' : '' }}
                                            class="mr-2 text-blue-600">
                                        <span class="text-sm">Teks</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="pilihan_{{ $value }}_tipe" value="gambar"
                                            {{ old("pilihan_{$value}_tipe", $soal->{"pilihan_{$value}_tipe"} ?? '') == 'gambar' ? 'checked' : '' }}
                                            class="mr-2 text-blue-600">
                                        <span class="text-sm">Gambar</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Pilihan Teks -->
                            <div
                                class="pilihan-teks-section-{{ $value }} {{ ($soal->{"pilihan_{$value}_tipe"} ?? 'teks') != 'gambar' ? '' : 'hidden' }}">
                                <textarea name="pilihan_{{ $value }}_teks" rows="2"
                                    placeholder="Masukkan pilihan {{ $label }}..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old("pilihan_{$value}_teks", $soal->{"pilihan_{$value}_teks"}) }}</textarea>
                                @error("pilihan_{$value}_teks")
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pilihan Gambar -->
                            <div
                                class="pilihan-gambar-section-{{ $value }} {{ ($soal->{"pilihan_{$value}_tipe"} ?? '') == 'gambar' ? '' : 'hidden' }}">
                                <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center">
                                    <input type="file" name="pilihan_{{ $value }}_gambar"
                                        id="pilihan-{{ $value }}-gambar" accept="image/*" class="hidden">
                                    <label for="pilihan-{{ $value }}-gambar" class="cursor-pointer">
                                        <i class="fa-solid fa-image text-gray-400 text-2xl mb-2"></i>
                                        <div class="text-sm text-gray-600">Upload gambar pilihan {{ $label }}</div>
                                        <div class="text-xs text-gray-400 mt-1">PNG, JPG hingga 2MB</div>
                                    </label>
                                    <div id="pilihan-{{ $value }}-preview"
                                        class="mt-2 {{ $soal->{"pilihan_{$value}_gambar"} ? '' : 'hidden' }}">
                                        <img id="pilihan-{{ $value }}-img"
                                            src="{{ $soal->{"pilihan_{$value}_gambar"} ? asset('storage/soal/pilihan/' . $soal->{"pilihan_{$value}_gambar"}) : '' }}"
                                            alt="Preview" class="max-w-full max-h-32 mx-auto rounded shadow">
                                    </div>
                                </div>
                                @error("pilihan_{$value}_gambar")
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <!-- Kunci Jawaban -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kunci Jawaban <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $kunci)
                                <label class="flex items-center">
                                    <input type="radio" name="kunci_jawaban" value="{{ $kunci }}"
                                        {{ old('kunci_jawaban', $soal->kunci_jawaban) == $kunci ? 'checked' : '' }}
                                        class="mr-2 text-blue-600">
                                    <span class="font-medium">{{ $kunci }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kunci_jawaban')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pembahasan -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pembahasan</h3>

                    <!-- Tipe Pembahasan -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Media Pembahasan</label>
                        <div class="grid grid-cols-3 gap-4">
                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="pembahasan_tipe" value="teks"
                                    {{ old('pembahasan_tipe', $soal->pembahasan_tipe ?? 'teks') == 'teks' ? 'checked' : '' }}
                                    class="mr-2 text-blue-600">
                                <span class="text-sm">Teks Saja</span>
                            </label>
                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="pembahasan_tipe" value="gambar"
                                    {{ old('pembahasan_tipe', $soal->pembahasan_tipe ?? '') == 'gambar' ? 'checked' : '' }}
                                    class="mr-2 text-blue-600">
                                <span class="text-sm">Gambar Saja</span>
                            </label>
                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="pembahasan_tipe" value="teks_gambar"
                                    {{ old('pembahasan_tipe', $soal->pembahasan_tipe ?? '') == 'teks_gambar' ? 'checked' : '' }}
                                    class="mr-2 text-blue-600">
                                <span class="text-sm">Teks + Gambar</span>
                            </label>
                        </div>
                    </div>

                    <!-- Pembahasan Teks -->
                    <div id="pembahasan-teks-section"
                        class="{{ in_array($soal->pembahasan_tipe ?? 'teks', ['teks', 'teks_gambar']) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pembahasan <span class="text-red-500" id="pembahasan-required">*</span>
                        </label>
                        <textarea name="pembahasan_teks" rows="4" placeholder="Masukkan pembahasan soal di sini..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('pembahasan_teks', $soal->pembahasan_teks) }}</textarea>
                        @error('pembahasan_teks')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pembahasan Gambar -->
                    <div id="pembahasan-gambar-section"
                        class="mt-6 {{ in_array($soal->pembahasan_tipe ?? '', ['gambar', 'teks_gambar']) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Pembahasan <span class="text-red-500" id="gambar-pembahasan-required">*</span>
                        </label>
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" name="pembahasan_gambar" id="pembahasan-gambar" accept="image/*"
                                class="hidden">
                            <label for="pembahasan-gambar" class="cursor-pointer">
                                <i class="fa-solid fa-cloud-upload-alt text-gray-400 text-4xl mb-4"></i>
                                <div class="text-gray-600">
                                    <span class="font-medium text-blue-600 hover:text-blue-500">Klik untuk upload</span>
                                    atau drag & drop gambar
                                </div>
                                <div class="text-sm text-gray-400 mt-2">PNG, JPG, GIF hingga 5MB</div>
                            </label>
                            <div id="pembahasan-gambar-preview"
                                class="mt-4 {{ $soal->pembahasan_gambar ? '' : 'hidden' }}">
                                <img id="pembahasan-gambar-img"
                                    src="{{ $soal->pembahasan_gambar ? asset('storage/soal/pembahasan/' . $soal->pembahasan_gambar) : '' }}"
                                    alt="Preview" class="max-w-full max-h-64 mx-auto rounded-lg shadow">
                                <button type="button" onclick="removePembahasanGambar()"
                                    class="mt-2 text-red-600 hover:text-red-800 text-sm">
                                    <i class="fa-solid fa-trash mr-1"></i>Hapus Gambar
                                </button>
                            </div>
                        </div>
                        @error('pembahasan_gambar')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between">
                        <a href="{{ route('naskah.soal.index') }}"
                            class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">
                            <i class="fa-solid fa-arrow-left mr-2"></i>Batal
                        </a>
                        <div class="flex space-x-3">
                            <button type="button" onclick="previewSoal()"
                                class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                                <i class="fa-solid fa-eye mr-2"></i>Preview
                            </button>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- JavaScript -->
    <script src="{{ asset('js/image-clipboard.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize our enhanced image clipboard handler
            const clipboardHandler = new ImageClipboardHandler({
                debug: false,
                maxSize: 5 * 1024 * 1024, // 5MB default for most images
                validTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']
            });

            // Handle tipe pertanyaan changes
            document.querySelectorAll('input[name="tipe_pertanyaan"]').forEach(radio => {
                radio.addEventListener('change', handleTipePertanyaanChange);
            });

            // Handle tipe soal changes  
            document.getElementById('tipe_soal').addEventListener('change', handleTipeSoalChange);

            // Handle pilihan tipe changes
            @foreach (['a', 'b', 'c', 'd', 'e'] as $value)
                document.querySelectorAll('input[name="pilihan_{{ $value }}_tipe"]').forEach(radio => {
                    radio.addEventListener('change', () => handlePilihanTipeChange('{{ $value }}'));
                });
            @endforeach

            // Handle pembahasan tipe changes
            document.querySelectorAll('input[name="pembahasan_tipe"]').forEach(radio => {
                radio.addEventListener('change', handlePembahasanTipeChange);
            });

            // Image preview handlers
            setupImagePreview('gambar-pertanyaan', 'gambar-pertanyaan-preview', 'gambar-pertanyaan-img');
            setupImagePreview('pembahasan-gambar', 'pembahasan-gambar-preview', 'pembahasan-gambar-img');

            @foreach (['a', 'b', 'c', 'd', 'e'] as $value)
                setupImagePreview('pilihan-{{ $value }}-gambar', 'pilihan-{{ $value }}-preview',
                    'pilihan-{{ $value }}-img');
            @endforeach

            // Initialize form state
            handleTipePertanyaanChange();
            handleTipeSoalChange();
            handlePembahasanTipeChange();
            @foreach (['a', 'b', 'c', 'd', 'e'] as $value)
                handlePilihanTipeChange('{{ $value }}');
            @endforeach

            // Add image-drop-zone class to all drop zones for clipboard handler
            document.querySelectorAll('.border-dashed').forEach(dropZone => {
                dropZone.classList.add('image-drop-zone');

                // Add data attributes for file type constraints
                const fileInput = dropZone.querySelector('input[type="file"]');
                if (fileInput) {
                    if (fileInput.id.includes('pilihan')) {
                        fileInput.setAttribute('data-max-size', 2 * 1024 * 1024); // 2MB for options
                    } else {
                        fileInput.setAttribute('data-max-size', 5 * 1024 * 1024); // 5MB for main images
                    }
                }
            });
        });

        function handleTipePertanyaanChange() {
            const tipe = document.querySelector('input[name="tipe_pertanyaan"]:checked').value;
            const teksSection = document.getElementById('pertanyaan-teks-section');
            const gambarSection = document.getElementById('pertanyaan-gambar-section');
            const teksRequired = document.getElementById('pertanyaan-required');
            const gambarRequired = document.getElementById('gambar-pertanyaan-required');

            if (tipe === 'teks') {
                teksSection.classList.remove('hidden');
                gambarSection.classList.add('hidden');
                teksRequired.classList.remove('hidden');
                gambarRequired.classList.add('hidden');
            } else if (tipe === 'gambar') {
                teksSection.classList.add('hidden');
                gambarSection.classList.remove('hidden');
                teksRequired.classList.add('hidden');
                gambarRequired.classList.remove('hidden');
            } else { // teks_gambar
                teksSection.classList.remove('hidden');
                gambarSection.classList.remove('hidden');
                teksRequired.classList.remove('hidden');
                gambarRequired.classList.remove('hidden');
            }
        }

        function handleTipeSoalChange() {
            const tipe = document.getElementById('tipe_soal').value;
            const pilihanSection = document.getElementById('pilihan-jawaban-section');

            if (tipe === 'pilihan_ganda') {
                pilihanSection.classList.remove('hidden');
            } else {
                pilihanSection.classList.add('hidden');
            }
        }

        function handlePilihanTipeChange(pilihan) {
            const tipe = document.querySelector(`input[name="pilihan_${pilihan}_tipe"]:checked`).value;
            const teksSection = document.querySelector(`.pilihan-teks-section-${pilihan}`);
            const gambarSection = document.querySelector(`.pilihan-gambar-section-${pilihan}`);

            if (tipe === 'teks') {
                teksSection.classList.remove('hidden');
                gambarSection.classList.add('hidden');
            } else {
                teksSection.classList.add('hidden');
                gambarSection.classList.remove('hidden');
            }
        }

        function handlePembahasanTipeChange() {
            const tipe = document.querySelector('input[name="pembahasan_tipe"]:checked').value;
            const teksSection = document.getElementById('pembahasan-teks-section');
            const gambarSection = document.getElementById('pembahasan-gambar-section');
            const teksRequired = document.getElementById('pembahasan-required');
            const gambarRequired = document.getElementById('gambar-pembahasan-required');

            if (tipe === 'teks') {
                teksSection.classList.remove('hidden');
                gambarSection.classList.add('hidden');
                teksRequired.classList.remove('hidden');
                gambarRequired.classList.add('hidden');
            } else if (tipe === 'gambar') {
                teksSection.classList.add('hidden');
                gambarSection.classList.remove('hidden');
                teksRequired.classList.add('hidden');
                gambarRequired.classList.remove('hidden');
            } else { // teks_gambar
                teksSection.classList.remove('hidden');
                gambarSection.classList.remove('hidden');
                teksRequired.classList.remove('hidden');
                gambarRequired.classList.remove('hidden');
            }
        }

        function setupImagePreview(inputId, previewId, imgId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const img = document.getElementById(imgId);
            const form = document.getElementById('soal-form');
            const dropZone = input ? input.parentElement : null;

            if (!input || !preview || !img || !dropZone) return;

            // Mark this element as a form group
            const formGroup = dropZone.closest('.bg-white') || dropZone.parentElement;
            if (formGroup) {
                formGroup.classList.add('form-group');
            }

            // Mark the preview container with a class
            preview.classList.add('image-preview');

            // Function to process file for preview
            function processFile(file) {
                if (!file) return false;

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, WebP)');
                    return false;
                }

                // Validate file size (5MB for main images, 2MB for options)
                const maxSize = inputId.includes('pilihan') ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    const maxSizeMB = inputId.includes('pilihan') ? '2MB' : '5MB';
                    alert(`File size must be less than ${maxSizeMB}`);
                    return false;
                }

                // Create a proper file object from the blob/file
                // This is necessary for reliable form submission
                try {
                    // Create a new File if needed to ensure proper file object
                    if (!(file instanceof File)) {
                        file = new File([file], `image-${Date.now()}.${getExtensionForMimeType(file.type)}`, {
                            type: file.type,
                            lastModified: new Date().getTime()
                        });
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);

                    // Create a new DataTransfer to hold our file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;

                    return true;
                } catch (error) {
                    console.error('Error processing file:', error);
                    return false;
                }
            }

            // Helper for getting file extension from MIME type
            function getExtensionForMimeType(mimeType) {
                const mimeToExt = {
                    'image/jpeg': 'jpg',
                    'image/jpg': 'jpg',
                    'image/png': 'png',
                    'image/gif': 'gif',
                    'image/webp': 'webp'
                };

                return mimeToExt[mimeType] || 'png';
            }

            // Handle file input change
            input.addEventListener('change', function(e) {
                const file = this.files[0];
                if (file) {
                    if (!processFile(file)) {
                        this.value = '';
                        preview.classList.add('hidden');
                        img.src = '';
                    }
                } else {
                    preview.classList.add('hidden');
                    img.src = '';
                }
            });

            // Make dropZone focusable to enable paste events
            dropZone.setAttribute('tabindex', '0');

            // Focus/blur effects
            dropZone.addEventListener('focus', function() {
                this.classList.add('border-blue-400');
            });

            dropZone.addEventListener('blur', function() {
                this.classList.remove('border-blue-400');
            });

            // Add paste hint text with keyboard shortcut
            const hintDiv = document.createElement('div');
            hintDiv.className = 'text-xs text-gray-400 mt-1';
            hintDiv.innerHTML = '<i class="fa-solid fa-clipboard mr-1"></i> Bisa juga paste gambar dari clipboard (Ctrl+V)';
            const label = dropZone.querySelector('label');
            if (label) {
                label.appendChild(hintDiv);
            }

            // Add keyboard shortcut for focus
            dropZone.addEventListener('keydown', function(e) {
                // If user presses Delete/Backspace to remove image
                if ((e.key === 'Delete' || e.key === 'Backspace') &&
                    img.src && !preview.classList.contains('hidden')) {

                    if (inputId === 'gambar-pertanyaan') {
                        removeGambarPertanyaan();
                    } else if (inputId === 'pembahasan-gambar') {
                        removePembahasanGambar();
                    } else {
                        // For pilihan gambar, just clear the input
                        input.value = '';
                        preview.classList.add('hidden');
                        img.src = '';
                    }
                    e.preventDefault();
                }
            });

            // Drag and drop functionality
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-blue-400');
            });

            dropZone.addEventListener('dragleave', function() {
                this.classList.remove('border-blue-400');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-blue-400');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    // Use the processFile function to handle the file properly
                    if (processFile(files[0])) {
                        // Trigger change event for any listeners
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                }
            });
        }

        function removeGambarPertanyaan() {
            const input = document.getElementById('gambar-pertanyaan');
            const preview = document.getElementById('gambar-pertanyaan-preview');
            const img = document.getElementById('gambar-pertanyaan-img');

            input.value = '';
            preview.classList.add('hidden');
            img.src = '';
        }

        function removePembahasanGambar() {
            const input = document.getElementById('pembahasan-gambar');
            const preview = document.getElementById('pembahasan-gambar-preview');
            const img = document.getElementById('pembahasan-gambar-img');

            input.value = '';
            preview.classList.add('hidden');
            img.src = '';
        }

        function previewSoal() {
            // Collect form data
            const formData = new FormData(document.getElementById('soal-form'));

            // Build preview content
            let previewContent = '<div class="space-y-6">';

            // Pertanyaan
            const tipePertanyaan = formData.get('tipe_pertanyaan');
            previewContent += '<div class="bg-white p-6 rounded-lg shadow">';
            previewContent += '<h3 class="font-bold text-lg mb-4">Pertanyaan:</h3>';

            if (tipePertanyaan === 'teks' || tipePertanyaan === 'teks_gambar') {
                const pertanyaan = formData.get('pertanyaan');
                if (pertanyaan) {
                    previewContent += `<p class="mb-4">${pertanyaan}</p>`;
                }
            }

            if (tipePertanyaan === 'gambar' || tipePertanyaan === 'teks_gambar') {
                const gambarFile = formData.get('gambar_pertanyaan');
                if (gambarFile && gambarFile.size > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent +=
                            `<img src="${e.target.result}" class="max-w-full h-auto rounded shadow mb-4">`;
                    };
                    reader.readAsDataURL(gambarFile);
                }
            }

            previewContent += '</div>';

            // Pilihan jawaban (jika pilihan ganda)
            const tipeSoal = formData.get('tipe_soal');
            if (tipeSoal === 'pilihan_ganda') {
                previewContent += '<div class="bg-white p-6 rounded-lg shadow">';
                previewContent += '<h3 class="font-bold text-lg mb-4">Pilihan Jawaban:</h3>';

                ['a', 'b', 'c', 'd', 'e'].forEach((pilihan, index) => {
                    const tipePilihan = formData.get(`pilihan_${pilihan}_tipe`);
                    const teksPilihan = formData.get(`pilihan_${pilihan}_teks`);
                    const gambarPilihan = formData.get(`pilihan_${pilihan}_gambar`);

                    if (teksPilihan || (gambarPilihan && gambarPilihan.size > 0)) {
                        const label = String.fromCharCode(65 + index); // A, B, C, D, E
                        previewContent += `<div class="flex items-start mb-3">`;
                        previewContent += `<span class="font-bold mr-3">${label}.</span>`;

                        if (tipePilihan === 'teks' && teksPilihan) {
                            previewContent += `<span>${teksPilihan}</span>`;
                        } else if (tipePilihan === 'gambar' && gambarPilihan && gambarPilihan.size > 0) {
                            // Handle image preview for options
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.querySelector(`#preview-pilihan-${pilihan}-img`).src = e.target.result;
                            };
                            reader.readAsDataURL(gambarPilihan);
                            previewContent +=
                                `<img id="preview-pilihan-${pilihan}-img" src="" class="max-w-48 h-auto rounded shadow">`;
                        }

                        previewContent += `</div>`;
                    }
                });

                // Kunci jawaban
                const kunciJawaban = formData.get('kunci_jawaban');
                if (kunciJawaban) {
                    previewContent += `<div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">`;
                    previewContent += `<strong>Kunci Jawaban: ${kunciJawaban}</strong>`;
                    previewContent += `</div>`;
                }

                previewContent += '</div>';
            }

            // Pembahasan
            const tipePembahasan = formData.get('pembahasan_tipe');
            const teksPembahasan = formData.get('pembahasan_teks');
            const gambarPembahasan = formData.get('pembahasan_gambar');

            if (teksPembahasan || (gambarPembahasan && gambarPembahasan.size > 0)) {
                previewContent += '<div class="bg-white p-6 rounded-lg shadow">';
                previewContent += '<h3 class="font-bold text-lg mb-4">Pembahasan:</h3>';

                if ((tipePembahasan === 'teks' || tipePembahasan === 'teks_gambar') && teksPembahasan) {
                    previewContent += `<p class="mb-4">${teksPembahasan}</p>`;
                }

                if ((tipePembahasan === 'gambar' || tipePembahasan === 'teks_gambar') && gambarPembahasan &&
                    gambarPembahasan.size > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('#preview-pembahasan-img').src = e.target.result;
                    };
                    reader.readAsDataURL(gambarPembahasan);
                    previewContent += `<img id="preview-pembahasan-img" src="" class="max-w-full h-auto rounded shadow">`;
                }

                previewContent += '</div>';
            }

            previewContent += '</div>';

            // Show preview in modal
            showPreviewModal(previewContent);
        }

        function showPreviewModal(content) {
            // Create modal HTML
            const modalHTML = `
                <div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg max-w-4xl max-h-screen overflow-y-auto w-full">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900">Preview Soal</h2>
                                <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600">
                                    <i class="fa-solid fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            ${content}
                        </div>
                        <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                            <button onclick="closePreviewModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                Tutup
                            </button>
                            <button onclick="submitForm()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Soal
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        function closePreviewModal() {
            const modal = document.getElementById('preview-modal');
            if (modal) {
                modal.remove();
            }
        }

        function submitForm() {
            closePreviewModal();
            document.getElementById('soal-form').submit();
        }

        // Form validation before submit
        document.getElementById('soal-form').addEventListener('submit', function(e) {
            // Validate required fields based on selected types
            const tipePertanyaan = document.querySelector('input[name="tipe_pertanyaan"]:checked').value;
            const pertanyaan = document.querySelector('textarea[name="pertanyaan"]').value.trim();
            const gambarPertanyaan = document.querySelector('input[name="gambar_pertanyaan"]').files[0];

            // Check pertanyaan requirements
            if (tipePertanyaan === 'teks' && !pertanyaan) {
                e.preventDefault();
                alert('Pertanyaan teks wajib diisi untuk tipe yang dipilih');
                return false;
            }

            if (tipePertanyaan === 'gambar' && !gambarPertanyaan) {
                e.preventDefault();
                alert('Gambar pertanyaan wajib diupload untuk tipe yang dipilih');
                return false;
            }

            if (tipePertanyaan === 'teks_gambar' && (!pertanyaan || !gambarPertanyaan)) {
                e.preventDefault();
                alert('Pertanyaan teks dan gambar wajib diisi untuk tipe yang dipilih');
                return false;
            }

            // Validate pilihan jawaban for multiple choice
            const tipeSoal = document.getElementById('tipe_soal').value;
            if (tipeSoal === 'pilihan_ganda') {
                const kunciJawaban = document.querySelector('input[name="kunci_jawaban"]:checked');
                if (!kunciJawaban) {
                    e.preventDefault();
                    alert('Kunci jawaban wajib dipilih untuk soal pilihan ganda');
                    return false;
                }

                // Check if at least options A and B are filled
                let validOptions = 0;
                ['a', 'b'].forEach(pilihan => {
                    const tipe = document.querySelector(`input[name="pilihan_${pilihan}_tipe"]:checked`)
                        .value;
                    const teks = document.querySelector(`textarea[name="pilihan_${pilihan}_teks"]`).value
                        .trim();
                    const gambar = document.querySelector(`input[name="pilihan_${pilihan}_gambar"]`).files[
                        0];

                    if ((tipe === 'teks' && teks) || (tipe === 'gambar' && gambar)) {
                        validOptions++;
                    }
                });

                if (validOptions < 2) {
                    e.preventDefault();
                    alert('Minimal pilihan A dan B wajib diisi');
                    return false;
                }
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;

            // If validation passes, allow form to submit
            return true;
        });

        // Auto-save draft functionality (optional)
        let autoSaveTimer;

        function enableAutoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Implement auto-save logic here if needed
                console.log('Auto-saving draft...');
            }, 30000); // Auto-save every 30 seconds
        }

        // Trigger auto-save on form changes
        document.getElementById('soal-form').addEventListener('input', enableAutoSave);
    </script>

    <!-- Custom CSS for enhanced styling -->
    <style>
        .has-\[\:checked\]\:border-blue-500:has(:checked) {
            border-color: #3b82f6;
        }

        .has-\[\:checked\]\:bg-blue-50:has(:checked) {
            background-color: #eff6ff;
        }

        .drag-over {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
        }

        .image-preview img {
            transition: transform 0.2s ease;
        }

        .image-preview img:hover {
            transform: scale(1.05);
        }

        .form-section {
            transition: all 0.3s ease;
        }

        .form-section.hidden {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
        }

        /* Loading button animation */
        .btn-loading {
            position: relative;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Preview modal custom scrollbar */
        #preview-modal .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }

        #preview-modal .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #preview-modal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        #preview-modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Clipboard paste animation */
        @keyframes paste-success-flash {
            0% {
                background-color: #d1fae5;
            }

            100% {
                background-color: transparent;
            }
        }

        .paste-success {
            animation: paste-success-flash 1s ease-out;
        }

        /* Drop zone focus styles */
        .border-dashed:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        /* Clipboard icon animation */
        @keyframes clipboard-pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .fa-clipboard {
            animation: clipboard-pulse 2s infinite ease-in-out;
            display: inline-block;
        }
    </style>
@endsection
