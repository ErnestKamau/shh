<?php

namespace Tests\Unit\Services\SubmissionForm;

use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrfSampleLineQuantityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    public function test_each_trf_row_maps_to_one_sample_regardless_of_quantity(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $template = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Food',
            'code' => 'TRF-FOOD',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $template->id,
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [
                'sample_rows' => [
                    [
                        'sample_description' => 'Milk',
                        'sample_quantity' => '2',
                        'sample_quantity_unit' => 'kg',
                    ],
                    [
                        'sample_description' => 'Juice',
                        'sample_quantity' => '1',
                        'sample_quantity_unit' => 'L',
                    ],
                ],
            ],
        ]);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForTrfi($trfi);

        $this->assertCount(2, $lines);
        $this->assertSame(1, $lines[0]['number_of_samples']);
        $this->assertSame(1, $lines[1]['number_of_samples']);
        $this->assertSame('2', $lines[0]['sample_quantity']);
        $this->assertSame('kg', $lines[0]['sample_quantity_unit']);
    }

    public function test_legacy_qty_is_parsed_into_quantity_and_unit_not_sample_count(): void
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
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [
                'sample_rows' => [
                    [
                        'sample_description' => 'Tap',
                        'qty' => '2 kg',
                    ],
                ],
            ],
        ]);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForTrfi($trfi);

        $this->assertCount(1, $lines);
        $this->assertSame(1, $lines[0]['number_of_samples']);
        $this->assertSame('2', $lines[0]['sample_quantity']);
        $this->assertSame('kg', $lines[0]['sample_quantity_unit']);
    }
}
