<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSoalRequest;
use App\Models\BankSoal;
use App\Models\Soal;
use App\Services\SoalImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SoalController extends Controller
{
    protected $imageService;

    public function __construct(SoalImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bankSoalId = $request->get('bank_soal_id');
        $mapelId = $request->get('mapel_id');
        $tingkat = $request->get('tingkat');
        $tipeSoal = $request->get('tipe_soal');
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Soal::with(['bankSoal', 'bankSoal.mapel']);

        // Bank Soal filter
        if ($bankSoalId) {
            $query->where('bank_soal_id', $bankSoalId);
        }

        // Mapel filter
        if ($mapelId) {
            $query->whereHas('bankSoal', function ($q) use ($mapelId) {
                $q->where('mapel_id', $mapelId);
            });
        }

        // Tingkat filter (using join with bank_soal table)
        if ($tingkat) {
            $query->whereHas('bankSoal', function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat);
            });
        }

        // Tipe Soal filter
        if ($tipeSoal) {
            $query->where('tipe_soal', $tipeSoal);
        }

        // Search by pertanyaan or kategori
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pertanyaan', 'like', '%' . $search . '%')
                    ->orWhere('kategori', 'like', '%' . $search . '%');
            });
        }

        $soals = $query->orderBy('nomor_soal')->paginate($perPage);
        $bankSoals = BankSoal::active()->get();
        $tingkatList = BankSoal::distinct('tingkat')->pluck('tingkat')->toArray();

        return view('features.naskah.soal.index', compact('soals', 'bankSoals', 'tingkatList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $bankSoals = BankSoal::active()->get();
        $selectedBankSoal = null;
        $nextNomorSoal = 1;

        if ($request->has('bank_soal_id')) {
            $selectedBankSoal = BankSoal::find($request->bank_soal_id);
            if ($selectedBankSoal) {
                $nextNomorSoal = $selectedBankSoal->soals()->max('nomor_soal') + 1;
            }
        }

        return view('features.naskah.soal.create', compact('bankSoals', 'selectedBankSoal', 'nextNomorSoal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSoalRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Debug logging - log raw request data
            Log::info('Raw request data received', [
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);

            // Log file upload info before processing
            $hasGambarPertanyaan = $request->hasFile('gambar_pertanyaan');
            $hasGambarPembahasan = $request->hasFile('pembahasan_gambar');

            $pilihanFiles = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $pilihan) {
                $fieldName = "pilihan_{$pilihan}_gambar";
                $pilihanFiles[$pilihan] = $request->hasFile($fieldName);
            }

            Log::info('Processing image uploads for new soal', [
                'has_gambar_pertanyaan' => $hasGambarPertanyaan,
                'has_gambar_pembahasan' => $hasGambarPembahasan,
                'pilihan_files' => $pilihanFiles,
                'tipe_pertanyaan' => $data['tipe_pertanyaan'],
                'bank_soal_id' => $data['bank_soal_id'],
                'nomor_soal' => $data['nomor_soal'],
                'validated_data_keys' => array_keys($data)
            ]);

            // Handle pertanyaan images
            if ($request->hasFile('gambar_pertanyaan')) {
                $file = $request->file('gambar_pertanyaan');
                Log::info('Uploading gambar_pertanyaan', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'is_valid' => $file->isValid(),
                    'path' => $file->getRealPath()
                ]);

                $data['gambar_pertanyaan'] = $this->imageService->uploadPertanyaanImage(
                    $file,
                    $data['bank_soal_id'],
                    $data['nomor_soal']
                );

                Log::info('Uploaded gambar_pertanyaan successfully', [
                    'filename' => $data['gambar_pertanyaan']
                ]);
            }

            // Handle pilihan images
            foreach (['a', 'b', 'c', 'd', 'e'] as $pilihan) {
                $fieldName = "pilihan_{$pilihan}_gambar";
                if ($request->hasFile($fieldName)) {
                    $file = $request->file($fieldName);
                    Log::info("Uploading {$fieldName}", [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);

                    $data[$fieldName] = $this->imageService->uploadPilihanImage(
                        $file,
                        $data['bank_soal_id'],
                        $data['nomor_soal'],
                        $pilihan
                    );

                    Log::info("Uploaded {$fieldName} successfully", [
                        'filename' => $data[$fieldName]
                    ]);
                }
            }

            // Handle pembahasan images
            if ($request->hasFile('pembahasan_gambar')) {
                $file = $request->file('pembahasan_gambar');
                Log::info('Uploading pembahasan_gambar', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);

                $data['pembahasan_gambar'] = $this->imageService->uploadPembahasanImage(
                    $file,
                    $data['bank_soal_id'],
                    $data['nomor_soal']
                );

                Log::info('Uploaded pembahasan_gambar successfully', [
                    'filename' => $data['pembahasan_gambar']
                ]);
            }

            // Set default values
            $data['bobot'] = $data['bobot'] ?? 1.00;
            $data['display_settings'] = $data['display_settings'] ?? [];

            // Create soal
            $soal = Soal::create($data);

            // Update bank soal total
            $bankSoal = BankSoal::find($data['bank_soal_id']);
            $bankSoal->updateTotalSoal();

            DB::commit();

            Log::info('Soal created successfully', [
                'soal_id' => $soal->id,
                'bank_soal_id' => $data['bank_soal_id'],
                'nomor_soal' => $data['nomor_soal'],
                'tipe_pertanyaan' => $data['tipe_pertanyaan'],
                'has_images' => [
                    'pertanyaan' => !empty($data['gambar_pertanyaan']),
                    'pembahasan' => !empty($data['pembahasan_gambar']),
                    'pilihan' => array_map(function ($p) use ($data) {
                        return !empty($data["pilihan_{$p}_gambar"]);
                    }, ['a', 'b', 'c', 'd', 'e'])
                ]
            ]);

            return redirect()
                ->route('naskah.soal.show', $soal)
                ->with('success', 'Soal berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating soal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan soal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Soal $soal)
    {
        $soal->load('bankSoal');

        return view('features.naskah.soal.show', compact('soal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Soal $soal)
    {
        $bankSoals = BankSoal::active()->get();

        return view('features.naskah.soal.edit', compact('soal', 'bankSoals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSoalRequest $request, Soal $soal)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Log file upload info before processing
            $hasGambarPertanyaan = $request->hasFile('gambar_pertanyaan');
            $hasGambarPembahasan = $request->hasFile('pembahasan_gambar');

            $pilihanFiles = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $pilihan) {
                $fieldName = "pilihan_{$pilihan}_gambar";
                $pilihanFiles[$pilihan] = $request->hasFile($fieldName);
            }

            Log::info('Processing image uploads for updated soal', [
                'soal_id' => $soal->id,
                'has_gambar_pertanyaan' => $hasGambarPertanyaan,
                'has_gambar_pembahasan' => $hasGambarPembahasan,
                'pilihan_files' => $pilihanFiles,
                'tipe_pertanyaan' => $data['tipe_pertanyaan'],
                'bank_soal_id' => $data['bank_soal_id'],
                'nomor_soal' => $data['nomor_soal']
            ]);

            // Handle pertanyaan image update
            if ($request->hasFile('gambar_pertanyaan')) {
                $file = $request->file('gambar_pertanyaan');
                Log::info('Uploading gambar_pertanyaan for update', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);

                // Delete old image if exists
                if ($soal->gambar_pertanyaan) {
                    $this->imageService->deleteImage('soal/pertanyaan/' . $soal->gambar_pertanyaan);
                    Log::info('Deleted old gambar_pertanyaan', ['old_filename' => $soal->gambar_pertanyaan]);
                }

                $data['gambar_pertanyaan'] = $this->imageService->uploadPertanyaanImage(
                    $file,
                    $data['bank_soal_id'],
                    $data['nomor_soal']
                );

                Log::info('Updated gambar_pertanyaan successfully', [
                    'filename' => $data['gambar_pertanyaan']
                ]);
            }

            // Handle pilihan images update
            foreach (['a', 'b', 'c', 'd', 'e'] as $pilihan) {
                $fieldName = "pilihan_{$pilihan}_gambar";
                if ($request->hasFile($fieldName)) {
                    $file = $request->file($fieldName);
                    Log::info("Uploading {$fieldName} for update", [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);

                    // Delete old image if exists
                    $oldImage = $soal->{"pilihan_{$pilihan}_gambar"};
                    if ($oldImage) {
                        $this->imageService->deleteImage('soal/pilihan/' . $oldImage);
                        Log::info("Deleted old {$fieldName}", ['old_filename' => $oldImage]);
                    }

                    $data[$fieldName] = $this->imageService->uploadPilihanImage(
                        $file,
                        $data['bank_soal_id'],
                        $data['nomor_soal'],
                        $pilihan
                    );

                    Log::info("Updated {$fieldName} successfully", [
                        'filename' => $data[$fieldName]
                    ]);
                }
            }

            // Handle pembahasan image update
            if ($request->hasFile('pembahasan_gambar')) {
                $file = $request->file('pembahasan_gambar');
                Log::info('Uploading pembahasan_gambar for update', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);

                // Delete old image if exists
                if ($soal->pembahasan_gambar) {
                    $this->imageService->deleteImage('soal/pembahasan/' . $soal->pembahasan_gambar);
                    Log::info('Deleted old pembahasan_gambar', ['old_filename' => $soal->pembahasan_gambar]);
                }

                // Delete gambar_pembahasan if exists (backward compatibility)
                if ($soal->gambar_pembahasan) {
                    $this->imageService->deleteImage('soal/pembahasan/' . $soal->gambar_pembahasan);
                    Log::info('Deleted old gambar_pembahasan (backward compatibility)', ['old_filename' => $soal->gambar_pembahasan]);
                    $data['gambar_pembahasan'] = null; // Reset old field
                }

                $data['pembahasan_gambar'] = $this->imageService->uploadPembahasanImage(
                    $file,
                    $data['bank_soal_id'],
                    $data['nomor_soal']
                );

                Log::info('Updated pembahasan_gambar successfully', [
                    'filename' => $data['pembahasan_gambar']
                ]);
            }

            // Update soal
            $soal->update($data);

            // Update bank soal total if changed
            if ($soal->wasChanged('bank_soal_id')) {
                // Update old bank soal
                $oldBankSoal = BankSoal::find($soal->getOriginal('bank_soal_id'));
                if ($oldBankSoal) {
                    $oldBankSoal->updateTotalSoal();
                }

                // Update new bank soal
                $newBankSoal = BankSoal::find($data['bank_soal_id']);
                $newBankSoal->updateTotalSoal();
            }

            DB::commit();

            Log::info('Soal updated successfully', [
                'soal_id' => $soal->id,
                'changes' => $soal->getChanges(),
                'has_images' => [
                    'pertanyaan' => !empty($data['gambar_pertanyaan']) || (!isset($data['gambar_pertanyaan']) && $soal->gambar_pertanyaan),
                    'pembahasan' => !empty($data['pembahasan_gambar']) || (!isset($data['pembahasan_gambar']) && $soal->pembahasan_gambar),
                    'pilihan' => array_map(function ($p) use ($data, $soal) {
                        $field = "pilihan_{$p}_gambar";
                        return !empty($data[$field]) || (!isset($data[$field]) && $soal->{$field});
                    }, ['a', 'b', 'c', 'd', 'e'])
                ]
            ]);

            return redirect()
                ->route('naskah.soal.show', $soal)
                ->with('success', 'Soal berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating soal', [
                'soal_id' => $soal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui soal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Soal $soal)
    {
        try {
            DB::beginTransaction();

            $bankSoalId = $soal->bank_soal_id;

            // Delete images
            $soal->deleteImages();

            // Delete soal
            $soal->delete();

            // Update bank soal total
            $bankSoal = BankSoal::find($bankSoalId);
            if ($bankSoal) {
                $bankSoal->updateTotalSoal();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Soal berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting soal', [
                'soal_id' => $soal->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus soal: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete soal.
     */
    public function bulkDelete(Request $request)
    {
        try {
            DB::beginTransaction();

            $soalIds = $request->input('soal_ids', []);
            $count = 0;

            foreach ($soalIds as $id) {
                $soal = Soal::find($id);
                if ($soal) {
                    // Delete soal
                    $soal->delete();
                    $count++;

                    // Update bank soal total
                    $bankSoal = BankSoal::find($soal->bank_soal_id);
                    if ($bankSoal) {
                        $bankSoal->updateTotalSoal();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('naskah.soal.index')
                ->with('success', "{$count} soal berhasil dihapus!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus soal: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a soal.
     */
    public function duplicate(Soal $soal)
    {
        try {
            DB::beginTransaction();

            // Clone the soal
            $newSoal = $soal->replicate();

            // Get the next available nomor_soal for this bank
            $nextNomorSoal = Soal::where('bank_soal_id', $soal->bank_soal_id)
                ->max('nomor_soal') + 1;
            $newSoal->nomor_soal = $nextNomorSoal;

            // Duplicate images if necessary
            if ($soal->gambar_pertanyaan) {
                $newSoal->gambar_pertanyaan = $this->duplicateImage(
                    'soal/pertanyaan/' . $soal->gambar_pertanyaan,
                    'soal/pertanyaan',
                    "soal_{$newSoal->bank_soal_id}_{$newSoal->nomor_soal}"
                );
            }

            // Duplicate pilihan images
            foreach (['a', 'b', 'c', 'd', 'e'] as $pilihan) {
                $imageField = "pilihan_{$pilihan}_gambar";
                if ($soal->$imageField) {
                    $newSoal->$imageField = $this->duplicateImage(
                        'soal/pilihan/' . $soal->$imageField,
                        'soal/pilihan',
                        "soal_{$newSoal->bank_soal_id}_{$newSoal->nomor_soal}_{$pilihan}"
                    );
                }
            }

            // Duplicate pembahasan image
            if ($soal->pembahasan_gambar) {
                $newSoal->pembahasan_gambar = $this->duplicateImage(
                    'soal/pembahasan/' . $soal->pembahasan_gambar,
                    'soal/pembahasan',
                    "soal_{$newSoal->bank_soal_id}_{$newSoal->nomor_soal}"
                );
            }

            // Duplicate gambar_pembahasan jika ada (backward compatibility)
            if ($soal->gambar_pembahasan) {
                $newSoal->gambar_pembahasan = $this->duplicateImage(
                    'soal/pembahasan/' . $soal->gambar_pembahasan,
                    'soal/pembahasan',
                    "soal_{$newSoal->bank_soal_id}_{$newSoal->nomor_soal}_old"
                );
            }

            $newSoal->save();

            // Update bank soal total
            $bankSoal = BankSoal::find($soal->bank_soal_id);
            if ($bankSoal) {
                $bankSoal->updateTotalSoal();
            }

            DB::commit();

            return redirect()
                ->route('naskah.soal.edit', $newSoal)
                ->with('success', 'Soal berhasil diduplikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating soal', [
                'soal_id' => $soal->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal menduplikasi soal: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to duplicate an image file
     */
    private function duplicateImage($sourcePath, $targetDir, $newFilenamePrefix)
    {
        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $newFilename = $newFilenamePrefix . '_' . \Illuminate\Support\Str::random(8) . '.' . $extension;
                $newPath = $targetDir . '/' . $newFilename;

                \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);

                return $newFilename;
            }
        } catch (\Exception $e) {
            Log::error('Error duplicating image', [
                'source' => $sourcePath,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Show import page.
     */
    public function import()
    {
        $bankSoals = BankSoal::active()->get();
        return view('features.naskah.soal.import', compact('bankSoals'));
    }

    /**
     * Preview soal.
     */
    public function preview(Soal $soal)
    {
        return view('features.naskah.soal.preview', compact('soal'));
    }

    /**
     * Export soal as Word document.
     */
    public function export(Request $request, $id = null)
    {
        try {
            if ($id) {
                // Export single soal
                $soal = Soal::with('bankSoal')->findOrFail($id);
                $filename = 'Soal_' . $soal->nomor_soal . '_' . \Illuminate\Support\Str::slug($soal->bankSoal->judul);

                // Generate the Word document
                $filePath = $this->generateWordDocument($soal);

                return response()->download($filePath, $filename . '.docx')
                    ->deleteFileAfterSend(true);
            } else {
                // Export multiple soals
                $bankSoalId = $request->input('bank_soal_id');
                if (!$bankSoalId) {
                    return redirect()->back()->with('error', 'Bank soal harus dipilih untuk ekspor');
                }

                $bankSoal = BankSoal::findOrFail($bankSoalId);
                $soals = $bankSoal->soals()->orderBy('nomor_soal')->get();

                if ($soals->isEmpty()) {
                    return redirect()->back()->with('error', 'Bank soal tidak memiliki soal untuk diekspor');
                }

                $filename = 'Bank_Soal_' . \Illuminate\Support\Str::slug($bankSoal->judul);

                // Generate the Word document containing all soals
                $filePath = $this->generateWordDocumentForMultipleSoals($soals, $bankSoal);

                return response()->download($filePath, $filename . '.docx')
                    ->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting soal', [
                'soal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengekspor soal: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for a single soal
     * 
     * @param Soal $soal
     * @return string File path to the generated document
     */
    private function generateWordDocument(Soal $soal)
    {
        // TODO: Implement actual Word document generation
        // This is a placeholder implementation

        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $filePath = $tempPath . '/export_soal_' . $soal->id . '_' . time() . '.docx';

        // Here you would use a library like PHPWord to generate the document
        // For now, we'll just create a simple file as a placeholder
        file_put_contents($filePath, 'Placeholder for soal export');

        return $filePath;
    }

    /**
     * Generate Word document for multiple soals
     * 
     * @param \Illuminate\Database\Eloquent\Collection $soals
     * @param BankSoal $bankSoal
     * @return string File path to the generated document
     */
    private function generateWordDocumentForMultipleSoals($soals, BankSoal $bankSoal)
    {
        // TODO: Implement actual Word document generation
        // This is a placeholder implementation

        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $filePath = $tempPath . '/export_bank_soal_' . $bankSoal->id . '_' . time() . '.docx';

        // Here you would use a library like PHPWord to generate the document
        // For now, we'll just create a simple file as a placeholder
        file_put_contents($filePath, 'Placeholder for bank soal export with ' . $soals->count() . ' soals');

        return $filePath;
    }
}
