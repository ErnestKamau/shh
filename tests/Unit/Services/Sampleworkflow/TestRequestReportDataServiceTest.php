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

    public function test_normalize_lab_section_id_accepts_uuid_only(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $valid = '550e8400-e29b-41d4-a716-446655440000';
        $two = '550e8400-e29b-41d4-a716-446655440001';

        $this->assertSame($valid, $service->normalizeLabSectionId($valid));
        $this->assertSame($valid, $service->normalizeLabSectionId('  '.$valid.'  '));
        $this->assertNull($service->normalizeLabSectionId(null));
        $this->assertNull($service->normalizeLabSectionId(''));
        $this->assertNull($service->normalizeLabSectionId('microbiology'));
        $this->assertNull($service->normalizeLabSectionId('123'));
        $this->assertSame([$valid, $two], $service->normalizeLabSectionIds([$valid, $two, 'bad', $valid]));
        $this->assertSame([$valid, $two], $service->normalizeLabSectionIds($valid.','.$two));
    }

    public function test_normalize_sample_ids_accepts_uuid_list_or_csv(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $one = '550e8400-e29b-41d4-a716-446655440000';
        $two = '550e8400-e29b-41d4-a716-446655440001';

        $this->assertSame([$one, $two], $service->normalizeSampleIds([$one, $two, 'bad', $one]));
        $this->assertSame([$one, $two], $service->normalizeSampleIds($one.','.$two.',not-a-uuid'));
        $this->assertSame([], $service->normalizeSampleIds(null));
        $this->assertSame([], $service->normalizeSampleIds(''));
    }

    public function test_enrich_sample_rows_copies_indexed_collection_fields_per_sample(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('enrichSampleRowsWithIndexedCollectionFields');
        $method->setAccessible(true);

        $rows = $method->invoke($service, [
            ['sample_description' => 'RO water'],
            ['sample_description' => 'Well water'],
        ], [
            'date_received' => ['2026-08-25', '2026-08-26'],
            'transport_condition' => ['chiller_vehicle', 'ambient'],
            'method_of_sampling' => ['APHA,DM', 'ASTM'],
            'sampling_location' => ['point-a', 'point-b'],
            'sampling_apparatus' => ['sterile_bottle', 'sterile_bag'],
        ]);

        $this->assertSame('2026-08-25', $rows[0]['date_received']);
        $this->assertSame('2026-08-26', $rows[1]['date_received']);
        $this->assertSame('chiller_vehicle', $rows[0]['transport_condition']);
        $this->assertSame('ambient', $rows[1]['transport_condition']);
        $this->assertSame('APHA,DM', $rows[0]['method_of_sampling']);
        $this->assertSame('sterile_bag', $rows[1]['sampling_apparatus']);
    }

    public function test_normalize_report_additional_details_keeps_filled_pairs_only(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizeReportAdditionalDetails');
        $method->setAccessible(true);

        $details = $method->invoke($service, [
            ['label' => 'Lot code', 'value' => 'L-42'],
            ['label' => '', 'value' => ''],
            ['label' => 'Color', 'value' => 'Clear'],
        ]);

        $this->assertSame([
            ['label' => 'Lot code', 'value' => 'L-42'],
            ['label' => 'Color', 'value' => 'Clear'],
        ], $details);
    }
}
