<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SampleAnalysisSetupLabSectionTest extends TestCase
{
    #[Test]
    public function create_captured_results_requires_resolvable_lab_section(): void
    {
        $analysisTypeId = (string) Str::uuid();
        $analysisType = new AnalysisType();
        $analysisType->id = $analysisTypeId;
        $analysisType->lab_section_id = null;
        $analysisType->has_no_result = 0;

        $analyte = new Analyte();
        $analyte->id = (string) Str::uuid();
        $analyte->code = 'TEST';
        $analyte->name = 'Test analyte';

        $element = new AnalysisElements();
        $element->id = (string) Str::uuid();
        $element->analysis_type_id = $analysisTypeId;
        $element->analyte_id = $analyte->id;
        $element->lab_section_id = null;
        $element->setRelation('analyte', $analyte);

        $service = new SampleAnalysisSetupService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lab section is required to create captured results');

        $service->createCapturedResultsForAnalysisType(
            batchId: (string) Str::uuid(),
            sampleDetailId: (string) Str::uuid(),
            analysisTypeId: $analysisTypeId,
            sampleCode: 'S-TEST-001',
            actingUserId: (string) Str::uuid(),
            context: [
                'analysis_type' => $analysisType,
                'analysis_elements' => new Collection([$element]),
                'sample_header' => null,
                'sample_detail' => null,
                'lab' => null,
                'reporting_units_by_key' => [],
                'standards_by_key' => [],
            ],
        );
    }
}
