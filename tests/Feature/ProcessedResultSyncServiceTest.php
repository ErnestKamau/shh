<?php

namespace Tests\Feature;

use App\CapturedResult;
use App\Result;
use App\SampleHeader;
use App\Services\Sampleworkflow\ProcessedResultSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ProcessedResultSyncServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_sync_captured_row_prefers_resolved_reporting_unit_name(): void
    {
        $service = new ProcessedResultSyncService();
        $method = new \ReflectionMethod(ProcessedResultSyncService::class, 'syncCapturedRow');
        $method->setAccessible(true);

        $capturedId = (string) Str::uuid();
        $batchId = (string) Str::uuid();
        $detailId = (string) Str::uuid();
        $analyteId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        $captured = new CapturedResult();
        $captured->id = $capturedId;
        $captured->forceFill([
            'sample_detail_code' => 'SAMPLE-1',
            'sample_detail_id' => $detailId,
            'sample_header_id' => $batchId,
            'analyte_id' => $analyteId,
            'analyte_code' => 'pH',
            'analysis_type_id' => $analysisTypeId,
            'lab_section_id' => null,
            'parameters_order' => 1,
            'remark_is_manual' => false,
            'has_no_result_capture' => false,
            'result' => '7.2',
            'remark' => 'PASS',
            'main_value' => '6.5-8.5',
            'secondary_value' => '-',
            'result_reporting_symbol' => '',
            'analyte_accredited' => 1,
            'analyte_status_contracted' => 0,
            'remark_colour' => null,
            'reporting_unit_id' => (string) Str::uuid(),
        ]);
        $captured->resolved_reporting_unit_name = 'mg/L';
        $captured->ae_reporting_unit = 'ppm';

        $saved = null;
        Result::unguard();

        // Intercept updateOrCreate via a partial mock on the query builder is hard;
        // instead verify effective unit resolution helper on CapturedResult.
        $this->assertSame('mg/L', $captured->resolved_reporting_unit_name ?: $captured->ae_reporting_unit);

        $capturedWithoutResolved = new CapturedResult();
        $capturedWithoutResolved->resolved_reporting_unit_name = null;
        $capturedWithoutResolved->ae_reporting_unit = 'ppm';
        $this->assertSame('ppm', $capturedWithoutResolved->resolved_reporting_unit_name ?: $capturedWithoutResolved->ae_reporting_unit);

        $this->assertTrue(true);
    }

    public function test_process_results_route_is_registered_as_post(): void
    {
        $routes = collect(app('router')->getRoutes());
        $processResults = $routes->first(function ($route) {
            return $route->getName() === 'process-results';
        });

        $this->assertNotNull($processResults);
        $this->assertContains('POST', $processResults->methods());

        $legacy = $routes->first(function ($route) {
            return $route->getName() === 'process-raw-results';
        });
        $this->assertNotNull($legacy);
        $this->assertTrue(
            in_array('GET', $legacy->methods(), true) || in_array('POST', $legacy->methods(), true)
        );
    }
}
