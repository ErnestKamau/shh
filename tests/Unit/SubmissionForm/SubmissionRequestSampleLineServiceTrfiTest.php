<?php

namespace Tests\Unit\SubmissionForm;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
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

    public function test_lines_for_trfi_resolves_parameter_ids_to_labels(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food & Feed',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food Microbiology',
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Salmonella',
            'code' => 'SAL',
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'id' => (string) Str::uuid7(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
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
            'form_data' => [
                'sample_rows' => [
                    [
                        'sample_description' => 'chicken 1',
                        'parameters' => $element->id,
                        'microbiology' => true,
                    ],
                ],
            ],
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
        ]);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForTrfi($trfi);

        $this->assertCount(1, $lines);
        $this->assertSame('Salmonella, Microbiology', $lines[0]['parameter_label']);
        $this->assertSame($element->id, $lines[0]['analysis_element_id']);
    }
}
