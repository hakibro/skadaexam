<?php

namespace App\Imports;

use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Siswa;
use App\Services\TahunAjaranService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
    protected $tahunAjaranId;

    public function __construct(?int $tahunAjaranId = null)
    {
        $this->tahunAjaranId = $tahunAjaranId ?: app(TahunAjaranService::class)->ensureActive()->id;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Convert numeric idyayasan to string if exists
            if ($this->rowValue($row, ['idyayasan']) !== null && is_numeric($this->rowValue($row, ['idyayasan']))) {
                $row['idyayasan'] = (string) $this->rowValue($row, ['idyayasan']);
            }

            // If no kode_ruangan, skip ruangan and sesi processing but check for student assignment
            if (empty($this->rowValue($row, ['kode_ruangan']))) {
                // If we have kode_sesi, try to find existing session and assign student
                if (!empty($this->rowValue($row, ['kode_sesi'])) && !empty($this->rowValue($row, ['idyayasan']))) {
                    $sesi = SesiRuangan::where('kode_sesi', $this->rowValue($row, ['kode_sesi']))->first();
                    if ($sesi) {
                        $this->processSiswaAssignment($row, $sesi);
                    }
                }
                continue;
            }

            // Step 1: Process Ruangan
            $ruangan = $this->processRuangan($row);

            // Only continue if we have a valid room
            if (!$ruangan) {
                continue;
            }

            // Step 2: Process SesiRuangan if kode_sesi is provided
            if (!empty($this->rowValue($row, ['kode_sesi']))) {
                $sesi = $this->processSesiRuangan($row, $ruangan);

                // Step 3: Process Student Assignment if student idyayasan is provided
                if ($sesi && !empty($this->rowValue($row, ['idyayasan']))) {
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
            $rawKapasitas = $this->rowValue($row, [
                'kapasitas_ruangan',
                'kapasitas',
                'kapasitas_ruang',
                'daya_tampung',
                'jumlah_kursi',
                'capacity',
            ]);

            $kodeRuangan = $this->rowValue($row, ['kode_ruangan']);

            // Check if ruangan exists by kode_ruangan
            $ruangan = Ruangan::where('tahun_ajaran_id', $this->tahunAjaranId)
                ->where('kode_ruangan', $kodeRuangan)
                ->first();

            if ($ruangan) {
                $updateData = [
                    'nama_ruangan' => $this->rowValue($row, ['nama_ruangan'], $ruangan->nama_ruangan),
                    'lokasi' => $this->rowValue($row, ['lokasi'], $ruangan->lokasi),
                    'status' => $this->rowValue($row, ['status'], $ruangan->status),
                ];

                // Kapasitas hanya di-update kalau kolom Excel memang berisi nilai
                if ($rawKapasitas !== null && $rawKapasitas !== '') {
                    $updateData['kapasitas'] = $this->normalizeInteger($rawKapasitas, $ruangan->kapasitas ?? 30);
                }

                $ruangan->update($updateData);

                $this->results['ruangan_updated']++;
            } else {
                $kapasitas = $this->normalizeInteger($rawKapasitas, 30);

                $ruangan = Ruangan::create([
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'kode_ruangan' => $kodeRuangan,
                    'nama_ruangan' => $this->rowValue($row, ['nama_ruangan'], 'Ruangan ' . $kodeRuangan),
                    'kapasitas' => $kapasitas,
                    'lokasi' => $this->rowValue($row, ['lokasi']),
                    'status' => $this->rowValue($row, ['status'], 'aktif'),
                ]);

                $this->results['ruangan_created']++;
            }

            return $ruangan;
        } catch (\Exception $e) {
            Log::error("Error processing ruangan {$this->rowValue($row, ['kode_ruangan'], '-')}: " . $e->getMessage());
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
            $kodeSesi = $this->rowValue($row, ['kode_sesi']);
            $sesi = SesiRuangan::where('tahun_ajaran_id', $this->tahunAjaranId)
                ->where('kode_sesi', $kodeSesi)
                ->first();

            // Validate time format
            $waktuMulai = $this->formatTime($this->rowValue($row, ['waktu_mulai_sesi', 'waktu_mulai'], '00:00'));
            $waktuSelesai = $this->formatTime($this->rowValue($row, ['waktu_selesai_sesi', 'waktu_selesai'], '00:00'));

            if ($sesi) {
                // Update existing sesi
                $sesi->update([
                    'nama_sesi' => $this->rowValue($row, ['nama_sesi'], $sesi->nama_sesi),
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'status' => $this->rowValue($row, ['status_sesi'], $sesi->status),
                    'sumber' => 'sumber',
                    'ruangan_id' => $ruangan->id, // Link to the correct ruangan
                ]);
                $this->results['sesi_updated']++;
            } else {
                // Create new sesi
                $sesi = new SesiRuangan([
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'kode_sesi' => $kodeSesi,
                    'nama_sesi' => $this->rowValue($row, ['nama_sesi'], 'Sesi ' . $kodeSesi),
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'status' => $this->rowValue($row, ['status_sesi'], 'belum_mulai'),
                    'sumber' => 'sumber',
                    'ruangan_id' => $ruangan->id,
                    // Other fields will use defaults
                ]);

                $sesi->save();
                $this->results['sesi_created']++;
            }

            return $sesi;
        } catch (\Exception $e) {
            Log::error("Error processing sesi {$this->rowValue($row, ['kode_sesi'], '-')}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process siswa assignment to session
     */
    protected function processSiswaAssignment($row, $sesi)
    {
        try {
            // Convert idyayasan to string if numeric
            $rawIdyayasan = $this->rowValue($row, ['idyayasan']);
            $idyayasan = is_numeric($rawIdyayasan) ? (string) $rawIdyayasan : $rawIdyayasan;

            // Find the student by idyayasan
            $siswa = Siswa::where('idyayasan', $idyayasan)->first();

            // If student doesn't exist, log and skip
            if (!$siswa) {
                Log::warning("Student with idyayasan {$idyayasan} not found, skipping assignment");
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
                    'status_kehadiran' => 'tidak_hadir' // Default status
                ]);
                $this->results['siswa_assigned']++;
            }

            return true;
        } catch (\Exception $e) {
            $loggedIdyayasan = $this->rowValue($row, ['idyayasan'], '-');
            Log::error("Error assigning student {$loggedIdyayasan} to session: " . $e->getMessage());
            return null;
        }
    }



    private function formatTime($time)
    {
        try {
            if ($time === null || $time === '') {
                return '00:00:00';
            }

            // Case 1: langsung DateTime
            if ($time instanceof \DateTime) {
                return Carbon::instance($time)->format('H:i:s');
            }

            if (is_numeric($time)) {
                $dt = Date::excelToDateTimeObject($time);
                Log::info("Converted time", ['float' => $time, 'result' => $dt->format('H:i:s')]);
                return $dt->format('H:i:s');
            }

            // Case 3: String format
            if (is_string($time)) {
                return Carbon::parse($time)->format('H:i:s');
            }
        } catch (\Exception $e) {
            return '00:00:00';
        }

        return '00:00:00';
    }

    private function rowValue($row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    private function normalizeInteger($value, int $default): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) preg_replace('/[^0-9]/', '', (string) $value) ?: $default;
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
            // Ruangan fields - kode_ruangan nullable to allow student-only rows
            '*.kode_ruangan' => 'nullable|string|max:20',
            '*.nama_ruangan' => 'nullable|string|max:191',
            '*.kapasitas' => 'nullable',
            '*.kapasitas_ruangan' => 'nullable',
            '*.lokasi' => 'nullable|string|max:191',
            '*.status' => 'nullable|string|in:aktif,perbaikan,tidak_aktif',

            // Sesi fields
            '*.kode_sesi' => 'nullable|string|max:20',
            '*.nama_sesi' => 'nullable|string|max:191',
            '*.waktu_mulai_sesi' => 'nullable',
            '*.waktu_selesai_sesi' => 'nullable',
            '*.waktu_mulai' => 'nullable',
            '*.waktu_selesai' => 'nullable',
            '*.status_sesi' => 'nullable|string|in:belum_mulai,berlangsung,selesai,dibatalkan',

            // Siswa fields - convert to string if needed
            '*.idyayasan' => 'nullable',
            '*.nama_siswa' => 'nullable|string|max:191',
        ];
    }
}
