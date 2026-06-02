<?php
// filepath: app\Http\Requests\StoreSoalRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tipe_soal' => 'required|in:pilihan_ganda,essay',
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
            'pilihan_a_teks' => 'required_if:pilihan_a_tipe,teks|nullable|string',
            'pilihan_a_tipe' => 'required_if:tipe_soal,pilihan_ganda|in:teks,gambar',
            'pilihan_a_gambar' => $this->imageOptionRules('a', $soal, $isUpdate),

            'pilihan_b_teks' => 'required_if:pilihan_b_tipe,teks|nullable|string',
            'pilihan_b_tipe' => 'required_if:tipe_soal,pilihan_ganda|in:teks,gambar',
            'pilihan_b_gambar' => $this->imageOptionRules('b', $soal, $isUpdate),

            'pilihan_c_teks' => 'required_if:pilihan_c_tipe,teks|nullable|string',
            'pilihan_c_tipe' => 'required_if:tipe_soal,pilihan_ganda|in:teks,gambar',
            'pilihan_c_gambar' => $this->imageOptionRules('c', $soal, $isUpdate),

            'pilihan_d_teks' => 'required_if:pilihan_d_tipe,teks|nullable|string',
            'pilihan_d_tipe' => 'required_if:tipe_soal,pilihan_ganda|in:teks,gambar',
            'pilihan_d_gambar' => $this->imageOptionRules('d', $soal, $isUpdate),

            'pilihan_e_teks' => 'nullable|string',
            'pilihan_e_tipe' => 'nullable|in:teks,gambar',
            'pilihan_e_gambar' => $this->imageOptionRules('e', $soal, $isUpdate, false),

            'kunci_jawaban' => 'required_if:tipe_soal,pilihan_ganda|nullable|in:A,B,C,D,E',

            'pembahasan_teks' => 'nullable|string',
            'pembahasan_tipe' => 'nullable|in:teks,gambar,teks_gambar',
            'pembahasan_gambar' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'gambar_pembahasan' => 'nullable|string', // Untuk backward compatibility

            'bobot' => 'nullable|numeric|min:0.01|max:100.00',
            'kategori' => 'nullable|string|max:50',
            'display_settings' => 'nullable|array',
        ];
    }

    private function imageOptionRules(string $option, $soal, bool $isUpdate, bool $requiredForPilihanGanda = true): array
    {
        $typeField = "pilihan_{$option}_tipe";
        $imageField = "pilihan_{$option}_gambar";

        return [
            Rule::requiredIf(fn() => $this->input('tipe_soal') === 'pilihan_ganda'
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

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pertanyaan.required_unless' => 'Pertanyaan teks harus diisi jika tipe pertanyaan adalah teks atau teks_gambar.',
            'pilihan_*.teks.required_if' => 'Teks pilihan harus diisi jika tipe pilihan adalah teks atau teks_gambar.',
            'kunci_jawaban.required_if' => 'Kunci jawaban harus diisi untuk soal pilihan ganda.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
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
}
