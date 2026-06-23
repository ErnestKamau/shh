<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\TestRequestFormReportDataBuilder;
use App\Services\Sampleworkflow\TestRequestReportDataService;
use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use PHPUnit\Framework\TestCase;

class TestRequestReportDataServiceTest extends TestCase
{
    public function test_trf_mapper_quantity_format_is_used_for_report_weight_fallback(): void
    {
        $mapper = new TrfSampleFieldMapper();

        $this->assertSame(
            '250 ml',
            $mapper->formatRowQuantity([
                'sample_quantity' => '250',
                'sample_quantity_unit' => 'ml',
            ]),
        );
    }

    public function test_scalar_value_handles_checkbox_array_for_sampling_apparatus(): void
    {
        $mapper = new TrfSampleFieldMapper();
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            $mapper,
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('scalarValue');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'STERILE BOTTLE' => true,
            'STERILE BAG' => false,
        ]);

        $this->assertSame('STERILE BOTTLE', $result);
    }
}
