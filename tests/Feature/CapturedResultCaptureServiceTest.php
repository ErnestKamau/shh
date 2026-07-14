<?php

namespace Tests\Feature;

use App\AnalysisElements;
use App\CapturedResult;
use App\Services\Sampleworkflow\CapturedResultCaptureService;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CapturedResultCaptureServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_apply_on_save_always_assigns_operator_and_analyst(): void
    {
        $actingUserId = (string) Str::uuid();
        $methodId = (string) Str::uuid();
        $unitId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $captured = Mockery::mock(CapturedResult::class)->makePartial();
        $captured->shouldAllowMockingProtectedMethods();
        $captured->operator_id = null;
        $captured->user_id = null;
        $captured->method_id = null;
        $captured->reporting_unit_id = null;
        $captured->analysis_element_id = null;
        $captured->analysis_type_id = (string) Str::uuid();
        $captured->analyte_id = (string) Str::uuid();

        $element = new AnalysisElements([
            'id' => $elementId,
            'method' => $methodId,
            'reporting_unit' => 'mg/L',
        ]);
        $element->id = $elementId;

        $captured->shouldReceive('resolveAnalysisElement')->andReturn($element);
        $captured->shouldReceive('save')->once()->andReturnTrue();

        // Stub helper resolution used by applyAnalysisElementDefaults
        $this->app->instance('tests.reporting_unit_id', $unitId);

        $service = new CapturedResultCaptureService();

        // Bypass global helper by setting unit via fill attributes instead
        $result = $service->applyOnSave($captured, [
            'result' => '12.5',
            'method_id' => $methodId,
            'reporting_unit_id' => $unitId,
        ], $actingUserId);

        $this->assertSame($actingUserId, $result->operator_id);
        $this->assertSame($actingUserId, $result->user_id);
        $this->assertSame($methodId, $result->method_id);
        $this->assertSame($unitId, $result->reporting_unit_id);
        $this->assertSame($elementId, $result->analysis_element_id);
        $this->assertSame('12.5', $result->result);
    }

    public function test_apply_on_save_does_not_overwrite_existing_method_and_unit(): void
    {
        $actingUserId = (string) Str::uuid();
        $existingMethod = (string) Str::uuid();
        $existingUnit = (string) Str::uuid();
        $elementMethod = (string) Str::uuid();

        $captured = Mockery::mock(CapturedResult::class)->makePartial();
        $captured->operator_id = null;
        $captured->user_id = null;
        $captured->method_id = $existingMethod;
        $captured->reporting_unit_id = $existingUnit;
        $captured->analysis_element_id = (string) Str::uuid();
        $captured->analysis_type_id = (string) Str::uuid();
        $captured->analyte_id = (string) Str::uuid();

        $element = new AnalysisElements([
            'method' => $elementMethod,
            'reporting_unit' => 'ppm',
        ]);

        $captured->shouldReceive('resolveAnalysisElement')->andReturn($element);
        $captured->shouldReceive('save')->once()->andReturnTrue();

        $service = new CapturedResultCaptureService();
        $result = $service->applyOnSave($captured, [
            'result' => '1.0',
        ], $actingUserId);

        $this->assertSame($existingMethod, $result->method_id);
        $this->assertSame($existingUnit, $result->reporting_unit_id);
        $this->assertSame($actingUserId, $result->operator_id);
    }

    public function test_ensure_analysis_element_linked_sets_id_when_missing(): void
    {
        $elementId = (string) Str::uuid();
        $captured = Mockery::mock(CapturedResult::class)->makePartial();
        $captured->analysis_element_id = null;

        $element = new AnalysisElements();
        $element->id = $elementId;

        $captured->shouldReceive('resolveAnalysisElement')->once()->andReturn($element);
        $captured->ensureAnalysisElementLinked();

        $this->assertSame($elementId, $captured->analysis_element_id);
    }
}
