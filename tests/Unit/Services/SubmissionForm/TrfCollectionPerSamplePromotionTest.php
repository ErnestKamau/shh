<?php

namespace Tests\Unit\Services\SubmissionForm;

use App\Services\Sampleworkflow\TestRequestFormReportDataBuilder;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use ReflectionClass;
use Tests\TestCase;

/**
 * Pure unit checks — no database. Do not add RefreshDatabase here.
 */
class TrfCollectionPerSamplePromotionTest extends TestCase
{
    public function test_schema_helper_recognizes_collection_fields_and_allows_location_on_rows(): void
    {
        $this->assertTrue(SubmissionFormSchemaHelper::isSampleCollectionFieldName('sampling_date'));
        $this->assertTrue(SubmissionFormSchemaHelper::isSampleCollectionFieldName('date_received'));
        $this->assertTrue(SubmissionFormSchemaHelper::isSampleCollectionSectionTitle('Sample collection data'));
        $this->assertFalse(in_array('sampling_location', SubmissionFormSchemaHelper::deprecatedTrfRowLocationFieldNames(), true));
    }

    public function test_pdf_builder_includes_additional_details_on_sample_rows(): void
    {
        $builder = app(TestRequestFormReportDataBuilder::class);
        $ref = new ReflectionClass($builder);
        $method = $ref->getMethod('resolveSampleRows');
        $method->setAccessible(true);

        $result = $method->invoke($builder, [
            'sample_rows' => [
                [
                    'sample_description' => 'Milk',
                    'sampling_point_manual' => 'Line 1',
                    'sample_quantity' => '1',
                    'sample_quantity_unit' => 'kg',
                    'sample_condition' => 'chilled',
                    'additional_details' => [
                        ['label' => 'Lot code', 'value' => 'L-42'],
                        ['label' => '', 'value' => ''],
                    ],
                ],
            ],
        ], 'food');

        $this->assertCount(1, $result);
        $this->assertSame([
            ['label' => 'Lot code', 'value' => 'L-42'],
        ], $result[0]['additional_details']);
    }

    public function test_pdf_builder_uses_first_sample_when_collection_is_indexed(): void
    {
        $builder = app(TestRequestFormReportDataBuilder::class);
        $ref = new ReflectionClass($builder);
        $method = $ref->getMethod('resolveCollectionFields');
        $method->setAccessible(true);

        $result = $method->invoke($builder, [
            'sampling_date' => ['2026-09-21', '2026-09-22'],
            'sampling_time' => ['10:15', '11:00'],
            'sampling_location' => ['point-a', 'point-b'],
            'date_received' => ['2026-09-21', '2026-09-22'],
            'thermometer_id' => ['EQ-1', 'EQ-2'],
            'sampling_apparatus' => [['sterile_bag'], ['sterile_bottle']],
            'method_of_sampling' => [['APHA'], ['ASTM']],
            'reason_of_collection' => [['contract'], ['non_contract']],
            'transport_condition' => [['chiller_vehicle'], ['ambient']],
        ], 'food');

        $this->assertSame('21/09/2026', $result['sampling_date_raw']);
        $this->assertSame('10:15', $result['sampling_time']);
        $this->assertSame('EQ-1', $result['thermometer_id']);
    }
}
