<?php
// filepath: app\Http\Requests\StoreSoalRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Soal;
use App\Models\BankSoal;

class StoreSoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Use your auth logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $soal = $this->route('soal');
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true) && $soal;

        return [
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'nomor_soal' => 'required|integer|min:1',
            'pertanyaan' => [
                Rule::requiredIf(fn() => in_array($this->input('tipe_pertanyaan'), ['teks', 'teks_gambar'], true)),
                'nullable',
                'string',
            ],
            'tipe_pertanyaan' => 'required|in:teks,gambar,teks_gambar',
            'tipe_soal' => ['required', Rule::in(array_keys(Soal::QUESTION_TYPES))],
            'gambar_pertanyaan' => [
                Rule::requiredIf(fn() => in_array($this->input('tipe_pertanyaan'), ['gambar', 'teks_gambar'], true)
                    && (!$isUpdate || empty($soal->gambar_pertanyaan))),
                'nullable',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
            ],

            // Pilihan jawaban (hanya divalidasi jika tipe_soal = pilihan_ganda)
            'pilihan_a_teks' => [Rule::requiredIf(fn() => $this->requiresOption('a', true)), 'nullable', 'string'],
            'pilihan_a_tipe' => [Rule::requiredIf(fn() => $this->usesOptions()), 'nullable', 'in:teks,gambar'],
            'pilihan_a_gambar' => $this->imageOptionRules('a', $soal, $isUpdate),

            'pilihan_b_teks' => [Rule::requiredIf(fn() => $this->requiresOption('b', true)), 'nullable', 'string'],
            'pilihan_b_tipe' => [Rule::requiredIf(fn() => $this->usesOptions()), 'nullable', 'in:teks,gambar'],
            'pilihan_b_gambar' => $this->imageOptionRules('b', $soal, $isUpdate),

            'pilihan_c_teks' => [Rule::requiredIf(fn() => $this->requiresOption('c')), 'nullable', 'string'],
            'pilihan_c_tipe' => [Rule::requiredIf(fn() => $this->usesOptions()), 'nullable', 'in:teks,gambar'],
            'pilihan_c_gambar' => $this->imageOptionRules('c', $soal, $isUpdate),

            'pilihan_d_teks' => [Rule::requiredIf(fn() => $this->requiresOption('d')), 'nullable', 'string'],
            'pilihan_d_tipe' => [Rule::requiredIf(fn() => $this->usesOptions()), 'nullable', 'in:teks,gambar'],
            'pilihan_d_gambar' => $this->imageOptionRules('d', $soal, $isUpdate),

            'pilihan_e_teks' => 'nullable|string',
            'pilihan_e_tipe' => 'nullable|in:teks,gambar',
            'pilihan_e_gambar' => $this->imageOptionRules('e', $soal, $isUpdate, false),

            'kunci_jawaban' => [Rule::requiredIf(fn() => in_array($this->input('tipe_soal'), Soal::OBJECTIVE_TYPES, true)), 'nullable', 'string', 'max:10000'],

            'pembahasan_teks' => 'nullable|string',
            'pembahasan_tipe' => 'nullable|in:teks,gambar,teks_gambar',
            'pembahasan_gambar' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'gambar_pembahasan' => 'nullable|string', // Untuk backward compatibility
            'soal_audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:10240',

            'bobot' => 'nullable|numeric|min:0.01|max:100.00',
            'kategori' => 'nullable|string|max:50',
            'display_settings' => 'nullable|array',
            'interactive_left' => 'nullable|array',
            'interactive_left.*' => 'nullable|string|max:500',
            'interactive_right' => 'nullable|array',
            'interactive_right.*' => 'nullable|string|max:500',
            'interactive_items' => 'nullable|array',
            'interactive_items.*' => 'nullable|string|max:500',
            'interactive_drag_items' => 'nullable|array',
            'interactive_drag_items.*' => 'nullable|string|max:500',
            'interactive_zones' => 'nullable|array',
            'interactive_zones.*' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tipe = $this->input('tipe_soal');
            $soal = $this->route('soal');

            if ($tipe === 'listening' && !$this->hasFile('soal_audio') && empty(data_get($soal?->display_settings, 'audio'))) {
                $validator->errors()->add('soal_audio', 'Audio wajib diupload untuk tipe soal Listening.');
            }

            if ($tipe === 'teks_rumpang' && !$this->hasClozeKeyPlaceholder() && empty(trim((string) $this->input('kunci_jawaban')))) {
                $validator->errors()->add('pertanyaan', 'Teks rumpang wajib memiliki placeholder [[jawaban]] atau kunci jawaban manual.');
            }

            if ($tipe === 'menjodohkan' && count(data_get($this->buildInteractiveSettings(), 'pairs', [])) < 2) {
                $validator->errors()->add('interactive_left', 'Soal menjodohkan minimal memiliki 2 pasangan lengkap.');
            }

            if ($tipe === 'mengurutkan' && count(data_get($this->buildInteractiveSettings(), 'items', [])) < 2) {
                $validator->errors()->add('interactive_items', 'Soal mengurutkan minimal memiliki 2 item.');
            }

            if ($tipe === 'drag_drop') {
                $settings = $this->buildInteractiveSettings();
                $items = data_get($settings, 'items', []);
                $zones = data_get($settings, 'zones', []);

                if (count($items) < 2 || count($zones) < 2) {
                    $validator->errors()->add('interactive_drag_items', 'Soal seret dan lepas minimal memiliki 2 item dan 2 area.');
                }

                if (count($items) !== count($zones)) {
                    $validator->errors()->add('interactive_zones', 'Jumlah item dan area tujuan harus sama.');
                }
            }
        });
    }

    private function imageOptionRules(string $option, $soal, bool $isUpdate, bool $requiredForPilihanGanda = true): array
    {
        $typeField = "pilihan_{$option}_tipe";
        $imageField = "pilihan_{$option}_gambar";

        return [
            Rule::requiredIf(fn() => in_array($this->input('tipe_soal'), ['pilihan_ganda', 'pilihan_kompleks', 'listening'], true)
                && $this->optionIsEnabled($option)
                && $this->input($typeField) === 'gambar'
                && (!$isUpdate || empty($soal->{$imageField}))
                && ($requiredForPilihanGanda || $this->has($typeField))),
            'nullable',
            'file',
            'image',
            'mimes:jpeg,png,jpg,gif,webp',
            'max:2048',
        ];
    }

    private function usesOptions(): bool
    {
        return in_array($this->input('tipe_soal'), Soal::OPTION_BASED_TYPES, true);
    }

    private function requiresOption(string $option, bool $always = false): bool
    {
        if (!$this->usesOptions()) {
            return false;
        }

        if (!$this->optionIsEnabled($option)) {
            return false;
        }

        if ($this->input('tipe_soal') === 'benar_salah') {
            return in_array($option, ['a', 'b'], true) && $this->input("pilihan_{$option}_tipe", 'teks') === 'teks';
        }

        return ($always || $this->input("pilihan_{$option}_teks") !== null || $this->has("pilihan_{$option}_tipe"))
            && $this->input("pilihan_{$option}_tipe", 'teks') === 'teks';
    }

    private function optionIsEnabled(string $option): bool
    {
        $letters = ['a', 'b', 'c', 'd', 'e'];
        $limit = $this->input('tipe_soal') === 'benar_salah' ? 2 : $this->optionLimit();
        $index = array_search($option, $letters, true);

        return $index !== false && $index < $limit;
    }

    private function optionLimit(): int
    {
        $bankSoal = BankSoal::find($this->input('bank_soal_id'));

        return $bankSoal?->jumlah_pilihan ?: 5;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pertanyaan.required_unless' => 'Pertanyaan teks harus diisi jika tipe pertanyaan adalah teks atau teks_gambar.',
            'pilihan_*.teks.required_if' => 'Teks pilihan harus diisi jika tipe pilihan adalah teks atau teks_gambar.',
            'kunci_jawaban.required_if' => 'Kunci jawaban harus diisi untuk tipe soal objektif.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->input('tipe_soal') === 'pilihan_kompleks' && is_array($this->input('kunci_jawaban_multi'))) {
            $this->merge([
                'kunci_jawaban' => implode(',', $this->input('kunci_jawaban_multi', [])),
            ]);
        }

        if ($this->input('tipe_soal') === 'teks_rumpang' && $this->hasClozeKeyPlaceholder()) {
            $answers = $this->extractClozeAnswers();
            $settings = $this->input('display_settings', []);
            $settings['cloze'] = [
                'answers' => $answers,
            ];

            $this->merge([
                'display_settings' => $settings,
                'kunci_jawaban' => json_encode([
                    'type' => 'teks_rumpang',
                    'data' => [
                        'answers' => $answers,
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        if (in_array($this->input('tipe_soal'), ['menjodohkan', 'mengurutkan', 'drag_drop'], true)) {
            $settings = $this->input('display_settings', []);
            $interactive = $this->buildInteractiveSettings();
            $settings['interactive'] = $interactive;

            $this->merge([
                'display_settings' => $settings,
                'kunci_jawaban' => json_encode([
                    'type' => $this->input('tipe_soal'),
                    'data' => $interactive,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        // Set default display_settings if not provided
        if (!$this->has('display_settings')) {
            $this->merge([
                'display_settings' => [
                    'shuffle_options' => false,
                    'show_number' => true,
                    'option_layout' => 'vertical',
                ],
            ]);
        }

        // Set default kategori if not provided or empty
        if (!$this->has('kategori') || $this->input('kategori') === null || $this->input('kategori') === '') {
            $this->merge([
                'kategori' => 'umum',
            ]);
        }
    }

    private function buildInteractiveSettings(): array
    {
        $compact = fn($values) => collect($values ?? [])
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        if ($this->input('tipe_soal') === 'menjodohkan') {
            $left = $compact($this->input('interactive_left', []));
            $right = $compact($this->input('interactive_right', []));

            return [
                'pairs' => collect($left)
                    ->map(fn($value, $index) => [
                        'left' => $value,
                        'right' => $right[$index] ?? '',
                    ])
                    ->filter(fn($pair) => $pair['left'] !== '' && $pair['right'] !== '')
                    ->values()
                    ->all(),
            ];
        }

        if ($this->input('tipe_soal') === 'mengurutkan') {
            return [
                'items' => $compact($this->input('interactive_items', [])),
            ];
        }

        return [
            'items' => $compact($this->input('interactive_drag_items', [])),
            'zones' => $compact($this->input('interactive_zones', [])),
        ];
    }

    private function hasClozeKeyPlaceholder(): bool
    {
        return preg_match('/\[\[(.+?)\]\]/', (string) $this->input('pertanyaan')) === 1;
    }

    private function extractClozeAnswers(): array
    {
        preg_match_all('/\[\[(.+?)\]\]/', (string) $this->input('pertanyaan'), $matches);

        return collect($matches[1] ?? [])
            ->map(fn($answer) => trim((string) $answer))
            ->filter()
            ->values()
            ->all();
    }
}
