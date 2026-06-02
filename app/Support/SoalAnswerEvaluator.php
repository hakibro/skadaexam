<?php

namespace App\Support;

class SoalAnswerEvaluator
{
    public static function evaluate($soal, mixed $jawaban, ?string $kunciOverride = null): array
    {
        $answer = is_array($jawaban) ? $jawaban : (string) ($jawaban ?? '');

        if (!is_array($answer) && trim($answer) === '') {
            return self::result('kosong', 0, []);
        }

        $type = $soal->tipe_soal ?? 'pilihan_ganda';
        $key = $kunciOverride ?? (string) ($soal->kunci_jawaban ?? '');

        return match ($type) {
            'pilihan_kompleks' => self::evaluateMultipleResponse($answer, $key),
            'isian_singkat' => self::evaluateShortAnswer($answer, $key),
            'teks_rumpang' => self::evaluateCloze($answer, $key),
            'menjodohkan' => self::evaluateMatching($answer, $key),
            'mengurutkan' => self::evaluateOrdering($answer, $key),
            'drag_drop' => self::evaluateDragDrop($answer, $key),
            default => self::evaluateSingleAnswer($answer, $key),
        };
    }

    public static function isCorrect($soal, mixed $jawaban, ?string $kunciOverride = null): bool
    {
        return self::evaluate($soal, $jawaban, $kunciOverride)['status'] === 'benar';
    }

    private static function evaluateSingleAnswer(mixed $answer, string $key): array
    {
        $correct = strtoupper(trim((string) $answer)) === strtoupper(trim($key));

        return self::result($correct ? 'benar' : 'salah', $correct ? 1 : 0, [
            'expected' => trim($key),
            'actual' => trim((string) $answer),
        ]);
    }

    private static function evaluateMultipleResponse(mixed $answer, string $key): array
    {
        $expected = self::letters($key);
        $actual = self::letters(is_array($answer) ? implode(',', $answer) : (string) $answer);

        if (empty($expected)) {
            return self::result('salah', 0, []);
        }

        $correctSelected = array_values(array_intersect($actual, $expected));
        $wrongSelected = array_values(array_diff($actual, $expected));
        $score = count($correctSelected) / count($expected);
        $status = $score >= 1 && empty($wrongSelected) ? 'benar' : ($score > 0 ? 'parsial' : 'salah');

        return self::result($status, $score, [
            'expected' => $expected,
            'actual' => $actual,
            'correct_selected' => $correctSelected,
            'wrong_selected' => $wrongSelected,
            'missing' => array_values(array_diff($expected, $actual)),
        ]);
    }

    private static function evaluateShortAnswer(mixed $answer, string $key): array
    {
        $expected = self::keywordAnswers($key);
        $actual = mb_strtolower(trim((string) $answer));
        $correct = in_array($actual, $expected, true);

        return self::result($correct ? 'benar' : 'salah', $correct ? 1 : 0, [
            'expected' => $expected,
            'actual' => trim((string) $answer),
        ]);
    }

    private static function evaluateCloze(mixed $answer, string $key): array
    {
        $expected = self::clozeAnswers($key);

        if (empty($expected)) {
            $actual = is_array($answer) ? $answer : (json_decode((string) $answer, true) ?: null);

            if (is_array($actual)) {
                return self::evaluateShortAnswer($actual[0] ?? '', $key);
            }

            return self::evaluateShortAnswer($answer, $key);
        }

        $actual = is_array($answer) ? $answer : (json_decode((string) $answer, true) ?: []);
        $details = [];
        $correct = 0;

        foreach ($expected as $index => $acceptedAnswers) {
            $actualValue = mb_strtolower(trim((string) ($actual[$index] ?? '')));
            $isCorrect = in_array($actualValue, $acceptedAnswers, true);
            $correct += $isCorrect ? 1 : 0;
            $details[] = [
                'index' => $index,
                'expected' => $acceptedAnswers,
                'actual' => $actual[$index] ?? '',
                'is_correct' => $isCorrect,
            ];
        }

        return self::fractionResult($correct, count($expected), ['items' => $details]);
    }

