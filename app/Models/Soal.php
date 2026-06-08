<?php

namespace App\Models;

use App\Support\SoalAnswerEvaluator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soal';

    public const OBJECTIVE_TYPES = [
        'pilihan_ganda',
        'pilihan_kompleks',
        'benar_salah',
        'isian_singkat',
        'teks_rumpang',
        'listening',
    ];

    public const INTERACTIVE_TYPES = [
        'menjodohkan',
        'mengurutkan',
        'drag_drop',
    ];

    public const QUESTION_TYPES = [
        'pilihan_ganda' => 'Pilihan Ganda Standar',
        'pilihan_kompleks' => 'Pilihan Ganda Kompleks',
        'benar_salah' => 'Benar / Salah',
        'isian_singkat' => 'Isian Singkat',
        'teks_rumpang' => 'Melengkapi Teks Rumpang',
        'listening' => 'Listening',
        'menjodohkan' => 'Menjodohkan',
        'mengurutkan' => 'Mengurutkan',
        'drag_drop' => 'Seret dan Lepas',
        'essay' => 'Essay',
    ];

    public const OPTION_BASED_TYPES = [
        'pilihan_ganda',
        'pilihan_kompleks',
        'benar_salah',
        'listening',
    ];

    protected $fillable = [
        'bank_soal_id',
        'nomor_soal',
        'pertanyaan',
        'gambar_pertanyaan',
        'tipe_pertanyaan',
        'tipe_soal',
        'pilihan_a_teks',
        'pilihan_a_gambar',
        'pilihan_a_tipe',
        'pilihan_b_teks',
        'pilihan_b_gambar',
        'pilihan_b_tipe',
        'pilihan_c_teks',
        'pilihan_c_gambar',
        'pilihan_c_tipe',
        'pilihan_d_teks',
        'pilihan_d_gambar',
        'pilihan_d_tipe',
        'pilihan_e_teks',
        'pilihan_e_gambar',
        'pilihan_e_tipe',
        'kunci_jawaban',
        'pembahasan_teks',
        'pembahasan_gambar',
        'pembahasan_tipe',
        'gambar_pembahasan',
        'bobot',
        'kategori',
        'display_settings'
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'nomor_soal' => 'integer',
        'display_settings' => 'array',
        'kunci_jawaban' => 'string',
        'tipe_soal' => 'string', // Default: pilihan_ganda
        'tipe_pertanyaan' => 'string', // Default: teks
        'pilihan_a_tipe' => 'string', // Default: teks
        'pilihan_b_tipe' => 'string', // Default: teks
        'pilihan_c_tipe' => 'string', // Default: teks
        'pilihan_d_tipe' => 'string', // Default: teks
        'pilihan_e_tipe' => 'string', // Default: teks
        'pembahasan_tipe' => 'string', // Default: teks
        'kategori' => 'string' // Default: sedang
    ];

    /**
     * Get the question bank that owns this question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class);
    }

    /**
     * Get the subject (mapel) associated with this question through bank soal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function mapel()
    {
        return $this->hasOneThrough(
            Mapel::class,
            BankSoal::class,
            'id', // Foreign key on bank_soal table...
            'id', // Foreign key on mapel table...
            'bank_soal_id', // Local key on soal table...
            'mapel_id' // Local key on bank_soal table...
        );
    }

    /**
     * Get exam schedules using this question through bank soal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function jadwalUjian()
    {
        return $this->hasManyThrough(
            JadwalUjian::class,
            BankSoal::class,
            'id', // Foreign key on bank_soal table...
            'bank_soal_id', // Foreign key on jadwal_ujian table...
            'bank_soal_id', // Local key on soal table...
            'id' // Local key on bank_soal table...
        );
    }

    /**
     * Get the URL for the question image.
     *
     * @return string|null
     */
    public function getGambarPertanyaanUrlAttribute()
    {
        if ($this->gambar_pertanyaan) {
            return Storage::url('soal/pertanyaan/' . $this->gambar_pertanyaan);
        }
        return null;
    }

    /**
     * Get the URL for option A image.
     *
     * @return string|null
     */
    public function getPilihanAGambarUrlAttribute()
    {
        if ($this->pilihan_a_gambar) {
            return Storage::url('soal/pilihan/' . $this->pilihan_a_gambar);
        }
        return null;
    }

    /**
     * Get the URL for option B image.
     *
     * @return string|null
     */
    public function getPilihanBGambarUrlAttribute()
    {
        if ($this->pilihan_b_gambar) {
            return Storage::url('soal/pilihan/' . $this->pilihan_b_gambar);
        }
        return null;
    }

    /**
     * Get the URL for option C image.
     *
     * @return string|null
     */
    public function getPilihanCGambarUrlAttribute()
    {
        if ($this->pilihan_c_gambar) {
            return Storage::url('soal/pilihan/' . $this->pilihan_c_gambar);
        }
        return null;
    }

    /**
     * Get the URL for option D image.
     *
     * @return string|null
     */
    public function getPilihanDGambarUrlAttribute()
    {
        if ($this->pilihan_d_gambar) {
            return Storage::url('soal/pilihan/' . $this->pilihan_d_gambar);
        }
        return null;
    }

    /**
     * Get the URL for option E image.
     *
     * @return string|null
     */
    public function getPilihanEGambarUrlAttribute()
    {
        if ($this->pilihan_e_gambar) {
            return Storage::url('soal/pilihan/' . $this->pilihan_e_gambar);
        }
        return null;
    }

    /**
     * Get the URL for the explanation image.
     *
     * @return string|null
     */
    public function getPembahasanGambarUrlAttribute()
    {
        if ($this->pembahasan_gambar) {
            return Storage::url('soal/pembahasan/' . $this->pembahasan_gambar);
        }

        // Backward compatibility untuk field gambar_pembahasan
        if ($this->gambar_pembahasan) {
            return Storage::url('soal/pembahasan/' . $this->gambar_pembahasan);
        }

        return null;
    }

    /**
     * Get the question content in a structured format.
     *
     * @return array
     */
    public function getPertanyaanContent()
    {
        return [
            'tipe' => $this->tipe_pertanyaan,
            'teks' => $this->pertanyaan,
            'gambar' => $this->gambar_pertanyaan,
            'gambar_url' => $this->gambar_pertanyaan_url
        ];
    }

    /**
     * Get the decoded HTML version of the question text.
     *
     * @return string
     */
    public function getPertanyaanHtmlAttribute()
    {
        return html_entity_decode($this->pertanyaan);
    }

    /**
     * Get a specific option content in a structured format.
     *
     * @param string $pilihan Option letter (A-E)
     * @return array
     */
    public function getPilihanContent($pilihan)
    {
        $pilihan = strtolower($pilihan);

        return [
            'tipe' => $this->{"pilihan_{$pilihan}_tipe"},
            'teks' => $this->{"pilihan_{$pilihan}_teks"},
            'gambar' => $this->{"pilihan_{$pilihan}_gambar"},
            'gambar_url' => $this->{"pilihan_{$pilihan}_gambar_url"}
        ];
    }

    /**
     * Get the decoded HTML version of option A text.
     *
     * @return string
     */
    public function getPilihanATeksHtmlAttribute()
    {
        return html_entity_decode($this->pilihan_a_teks);
    }

    /**
     * Get the decoded HTML version of option B text.
     *
     * @return string
     */
    public function getPilihanBTeksHtmlAttribute()
    {
        return html_entity_decode($this->pilihan_b_teks);
    }

    /**
     * Get the decoded HTML version of option C text.
     *
     * @return string
     */
    public function getPilihanCTeksHtmlAttribute()
    {
        return html_entity_decode($this->pilihan_c_teks);
    }

    /**
     * Get the decoded HTML version of option D text.
     *
     * @return string
     */
    public function getPilihanDTeksHtmlAttribute()
    {
        return html_entity_decode($this->pilihan_d_teks);
    }

    /**
     * Get the decoded HTML version of option E text.
     *
     * @return string
     */
    public function getPilihanETeksHtmlAttribute()
    {
        return html_entity_decode($this->pilihan_e_teks);
    }

    /**
     * Get all options content in a structured format.
     *
     * @return array
     */
    public function getAllPilihanContent()
    {
        $pilihan = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $key) {
            $pil = strtolower($key);
            if ($this->{"pilihan_{$pil}_teks"} || $this->{"pilihan_{$pil}_gambar"}) {
                $pilihan[$key] = [
                    'tipe' => $this->{"pilihan_{$pil}_tipe"},
                    'teks' => $this->{"pilihan_{$pil}_teks"},
                    'gambar' => $this->{"pilihan_{$pil}_gambar"},
                    'gambar_url' => $this->{"pilihan_{$pil}_gambar_url"}
                ];
            }
        }
        return $pilihan;
    }

    /**
     * Get the explanation content in a structured format.
     *
     * @return array
     */
    public function getPembahasanContent()
    {
        return [
            'tipe' => $this->pembahasan_tipe,
            'teks' => $this->pembahasan_teks,
            'gambar' => $this->pembahasan_gambar,
            'gambar_url' => $this->pembahasan_gambar_url
        ];
    }

    /**
     * Get the decoded HTML version of the explanation text.
     *
     * @return string
     */
    public function getPembahasanTeksHtmlAttribute()
    {
        return html_entity_decode($this->pembahasan_teks);
    }

    /**
     * Check if an answer is correct.
     *
     * @param string $jawaban
     * @return bool
     */
    public function checkJawaban($jawaban)
    {
        return SoalAnswerEvaluator::isCorrect($this, $jawaban);
    }

    public function usesOptions(): bool
    {
        return in_array($this->tipe_soal, self::OPTION_BASED_TYPES, true);
    }

    public function getTipeSoalLabelAttribute(): string
    {
        return self::QUESTION_TYPES[$this->tipe_soal] ?? ucfirst(str_replace('_', ' ', (string) $this->tipe_soal));
    }

    public function getKunciJawabanLabelAttribute(): string
    {
        $key = (string) ($this->kunci_jawaban ?? '');

        if ($key === '') {
            return '-';
        }

        if ($this->tipe_soal === 'menjodohkan') {
            $decoded = json_decode($key, true);
            return collect(data_get($decoded, 'data.pairs', []))
                ->map(fn($pair) => ($pair['left'] ?? '') . ' = ' . ($pair['right'] ?? ''))
                ->filter(fn($line) => trim(str_replace('=', '', $line)) !== '')
                ->implode('; ') ?: '-';
        }

        if ($this->tipe_soal === 'mengurutkan') {
            $decoded = json_decode($key, true);
            return collect(data_get($decoded, 'data.items', []))
                ->filter()
                ->values()
                ->map(fn($item, $index) => ($index + 1) . '. ' . $item)
                ->implode('; ') ?: '-';
        }

        if ($this->tipe_soal === 'drag_drop') {
            $decoded = json_decode($key, true);
            $items = data_get($decoded, 'data.items', []);
            $zones = data_get($decoded, 'data.zones', []);

            return collect($items)
                ->map(fn($item, $index) => $item . ' -> ' . ($zones[$index] ?? '-'))
                ->implode('; ') ?: '-';
        }

        if ($this->tipe_soal === 'teks_rumpang') {
            $decoded = json_decode($key, true);
            $answers = data_get($decoded, 'data.answers');

            if (is_array($answers)) {
                return collect($answers)
                    ->map(fn($answer, $index) => 'Rumpang ' . ($index + 1) . ': ' . (is_array($answer) ? implode('|', $answer) : $answer))
                    ->implode('; ') ?: '-';
            }
        }

        return $key;
    }

    public function isAutoCorrected(): bool
    {
        return in_array($this->tipe_soal, array_merge(self::OBJECTIVE_TYPES, self::INTERACTIVE_TYPES), true);
    }

    /**
     * Check if this question has an image.
     *
     * @return bool
     */
    public function hasImage()
    {
        return $this->tipe_pertanyaan !== 'teks';
    }

    /**
     * Check if a specific option has an image.
     *
     * @param string $pilihan
     * @return bool
     */
    public function pilihanHasImage($pilihan)
    {
        $pil = strtolower($pilihan);
        return $this->{"pilihan_{$pil}_tipe"} !== 'teks';
    }

    /**
     * Check if the explanation has an image.
     *
     * @return bool
     */
    public function pembahasanHasImage()
    {
        return $this->pembahasan_tipe !== 'teks';
    }

    /**
     * Delete all images associated with this question.
     *
     * @return void
     */
    public function deleteImages()
    {
        $images = [
            'soal/pertanyaan/' . $this->gambar_pertanyaan,
            'soal/pilihan/' . $this->pilihan_a_gambar,
            'soal/pilihan/' . $this->pilihan_b_gambar,
            'soal/pilihan/' . $this->pilihan_c_gambar,
            'soal/pilihan/' . $this->pilihan_d_gambar,
            'soal/pilihan/' . $this->pilihan_e_gambar,
            'soal/pembahasan/' . $this->pembahasan_gambar,
            'soal/pembahasan/' . $this->gambar_pembahasan,
        ];

        foreach ($images as $image) {
            if ($image && $image !== 'soal/pertanyaan/' && $image !== 'soal/pilihan/' && $image !== 'soal/pembahasan/') {
                Storage::delete($image);
            }
        }
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($soal) {
            $soal->deleteImages();
        });
    }

    /**
     * Get a display setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getDisplaySetting($key, $default = null)
    {
        return $this->display_settings[$key] ?? $default;
    }

    /**
     * Set a display setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setDisplaySetting($key, $value)
    {
        $settings = $this->display_settings ?? [];
        $settings[$key] = $value;
        $this->display_settings = $settings;
    }

    /**
     * Validate image types.
     *
     * @return bool
     */
    public function validateImageTypes()
    {
        $validTypes = ['teks', 'gambar', 'teks_gambar'];

        return in_array($this->tipe_pertanyaan, $validTypes) &&
            in_array($this->pilihan_a_tipe, array_slice($validTypes, 0, 2)) &&
            in_array($this->pilihan_b_tipe, array_slice($validTypes, 0, 2)) &&
            in_array($this->pilihan_c_tipe, array_slice($validTypes, 0, 2)) &&
            in_array($this->pilihan_d_tipe, array_slice($validTypes, 0, 2)) &&
            in_array($this->pilihan_e_tipe, array_slice($validTypes, 0, 2)) &&
            in_array($this->pembahasan_tipe, $validTypes);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
