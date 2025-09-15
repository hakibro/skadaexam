<!DOCTYPE html>
<html>

<head>
    <title>Test Form Submission</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/image-clipboard.js') }}"></script>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .border-dashed {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
        }

        .border-dashed.image-drop-zone {
            background: #f9f9f9;
        }

        .hidden {
            display: none;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 150px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background: #007cba;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #005a8b;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Test Form - Soal dengan Gambar</h1>

        <form action="{{ route('naskah.soal.store') }}" method="POST" enctype="multipart/form-data" id="test-form">
            @csrf

            <div class="form-group">
                <label>Bank Soal</label>
                <select name="bank_soal_id" required>
                    @foreach (\App\Models\BankSoal::all() as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->nama ?: "Bank Soal #{$bank->id}" }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Nomor Soal</label>
                <input type="number" name="nomor_soal" value="100" required>
            </div>

            <div class="form-group">
                <label>Tipe Soal</label>
                <select name="tipe_soal" required>
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tipe Pertanyaan</label>
                <select name="tipe_pertanyaan" id="tipe_pertanyaan" required>
                    <option value="teks">Teks Saja</option>
                    <option value="gambar">Gambar Saja</option>
                    <option value="teks_gambar">Teks + Gambar</option>
                </select>
            </div>

            <div class="form-group" id="pertanyaan-teks">
                <label>Pertanyaan</label>
                <textarea name="pertanyaan" rows="3">Ini adalah pertanyaan test dengan gambar</textarea>
            </div>

            <div class="form-group" id="pertanyaan-gambar" style="display: none;">
                <label>Gambar Pertanyaan</label>
                <div class="border-dashed image-drop-zone">
                    <input type="file" name="gambar_pertanyaan" accept="image/*" style="display: none;"
                        id="gambar_pertanyaan">
                    <label for="gambar_pertanyaan" style="cursor: pointer;">
                        Click to upload or paste image from clipboard
                    </label>
                    <div class="image-preview hidden">
                        <img id="preview-pertanyaan" src="" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Pilihan A -->
            <div class="form-group">
                <label>Tipe Pilihan A</label>
                <select name="pilihan_a_tipe" id="pilihan_a_tipe" required>
                    <option value="teks">Teks</option>
                    <option value="gambar">Gambar</option>
                </select>
            </div>

            <div class="form-group" id="pilihan-a-teks">
                <label>Pilihan A (Teks)</label>
                <textarea name="pilihan_a_teks" rows="2">Pilihan A dengan teks</textarea>
            </div>

            <div class="form-group" id="pilihan-a-gambar" style="display: none;">
                <label>Pilihan A (Gambar)</label>
                <div class="border-dashed image-drop-zone">
                    <input type="file" name="pilihan_a_gambar" accept="image/*" style="display: none;"
                        id="file_pilihan_a">
                    <label for="file_pilihan_a" style="cursor: pointer;">
                        Click to upload or paste image
                    </label>
                    <div class="image-preview hidden">
                        <img id="preview-pilihan-a" src="" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Pilihan B, C, D with simple text -->
            <div class="form-group">
                <input type="hidden" name="pilihan_b_tipe" value="teks">
                <label>Pilihan B</label>
                <textarea name="pilihan_b_teks" rows="2">Pilihan B</textarea>
            </div>

            <div class="form-group">
                <input type="hidden" name="pilihan_c_tipe" value="teks">
                <label>Pilihan C</label>
                <textarea name="pilihan_c_teks" rows="2">Pilihan C</textarea>
            </div>

            <div class="form-group">
                <input type="hidden" name="pilihan_d_tipe" value="teks">
                <label>Pilihan D</label>
                <textarea name="pilihan_d_teks" rows="2">Pilihan D</textarea>
            </div>

            <div class="form-group">
                <label>Kunci Jawaban</label>
                <select name="kunci_jawaban" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>

            <div class="form-group">
                <input type="hidden" name="pembahasan_tipe" value="teks">
                <label>Pembahasan</label>
                <textarea name="pembahasan_teks" rows="3">Ini adalah pembahasan untuk soal test</textarea>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" value="test">
            </div>

            <button type="submit">Simpan Soal Test</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize clipboard handler
            const clipboardHandler = new ImageClipboardHandler({
                debug: true,
                maxSize: 5 * 1024 * 1024,
                validTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']
            });

            // Handle tipe pertanyaan change
            document.getElementById('tipe_pertanyaan').addEventListener('change', function() {
                const value = this.value;
                const teksDiv = document.getElementById('pertanyaan-teks');
                const gambarDiv = document.getElementById('pertanyaan-gambar');

                if (value === 'teks') {
                    teksDiv.style.display = 'block';
                    gambarDiv.style.display = 'none';
                } else if (value === 'gambar') {
                    teksDiv.style.display = 'none';
                    gambarDiv.style.display = 'block';
                } else { // teks_gambar
                    teksDiv.style.display = 'block';
                    gambarDiv.style.display = 'block';
                }
            });

            // Handle pilihan A tipe change
            document.getElementById('pilihan_a_tipe').addEventListener('change', function() {
                const value = this.value;
                const teksDiv = document.getElementById('pilihan-a-teks');
                const gambarDiv = document.getElementById('pilihan-a-gambar');

                if (value === 'teks') {
                    teksDiv.style.display = 'block';
                    gambarDiv.style.display = 'none';
                } else {
                    teksDiv.style.display = 'none';
                    gambarDiv.style.display = 'block';
                }
            });

            // Setup image preview for pertanyaan
            setupImagePreview('gambar_pertanyaan', 'preview-pertanyaan');
            setupImagePreview('file_pilihan_a', 'preview-pilihan-a');

            function setupImagePreview(inputId, previewId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const container = preview.closest('.image-preview');

                if (!input || !preview) return;

                input.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            container.classList.remove('hidden');
                        };
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        container.classList.add('hidden');
                    }
                });
            }

            // Add form submission logging
            document.getElementById('test-form').addEventListener('submit', function(e) {
                console.log('Form submitting with data:');
                const formData = new FormData(this);
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
            });
        });
    </script>
</body>

</html>
