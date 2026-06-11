<?php

namespace Tests\Unit;

use App\Services\GuideRepository;
use Tests\TestCase;

class GuideRepositoryTest extends TestCase
{
    public function test_it_loads_guides_with_public_screenshot_urls(): void
    {
        $repository = new GuideRepository();

        $payload = $repository->all();

        $this->assertArrayHasKey('guides', $payload);
        $this->assertNotEmpty($payload['guides']);

        $siswaGuide = $repository->forRole('siswa');

        $this->assertNotNull($siswaGuide);
        $this->assertSame('siswa', $siswaGuide['role']);
        $this->assertNotEmpty($siswaGuide['sections'][0]['steps'][0]['screenshot_url']);
    }

    public function test_it_returns_null_for_unknown_role(): void
    {
        $repository = new GuideRepository();

        $this->assertNull($repository->forRole('tidak-ada'));
    }
}
