<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Services\SikeuApiService;
use ReflectionClass;

class TestKelasExtractor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:kelas-extractor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test kelas name extraction functions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Kelas Extractor Methods');

        $controller = new SiswaController(new SikeuApiService());
        $reflection = new ReflectionClass($controller);

        // Test tingkat extraction
        $tingkatMethod = $reflection->getMethod('extractTingkatFromKelas');
        $tingkatMethod->setAccessible(true);

        $this->info('Testing extractTingkatFromKelas:');
        $this->table(
            ['Input', 'Output'],
            $this->testTingkatExtraction($controller, $tingkatMethod)
        );

        // Test jurusan extraction
        $jurusanMethod = $reflection->getMethod('extractJurusanFromKelas');
        $jurusanMethod->setAccessible(true);

        $this->info('Testing extractJurusanFromKelas:');
        $this->table(
            ['Input', 'Output'],
            $this->testJurusanExtraction($controller, $jurusanMethod)
        );

        $this->info('Testing completed!');

        return Command::SUCCESS;
    }

    /**
     * Test tingkat extraction with various inputs
     */
    private function testTingkatExtraction($controller, $method)
    {
        $testCases = [
            'X IPA 1',
            'XI IPS 2',
            'XII MIPA 3',
            'X BAHASA',
            'XI AGAMA 1',
            'XII IPA',
            'XIIPA1',
            'XIIPS2',
            'XIPA1',
            'X',
            'XI',
            'XII',
            // New test cases from API
            'X DPIB -',
            'X DKV 2',
            'X BD 2',
            'XI DPIB',
            'XII DKV 1',
            'X MEKATRONIKA',
            'X RPL 1',
            'XI MM 2',
            'XII TKJ 3',
            'X - TEI',
            'Random',
        ];

        // Debug substring checks
        $this->line('Debug substring checks for tingkat:');
        foreach (['XIIPA1', 'XIIPS2', 'X DPIB -', 'X DKV 2', 'X BD 2'] as $debugCase) {
            $this->line("Testing: $debugCase");
            $this->line("  Starts with XII: " . (substr(strtoupper($debugCase), 0, 3) === 'XII' ? 'true' : 'false'));
            $this->line("  Starts with XI: " . (substr(strtoupper($debugCase), 0, 2) === 'XI' ? 'true' : 'false'));
            $this->line("  Starts with X: " . (substr(strtoupper($debugCase), 0, 1) === 'X' ? 'true' : 'false'));

            // Check pattern for new format
            preg_match('/^(X|XI|XII)\s+([A-Z]+)\s*(\d*|\-)?$/i', $debugCase, $matches);
            $this->line("  New pattern match: " . (!empty($matches) ? print_r($matches, true) : 'false'));
        }

        $results = [];

        foreach ($testCases as $input) {
            $result = $method->invoke($controller, $input);
            $results[] = [$input, $result];
        }

        return $results;
    }

    /**
     * Test jurusan extraction with various inputs
     */
    private function testJurusanExtraction($controller, $method)
    {
        $testCases = [
            'X IPA 1',
            'XI IPS 2',
            'XII MIPA 3',
            'X BAHASA',
            'XI AGAMA 1',
            'XII IPA',
            'XIIPA1',
            'XIIPS2',
            'XIPA1',
            'X',
            'XI',
            'XII',
            // New test cases from API
            'X DPIB -',
            'X DKV 2',
            'X BD 2',
            'XI DPIB',
            'XII DKV 1',
            'X MEKATRONIKA',
            'X RPL 1',
            'XI MM 2',
            'XII TKJ 3',
            'X - TEI',
            'Random',
        ];

        $results = [];

        foreach ($testCases as $input) {
            $result = $method->invoke($controller, $input);
            $results[] = [$input, $result];
        }

        return $results;
    }
}
