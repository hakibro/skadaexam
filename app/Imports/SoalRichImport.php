<?php

namespace App\Imports;

use App\Models\BankSoal;
use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalRichImport implements ToCollection, WithHeadingRow
{
    private array $results = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
        'timestamp' => null,
        'parser' => 'excel_rich',
    ];

    public function __construct(private BankSoal $bankSoal)
    {
        $this->results['timestamp'] = now()->toDateTimeString();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            try {
                $nomor = (int) $this->value($row, ['nomor_soal', 'nomor', 'no']);
                $pertanyaan = trim((string) $this->value($row, ['pertanyaan', 'soal']));

                if ($nomor <= 0 || $pertanyaan === '') {
                    $this->results['skipped']++;
                    continue;
                }

                $tipe = $this->normalizeType($this->value($row, ['tipe_soal', 'tipe'], $this->bankSoal->tipe_soal_default));
                $data = [
                    'bank_soal_id' => $this->bankSoal->id,
                    'nomor_soal' => $nomor,
                    'pertanyaan' => $pertanyaan,
                    'tipe_pertanyaan' => 'teks',
                    'tipe_soal' => $tipe,
                    'kunci_jawaban' => trim((string) $this->value($row, ['kunci_jawaban', 'kunci'])),
                    'pembahasan_teks' => $this->nullableString($this->value($row, ['pembahasan', 'pembahasan_teks'])),
                    'pembahasan_tipe' => 'teks',
                    'bobot' => (float) ($this->value($row, ['bobot'], 1) ?: 1),
                    'kategori' => $this->nullableString($this->value($row, ['kategori'], 'umum')) ?: 'umum',
                    'display_settings' => [],
                ];

                foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                    $data["pilihan_{$option}_teks"] = $this->nullableString($this->value($row, ["opsi_{$option}", "pilihan_{$option}", strtoupper($option)]));
                    $data["pilihan_{$option}_tipe"] = 'teks';
                }

                $this->applyTypeSpecificData($data, $row);
                $this->validateRow($data, $line);

                $existing = Soal::where('bank_soal_id', $this->bankSoal->id)
                    ->where('nomor_soal', $nomor)
                    ->first();

                if ($existing) {
                    $existing->update($data);
                    $this->results['updated']++;
                } else {
                    Soal::create($data);
                    $this->results['imported']++;
                }
            } catch (\Throwable $e) {
                $this->results['errors'][] = "Baris {$line}: " . $e->getMessage();
            }
        }

        $this->bankSoal->updateTotalSoal();
    }

    public function results(): array
    {
        return $this->results;
    }

    private function applyTypeSpecificData(array &$data, $row): void
    {
        if ($data['tipe_soal'] === 'benar_salah') {
            $data['pilihan_a_teks'] = $data['pilihan_a_teks'] ?: 'Benar';
            $data['pilihan_b_teks'] = $data['pilihan_b_teks'] ?: 'Salah';
            $data['pilihan_c_teks'] = null;
            $data['pilihan_d_teks'] = null;
            $data['pilihan_e_teks'] = null;
            $data['kunci_jawaban'] = strtoupper($data['kunci_jawaban'] ?: 'A');
        }

        if ($data['tipe_soal'] === 'pilihan_kompleks') {
            $data['kunci_jawaban'] = collect(preg_split('/[,;|]/', strtoupper($data['kunci_jawaban'])))
                ->map(fn($item) => trim($item))
                ->filter(fn($item) => preg_match('/^[A-E]$/', $item))
                ->unique()
                ->values()
                ->implode(',');
        }

        if ($data['tipe_soal'] === 'teks_rumpang') {
            $answers = $this->extractClozeAnswers($data['pertanyaan'], $data['kunci_jawaban']);
            if (!empty($answers)) {
                $data['display_settings']['cloze'] = ['answers' => $answers];
                $data['kunci_jawaban'] = json_encode([
                    'type' => 'teks_rumpang',
                    'data' => ['answers' => $answers],
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        if ($data['tipe_soal'] === 'menjodohkan') {
            $pairs = $this->matchingPairs($row);
            $data['display_settings']['interactive'] = ['pairs' => $pairs];
            $data['kunci_jawaban'] = json_encode(['type' => 'menjodohkan', 'data' => ['pairs' => $pairs]], JSON_UNESCAPED_UNICODE);
        }

        if ($data['tipe_soal'] === 'mengurutkan') {
            $items = $this->listValues($this->value($row, ['interactive_items', 'items', 'urutan'], $data['kunci_jawaban']));
            $data['display_settings']['interactive'] = ['items' => $items];
            $data['kunci_jawaban'] = json_encode(['type' => 'mengurutkan', 'data' => ['items' => $items]], JSON_UNESCAPED_UNICODE);
        }

        if ($data['tipe_soal'] === 'drag_drop') {
            $items = $this->listValues($this->value($row, ['interactive_items', 'interactive_drag_items', 'items']));
            $zones = $this->listValues($this->value($row, ['interactive_zones', 'zones', 'area']));
            $data['display_settings']['interactive'] = ['items' => $items, 'zones' => $zones];
            $data['kunci_jawaban'] = json_encode(['type' => 'drag_drop', 'data' => ['items' => $items, 'zones' => $zones]], JSON_UNESCAPED_UNICODE);
        }

        if ($data['tipe_soal'] === 'listening') {
            $audio = $this->nullableString($this->value($row, ['audio_path', 'audio_file', 'audio']));
            if ($audio) {
                $filename = basename($audio);
                if (Storage::disk('public')->exists('soal/audio/' . $filename)) {
                    $data['display_settings']['audio'] = $filename;
                }
            }
        }
    }

    private function validateRow(array $data, int $line): void
    {
        if (!array_key_exists($data['tipe_soal'], Soal::QUESTION_TYPES)) {
            throw new \InvalidArgumentException('Tipe soal tidak valid.');
        }

        if (in_array($data['tipe_soal'], array_merge(Soal::OPTION_BASED_TYPES, ['isian_singkat', 'teks_rumpang']), true) && $data['kunci_jawaban'] === '') {
            throw new \InvalidArgumentException('Kunci jawaban wajib diisi.');
        }

        if ($data['tipe_soal'] === 'benar_salah' && !in_array($data['kunci_jawaban'], ['A', 'B'], true)) {
            throw new \InvalidArgumentException('Kunci benar/salah hanya boleh A atau B.');
        }

        if ($data['tipe_soal'] === 'listening' && empty(data_get($data, 'display_settings.audio'))) {
            throw new \InvalidArgumentException('Audio listening tidak ditemukan di storage/app/public/soal/audio.');
        }

        if ($data['tipe_soal'] === 'menjodohkan' && count(data_get($data, 'display_settings.interactive.pairs', [])) < 2) {
            throw new \InvalidArgumentException('Menjodohkan minimal 2 pasangan.');
        }

        if ($data['tipe_soal'] === 'mengurutkan' && count(data_get($data, 'display_settings.interactive.items', [])) < 2) {
            throw new \InvalidArgumentException('Mengurutkan minimal 2 item.');
        }

        if ($data['tipe_soal'] === 'drag_drop') {
            $items = data_get($data, 'display_settings.interactive.items', []);
            $zones = data_get($data, 'display_settings.interactive.zones', []);
            if (count($items) < 2 || count($items) !== count($zones)) {
                throw new \InvalidArgumentException('Drag-drop minimal 2 item dan jumlah item harus sama dengan area.');
            }
        }
    }

    private function matchingPairs($row): array
    {
        $left = $this->listValues($this->value($row, ['interactive_left', 'left', 'kiri']));
        $right = $this->listValues($this->value($row, ['interactive_right', 'right', 'kanan']));

        return collect($left)
            ->map(fn($value, $index) => ['left' => $value, 'right' => $right[$index] ?? ''])
            ->filter(fn($pair) => $pair['left'] !== '' && $pair['right'] !== '')
            ->values()
            ->all();
    }

    private function extractClozeAnswers(string $question, string $key): array
    {
        preg_match_all('/\[\[(.+?)\]\]/', $question, $matches);
        $answers = $matches[1] ?? [];

        if (empty($answers) && $key !== '') {
            $answers = $this->listValues($key);
        }

        return collect($answers)
            ->map(fn($answer) => trim((string) $answer))
            ->filter()
            ->values()
            ->all();
    }

    private function listValues($value): array
    {
        if (is_array($value)) {
            return collect($value)->map(fn($item) => trim((string) $item))->filter()->values()->all();
        }

        return collect(preg_split('/\s*[|;]\s*/', (string) $value))
            ->map(fn($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeType($type): string
    {
        $type = strtolower(trim((string) $type));
        $aliases = [
            'pg' => 'pilihan_ganda',
            'pilihan ganda' => 'pilihan_ganda',
            'multiple_choice' => 'pilihan_ganda',
            'multiple_response' => 'pilihan_kompleks',
            'pilihan kompleks' => 'pilihan_kompleks',
            'true_false' => 'benar_salah',
            'benar salah' => 'benar_salah',
            'short_answer' => 'isian_singkat',
            'isian' => 'isian_singkat',
            'fill_blank' => 'teks_rumpang',
            'rumpang' => 'teks_rumpang',
            'matching' => 'menjodohkan',
            'ordering' => 'mengurutkan',
            'dragdrop' => 'drag_drop',
            'drag and drop' => 'drag_drop',
        ];

        return $aliases[$type] ?? $type ?: 'pilihan_ganda';
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

    private function nullableString($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
