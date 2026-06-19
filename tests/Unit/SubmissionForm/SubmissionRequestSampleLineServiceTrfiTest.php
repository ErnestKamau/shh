<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionRequestSampleLineServiceTrfiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lines_for_trfi_returns_normalized_sample_rows(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $template = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Water',
            'code' => 'TRF-WATER',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $template->id,
            'form_data' => [
                'sample_rows' => [
                    [
                        'sample_no' => 'S-001',
                        'sample_description' => 'Pool sample',
                        'qty' => 3,
                    ],
                ],
            ],
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
        ]);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForTrfi($trfi);

        $this->assertCount(1, $lines);
        $this->assertSame('S-001', $lines[0]['customer_sample_id']);
        $this->assertSame('Pool sample', $lines[0]['sample_description']);
        $this->assertSame(3, $lines[0]['number_of_samples']);
        $this->assertSame($sampleType->id, $lines[0]['sample_type_id']);
    }
}
