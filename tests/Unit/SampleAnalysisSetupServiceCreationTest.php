<?php

namespace Tests\Unit;

use App\AnalysisElements;
use App\Analyte;
use App\CapturedResult;
use App\ReportingUnit;
use App\Result;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SampleAnalysisSetupServiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_captured_results_sets_analysis_element_id_and_unit_uuid(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $user = User::query()->create([
            'name' => 'Setup Tester',
            'email' => 'setup-tester-'.Str::random(6).'@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'JOB-TEST-'.Str::random(4),
            'status' => 'Samples In Lab',
            'created_by' => $user->id,
        ]);

        $detail = SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => 'CH-001',
        ]);

        $analyte = Analyte::query()->create([
            'name' => 'pH',
            'code' => 'pH-'.Str::random(4),
            'active' => 1,
        ]);

        $unit = ReportingUnit::query()->create([
            'name' => 'pH_unit_'.Str::random(4),
            'active' => 1,
        ]);

        $analysisTypeId = (string) Str::uuid();
        $methodId = (string) Str::uuid();

        $element = AnalysisElements::query()->create([
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyte->id,
            'method' => $methodId,
            'reporting_unit' => $unit->name,
            'active' => 1,
            'level' => 1,
            'non_accredited' => 0,
        ]);

        $service = app(SampleAnalysisSetupService::class);
        $service->createCapturedResultsForAnalysisType(
            (string) $batch->id,
            (string) $detail->id,
            $analysisTypeId,
            (string) $detail->sample_code,
            (string) $user->id,
        );

        $captured = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('analyte_id', $analyte->id)
            ->first();

        $this->assertNotNull($captured);
        $this->assertSame((string) $element->id, (string) $captured->analysis_element_id);
        $this->assertSame((string) $unit->id, (string) $captured->reporting_unit_id);
        $this->assertSame((string) $methodId, (string) $captured->method_id);
        $this->assertNull($captured->operator_id);

        $result = Result::query()->where('captured_result_id', $captured->id)->first();
        $this->assertNotNull($result);
        $this->assertSame($unit->name, $result->unit_code);
    }
}
