<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class GuideRepository
{
    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? resource_path('guides/skadaexam-guides.json');
    }

    public function all(): array
    {
        if (! File::exists($this->path)) {
            throw new RuntimeException('Guide source file was not found.');
        }

        $content = File::get($this->path);
        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Guide source file contains invalid JSON.');
        }

        $this->validatePayload($payload);

        $payload['guides'] = array_map(fn (array $guide) => $this->withScreenshotUrls($guide), $payload['guides']);

        return $payload;
    }

    public function forRole(string $role): ?array
    {
        $role = Str::lower($role);

        return collect($this->all()['guides'])
            ->first(fn (array $guide) => Str::lower($guide['role']) === $role);
    }

    private function withScreenshotUrls(array $guide): array
    {
        $guide['sections'] = array_map(function (array $section) {
            $section['steps'] = array_map(function (array $step) {
                if (! empty($step['screenshot_path'])) {
                    $step['screenshot_url'] = asset($step['screenshot_path']);
                }

                return $step;
            }, $section['steps'] ?? []);

            return $section;
        }, $guide['sections'] ?? []);

        return $guide;
    }

    private function validatePayload(array $payload): void
    {
        foreach (['updated_at', 'app_version', 'guides'] as $field) {
            if (! Arr::has($payload, $field)) {
                throw new RuntimeException("Guide source is missing {$field}.");
            }
        }

        if (! is_array($payload['guides']) || $payload['guides'] === []) {
            throw new RuntimeException('Guide source must contain at least one guide.');
        }

        foreach ($payload['guides'] as $guideIndex => $guide) {
            foreach (['role', 'title', 'description', 'audience', 'sections'] as $field) {
                if (empty($guide[$field])) {
                    throw new RuntimeException("Guide #{$guideIndex} is missing {$field}.");
                }
            }

            foreach ($guide['sections'] as $sectionIndex => $section) {
                if (empty($section['title']) || empty($section['steps']) || ! is_array($section['steps'])) {
                    throw new RuntimeException("Guide {$guide['role']} section #{$sectionIndex} is incomplete.");
                }

                foreach ($section['steps'] as $stepIndex => $step) {
                    foreach (['id', 'title', 'instruction', 'url', 'screenshot_path'] as $field) {
                        if (empty($step[$field])) {
                            throw new RuntimeException("Guide {$guide['role']} step #{$stepIndex} is missing {$field}.");
                        }
                    }
                }
            }
        }
    }
}
