<?php

namespace App\Imports;

use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ComprehensiveRuanganImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $results = [
        'ruangan_created' => 0,
        'ruangan_updated' => 0,
        'sesi_created' => 0,
        'sesi_updated' => 0,
        'siswa_assigned' => 0,
    ];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip rows with empty kode_ruangan
            if (empty($row['kode_ruangan'])) {
                continue;
            }

            // Step 1: Process Ruangan
            $ruangan = $this->processRuangan($row);

            // Only continue if we have a valid room
            if (!$ruangan) {
                continue;
            }

            // Step 2: Process SesiRuangan if kode_sesi is provided
            if (!empty($row['kode_sesi'])) {
                $sesi = $this->processSesiRuangan($row, $ruangan);

                // Step 3: Process Student Assignment if student idyayasan is provided
                if ($sesi && !empty($row['idyayasan'])) {
                    $this->processSiswaAssignment($row, $sesi);
                }
            }
        }
    }

    /**
     * Process ruangan data
     */
    protected function processRuangan($row)
    {
        try {
            // Check if ruangan exists by kode_ruangan
            $ruangan = Ruangan::where('kode_ruangan', $row['kode_ruangan'])->first();

            if ($ruangan) {
                // Update existing ruangan
                $ruangan->update([
                    'nama_ruangan' => $row['nama_ruangan'] ?? $ruangan->nama_ruangan,
                    'kapasitas' => $row['kapasitas_ruangan'] ?? $ruangan->kapasitas,
                    'lokasi' => $row['lokasi_ruangan'] ?? $ruangan->lokasi,
                    'status' => $row['status_ruangan'] ?? $ruangan->status,
                    // Only update other fields if provided
                ]);
                $this->results['ruangan_updated']++;
            } else {
                // Create new ruangan
                $ruangan = Ruangan::create([
                    'kode_ruangan' => $row['kode_ruangan'],
                    'nama_ruangan' => $row['nama_ruangan'] ?? 'Ruangan ' . $row['kode_ruangan'],
                    'kapasitas' => $row['kapasitas_ruangan'] ?? 30,
                    'lokasi' => $row['lokasi_ruangan'] ?? null,
                    'status' => $row['status_ruangan'] ?? 'aktif',
                    'jenis_ruangan' => $row['jenis_ruangan'] ?? 'kelas',
                ]);
                $this->results['ruangan_created']++;
            }

            return $ruangan;
        } catch (\Exception $e) {
            Log::error("Error processing ruangan {$row['kode_ruangan']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process sesi ruangan data
     */
    protected function processSesiRuangan($row, $ruangan)
    {
        try {
            // Check if sesi exists by kode_sesi
            $sesi = SesiRuangan::where('kode_sesi', $row['kode_sesi'])->first();

            // Validate time format
            $waktuMulai = $this->formatTime($row['waktu_mulai_sesi'] ?? '08:00');
            $waktuSelesai = $this->formatTime($row['waktu_selesai_sesi'] ?? '10:00');

            if ($sesi) {
                // Update existing sesi
                $sesi->update([
                    'nama_sesi' => $row['nama_sesi'] ?? $sesi->nama_sesi,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'status' => $row['status_sesi'] ?? $sesi->status,
                    'ruangan_id' => $ruangan->id, // Link to the correct ruangan
                ]);
                $this->results['sesi_updated']++;
            } else {
                // Create new sesi
                $sesi = new SesiRuangan([
                    'kode_sesi' => $row['kode_sesi'],
                    'nama_sesi' => $row['nama_sesi'] ?? 'Sesi ' . $row['kode_sesi'],
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'status' => $row['status_sesi'] ?? 'belum_mulai',
                    'ruangan_id' => $ruangan->id,
                    // Other fields will use defaults
                ]);

                $sesi->save();
                $this->results['sesi_created']++;
            }

            return $sesi;
        } catch (\Exception $e) {
            Log::error("Error processing sesi {$row['kode_sesi']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process siswa assignment to session
     */
    protected function processSiswaAssignment($row, $sesi)
    {
        try {
            // Find the student by idyayasan
            $siswa = Siswa::where('idyayasan', $row['idyayasan'])->first();

            // If student doesn't exist, log and skip
            if (!$siswa) {
                Log::warning("Student with idyayasan {$row['idyayasan']} not found, skipping assignment");
                return null;
            }

            // Check if student is already assigned to this session
            $existing = SesiRuanganSiswa::where('sesi_ruangan_id', $sesi->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if (!$existing) {
                // Only assign if not already assigned
                SesiRuanganSiswa::create([
                    'sesi_ruangan_id' => $sesi->id,
                    'siswa_id' => $siswa->id,
                    'status' => 'tidak_hadir' // Default status
                ]);
                $this->results['siswa_assigned']++;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error assigning student {$row['idyayasan']} to session: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Format time string to HH:MM:SS format
     */
    protected function formatTime($timeStr)
    {
        try {
            // Already in time format
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeStr)) {
                // Add seconds if not included
                if (substr_count($timeStr, ':') === 1) {
                    $timeStr .= ':00';
                }
                return $timeStr;
            }

            // Try to parse as a datetime string
            $time = Carbon::parse($timeStr)->format('H:i:s');
            return $time;
        } catch (\Exception $e) {
            // Default to 08:00:00 if parsing fails
            return '08:00:00';
        }
    }

    /**
     * Get import results summary
     */
    public function getImportResults()
    {
        return $this->results;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.kode_ruangan' => 'required|string|max:20',
            '*.nama_ruangan' => 'nullable|string|max:191',
            '*.kapasitas_ruangan' => 'nullable|integer|min:1|max:1000',
            '*.lokasi_ruangan' => 'nullable|string|max:191',
            '*.status_ruangan' => 'nullable|string|in:aktif,perbaikan,tidak_aktif',

            '*.kode_sesi' => 'nullable|string|max:20',
            '*.nama_sesi' => 'nullable|string|max:191',
            '*.waktu_mulai_sesi' => 'nullable',
            '*.waktu_selesai_sesi' => 'nullable',
            '*.status_sesi' => 'nullable|string|in:belum_mulai,berlangsung,selesai,dibatalkan',

            '*.idyayasan' => 'nullable|string|max:50',
            '*.nama_siswa' => 'nullable|string|max:191',
        ];
    }
}
