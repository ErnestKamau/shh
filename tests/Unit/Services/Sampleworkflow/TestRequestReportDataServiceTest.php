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

    public function test_enrich_sample_rows_keeps_existing_array_field_values(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('enrichSampleRowsWithIndexedCollectionFields');
        $method->setAccessible(true);

        $existingMethod = ['APHA' => true, 'DM' => true];
        $existingDetails = [
            ['label' => 'Lot code', 'value' => 'L-42'],
        ];

        $rows = $method->invoke($service, [
            [
                'method_of_sampling' => $existingMethod,
                'additional_details' => $existingDetails,
            ],
            [
                'method_of_sampling' => [],
                'additional_details' => [],
            ],
        ], [
            'method_of_sampling' => ['ASTM', 'ISO'],
            'additional_details' => [
                [['label' => 'Color', 'value' => 'Clear']],
                [['label' => 'Grade', 'value' => 'A']],
            ],
        ]);

        $this->assertSame($existingMethod, $rows[0]['method_of_sampling']);
        $this->assertSame($existingDetails, $rows[0]['additional_details']);
        $this->assertSame('ISO', $rows[1]['method_of_sampling']);
        $this->assertSame([['label' => 'Grade', 'value' => 'A']], $rows[1]['additional_details']);
    }

    public function test_group_result_rows_by_lab_section_keeps_sections_separate(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $microId = '550e8400-e29b-41d4-a716-446655440000';
        $chemId = '550e8400-e29b-41d4-a716-446655440001';

        $grouped = $service->groupResultRowsByLabSection([
            ['lab_section_id' => $chemId, 'lab_section_name' => 'Chemistry', 'analyte' => 'pH'],
            ['lab_section_id' => $microId, 'lab_section_name' => 'Microbiology', 'analyte' => 'E. coli'],
            ['lab_section_id' => $microId, 'lab_section_name' => 'Microbiology', 'analyte' => 'Coliforms'],
            ['lab_section_id' => '', 'lab_section_name' => '', 'analyte' => 'Unknown'],
        ]);

        $this->assertCount(3, $grouped);
        $this->assertSame('Chemistry', $grouped[0]['name']);
        $this->assertSame(['pH'], array_column($grouped[0]['rows'], 'analyte'));
        $this->assertSame('Lab Section', $grouped[1]['name']);
        $this->assertSame(['Unknown'], array_column($grouped[1]['rows'], 'analyte'));
        $this->assertSame('Microbiology', $grouped[2]['name']);
        $this->assertSame(['E. coli', 'Coliforms'], array_column($grouped[2]['rows'], 'analyte'));
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
            ['label' => 'Color', 'value' => ''],
            ['label' => '', 'value' => 'Clear'],
            ['label' => 'Color', 'value' => 'Clear'],
        ]);

        $this->assertSame([
            ['label' => 'Lot code', 'value' => 'L-42'],
            ['label' => 'Color', 'value' => 'Clear'],
        ], $details);
    }

    public function test_format_sample_temperature_for_report_appends_degree_celsius(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatSampleTemperatureForReport');
        $method->setAccessible(true);

        $this->assertSame('25-27 °C', $method->invoke($service, '25-27'));
        $this->assertSame('25 °C', $method->invoke($service, '25 °C'));
        $this->assertSame('25 °C', $method->invoke($service, '25 C'));
        $this->assertSame('NP', $method->invoke($service, 'NP'));
    }

    public function test_append_additional_detail_rows_omits_incomplete_pairs(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('appendAdditionalDetailRows');
        $method->setAccessible(true);

        $rows = $method->invoke($service, [
            [
                'left' => ['label' => 'transport_condition', 'value' => 'Ambient'],
                'right' => ['label' => 'sampling_method', 'value' => 'SOP'],
            ],
        ], [
            ['label' => 'Lot code', 'value' => 'L-42'],
            ['label' => 'Skip me', 'value' => ''],
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('Lot code', $rows[1]['left']['label']);
        $this->assertSame('L-42', $rows[1]['left']['value']);
        $this->assertNull($rows[1]['right']);
    }

    public function test_append_additional_detail_rows_pairs_into_left_and_right_columns(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('appendAdditionalDetailRows');
        $method->setAccessible(true);

        $rows = $method->invoke($service, [
            [
                'left' => ['label' => 'transport_condition', 'value' => 'chiller'],
                'right' => ['label' => 'sampling_method', 'value' => 'apha,astm'],
            ],
        ], [
            ['label' => 'TestField', 'value' => 'Value 1'],
            ['label' => 'TestField2', 'value' => 'Value2'],
            ['label' => 'Extra', 'value' => 'Alone'],
        ]);

        $this->assertCount(3, $rows);
        $this->assertSame('TestField', $rows[1]['left']['label']);
        $this->assertSame('Value 1', $rows[1]['left']['value']);
        $this->assertSame('TestField2', $rows[1]['right']['label']);
        $this->assertSame('Value2', $rows[1]['right']['value']);
        $this->assertSame('Extra', $rows[2]['left']['label']);
        $this->assertNull($rows[2]['right']);
    }

    public function test_normalize_sample_detail_cell_humanizes_option_list_values(): void
    {
        $service = new TestRequestReportDataService(
            new TestRequestFormReportDataBuilder(),
            new TrfSampleFieldMapper(),
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizeSampleDetailCell');
        $method->setAccessible(true);

        $container = $method->invoke($service, [
            'label' => 'container_type',
            'value' => 'sterile_bag,sterile_bottle',
        ]);
        $this->assertSame('sterile bag, sterile bottle', $container['value']);

        $sampling = $method->invoke($service, [
            'label' => 'sampling_method',
            'value' => 'apha,astm',
        ]);
        $this->assertSame('apha, astm', $sampling['value']);

        $other = $method->invoke($service, [
            'label' => 'lot_no',
            'value' => 'LOT_001',
        ]);
        $this->assertSame('LOT_001', $other['value']);
    }
}
