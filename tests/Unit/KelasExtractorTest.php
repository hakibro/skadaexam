<?php

namespace Tests\Unit;

use Tests\TestCase;
use ReflectionClass;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Services\SikeuApiService;

class KelasExtractorTest extends TestCase
{
    protected $controller;
    protected $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SiswaController(new SikeuApiService());
        $this->reflection = new ReflectionClass($this->controller);
    }

    /**
     * Test untuk mengekstrak tingkat dari nama kelas.
     */
    public function test_extract_tingkat_from_kelas()
    {
        $method = $this->reflection->getMethod('extractTingkatFromKelas');
        $method->setAccessible(true);

        $testCases = [
            'X IPA 1' => 'X',
            'XI IPS 2' => 'XI',
            'XII MIPA 3' => 'XII',
            'X BAHASA' => 'X',
            'XI AGAMA 1' => 'XI',
            'XII IPA' => 'XII',
            'XIIPA1' => 'XI',
            'XIIPS2' => 'XI',
            'X' => 'X',
            'XI' => 'XI',
            'XII' => 'XII',
            'Random' => 'Ran', // Default behavior for unknown format
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->controller, $input);
            echo "$input => Expected: $expected, Got: $result\n";
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Test untuk mengekstrak jurusan dari nama kelas.
     */
    public function test_extract_jurusan_from_kelas()
    {
        $method = $this->reflection->getMethod('extractJurusanFromKelas');
        $method->setAccessible(true);

        $testCases = [
            'X IPA 1' => 'IPA',
            'XI IPS 2' => 'IPS',
            'XII MIPA 3' => 'MIPA',
            'X BAHASA' => 'BAHASA',
            'XI AGAMA 1' => 'AGAMA',
            'XII IPA' => 'IPA',
            'XIIPA1' => 'IPA',
            'XIIPS2' => 'IPS',
            'X' => 'UMUM', // Default
            'Random' => 'UMUM', // Default
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->controller, $input);
            echo "$input => Expected: $expected, Got: $result\n";
            $this->assertEquals($expected, $result);
        }
    }
}
