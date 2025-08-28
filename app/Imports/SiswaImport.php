<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    protected $results = [
        'total_rows' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'errors' => [],
        'created' => [],
        'updated' => []
    ];

    /**
     * Process the collection of rows
     */
    public function collection(Collection $rows)
    {
        $this->results['total_rows'] = $rows->count();

        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row, $index + 2); // +2 because of header and 0-based index
            } catch (\Exception $e) {
                $this->results['error_count']++;
                $this->results['errors'][] = [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];

                Log::error('Excel import row error', [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ]);
            }
        }
    }

    /**
     * Process individual row
     */
    protected function processRow(Collection $row, $rowNumber)
    {
        // Clean and validate data
        $data = $this->cleanRowData($row);

        if (!$this->validateRowData($data, $rowNumber)) {
            return;
        }

        // Check if siswa exists
        $siswa = Siswa::where('idyayasan', $data['idyayasan'])->first();

        if ($siswa) {
            // Update existing siswa
            $this->updateSiswa($siswa, $data, $rowNumber);
        } else {
            // Create new siswa
            $this->createSiswa($data, $rowNumber);
        }
    }

    /**
     * Clean row data
     */
    protected function cleanRowData(Collection $row)
    {
        return [
            'idyayasan' => trim((string) $row->get('idyayasan', $row->get('id_yayasan', ''))),
            'nama' => trim((string) $row->get('nama', '')),

            'kelas' => trim((string) $row->get('kelas', '')),

            'rekomendasi' => strtolower(trim((string) $row->get('rekomendasi', 'tidak'))),
            'catatan_rekomendasi' => trim((string) $row->get('catatan_rekomendasi', $row->get('catatan', ''))),
            'email' => trim((string) $row->get('email', ''))
        ];
    }

    /**
     * Validate row data
     */
    protected function validateRowData($data, $rowNumber)
    {
        $validator = Validator::make($data, [
            'idyayasan' => 'required|string|max:20',
            'nama' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'rekomendasi' => 'required|in:ya,tidak,yes,no,1,0',
            'catatan_rekomendasi' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255'
        ]);

        if ($validator->fails()) {
            $this->results['error_count']++;
            $this->results['errors'][] = [
                'row' => $rowNumber,
                'error' => 'Validation failed: ' . implode(', ', $validator->errors()->all()),
                'data' => $data
            ];
            return false;
        }

        return true;
    }

    /**
     * Create new siswa
     */
    protected function createSiswa($data, $rowNumber)
    {
        // Normalize rekomendasi
        $rekomendasi = $this->normalizeRekomendasi($data['rekomendasi']);

        // Generate email if not provided
        $email = $data['email'] ?: Siswa::generateEmailFromNama($data['nama'] ?: $data['idyayasan']);

        $siswaData = [
            'idyayasan' => $data['idyayasan'],
            'nama' => $data['nama'] ?: null,
            'email' => $email,
            'password' => 'password', // Default password
            'kelas' => $data['kelas'] ?: null,
            'rekomendasi' => $rekomendasi,
            'catatan_rekomendasi' => $data['catatan_rekomendasi'] ?: null,
            'sync_status' => 'pending',
            'user_id' => auth()->id()
        ];

        $siswa = Siswa::create($siswaData);

        $this->results['success_count']++;
        $this->results['created'][] = [
            'row' => $rowNumber,
            'idyayasan' => $siswa->idyayasan,
            'nama' => $siswa->nama
        ];

        Log::info('Siswa created from import', [
            'row' => $rowNumber,
            'idyayasan' => $siswa->idyayasan
        ]);
    }

    /**
     * Update existing siswa
     */
    protected function updateSiswa($siswa, $data, $rowNumber)
    {
        $rekomendasi = $this->normalizeRekomendasi($data['rekomendasi']);
        $email = $data['email'] ?: Siswa::generateEmailFromNama($data['nama'] ?: $siswa->nama);

        $siswa->update([
            'nama' => $data['nama'] ?: $siswa->nama,
            'email' => $email,
            'kelas' => $data['kelas'] ?: $siswa->kelas,
            'rekomendasi' => $rekomendasi,
            'catatan_rekomendasi' => $data['catatan_rekomendasi'] ?: $siswa->catatan_rekomendasi,
        ]);

        $this->results['success_count']++;
        $this->results['updated'][] = [
            'row' => $rowNumber,
            'idyayasan' => $siswa->idyayasan,
            'nama' => $siswa->nama
        ];

        Log::info('Siswa updated from import', [
            'row' => $rowNumber,
            'idyayasan' => $siswa->idyayasan
        ]);
    }

    /**
     * Normalize rekomendasi values
     */
    protected function normalizeRekomendasi($value)
    {
        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['ya', 'yes', '1', 'true'])) {
            return 'ya';
        }

        return 'tidak';
    }

    /**
     * Get import results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Process Excel import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

        try {
            $import = new SiswaImport();
            Excel::import($import, $request->file('file'));

            $results = $import->getResults();

            // Store results in session for display
            session(['import_results' => $results]);

            Log::info('Import completed', [
                'results' => $results,
                'file' => $request->file('file')->getClientOriginalName()
            ]);

            return redirect()->route('data.siswa.import-results')
                ->with('success', "Import completed: {$results['success_count']} success, {$results['error_count']} errors");
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());

            // Store error results in session
            session(['import_results' => [
                'total_rows' => 0,
                'success_count' => 0,
                'error_count' => 1,
                'errors' => [
                    [
                        'row' => 'N/A',
                        'message' => $e->getMessage(),
                        'data' => [],
                        'details' => []
                    ]
                ],
                'created' => [],
                'updated' => []
            ]]);

            return redirect()->route('data.siswa.import-results')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
