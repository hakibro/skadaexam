<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\Text;
use Illuminate\Support\Facades\Storage;
use App\Models\Soal;

class BankSoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BankSoal::with('mapel'); // Tambahkan relasi mapel

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('judul', 'like', "%$search%")
                ->orWhere('deskripsi', 'like', "%$search%");
        }

        // Apply mapel filter
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Apply tingkat filter
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply jenis_soal filter
        if ($request->filled('jenis_soal')) {
            $query->where('jenis_soal', $request->jenis_soal);
        }

        $perPage = $request->get('per_page', 10);
        $bankSoals = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $mapels = Mapel::active()->get(); // Ambil semua mata pelajaran aktif

        return view('features.naskah.banksoal.index', compact('bankSoals', 'mapels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mapels = Mapel::active()->get(); // Ambil semua mata pelajaran aktif
        return view('features.naskah.banksoal.create', compact('mapels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tingkat' => 'required|in:X,XI,XII',
            'status' => 'required|in:aktif,draft,arsip',
            'mapel_id' => 'required|exists:mapel,id',
            'jenis_soal' => 'nullable|in:uts,uas,ulangan,latihan',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode bank soal unik
            $kodeBank = 'BS' . date('Ym') . rand(1000, 9999);

            // Pastikan kode unik
            while (BankSoal::where('kode_bank', $kodeBank)->exists()) {
                $kodeBank = 'BS' . date('Ym') . rand(1000, 9999);
            }

            // Simpan data bank soal
            $bankSoal = BankSoal::create([
                'kode_bank' => $kodeBank,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tingkat' => $request->tingkat,
                'status' => $request->status,
                'jenis_soal' => $request->jenis_soal ?? 'ulangan',
                'total_soal' => 0,
                'created_by' => Auth::id(),
                'mapel_id' => $request->mapel_id,
                'pengaturan' => [
                    'created_at' => now()->toDateTimeString(),
                    'creator_name' => Auth::user()->name ?? 'System',
                ],
            ]);

            DB::commit();

            // Log untuk debugging
            Log::info('Bank Soal created successfully', [
                'id' => $bankSoal->id,
                'title' => $bankSoal->judul,
                'redirect_url' => route('naskah.banksoal.show', $bankSoal->id)
            ]);

            // Redirect ke halaman detail dengan pesan sukses
            return redirect()->route('naskah.banksoal.show', $bankSoal->id)
                ->with('success', 'Bank Soal berhasil dibuat! Kode Bank: ' . $bankSoal->kode_bank);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bank soal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BankSoal $banksoal)
    {
        $banksoal->load('creator', 'mapel'); // Tambahkan load mapel
        $soals = $banksoal->soals()->orderBy('nomor_soal')->paginate(10);

        return view('features.naskah.banksoal.show', compact('banksoal', 'soals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankSoal $banksoal)
    {
        $mapels = Mapel::active()->get(); // Ambil semua mata pelajaran aktif
        $soals = $banksoal->soals()->orderBy('nomor_soal', 'asc')->paginate(10); // Tambahkan daftar soal
        return view('features.naskah.banksoal.edit', compact('banksoal', 'mapels', 'soals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankSoal $banksoal)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tingkat' => 'required|in:X,XI,XII',
            'status' => 'required|in:aktif,draft,arsip',
            'mapel_id' => 'required|exists:mapel,id',
            'jenis_soal' => 'nullable|in:uts,uas,ulangan,latihan',
            'docx_file' => 'nullable|file|mimes:docx|max:10240', // 10MB max
        ]);

        try {
            // Update basic info
            $banksoal->update([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tingkat' => $request->tingkat,
                'status' => $request->status,
                'mapel_id' => $request->mapel_id,
                'jenis_soal' => $request->jenis_soal ?? $banksoal->jenis_soal,
            ]);

            // Process DOCX file if uploaded
            if ($request->hasFile('docx_file')) {
                $importResult = $this->processDocxFile($request->file('docx_file'), $banksoal);

                // Update source file and import log in pengaturan
                $filename = time() . '_' . $request->file('docx_file')->getClientOriginalName();
                $path = $request->file('docx_file')->storeAs('bank-soal/sources', $filename, 'public');

                // Update pengaturan
                $pengaturan = $banksoal->pengaturan ?? [];
                $pengaturan['source_file'] = $filename;
                $pengaturan['import_log'] = $importResult;
                $pengaturan['last_updated'] = now()->toDateTimeString();

                $banksoal->update([
                    'pengaturan' => $pengaturan,
                ]);

                // Update total soal count
                $banksoal->updateTotalSoal();

                return redirect()->route('naskah.banksoal.show', $banksoal->id)
                    ->with('success', 'Bank Soal berhasil diperbarui dengan ' . $importResult['imported'] . ' soal baru!');
            }

            return redirect()->route('naskah.banksoal.show', $banksoal->id)
                ->with('success', 'Bank Soal berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating bank soal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankSoal $banksoal, Request $request)
    {
        try {
            // Check if bank soal has soals and force delete not requested
            if ($banksoal->soals()->count() > 0 && !$request->has('force_delete')) {
                // Store the ID in session for force delete confirmation
                session(['pending_delete_banksoal' => $banksoal->id]);

                return redirect()
                    ->back()
                    ->with('error_with_action', [
                        'message' => 'Bank Soal tidak dapat dihapus karena masih memiliki ' . $banksoal->soals()->count() . ' soal!',
                        'bank_soal_id' => $banksoal->id,
                        'bank_soal_name' => $banksoal->judul,
                        'soal_count' => $banksoal->soals()->count()
                    ]);
            }

            // Force delete with all related soals
            if ($request->has('force_delete')) {
                DB::beginTransaction();

                // Count soals before deletion
                $soalCount = $banksoal->soals()->count();

                // Delete all related soal images first
                $soals = $banksoal->soals;
                foreach ($soals as $soal) {
                    $soal->deleteImages();
                }

                // Delete all related soals
                $banksoal->soals()->delete();

                // Delete the bank soal itself
                $banksoal->delete();

                DB::commit();

                return redirect()
                    ->route('naskah.banksoal.index')
                    ->with('success', 'Bank Soal berhasil dihapus beserta ' . $soalCount . ' soal terkait!');
            }

            // Normal delete (no soals)
            $banksoal->delete();

            return redirect()
                ->route('naskah.banksoal.index')
                ->with('success', 'Bank Soal berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting bank soal', [
                'bank_soal_id' => $banksoal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus Bank Soal: ' . $e->getMessage());
        }
    }

    /**
     * Process the DOCX file and extract questions
     */
    private function processDocxFile($file, BankSoal $banksoal)
    {
        try {
            // Log file details before processing
            Log::info('Starting DOCX processing', [
                'file_path' => $file->getPathname(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            // Verify file exists and is readable
            if (!file_exists($file->getPathname())) {
                throw new \Exception("DOCX file not found at path: " . $file->getPathname());
            }

            if (!is_readable($file->getPathname())) {
                throw new \Exception("DOCX file is not readable: " . $file->getPathname());
            }

            // Copy file to a temporary location that's definitely accessible
            $tempFile = storage_path('app/temp_' . time() . '_' . $file->getClientOriginalName());
            $copied = copy($file->getPathname(), $tempFile);

            if (!$copied) {
                Log::error('Failed to copy DOCX to temporary location', [
                    'source' => $file->getPathname(),
                    'destination' => $tempFile
                ]);
                throw new \Exception('Gagal menyalin file untuk diproses. Harap coba lagi.');
            }

            // Load the DOCX file with PhpWord
            try {
                $phpWord = IOFactory::load($tempFile);
                Log::info('PhpWord successfully loaded DOCX file from temp location', [
                    'temp_file' => $tempFile
                ]);

                // Clean up temp file after successful loading
                @unlink($tempFile);
            } catch (\Exception $e) {
                Log::error('Failed to load DOCX with PhpWord', [
                    'error' => $e->getMessage(),
                    'file_path' => $tempFile,
                    'original_path' => $file->getPathname()
                ]);

                // Clean up temp file
                @unlink($tempFile);

                throw new \Exception("Gagal membaca file DOCX: " . $e->getMessage());
            }

            $log = [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
                'timestamp' => now()->toDateTimeString(),
            ];

            // Array for current question being processed
            $currentQuestion = null;
            $questionNumber = 0;
            $sectionCount = count($phpWord->getSections());

            Log::info("DOCX file structure", [
                'section_count' => $sectionCount,
            ]);

            // Process each section in the document
            foreach ($phpWord->getSections() as $sectionIndex => $section) {
                $elementCount = count($section->getElements());
                Log::info("Processing section", [
                    'section_index' => $sectionIndex + 1,
                    'element_count' => $elementCount
                ]);

                foreach ($section->getElements() as $elementIndex => $element) {
                    // Log element type for debugging
                    $elementType = get_class($element);

                    // Process text elements
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        $text = $this->extractTextFromTextRun($element);

                        // For debugging, log significant text elements
                        if (strlen(trim($text)) > 0) {
                            Log::debug("Found text element", [
                                'text' => substr($text, 0, 50) . (strlen($text) > 50 ? '...' : ''),
                                'length' => strlen($text)
                            ]);
                        }

                        // Check if this is a new question
                        if (preg_match('/^(\d+)\.\s+(.+)$/i', $text, $matches)) {
                            // Save previous question if exists
                            if ($currentQuestion) {
                                $this->saveQuestion($currentQuestion, $banksoal);
                                $log['imported']++;

                                Log::info("Saved question", [
                                    'number' => $currentQuestion['nomor_soal'],
                                    'has_answer' => !empty($currentQuestion['kunci_jawaban']),
                                    'question_length' => strlen($currentQuestion['pertanyaan'])
                                ]);
                            }

                            // Start new question
                            $questionNumber = (int)$matches[1];
                            $currentQuestion = [
                                'bank_soal_id' => $banksoal->id,
                                'nomor_soal' => $questionNumber,
                                'pertanyaan' => $matches[2],
                                'tipe_pertanyaan' => 'teks', // default
                                'tipe_soal' => 'pilihan_ganda',
                                'gambar_pertanyaan' => null,
                                'pilihan_a_teks' => null,
                                'pilihan_a_gambar' => null,
                                'pilihan_a_tipe' => 'teks',
                                'pilihan_b_teks' => null,
                                'pilihan_b_gambar' => null,
                                'pilihan_b_tipe' => 'teks',
                                'pilihan_c_teks' => null,
                                'pilihan_c_gambar' => null,
                                'pilihan_c_tipe' => 'teks',
                                'pilihan_d_teks' => null,
                                'pilihan_d_gambar' => null,
                                'pilihan_d_tipe' => 'teks',
                                'pilihan_e_teks' => null,
                                'pilihan_e_gambar' => null,
                                'pilihan_e_tipe' => 'teks',
                                'kunci_jawaban' => null,
                                'pembahasan_teks' => null,
                                'pembahasan_gambar' => null,
                                'pembahasan_tipe' => 'teks',
                                'bobot' => 1.00
                            ];

                            Log::info("Found new question", [
                                'number' => $questionNumber,
                                'text_preview' => substr($matches[2], 0, 50) . (strlen($matches[2]) > 50 ? '...' : '')
                            ]);
                        }
                        // Check if this is an answer option (termasuk pilihan hanya dengan huruf atau yang dengan [*])
                        elseif (preg_match('/^([A-E])\.(?:\s+(.+?))?(\s*\[\*\])?$/i', $text, $matches)) {
                            if ($currentQuestion) {
                                $answerKey = strtolower($matches[1]);
                                // Jika ada teks jawaban (group 2), gunakan itu. Jika tidak, gunakan string kosong.
                                $answerText = isset($matches[2]) ? $matches[2] : '';
                                $isCorrect = !empty($matches[3]);

                                // Simpan teks jawaban meski kosong
                                $currentQuestion['pilihan_' . $answerKey . '_teks'] = $answerText;

                                // Tandai pilihan kosong sebagai pilihan untuk gambar
                                if (empty(trim($answerText))) {
                                    Log::info("Empty answer option detected - likely an image placeholder", [
                                        'question' => $questionNumber,
                                        'option' => strtoupper($answerKey)
                                    ]);
                                }

                                // Jika pilihan ini adalah jawaban yang benar
                                if ($isCorrect) {
                                    $currentQuestion['kunci_jawaban'] = strtoupper($answerKey);
                                    Log::info("Found correct answer", [
                                        'question' => $questionNumber,
                                        'option' => strtoupper($answerKey)
                                    ]);
                                }

                                Log::info("Found answer option", [
                                    'question' => $questionNumber,
                                    'option' => strtoupper($answerKey),
                                    'text' => $answerText,
                                    'is_correct' => $isCorrect ? 'Yes' : 'No',
                                    'is_empty' => empty(trim($answerText)) ? 'Yes' : 'No'
                                ]);
                            } else {
                                Log::warning("Found answer option but no active question", [
                                    'text' => $text
                                ]);
                            }
                        }
                        // If it's not a new question or answer, check for pembahasan (explanation)
                        elseif ($currentQuestion && preg_match('/^Pembahasan:\s+(.+)$/i', $text, $matches)) {
                            $currentQuestion['pembahasan_teks'] = $matches[1];
                            Log::info("Found explanation for question", [
                                'question' => $questionNumber
                            ]);
                        }
                        // If it's not a new question or answer or explicit explanation, append to current question text
                        elseif ($currentQuestion && trim($text) !== '') {
                            // Check if this is a continuation of an explanation
                            if (!empty($currentQuestion['pembahasan_teks'])) {
                                $currentQuestion['pembahasan_teks'] .= "\n" . $text;
                            } else {
                                $currentQuestion['pertanyaan'] .= "\n" . $text;
                            }
                        }
                    }
                    // Process TextRun elements, but skip image extraction
                    elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach ($element->getElements() as $inline) {
                            if ($inline instanceof \PhpOffice\PhpWord\Element\Image) {
                                // Simply log that we found an image but are skipping it
                                Log::info("Image found in document but skipping extraction per user request", [
                                    'question' => $questionNumber ?? 'No active question'
                                ]);

                                // Add a note to indicate images were found but skipped
                                if ($currentQuestion) {
                                    // Check if this might be an answer option with image
                                    if (!empty($currentQuestion['pilihan_a_teks']) && $currentQuestion['pilihan_a_teks'] === '') {
                                        $currentQuestion['pilihan_a_teks'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        Log::info("Marked option A for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    } else if (!empty($currentQuestion['pilihan_b_teks']) && $currentQuestion['pilihan_b_teks'] === '') {
                                        $currentQuestion['pilihan_b_teks'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        Log::info("Marked option B for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    } else if (!empty($currentQuestion['pilihan_c_teks']) && $currentQuestion['pilihan_c_teks'] === '') {
                                        $currentQuestion['pilihan_c_teks'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        Log::info("Marked option C for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    } else if (!empty($currentQuestion['pilihan_d_teks']) && $currentQuestion['pilihan_d_teks'] === '') {
                                        $currentQuestion['pilihan_d_teks'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        Log::info("Marked option D for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    } else if (!empty($currentQuestion['pilihan_e_teks']) && $currentQuestion['pilihan_e_teks'] === '') {
                                        $currentQuestion['pilihan_e_teks'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        Log::info("Marked option E for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    } else {
                                        // If no specific context, assume it's for the question itself
                                        if (empty($currentQuestion['pertanyaan']) || $currentQuestion['pertanyaan'] === '') {
                                            $currentQuestion['pertanyaan'] = '[Perlu Tambahkan Gambar Secara Manual]';
                                        } else {
                                            $currentQuestion['pertanyaan'] .= "\n[Perlu Tambahkan Gambar Secara Manual]";
                                        }
                                        Log::info("Marked question for manual image upload", [
                                            'question' => $questionNumber
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Don't forget to save the last question
            if ($currentQuestion) {
                try {
                    $this->saveQuestion($currentQuestion, $banksoal);
                    $log['imported']++;

                    Log::info("Saved final question", [
                        'number' => $currentQuestion['nomor_soal'],
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to save final question", [
                        'question' => $questionNumber,
                        'error' => $e->getMessage()
                    ]);
                    $log['errors'][] = "Gagal menyimpan soal #$questionNumber: " . $e->getMessage();
                }
            }

            Log::info("DOCX processing completed", [
                'imported' => $log['imported'],
                'skipped' => $log['skipped'],
                'error_count' => count($log['errors'])
            ]);

            return $log;
        } catch (\Exception $e) {
            Log::error("DOCX processing failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
                'timestamp' => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * Extract text from a TextRun element
     */
    private function extractTextFromTextRun($textRun)
    {
        $text = '';
        foreach ($textRun->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $text .= $element->getText();
            }
        }
        return $text;
    }

    // Image saving functionality removed - images will be added manually through the edit interface

    /**
     * Save a question to database
     */
    private function saveQuestion($data, $banksoal)
    {
        try {
            // Validate required fields
            if (empty($data['nomor_soal'])) {
                throw new \Exception("Nomor soal tidak boleh kosong");
            }

            if (empty($data['pertanyaan'])) {
                throw new \Exception("Pertanyaan tidak boleh kosong");
            }

            // Pastikan field gambar_pembahasan ada, agar kompatibel dengan model
            if (!isset($data['gambar_pembahasan'])) {
                $data['gambar_pembahasan'] = null;
            }

            // Handle empty answer options which would need manual image upload
            foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                $teksField = 'pilihan_' . $option . '_teks';
                $tipeField = 'pilihan_' . $option . '_tipe';

                // If there's an empty answer option, it's likely a placeholder for an image
                // that will need to be added manually later
                if (isset($data[$teksField]) && $data[$teksField] === '') {
                    // Mark these empty options with a placeholder text
                    $data[$teksField] = '[Perlu Tambahkan Gambar Secara Manual]';
                    Log::info("Empty answer option detected - manual image upload required", [
                        'question' => $data['nomor_soal'],
                        'option' => strtoupper($option)
                    ]);
                }
            }

            // Pastikan kunci jawaban tidak null
            if ($data['tipe_soal'] == 'pilihan_ganda' && empty($data['kunci_jawaban'])) {
                Log::warning("Soal pilihan ganda tanpa kunci jawaban. Mencoba mendeteksi dari tanda [*]", [
                    'bank_soal_id' => $banksoal->id,
                    'nomor_soal' => $data['nomor_soal']
                ]);

                // Coba deteksi kunci jawaban dari tanda [*] di teks pilihan
                $found = false;
                foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                    $pilihan = 'pilihan_' . $option . '_teks';
                    if (isset($data[$pilihan]) && strpos($data[$pilihan], '[*]') !== false) {
                        $data['kunci_jawaban'] = strtoupper($option);
                        $data[$pilihan] = str_replace('[*]', '', $data[$pilihan]);
                        $found = true;
                        Log::info("Kunci jawaban ditemukan pada opsi $option dari tanda [*]");
                        break;
                    }
                }

                // No need to check for image-based answers since images are now handled manually

                // Jika masih tidak ditemukan, tetapkan nilai default 'A'
                if (!$found) {
                    $data['kunci_jawaban'] = 'A';
                    Log::warning("Tidak ada kunci jawaban ditemukan, menggunakan default 'A'", [
                        'nomor_soal' => $data['nomor_soal']
                    ]);
                }
            }

            // Check if question already exists
            $existing = Soal::where('bank_soal_id', $banksoal->id)
                ->where('nomor_soal', $data['nomor_soal'])
                ->first();

            if ($existing) {
                // Update existing question
                $existing->update($data);
                Log::info("Updated existing question", [
                    'soal_id' => $existing->id,
                    'nomor_soal' => $data['nomor_soal']
                ]);
                return $existing;
            } else {
                // Create new question
                $soal = Soal::create($data);
                Log::info("Created new question", [
                    'soal_id' => $soal->id,
                    'nomor_soal' => $data['nomor_soal']
                ]);
                return $soal;
            }
        } catch (\Exception $e) {
            Log::error("Failed to save question", [
                'bank_soal_id' => $banksoal->id,
                'nomor_soal' => $data['nomor_soal'] ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to be handled by the caller
        }
    }
}