    private static function evaluateMatching(mixed $answer, string $key): array
    {
        $expected = json_decode($key, true);
        $actual = is_array($answer) ? $answer : (json_decode((string) $answer, true) ?: []);
        $pairs = data_get($expected, 'data.pairs', []);
        $details = [];
        $correct = 0;

        foreach ($pairs as $pair) {
            $left = trim((string) ($pair['left'] ?? ''));
            $right = trim((string) ($pair['right'] ?? ''));
            $actualRight = trim((string) ($actual[$left] ?? ''));
            $isCorrect = $left !== '' && mb_strtolower($actualRight) === mb_strtolower($right);
            $correct += $isCorrect ? 1 : 0;
            $details[] = compact('left', 'right', 'actualRight', 'isCorrect');
        }

        return self::fractionResult($correct, count($pairs), ['pairs' => $details]);
    }

    private static function evaluateOrdering(mixed $answer, string $key): array
    {
        $expected = json_decode($key, true);
        $items = array_values(array_filter(data_get($expected, 'data.items', [])));
        $actual = is_array($answer) ? $answer : (json_decode((string) $answer, true) ?: []);
        $details = [];
        $correct = 0;

        foreach ($items as $index => $item) {
            $actualValue = (string) ($actual[$index] ?? '');
            $isCorrect = mb_strtolower(trim($actualValue)) === mb_strtolower(trim((string) $item));
            $correct += $isCorrect ? 1 : 0;
            $details[] = compact('index', 'item', 'actualValue', 'isCorrect');
        }

        return self::fractionResult($correct, count($items), ['items' => $details]);
    }

    private static function evaluateDragDrop(mixed $answer, string $key): array
    {
        $expected = json_decode($key, true);
        $items = data_get($expected, 'data.items', []);
        $zones = data_get($expected, 'data.zones', []);
        $actual = is_array($answer) ? $answer : (json_decode((string) $answer, true) ?: []);
        $details = [];
        $correct = 0;

        foreach ($items as $index => $item) {
            $zone = (string) ($zones[$index] ?? '');
            $actualZone = (string) ($actual[$item] ?? '');
            $isCorrect = $zone !== '' && mb_strtolower(trim($actualZone)) === mb_strtolower(trim($zone));
            $correct += $isCorrect ? 1 : 0;
            $details[] = compact('item', 'zone', 'actualZone', 'isCorrect');
        }

        return self::fractionResult($correct, count($items), ['items' => $details]);
    }

    private static function fractionResult(int $correct, int $total, array $details): array
    {
        if ($total <= 0) {
            return self::result('salah', 0, $details);
        }

        $score = $correct / $total;
        $status = $score >= 1 ? 'benar' : ($score > 0 ? 'parsial' : 'salah');

        return self::result($status, $score, array_merge($details, [
            'correct_count' => $correct,
            'total_count' => $total,
        ]));
    }

    private static function result(string $status, float $score, array $details): array
    {
        return [
            'status' => $status,
            'is_correct' => $status === 'benar',
            'score_fraction' => max(0, min(1, $score)),
            'details' => $details,
        ];
    }

    private static function letters(string $value): array
    {
        return collect(explode(',', strtoupper($value)))
            ->map(fn($letter) => trim($letter))
            ->filter(fn($letter) => preg_match('/^[A-E]$/', $letter))
            ->values()
            ->all();
    }

    private static function keywordAnswers(string $key): array
    {
        return collect(explode('|', $key))
            ->map(fn($value) => mb_strtolower(trim($value)))
            ->filter()
            ->values()
            ->all();
    }

    private static function clozeAnswers(string $key): array
    {
        $decoded = json_decode($key, true);
        $answers = data_get($decoded, 'data.answers');

        if (!is_array($answers)) {
            return [];
        }

        return collect($answers)
            ->map(fn($answer) => self::keywordAnswers(is_array($answer) ? implode('|', $answer) : (string) $answer))
            ->filter(fn($answer) => !empty($answer))
            ->values()
            ->all();
    }
}
