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

    public function test_request_view_context_rail_reads_collection_fields_from_sample_rows(): void
    {
        $form = new \App\Models\SubmissionForm([
            'id' => (string) \Illuminate\Support\Str::uuid7(),
            'name' => 'Food TRF',
            'document_code' => 'TRF-FOOD',
        ]);
        $instance = new \App\Models\SubmissionFormInstance([
            'id' => (string) \Illuminate\Support\Str::uuid7(),
            'submission_form_id' => $form->id,
            'status' => 'submitted',
            'title' => 'Portal request',
        ]);
        $instance->setRelation('batches', collect());
        $instance->setRelation('crmCustomer', null);

        $presenter = new \App\Services\SubmissionForm\RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: null,
        );

        $rail = $presenter->contextRail([
            'sections' => [
                [
                    'title' => 'Sample collection data',
                    'section_type' => 'regular',
                    'element_holders' => [
                        ['holder_type' => 'field', 'elements' => []],
                    ],
                ],
                [
                    'title' => 'Test & sample information',
                    'section_type' => 'rows_section',
                    'element_holders' => [
                        [
                            'holder_type' => 'rows',
                            'elements' => [
                                [
                                    'name' => 'sampling_date',
                                    'label' => 'Sampling date',
                                    'element_type' => 'date',
                                    'saved_values' => [
                                        ['value' => '2026-09-22', 'display_value' => '2026-09-22', 'array_index' => 0],
                                        ['value' => '2026-09-22', 'display_value' => '2026-09-22', 'array_index' => 1],
                                    ],
                                ],
                                [
                                    'name' => 'sampling_location',
                                    'label' => 'Sampling location',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => 'Line A', 'display_value' => 'Line A', 'array_index' => 0],
                                        ['value' => 'Line B', 'display_value' => 'Line B', 'array_index' => 1],
                                    ],
                                ],
                                [
                                    'name' => 'sample_description',
                                    'label' => 'Sample description',
                                    'element_type' => 'textarea',
                                    'saved_values' => [
                                        ['value' => 'Chicken', 'display_value' => 'Chicken', 'array_index' => 0],
                                    ],
                                ],
                                [
                                    'name' => 'thermometer_id',
                                    'label' => 'Equipment ID',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => '["1234"]', 'display_value' => '["1234"]', 'array_index' => 0],
                                        ['value' => '["1234"]', 'display_value' => '["1234"]', 'array_index' => 1],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], []);

        $byName = collect($rail['sample_collection'])->keyBy('name');

        $this->assertCount(3, $rail['sample_collection']);
        $this->assertSame('2026-09-22', $byName['sampling_date']['value']);
        $this->assertSame('Sample 1: Line A · Sample 2: Line B', $byName['sampling_location']['value']);
        $this->assertSame('1234', $byName['thermometer_id']['value']);
        $this->assertFalse($byName->has('sample_description'));
    }
}
