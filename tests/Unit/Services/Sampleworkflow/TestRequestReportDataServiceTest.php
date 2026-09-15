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

    public function test_resolve_reporting_unit_label_returns_legacy_name_unchanged(): void
    {
        $this->assertSame('mg/L', resolveReportingUnitLabel('mg/L'));
        $this->assertSame('CFU/g', resolveReportingUnitLabel('CFU/g'));
        $this->assertSame('-', resolveReportingUnitLabel(null));
        $this->assertSame('-', resolveReportingUnitLabel(''));
    }

    public function test_build_company_letterhead_uses_three_line_address_without_po_box_or_email(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('buildCompanyLetterhead');
        $method->setAccessible(true);

        $letterhead = $method->invoke($service, (object) [
            'name' => 'AmSpec Middle East Inspection and Testing LLC – Branch',
            'address' => 'Warehouse Phase 2, Block D Premises No. D05, Dubai Science Park, Al Barsha South, Dubai, United Arab Emirates',
            'po_box' => '500767',
            'telephone' => '+971 45576370',
            'email' => 'AgriFood.UAE.Commercial@amspecgroup.com',
            'website' => 'www.amspecgroup.com',
            'fax' => '',
        ]);

        $this->assertSame([
            'Warehouse Phase 2, Block D Premises No. D05,',
            'Dubai Science Park, Al Barsha South,',
            'Dubai, United Arab Emirates',
            'T: +971 45576370',
            'W: www.amspecgroup.com',
        ], $letterhead['lines']);
    }
}
