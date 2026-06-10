<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\PaketUjian;
use App\Exports\NaskahComprehensiveTemplateExport;
use App\Imports\NaskahComprehensiveImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\Text;
use Illuminate\Support\Facades\Storage;
use App\Models\Soal;
use App\Services\TahunAjaranService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class BankSoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjians = \App\Models\PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('status', '!=', 'arsip')
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_mulai')
            ->get();
        $defaultPaketId = $paketUjians->firstWhere('status', 'aktif')?->id ?? $paketUjians->first()?->id;
        $showAllPaket = $request->get('paket_ujian_id') === '__all';
        $paketUjianId = null;

        if ($request->has('paket_ujian_id')) {
            $paketUjianId = $showAllPaket ? null : $request->get('paket_ujian_id');
        } else {
            $paketUjianId = $defaultPaketId;
        }

        if ($paketUjianId && !$paketUjians->contains('id', (int) $paketUjianId)) {
            $paketUjianId = $defaultPaketId;
        }

        $query = BankSoal::with('mapel', 'paketUjian')->forTahunAjaran($tahunAjaranId);

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

        // Apply paket ujian filter
        if ($paketUjianId) {
            $query->where('paket_ujian_id', $paketUjianId);
        }

        // Apply tingkat filter
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $bankSoals = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $mapels = Mapel::active()->forTahunAjaran($tahunAjaranId)->get();
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        return view('features.naskah.banksoal.index', compact('bankSoals', 'mapels', 'paketUjians', 'tahunAjarans', 'tahunAjaranId', 'paketUjianId', 'showAllPaket'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $mapels = Mapel::active()->forTahunAjaran($activeYear->id)->get();
        $paketUjians = \App\Models\PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();
        $selectedMapel = $request->filled('mapel_id')
            ? Mapel::active()->forTahunAjaran($activeYear->id)->findOrFail($request->mapel_id)
            : null;

        return view('features.naskah.banksoal.create', compact('mapels', 'paketUjians', 'selectedMapel'));
    }

    public function comprehensiveImport()
    {
        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $paketUjians = PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('status', '!=', 'arsip')
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_mulai')
            ->get();
        $defaultPaketId = $paketUjians->firstWhere('status', 'aktif')?->id ?? $paketUjians->first()?->id;

        return view('features.naskah.import-komprehensif', compact('paketUjians', 'activeYear', 'defaultPaketId'));
    }

    public function processComprehensiveImport(Request $request)
    {
        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'paket_ujian_id' => [
                'nullable',
                Rule::exists('paket_ujian', 'id')
                    ->where('tahun_ajaran_id', $activeYear->id)
                    ->where(fn($query) => $query->where('status', '!=', 'arsip')),
            ],
        ]);

        $import = new NaskahComprehensiveImport($activeYear->id, $validated['paket_ujian_id'] ?? null);
        Excel::import($import, $request->file('file'));

        return redirect()->route('naskah.import-komprehensif')
            ->with('success', 'Import komprehensif naskah selesai.')
            ->with('import_results', $import->results());
    }

    public function downloadComprehensiveTemplate()
    {
        return Excel::download(
            new NaskahComprehensiveTemplateExport(),
            'template_import_komprehensif_naskah.xlsx'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tingkat' => 'nullable|in:X,XI,XII',
            'status' => 'nullable|in:aktif,draft,arsip',
            'mapel_id' => 'required|exists:mapel,id',
            'jumlah_pilihan' => 'nullable|integer|in:2,3,4,5',
            'tipe_soal_default' => 'nullable|in:' . implode(',', array_keys(\App\Models\Soal::QUESTION_TYPES)),
        ]);

        try {
            $activeYear = app(TahunAjaranService::class)->ensureActive();
            DB::beginTransaction();
            $mapel = Mapel::active()->forTahunAjaran($activeYear->id)->findOrFail($request->mapel_id);

            // Generate kode bank soal unik
            $kodeBank = 'BS' . date('Ym') . rand(1000, 9999);

            // Pastikan kode unik
            while (BankSoal::where('kode_bank', $kodeBank)->exists()) {
                $kodeBank = 'BS' . date('Ym') . rand(1000, 9999);
            }

            // Simpan data bank soal
            $bankSoal = BankSoal::create([
                'tahun_ajaran_id' => $activeYear->id,
                'paket_ujian_id' => $request->paket_ujian_id,
                'kode_bank' => $kodeBank,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tingkat' => $mapel->tingkat ?: $request->tingkat,
                'status' => 'aktif',
                'total_soal' => 0,
                'created_by' => Auth::id(),
                'mapel_id' => $mapel->id,
                'pengaturan' => [
                    'created_at' => now()->toDateTimeString(),
                    'creator_name' => Auth::user()->name ?? 'System',
                    'jumlah_pilihan' => (int) $request->input('jumlah_pilihan', 5),
                    'tipe_soal_default' => $request->input('tipe_soal_default', 'pilihan_ganda'),
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
        $banksoal->load('creator', 'mapel', 'paketUjian');
        $soals = $banksoal->soals()->orderBy('nomor_soal')->paginate(10);

        return view('features.naskah.banksoal.show', compact('banksoal', 'soals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankSoal $banksoal)
    {
        if ($banksoal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.banksoal.show', $banksoal->id)
                ->with('error', 'Bank soal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $mapels = Mapel::active()->forTahunAjaran($banksoal->tahun_ajaran_id)->get();
        $paketUjians = \App\Models\PaketUjian::where('tahun_ajaran_id', $banksoal->tahun_ajaran_id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();
        $soals = $banksoal->soals()->orderBy('nomor_soal', 'asc')->paginate(10);
        return view('features.naskah.banksoal.edit', compact('banksoal', 'mapels', 'paketUjians', 'soals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankSoal $banksoal)
    {
        if ($banksoal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.banksoal.show', $banksoal->id)
                ->with('error', 'Bank soal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tingkat' => 'required|in:X,XI,XII',
            'status' => 'required|in:aktif,draft,arsip',
            'mapel_id' => 'required|exists:mapel,id',
            'jumlah_pilihan' => 'nullable|integer|in:2,3,4,5',
            'tipe_soal_default' => 'nullable|in:' . implode(',', array_keys(\App\Models\Soal::QUESTION_TYPES)),
            'docx_file' => 'nullable|file|mimes:docx|max:10240', // 10MB max
        ]);

        try {
            Mapel::forTahunAjaran($banksoal->tahun_ajaran_id)->findOrFail($request->mapel_id);

            $pengaturan = $banksoal->pengaturan ?? [];
            $pengaturan['jumlah_pilihan'] = (int) $request->input('jumlah_pilihan', $banksoal->jumlah_pilihan);
            $pengaturan['tipe_soal_default'] = $request->input('tipe_soal_default', $banksoal->tipe_soal_default);

            // Update basic info
            $banksoal->update([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tingkat' => $request->tingkat,
                'status' => $request->status,
                'mapel_id' => $request->mapel_id,
                'paket_ujian_id' => $request->paket_ujian_id,
                'pengaturan' => $pengaturan,
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
    public function importDocxToBankSoal($file, BankSoal $banksoal): array
    {
        return $this->processDocxFile($file, $banksoal);
    }

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

            try {
                $xmlImportResult = $this->processDocxXmlFile($tempFile, $banksoal);
                if (($xmlImportResult['imported'] ?? 0) > 0) {
                    @unlink($tempFile);
                    return $xmlImportResult;
                }
            } catch (\Throwable $e) {
                Log::warning('DOCX XML parser failed, falling back to PhpWord parser', [
                    'error' => $e->getMessage(),
                ]);
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
            $lastImageTarget = null;
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
                            $questionNumber = (int) $matches[1];
                            $lastImageTarget = 'pertanyaan';
                            $currentQuestion = [
                                'bank_soal_id' => $banksoal->id,
                                'nomor_soal' => $questionNumber,
                                'pertanyaan' => $matches[2],
                                'tipe_pertanyaan' => 'teks', // default
                                'tipe_soal' => $banksoal->tipe_soal_default ?: 'pilihan_ganda',
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
                                $lastImageTarget = 'pilihan_' . $answerKey;

                                // Tandai pilihan kosong sebagai pilihan untuk gambar
                                if (empty(trim($answerText))) {
                                    Log::info("Empty answer option detected - likely an image placeholder", [
                                        'question' => $questionNumber,
                                        'option' => strtoupper($answerKey)
                                    ]);
                                }

                                // Jika pilihan ini adalah jawaban yang benar
                                if ($isCorrect) {
                                    $this->markImportedCorrectOption($currentQuestion, strtoupper($answerKey));
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
                            $lastImageTarget = 'pembahasan';
                            Log::info("Found explanation for question", [
                                'question' => $questionNumber
                            ]);
                        } elseif ($currentQuestion && $this->applyDocxKeyMarker($text, $currentQuestion)) {
                            // Kunci marker handled.
                        } elseif ($currentQuestion && $this->applyDocxTypeMarker($text, $currentQuestion)) {
                            // Tipe marker handled.
                        }
                        // If it's not a new question or answer or explicit explanation, append to current question text
                        elseif ($currentQuestion && trim($text) !== '') {
                            // Check if this is a continuation of an explanation
                            if (!empty($currentQuestion['pembahasan_teks'])) {
                                $currentQuestion['pembahasan_teks'] .= "\n" . $text;
                                $lastImageTarget = 'pembahasan';
                            } else {
                                $currentQuestion['pertanyaan'] .= "\n" . $text;
                                $lastImageTarget = 'pertanyaan';
                            }
                        }

                        $this->attachImagesFromTextRun($element, $currentQuestion, $lastImageTarget, $banksoal);
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

    private function processDocxXmlFile(string $docxPath, BankSoal $banksoal): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            throw new \Exception('Gagal membuka file DOCX sebagai arsip.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels') ?: '';

        if (!$documentXml) {
            $zip->close();
            throw new \Exception('Struktur DOCX tidak memiliki word/document.xml.');
        }

        $mediaMap = $this->docxMediaRelationshipMap($relsXml);
        $paragraphs = $this->docxParagraphs($documentXml);

        $log = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
            'timestamp' => now()->toDateTimeString(),
            'parser' => 'docx_xml',
        ];

        $currentQuestion = null;
        $questionNumber = 0;
        $lastImageTarget = null;

        foreach ($paragraphs as $paragraph) {
            $text = trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $paragraph['text'])));
            $imagesHandledInline = false;

            if (preg_match('/^(\d+)\.\s*(.*)$/u', $text, $matches)) {
                if ($currentQuestion) {
                    $this->saveQuestion($currentQuestion, $banksoal);
                    $log['imported']++;
                }

                $questionNumber = (int) $matches[1];
                $currentQuestion = $this->newImportedQuestionData($banksoal->id, $questionNumber, trim($matches[2] ?? ''));
                $currentQuestion['tipe_soal'] = $banksoal->tipe_soal_default ?: 'pilihan_ganda';
                $lastImageTarget = 'pertanyaan';

                if (($paragraph['has_rich_content'] ?? false) && trim($matches[2] ?? '') !== '') {
                    $currentQuestion['pertanyaan'] = $this->stripDocxQuestionNumber(
                        $this->docxParagraphHtml($paragraph, $zip, $mediaMap, $banksoal, $questionNumber, 'pertanyaan')
                    );
                    $currentQuestion['tipe_pertanyaan'] = 'teks';
                    $imagesHandledInline = true;
                }
            } elseif ($currentQuestion && preg_match('/^([A-E])\.\s*(.*)$/iu', $text, $matches)) {
                $answerKey = strtolower($matches[1]);
                $answerText = trim($matches[2] ?? '');
                $isCorrect = preg_match('/\[\*\]\s*$/u', $answerText) === 1;
                $answerText = trim(preg_replace('/\s*\[\*\]\s*$/u', '', $answerText));

                $lastImageTarget = 'pilihan_' . $answerKey;

                if (($paragraph['has_rich_content'] ?? false) && $answerText !== '') {
                    $currentQuestion['pilihan_' . $answerKey . '_teks'] = $this->stripDocxAnswerPrefix(
                        $this->docxParagraphHtml($paragraph, $zip, $mediaMap, $banksoal, $questionNumber, $lastImageTarget)
                    );
                    $currentQuestion['pilihan_' . $answerKey . '_tipe'] = 'teks';
                    $imagesHandledInline = true;
                } else {
                    $currentQuestion['pilihan_' . $answerKey . '_teks'] = $answerText;
                }

                if ($isCorrect) {
                    $this->markImportedCorrectOption($currentQuestion, strtoupper($answerKey));
                }
            } elseif ($currentQuestion && preg_match('/^Pembahasan:\s*(.*)$/iu', $text, $matches)) {
                $currentQuestion['pembahasan_teks'] = trim($matches[1] ?? '');
                $lastImageTarget = 'pembahasan';

                if (($paragraph['has_rich_content'] ?? false) && trim($matches[1] ?? '') !== '') {
                    $currentQuestion['pembahasan_teks'] = $this->stripDocxPembahasanPrefix(
                        $this->docxParagraphHtml($paragraph, $zip, $mediaMap, $banksoal, $questionNumber, 'pembahasan')
                    );
                    $currentQuestion['pembahasan_tipe'] = 'teks';
                    $imagesHandledInline = true;
                }
            } elseif ($currentQuestion && $this->applyDocxTypeMarker($text, $currentQuestion)) {
                // Tipe marker handled.
            } elseif ($currentQuestion && $this->applyDocxKeyMarker($text, $currentQuestion)) {
                // Kunci marker handled.
            } elseif ($currentQuestion && $text !== '') {
                $inlineHtml = ($paragraph['has_rich_content'] ?? false)
                    ? $this->docxParagraphHtml($paragraph, $zip, $mediaMap, $banksoal, $questionNumber, $lastImageTarget ?: 'pertanyaan')
                    : null;

                if ($lastImageTarget === 'pembahasan' || $currentQuestion['pembahasan_teks'] !== null) {
                    $currentQuestion['pembahasan_teks'] = trim(($currentQuestion['pembahasan_teks'] ?? '') . "\n" . ($inlineHtml ?: $text));
                    $lastImageTarget = 'pembahasan';
                } else {
                    $currentQuestion['pertanyaan'] = trim(($currentQuestion['pertanyaan'] ?? '') . "\n" . ($inlineHtml ?: $text));
                    $lastImageTarget = 'pertanyaan';
                }

                $imagesHandledInline = $inlineHtml !== null;
            }

            if ($currentQuestion && !$imagesHandledInline && !empty($paragraph['image_ids'])) {
                foreach ($paragraph['image_ids'] as $relationId) {
                    $mediaName = $mediaMap[$relationId] ?? null;
                    if (!$mediaName) {
                        continue;
                    }

                    $binary = $zip->getFromName('word/media/' . $mediaName);
                    if (!$binary) {
                        continue;
                    }

                    $target = $lastImageTarget ?: 'pertanyaan';
                    $extension = pathinfo($mediaName, PATHINFO_EXTENSION) ?: 'png';
                    $filename = $this->saveDocxImageBinary($binary, $extension, $banksoal->id, $questionNumber, $target);
                    $this->attachSavedImageToQuestion($currentQuestion, $target, $filename);
                }
            }
        }

        if ($currentQuestion) {
            $this->saveQuestion($currentQuestion, $banksoal);
            $log['imported']++;
        }

        $zip->close();

        Log::info('DOCX XML processing completed', [
            'imported' => $log['imported'],
            'media_count' => count($mediaMap),
        ]);

        return $log;
    }

    private function docxMediaRelationshipMap(string $relsXml): array
    {
        $map = [];
        preg_match_all('/<Relationship\b[^>]*>/i', $relsXml, $relationships);

        foreach ($relationships[0] ?? [] as $relationship) {
            if (!preg_match('/\bId="([^"]+)"/i', $relationship, $idMatch)) {
                continue;
            }

            if (!preg_match('/\bTarget="media\/([^"]+)"/i', $relationship, $targetMatch)) {
                continue;
            }

            $map[$idMatch[1]] = html_entity_decode($targetMatch[1]);
        }

        return $map;
    }

    private function docxParagraphs(string $documentXml): array
    {
        $bodyXml = $documentXml;
        if (preg_match('/<w:body\b[^>]*>(.*?)<\/w:body>/is', $documentXml, $bodyMatch)) {
            $bodyXml = $bodyMatch[1];
        }

        preg_match_all('/<w:tbl\b[^>]*>.*?<\/w:tbl>|<w:p\b[^>]*>.*?<\/w:p>/is', $bodyXml, $blocks);

        $paragraphs = [];
        foreach ($blocks[0] ?? [] as $blockXml) {
            if (str_starts_with($blockXml, '<w:tbl')) {
                array_push($paragraphs, ...$this->docxTableParagraphs($blockXml));
                continue;
            }

            $paragraphs[] = $this->docxParagraphData($blockXml);
        }

        return array_values(array_filter($paragraphs, fn($paragraph) => trim($paragraph['text'] ?? '') !== '' || !empty($paragraph['image_ids'] ?? [])));
    }

    private function docxTableParagraphs(string $tableXml): array
    {
        preg_match_all('/<w:tr\b[^>]*>.*?<\/w:tr>/is', $tableXml, $rowMatches);

        $paragraphs = [];
        foreach ($rowMatches[0] ?? [] as $rowXml) {
            $cells = $this->docxTableCells($rowXml);
            if (empty($cells) || $this->docxLooksLikeHeaderRow($cells)) {
                continue;
            }

            $synthetic = $this->docxSyntheticParagraphsFromTableRow($cells);
            if (!empty($synthetic)) {
                array_push($paragraphs, ...$synthetic);
                continue;
            }

            foreach ($cells as $cell) {
                foreach ($cell['paragraphs'] as $paragraph) {
                    $paragraphs[] = $paragraph;
                }
            }
        }

        return $paragraphs;
    }

    private function docxTableCells(string $rowXml): array
    {
        preg_match_all('/<w:tc\b[^>]*>.*?<\/w:tc>/is', $rowXml, $cellMatches);

        return collect($cellMatches[0] ?? [])->map(function ($cellXml) {
            preg_match_all('/<w:p\b[^>]*>.*?<\/w:p>/is', $cellXml, $paragraphMatches);
            $paragraphs = collect($paragraphMatches[0] ?? [])
                ->map(fn($paragraphXml) => $this->docxParagraphData($paragraphXml))
                ->filter(fn($paragraph) => trim($paragraph['text'] ?? '') !== '' || !empty($paragraph['image_ids'] ?? []))
                ->values()
                ->all();

            return [
                'text' => trim(collect($paragraphs)->pluck('text')->filter()->implode("\n")),
                'paragraphs' => $paragraphs,
                'segments' => $this->docxJoinParagraphSegments($paragraphs),
                'image_ids' => collect($paragraphs)->flatMap(fn($paragraph) => $paragraph['image_ids'] ?? [])->values()->all(),
                'has_rich_content' => collect($paragraphs)->contains(fn($paragraph) => $paragraph['has_rich_content'] ?? false),
            ];
        })->all();
    }

    private function docxSyntheticParagraphsFromTableRow(array $cells): array
    {
        $texts = collect($cells)->pluck('text')->map(fn($text) => trim(preg_replace('/\s+/u', ' ', (string) $text)))->all();
        $nonEmptyCells = collect($cells)->filter(fn($cell) => trim((string) ($cell['text'] ?? '')) !== '')->values()->all();

        if (count($nonEmptyCells) < 2) {
            return [];
        }

        $firstText = trim(preg_replace('/\s+/u', ' ', (string) ($nonEmptyCells[0]['text'] ?? '')));
        $secondText = trim(preg_replace('/\s+/u', ' ', (string) ($nonEmptyCells[1]['text'] ?? '')));

        if (preg_match('/^(\d+)\.?$/u', $firstText, $matches) && $secondText !== '') {
            $paragraphs = [
                $this->docxMergeCellsAsParagraph([
                    $this->docxTextCell($matches[1] . '. '),
                    $nonEmptyCells[1],
                ]),
            ];

            foreach (array_slice($nonEmptyCells, 2) as $cell) {
                array_push($paragraphs, ...$this->docxOptionParagraphsFromCell($cell));
            }

            return $paragraphs;
        }

        if (preg_match('/^[A-E]$/iu', $firstText) && $secondText !== '') {
            return [
                $this->docxMergeCellsAsParagraph([
                    $this->docxTextCell(strtoupper($firstText) . '. '),
                    $nonEmptyCells[1],
                ]),
            ];
        }

        if (preg_match('/^\d+\.\s+/u', $firstText)) {
            $paragraphs = [$nonEmptyCells[0]];

            foreach (array_slice($nonEmptyCells, 1) as $cell) {
                array_push($paragraphs, ...$this->docxOptionParagraphsFromCell($cell));
            }

            return $paragraphs;
        }

        $optionParagraphs = [];
        foreach ($nonEmptyCells as $cell) {
            array_push($optionParagraphs, ...$this->docxOptionParagraphsFromCell($cell));
        }

        return count($optionParagraphs) === count($nonEmptyCells) ? $optionParagraphs : [];
    }

    private function docxOptionParagraphsFromCell(array $cell): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) ($cell['text'] ?? '')));

        if (preg_match('/^([A-E])[\.\)]?\s*(.*)$/iu', $text, $matches)) {
            $prefix = strtoupper($matches[1]) . '. ';
            $cellWithoutPrefix = $cell;
            $cellWithoutPrefix['text'] = trim($matches[2] ?? '');
            $cellWithoutPrefix['segments'] = $this->docxStripLeadingOptionPrefixFromSegments($cell['segments'] ?? []);

            return [$this->docxMergeCellsAsParagraph([$this->docxTextCell($prefix), $cellWithoutPrefix])];
        }

        return [];
    }

    private function docxLooksLikeHeaderRow(array $cells): bool
    {
        $text = strtolower(trim(collect($cells)->pluck('text')->filter()->implode(' ')));

        if ($text === '') {
            return true;
        }

        return preg_match('/\b(no|nomor|soal|pertanyaan|jawaban|opsi|pilihan|kunci)\b/u', $text) === 1
            && preg_match('/^\d+\.?/u', $text) !== 1
            && preg_match('/^[a-e][\.\)]/iu', $text) !== 1;
    }

    private function docxParagraphData(string $paragraphXml): array
    {
        preg_match_all('/r:embed="([^"]+)"/i', $paragraphXml, $imageMatches);
        preg_match_all('/<w:t\b[^>]*>(.*?)<\/w:t>|<m:oMath\b[^>]*>.*?<\/m:oMath>|r:embed="([^"]+)"/is', $paragraphXml, $segmentMatches, PREG_SET_ORDER);

        $segments = collect($segmentMatches)->map(function ($match) {
            if (!empty($match[1])) {
                return ['type' => 'text', 'value' => html_entity_decode($match[1])];
            }

            if (str_starts_with($match[0] ?? '', '<m:oMath')) {
                return [
                    'type' => 'math',
                    'value' => $this->docxOmmlToHtml($match[0]),
                    'text' => $this->docxOmmlToText($match[0]),
                ];
            }

            return ['type' => 'image', 'value' => $match[2] ?? null];
        })->filter(function ($segment) {
            return ($segment['type'] === 'image' && !empty($segment['value']))
                || (($segment['value'] ?? '') !== '');
        })->values()->all();

        return [
            'text' => $this->docxSegmentsText($segments),
            'image_ids' => $imageMatches[1] ?? [],
            'has_rich_content' => !empty($imageMatches[1] ?? [])
                || collect($segments)->contains(fn($segment) => $segment['type'] === 'math'),
            'segments' => $segments,
        ];
    }

    private function docxSegmentsText(array $segments): string
    {
        return collect($segments)->map(function ($segment) {
            return $segment['type'] === 'math'
                ? ($segment['text'] ?? '')
                : ($segment['type'] === 'text' ? $segment['value'] : '');
        })->implode('');
    }

    private function docxJoinParagraphSegments(array $paragraphs): array
    {
        $segments = [];

        foreach ($paragraphs as $index => $paragraph) {
            if ($index > 0) {
                $segments[] = ['type' => 'text', 'value' => "\n"];
            }

            array_push($segments, ...($paragraph['segments'] ?? []));
        }

        return $segments;
    }

    private function docxTextCell(string $text): array
    {
        return [
            'text' => $text,
            'segments' => [['type' => 'text', 'value' => $text]],
            'image_ids' => [],
            'has_rich_content' => false,
            'paragraphs' => [],
        ];
    }

    private function docxMergeCellsAsParagraph(array $cells): array
    {
        $segments = [];
        $imageIds = [];
        $hasRichContent = false;

        foreach ($cells as $cell) {
            array_push($segments, ...($cell['segments'] ?? []));
            array_push($imageIds, ...($cell['image_ids'] ?? []));
            $hasRichContent = $hasRichContent || (bool) ($cell['has_rich_content'] ?? false);
        }

        return [
            'text' => $this->docxSegmentsText($segments),
            'image_ids' => $imageIds,
            'has_rich_content' => $hasRichContent || !empty($imageIds),
            'segments' => $segments,
        ];
    }

    private function docxStripLeadingOptionPrefixFromSegments(array $segments): array
    {
        $stripped = false;

        return collect($segments)->map(function ($segment) use (&$stripped) {
            if ($stripped || ($segment['type'] ?? '') !== 'text') {
                return $segment;
            }

            $segment['value'] = preg_replace('/^\s*[A-E][\.\)]?\s*/iu', '', (string) ($segment['value'] ?? ''), 1, $count);
            $stripped = $count > 0;

            return $segment;
        })->filter(fn($segment) => ($segment['type'] ?? '') === 'image' || (($segment['value'] ?? '') !== ''))->values()->all();
    }

    private function docxParagraphHtml(array $paragraph, \ZipArchive $zip, array $mediaMap, BankSoal $banksoal, int $questionNumber, string $target): string
    {
        $html = '';

        foreach ($paragraph['segments'] ?? [] as $segment) {
            if ($segment['type'] === 'text') {
                $html .= e(str_replace("\xc2\xa0", ' ', $segment['value']));
                continue;
            }

            if ($segment['type'] === 'math') {
                $html .= $segment['value'];
                continue;
            }

            $mediaName = $mediaMap[$segment['value']] ?? null;
            if (!$mediaName) {
                continue;
            }

            $binary = $zip->getFromName('word/media/' . $mediaName);
            if (!$binary) {
                continue;
            }

            $extension = pathinfo($mediaName, PATHINFO_EXTENSION) ?: 'png';
            $filename = $this->saveDocxImageBinary($binary, $extension, $banksoal->id, $questionNumber, $target);
            $folder = $this->docxImageFolder($target);
            $src = e(\Illuminate\Support\Facades\Storage::url($folder . '/' . $filename));
            $html .= '<img src="' . $src . '" alt="Gambar soal" style="max-width:100%;height:auto;vertical-align:middle;display:inline-block;margin:0 4px;">';
        }

        return trim($html);
    }

    private function docxOmmlToText(string $omml): string
    {
        $root = $this->docxOmmlDomRoot($omml);

        if (!$root) {
            return trim(html_entity_decode(strip_tags($omml)));
        }

        return trim($this->docxOmmlNodeToText($root));
    }

    private function docxOmmlToHtml(string $omml): string
    {
        $root = $this->docxOmmlDomRoot($omml);

        if (!$root) {
            return e(trim(html_entity_decode(strip_tags($omml))));
        }

        return '<span class="docx-math" style="display:inline-block;vertical-align:middle;margin:0 2px;">'
            . $this->docxOmmlNodeToHtml($root)
            . '</span>';
    }

    private function docxOmmlDomRoot(string $omml): ?\DOMNode
    {
        $dom = new \DOMDocument();
        $xml = '<root xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" '
            . 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . $omml
            . '</root>';

        if (!@$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return null;
        }

        foreach ($dom->documentElement->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                return $child;
            }
        }

        return null;
    }

    private function docxOmmlNodeToText(\DOMNode $node): string
    {
        $name = $node->localName;

        return match ($name) {
            't' => $node->nodeValue,
            'f' => '(' . $this->docxOmmlFirstChildText($node, 'num') . ')/(' . $this->docxOmmlFirstChildText($node, 'den') . ')',
            'sSub' => $this->docxOmmlFirstChildText($node, 'e') . '_' . $this->docxOmmlFirstChildText($node, 'sub'),
            'rad' => $this->docxOmmlRadicalText($node),
            'd' => '(' . $this->docxOmmlFirstChildText($node, 'e') . ')',
            'ctrlPr', 'fPr', 'sSubPr', 'radPr', 'dPr', 'degHide' => '',
            default => $this->docxOmmlChildrenToText($node),
        };
    }

    private function docxOmmlNodeToHtml(\DOMNode $node): string
    {
        $name = $node->localName;

        return match ($name) {
            't' => e($node->nodeValue),
            'f' => $this->docxOmmlFractionHtml($node),
            'sSub' => $this->docxOmmlFirstChildHtml($node, 'e') . '<sub>' . $this->docxOmmlFirstChildHtml($node, 'sub') . '</sub>',
            'rad' => $this->docxOmmlRadicalHtml($node),
            'd' => '(' . $this->docxOmmlFirstChildHtml($node, 'e') . ')',
            'ctrlPr', 'fPr', 'sSubPr', 'radPr', 'dPr', 'degHide' => '',
            default => $this->docxOmmlChildrenToHtml($node),
        };
    }

    private function docxOmmlChildrenToText(\DOMNode $node): string
    {
        $text = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $text .= $this->docxOmmlNodeToText($child);
            }
        }

        return $text;
    }

    private function docxOmmlChildrenToHtml(\DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $html .= $this->docxOmmlNodeToHtml($child);
            }
        }

        return $html;
    }

    private function docxOmmlFirstChildText(\DOMNode $node, string $localName): string
    {
        $child = $this->docxOmmlFirstChild($node, $localName);
        return $child ? $this->docxOmmlChildrenToText($child) : '';
    }

    private function docxOmmlFirstChildHtml(\DOMNode $node, string $localName): string
    {
        $child = $this->docxOmmlFirstChild($node, $localName);
        return $child ? $this->docxOmmlChildrenToHtml($child) : '';
    }

    private function docxOmmlFirstChild(\DOMNode $node, string $localName): ?\DOMNode
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === $localName) {
                return $child;
            }
        }

        return null;
    }

    private function docxOmmlFractionHtml(\DOMNode $node): string
    {
        $num = $this->docxOmmlFirstChildHtml($node, 'num');
        $den = $this->docxOmmlFirstChildHtml($node, 'den');

        return '<span style="display:inline-flex;flex-direction:column;align-items:center;vertical-align:middle;line-height:1.05;margin:0 2px;">'
            . '<span style="padding:0 3px;">' . $num . '</span>'
            . '<span style="border-top:1px solid currentColor;padding:0 3px;">' . $den . '</span>'
            . '</span>';
    }

    private function docxOmmlRadicalText(\DOMNode $node): string
    {
        $degree = $this->docxOmmlFirstChildText($node, 'deg');
        $base = $this->docxOmmlFirstChildText($node, 'e');

        return $degree !== '' ? $degree . '√(' . $base . ')' : '√(' . $base . ')';
    }

    private function docxOmmlRadicalHtml(\DOMNode $node): string
    {
        $degree = $this->docxOmmlFirstChildHtml($node, 'deg');
        $base = $this->docxOmmlFirstChildHtml($node, 'e');

        return '<span style="display:inline-block;vertical-align:middle;">'
            . ($degree !== '' ? '<sup>' . $degree . '</sup>' : '')
            . '&radic;<span style="border-top:1px solid currentColor;padding:0 2px;">' . $base . '</span>'
            . '</span>';
    }

    private function stripDocxQuestionNumber(string $html): string
    {
        return trim(preg_replace('/^\s*\d+\.\s*/u', '', $html));
    }

    private function stripDocxAnswerPrefix(string $html): string
    {
        $html = preg_replace('/^\s*[A-E]\.\s*/iu', '', $html);
        return trim(preg_replace('/\s*\[\*\]\s*/u', '', $html));
    }

    private function stripDocxPembahasanPrefix(string $html): string
    {
        return trim(preg_replace('/^\s*Pembahasan:\s*/iu', '', $html));
    }

    private function newImportedQuestionData(int $bankSoalId, int $questionNumber, string $questionText): array
    {
        return [
            'bank_soal_id' => $bankSoalId,
            'nomor_soal' => $questionNumber,
            'pertanyaan' => $questionText,
            'tipe_pertanyaan' => 'teks',
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
            'bobot' => 1.00,
        ];
    }

    private function applyDocxTypeMarker(string $text, array &$question): bool
    {
        if (!preg_match('/^Tipe\s*:\s*(.+)$/iu', trim($text), $matches)) {
            return false;
        }

        $type = $this->normalizeImportedQuestionType($matches[1] ?? '');
        if (array_key_exists($type, Soal::QUESTION_TYPES)) {
            $question['tipe_soal'] = $type;
        }

        return true;
    }

    private function applyDocxKeyMarker(string $text, array &$question): bool
    {
        if (!preg_match('/^Kunci\s*:\s*(.+)$/iu', trim($text), $matches)) {
            return false;
        }

        $question['kunci_jawaban'] = trim((string) ($matches[1] ?? ''));

        return true;
    }

    private function markImportedCorrectOption(array &$question, string $option): void
    {
        if (($question['tipe_soal'] ?? 'pilihan_ganda') === 'pilihan_kompleks') {
            $keys = collect(explode(',', strtoupper((string) ($question['kunci_jawaban'] ?? ''))))
                ->map(fn($item) => trim($item))
                ->filter()
                ->push($option)
                ->unique()
                ->sort()
                ->values()
                ->implode(',');
            $question['kunci_jawaban'] = $keys;
            return;
        }

        $question['kunci_jawaban'] = $option;
    }

    private function normalizeImportedQuestionType(string $type): string
    {
        $type = strtolower(trim($type));
        $aliases = [
            'pg' => 'pilihan_ganda',
            'pilihan ganda' => 'pilihan_ganda',
            'pilihan kompleks' => 'pilihan_kompleks',
            'benar salah' => 'benar_salah',
            'isian' => 'isian_singkat',
            'isian singkat' => 'isian_singkat',
            'rumpang' => 'teks_rumpang',
            'teks rumpang' => 'teks_rumpang',
            'matching' => 'menjodohkan',
            'ordering' => 'mengurutkan',
            'dragdrop' => 'drag_drop',
        ];

        return $aliases[$type] ?? str_replace(' ', '_', $type);
    }

    private function attachImagesFromTextRun($textRun, ?array &$currentQuestion, ?string &$lastImageTarget, BankSoal $banksoal): void
    {
        if (!$currentQuestion) {
            return;
        }

        foreach ($textRun->getElements() as $inline) {
            if (!$inline instanceof \PhpOffice\PhpWord\Element\Image) {
                continue;
            }

            $target = $lastImageTarget ?: 'pertanyaan';
            $filename = $this->saveDocxImage($inline, $banksoal->id, (int) $currentQuestion['nomor_soal'], $target);

            if (!$filename) {
                Log::warning('DOCX image found but could not be saved', [
                    'question' => $currentQuestion['nomor_soal'],
                    'target' => $target,
                ]);
                continue;
            }

            $this->attachSavedImageToQuestion($currentQuestion, $target, $filename);

            Log::info('DOCX image imported', [
                'question' => $currentQuestion['nomor_soal'],
                'target' => $target,
                'filename' => $filename,
            ]);
        }
    }

    private function saveDocxImage(Image $image, int $bankSoalId, int $nomorSoal, string $target): ?string
    {
        $binary = null;
        $source = method_exists($image, 'getSource') ? $image->getSource() : null;

        if (method_exists($image, 'getImageStringData')) {
            try {
                $binary = $image->getImageStringData();
            } catch (\Throwable $e) {
                $binary = null;
            }
        }

        if (!$binary && $source) {
            try {
                $binary = @file_get_contents($source) ?: null;
            } catch (\Throwable $e) {
                $binary = null;
            }
        }

        if (!$binary) {
            return null;
        }

        $extension = method_exists($image, 'getImageExtension') ? $image->getImageExtension() : null;
        if (!$extension && $source) {
            $extension = pathinfo(parse_url($source, PHP_URL_PATH) ?: $source, PATHINFO_EXTENSION);
        }
        $extension = strtolower($extension ?: 'png');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        return $this->saveDocxImageBinary($binary, $extension, $bankSoalId, $nomorSoal, $target);
    }

    private function saveDocxImageBinary(string $binary, string $extension, int $bankSoalId, int $nomorSoal, string $target): string
    {
        $extension = strtolower($extension ?: 'png');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        $folder = $this->docxImageFolder($target);

        $filename = implode('_', [
            'docx',
            'bank' . $bankSoalId,
            'soal' . $nomorSoal,
            $target,
            \Illuminate\Support\Str::random(8),
        ]) . '.' . $extension;

        Storage::disk('public')->put($folder . '/' . $filename, $binary);

        return $filename;
    }

    private function docxImageFolder(string $target): string
    {
        return match (true) {
            str_starts_with($target, 'pilihan_') => 'soal/pilihan',
            $target === 'pembahasan' => 'soal/pembahasan',
            default => 'soal/pertanyaan',
        };
    }

    private function attachSavedImageToQuestion(array &$question, string $target, string $filename): void
    {
        if ($target === 'pembahasan') {
            $question['pembahasan_gambar'] = $filename;
            $question['pembahasan_tipe'] = empty(trim((string) ($question['pembahasan_teks'] ?? ''))) ? 'gambar' : 'teks_gambar';
            return;
        }

        if (str_starts_with($target, 'pilihan_')) {
            $option = substr($target, -1);
            $question["pilihan_{$option}_gambar"] = $filename;
            $question["pilihan_{$option}_tipe"] = 'gambar';
            return;
        }

        $question['gambar_pertanyaan'] = $filename;
        $question['tipe_pertanyaan'] = empty(trim((string) ($question['pertanyaan'] ?? ''))) ? 'gambar' : 'teks_gambar';
    }

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

            if (empty($data['pertanyaan']) && empty($data['gambar_pertanyaan'])) {
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
                $gambarField = 'pilihan_' . $option . '_gambar';

                if (isset($data[$teksField]) && $data[$teksField] === '' && empty($data[$gambarField])) {
                    // Mark these empty options with a placeholder text
                    $data[$teksField] = '[Perlu Tambahkan Gambar Secara Manual]';
                    Log::info("Empty answer option detected - manual image upload required", [
                        'question' => $data['nomor_soal'],
                        'option' => strtoupper($option)
                    ]);
                }
            }

            if (($data['tipe_soal'] ?? null) === 'teks_rumpang') {
                preg_match_all('/\[\[(.+?)\]\]/', (string) ($data['pertanyaan'] ?? ''), $matches);
                $answers = collect($matches[1] ?? [])
                    ->map(fn($answer) => trim(strip_tags((string) $answer)))
                    ->filter(fn($answer) => $answer !== '')
                    ->values()
                    ->all();

                if (!empty($answers)) {
                    $data['display_settings'] = array_merge($data['display_settings'] ?? [], [
                        'cloze' => ['answers' => $answers],
                    ]);
                    $data['kunci_jawaban'] = json_encode([
                        'type' => 'teks_rumpang',
                        'data' => ['answers' => $answers],
                    ], JSON_UNESCAPED_UNICODE);
                }
            }

            // Pastikan kunci jawaban tidak null
            if (in_array($data['tipe_soal'], Soal::OBJECTIVE_TYPES, true) && trim((string) ($data['kunci_jawaban'] ?? '')) === '') {
                if (in_array($data['tipe_soal'], ['isian_singkat', 'teks_rumpang'], true)) {
                    throw new \Exception("Kunci jawaban wajib diisi untuk tipe soal {$data['tipe_soal']} pada nomor {$data['nomor_soal']}");
                }

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
