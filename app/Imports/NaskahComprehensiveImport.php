<?php

namespace App\Imports;

use App\Models\BankSoal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PaketUjian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class NaskahComprehensiveImport implements ToCollection, WithHeadingRow
{
    private array $results = [
        'mapel_created' => 0,
        'mapel_updated' => 0,
        'bank_created' => 0,
        'bank_updated' => 0,
        'jadwal_created' => 0,
        'jadwal_updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct(private int $tahunAjaranId, private ?int $paketUjianId = null)
    {
    }

    public function collection(Collection $rows)
    {
        $paket = $this->paketUjianId
            ? PaketUjian::where('tahun_ajaran_id', $this->tahunAjaranId)->find($this->paketUjianId)
            : PaketUjian::where('tahun_ajaran_id', $this->tahunAjaranId)->where('status', 'aktif')->first();

        foreach ($rows as $index => $row) {
            try {
                $namaMapel = trim((string) $this->value($row, ['nama_mapel', 'mata_pelajaran']));
                $tingkat = strtoupper(trim((string) $this->value($row, ['tingkat', 'kelas_tingkat'])));

                if ($namaMapel === '' || $tingkat === '') {
                    $this->results['skipped']++;
                    continue;
                }

                $jurusan = $this->value($row, ['jurusan']);
                $mapel = Mapel::withTrashed()
                    ->where('tahun_ajaran_id', $this->tahunAjaranId)
                    ->where('nama_mapel', $namaMapel)
                    ->where('tingkat', $tingkat)
                    ->where(function ($q) use ($jurusan) {
                        $q->where('jurusan', $jurusan)->orWhereNull('jurusan');
                    })
                    ->first();

                if ($mapel) {
                    $mapel->restore();
                    $mapel->update([
                        'jurusan' => $jurusan,
                        'status' => 'aktif',
                    ]);
                    $this->results['mapel_updated']++;
                } else {
                    $mapel = Mapel::create([
                        'tahun_ajaran_id' => $this->tahunAjaranId,
                        'kode_mapel' => Mapel::generateKode($namaMapel, $tingkat, $this->tahunAjaranId),
                        'nama_mapel' => $namaMapel,
                        'tingkat' => $tingkat,
                        'jurusan' => $jurusan,
                        'status' => 'aktif',
                    ]);
                    $this->results['mapel_created']++;
                }

                $judulBank = $this->value($row, ['judul_bank_soal', 'bank_soal', 'judul_bank']) ?: $namaMapel . ' ' . $tingkat;
                $bank = BankSoal::where('tahun_ajaran_id', $this->tahunAjaranId)
                    ->where('mapel_id', $mapel->id)
                    ->where('judul', $judulBank)
                    ->first();

                if ($bank) {
                    $bank->update([
                        'tingkat' => $mapel->tingkat,
                        'status' => 'aktif',
                        'paket_ujian_id' => $paket?->id,
                    ]);
                    $this->results['bank_updated']++;
                } else {
                    $bank = BankSoal::create([
                        'tahun_ajaran_id' => $this->tahunAjaranId,
                        'paket_ujian_id' => $paket?->id,
                        'kode_bank' => $this->generateBankCode(),
                        'judul' => $judulBank,
                        'deskripsi' => $this->value($row, ['deskripsi_bank', 'deskripsi']),
                        'tingkat' => $mapel->tingkat,
                        'status' => 'aktif',
                        'total_soal' => 0,
                        'created_by' => Auth::id(),
                        'mapel_id' => $mapel->id,
                    ]);
                    $this->results['bank_created']++;
                }

                $tanggal = $this->parseDate($this->value($row, ['tanggal', 'tanggal_ujian']));
                if (!$tanggal) {
                    continue;
                }

                $judulJadwal = $this->value($row, ['judul_ujian', 'nama_ujian']) ?: $mapel->nama_mapel;
                $jadwal = JadwalUjian::where('tahun_ajaran_id', $this->tahunAjaranId)
                    ->where('paket_ujian_id', $paket?->id)
                    ->where('mapel_id', $mapel->id)
                    ->whereDate('tanggal', $tanggal->toDateString())
                    ->first();

                $payload = [
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'paket_ujian_id' => $paket?->id,
                    'judul' => $judulJadwal,
                    'mapel_id' => $mapel->id,
                    'tanggal' => $tanggal,
                    'durasi_menit' => (int) ($this->value($row, ['durasi_menit', 'durasi']) ?: 30),
                    'status' => 'aktif',
                    'jumlah_soal' => $bank->total_soal ?? 0,
                    'kelas_target' => $this->kelasTarget($row, $mapel),
                    'bank_soal_id' => $bank->id,
                    'created_by' => Auth::id(),
                    'kode_ujian' => $this->generateExamCode(),
                    'acak_soal' => true,
                    'acak_jawaban' => true,
                    'auto_assign_sesi' => false,
                    'auto_enroll' => false,
                ];

                if ($jadwal) {
                    unset($payload['kode_ujian'], $payload['created_by']);
                    $jadwal->update($payload);
                    $this->results['jadwal_updated']++;
                } else {
                    JadwalUjian::create($payload);
                    $this->results['jadwal_created']++;
                }
            } catch (\Throwable $e) {
                $this->results['errors'][] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }
    }

    public function results(): array
    {
        return $this->results;
    }

    private function value($row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    private function parseDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value));
        }

        $value = trim((string) $value);
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'd.m.Y',
            'd/m/y',
            'd-m-y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
            'd/m/Y H:i',
            'd-m-Y H:i',
            'Y-m-d H:i',
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (\Throwable) {
                // Try next known format.
            }
        }

        return Carbon::parse($value)->startOfDay();
    }

    private function kelasTarget($row, Mapel $mapel): array
    {
        $raw = $this->value($row, ['kelas_target', 'kelas']);
        if ($raw) {
            $names = collect(explode(',', (string) $raw))->map(fn($item) => trim($item))->filter();

            return Kelas::where('tahun_ajaran_id', $this->tahunAjaranId)
                ->whereIn('nama_kelas', $names)
                ->pluck('id')
                ->all();
        }

        return Kelas::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->where('tingkat', $mapel->tingkat)
            ->when($mapel->jurusan, fn($q) => $q->where(fn($sub) => $sub->where('jurusan', $mapel->jurusan)->orWhere('jurusan', 'UMUM')))
            ->pluck('id')
            ->all();
    }

    private function generateBankCode(): string
    {
        do {
            $code = 'BS' . now()->format('Ym') . random_int(1000, 9999);
        } while (BankSoal::where('kode_bank', $code)->exists());

        return $code;
    }

    private function generateExamCode(): string
    {
        do {
            $code = 'UJ' . strtoupper(Str::random(6));
        } while (JadwalUjian::where('kode_ujian', $code)->exists());

        return $code;
    }
}
