<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TestRequestFormPdfServiceTest extends TestCase
{
    public function test_default_orientation_matches_amspec_form_types(): void
    {
        $service = app(TestRequestFormPdfService::class);

        $this->assertSame('landscape', $service->defaultOrientationForVariant('food'));
        $this->assertSame('landscape', $service->defaultOrientationForVariant('water'));
        $this->assertSame('portrait', $service->defaultOrientationForVariant('waste_water'));
    }

    public function test_normalize_orientation_accepts_landscape_and_portrait(): void
    {
        $service = app(TestRequestFormPdfService::class);

        $this->assertSame('landscape', $service->normalizeOrientation('Landscape'));
        $this->assertSame('portrait', $service->normalizeOrientation('portrait'));
        $this->assertNull($service->normalizeOrientation(null));
        $this->assertNull($service->normalizeOrientation(''));
    }

    public function test_normalize_orientation_rejects_invalid_values(): void
    {
        $this->expectException(ValidationException::class);

        app(TestRequestFormPdfService::class)->normalizeOrientation('square');
    }

    public function test_resolve_orientation_prefers_explicit_override(): void
    {
        $service = app(TestRequestFormPdfService::class);

        $this->assertSame(
            'portrait',
            $service->resolveOrientation('portrait', 'food'),
        );
        $this->assertSame(
            'landscape',
            $service->resolveOrientation(null, 'food'),
        );
        $this->assertSame(
            'portrait',
            $service->resolveOrientation(null, 'waste_water'),
        );
    }
}
