<?php

namespace Tests\Unit\Sampleworkflow;

use App\Models\System\SystemConfiguration;
use App\Services\Sampleworkflow\SampleRejectionReasonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleRejectionReasonServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_structured_reason_objects(): void
    {
        SystemConfiguration::query()->create([
            'key' => 'sample_rejection_reasons',
            'value' => json_encode([
                ['key' => 'leaking', 'label' => 'Sample was leaking'],
                ['key' => 'other', 'label' => 'Other'],
            ]),
            'status' => true,
        ]);

        $reasons = app(SampleRejectionReasonService::class)->getReasons();

        $this->assertCount(2, $reasons);
        $this->assertSame('leaking', $reasons[0]['key']);
        $this->assertSame('Sample was leaking', $reasons[0]['label']);
    }

    public function test_parses_string_reason_list(): void
    {
        SystemConfiguration::query()->create([
            'key' => 'sample_rejection_reasons',
            'value' => json_encode([
                'Improper container',
                'Other reason',
            ]),
            'status' => true,
        ]);

        $reasons = app(SampleRejectionReasonService::class)->getReasons();

        $this->assertCount(2, $reasons);
        $this->assertSame('improper_container', $reasons[0]['key']);
        $this->assertSame('Improper container', $reasons[0]['label']);
    }

    public function test_returns_empty_when_config_missing(): void
    {
        $this->assertSame([], app(SampleRejectionReasonService::class)->getReasons());
    }
}
