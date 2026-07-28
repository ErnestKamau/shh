<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use App\Company;
use App\SampleType;
use App\Services\Sampleworkflow\TestRequestFormReportDataBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestRequestFormReportDataBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_report_variant_returns_food_for_food_sample_type(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'SMP-FOOD',
            'active' => true,
        ]);

        $this->assertSame('food', app(TrfDocumentCodeForSampleType::class)->resolveReportVariant($sampleType));
    }

    public function test_food_and_feed_resolves_to_dedicated_document_code(): void
    {
        $foodAndFeed = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food & Feed',
            'code' => 'Food & Feed',
            'active' => true,
        ]);

        $food = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'Food',
            'active' => true,
        ]);

        $resolver = app(TrfDocumentCodeForSampleType::class);

        $this->assertTrue($resolver->isFoodAndFeed($foodAndFeed));
        $this->assertFalse($resolver->isFood($foodAndFeed));
        $this->assertSame(TrfDocumentCodeForSampleType::FOOD_AND_FEED, $resolver->resolve($foodAndFeed));
        $this->assertSame('food', $resolver->resolveReportVariant($foodAndFeed));

        $this->assertFalse($resolver->isFoodAndFeed($food));
        $this->assertTrue($resolver->isFood($food));
        $this->assertSame(TrfDocumentCodeForSampleType::FOOD, $resolver->resolve($food));
    }

    public function test_resolve_report_variant_returns_water_for_water_and_other_types(): void
    {
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'SMP-WTR',
            'active' => true,
        ]);

        $other = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Soil',
            'code' => 'SMP-SOIL',
            'active' => true,
        ]);

        $resolver = app(TrfDocumentCodeForSampleType::class);
        $this->assertSame('water', $resolver->resolveReportVariant($water));
        $this->assertSame('water', $resolver->resolveReportVariant($other));
    }

    public function test_resolve_report_variant_returns_waste_water_for_waste_water_type(): void
    {
        $wasteWater = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Waste Water',
            'code' => 'SMP-WWTR',
            'active' => true,
        ]);

        $this->assertSame('waste_water', app(TrfDocumentCodeForSampleType::class)->resolveReportVariant($wasteWater));
    }

    public function test_state_of_sample_checks_maps_full_words_and_abbreviations(): void
    {
        $this->assertSame(
            ['L' => true, 'SS' => false, 'S' => false],
            TestRequestFormReportDataBuilder::stateOfSampleChecks('Liquid')
        );

        $this->assertSame(
            ['L' => false, 'SS' => true, 'S' => false],
            TestRequestFormReportDataBuilder::stateOfSampleChecks('Semi Solid')
        );

        $this->assertSame(
            ['L' => false, 'SS' => false, 'S' => true],
            TestRequestFormReportDataBuilder::stateOfSampleChecks('Solid')
        );

        $this->assertSame(
            ['L' => true, 'SS' => false, 'S' => false],
            TestRequestFormReportDataBuilder::stateOfSampleChecks('l')
        );

        $this->assertSame(
            ['L' => false, 'SS' => true, 'S' => false],
            TestRequestFormReportDataBuilder::stateOfSampleChecks('ss')
        );
    }

    public function test_build_from_draft_includes_company_and_serial_number(): void
    {
        Company::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'AmSpec Middle East Inspection & Testing Services L.L.C',
            'active' => 1,
            'telephone' => '+971 4 123 4567',
            'email' => 'info@example.test',
            'address' => 'Dubai Industrial City',
            'fax' => '+971 4 123 4568',
            'website' => 'www.amspec.test',
        ]);

        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'SMP-FOOD',
            'active' => true,
        ]);

        $data = app(TestRequestFormReportDataBuilder::class)->buildFromDraft(
            [
                'customer_name' => 'ABC COMPANY',
                'sampling_date' => '2026-06-03',
                'sampling_apparatus' => ['APHA'],
                'method_of_sampling' => ['APHA'],
                'reason_of_collection' => ['CONTRACT'],
                'transport_condition' => ['CHILLER VEHICLE'],
                'sample_rows' => [
                    [
                        'sample_no' => '1',
                        'sample_description' => 'Chicken Salad',
                        'sample_type' => 'Ready To Eat',
                        'sample_condition' => 'Chilled',
                        'state_of_sample' => 'Semi Solid',
                    ],
                ],
            ],
            $sampleType,
            null,
            false
        );

        $this->assertSame('food', $data['variant']);
        $this->assertSame('TEST REQUEST FORM - FOOD', $data['formTitle']);
        $this->assertSame('ABC COMPANY', $data['customer']['customer_name']);
        $this->assertNotEmpty($data['company']);
        $this->assertArrayHasKey('companyHeader', $data);
        $this->assertSame('+971 4 123 4567', $data['companyHeader']['telephone']);
        $this->assertArrayNotHasKey('hexClusterSrc', $data);
        $this->assertTrue($data['sampleRows'][0]['state_of_sample']['SS']);
        $this->assertTrue($data['sampleRows'][0]['sample_type_checks']['Ready To Eat']);
        $this->assertTrue($data['sampleRows'][0]['sample_condition_checks']['Chilled']);
        $this->assertStringContainsString('3rd June 2026', $data['collection']['sampling_date']);
        $this->assertNotEmpty($data['collectionGrid']['rows']);
    }

    public function test_sample_type_condition_and_sampling_point_checks(): void
    {
        $this->assertSame(
            ['Raw' => true, 'Cooked' => false, 'Ready To Eat' => false],
            TestRequestFormReportDataBuilder::sampleTypeChecks('Raw')
        );

        $this->assertSame(
            ['Acceptable' => false, 'Chilled' => false, 'Frozen' => true, 'Ambient' => false],
            TestRequestFormReportDataBuilder::sampleConditionChecks('Frozen')
        );

        $this->assertSame(
            ['Tap' => false, 'Tank' => true, 'Pool' => false, 'Shower Head' => false, 'Others' => false],
            TestRequestFormReportDataBuilder::samplingPointChecks('Tank')
        );
    }

    public function test_build_from_draft_waste_water_includes_lws_036_and_field_checks(): void
    {
        $wasteWater = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Waste Water',
            'code' => 'SMP-WWTR',
            'active' => true,
        ]);

        $data = app(TestRequestFormReportDataBuilder::class)->buildFromDraft(
            [
                'customer_name' => 'ABC COMPANY',
                'job_number' => 'JOB-001',
                'sample_number' => 'WW-456',
                'sampling_date' => '2026-06-03',
                'sampling_time' => '10:30',
                'sampling_location' => 'Site A',
                'sample_description' => 'Effluent discharge point',
                'sampling_apparatus' => ['STERILE BOTTLE'],
                'method_of_sampling' => ['APHA'],
                'reason_of_collection' => ['CONTRACT'],
                'sampling_technique' => ['GRAB'],
                'sampling_source' => ['STP'],
                'sample_types_ww' => ['LIQUID'],
                'transport_condition' => ['AMBIENT'],
                'field_data_quantity' => '2',
                'field_data_ph' => '7.1',
                'field_data_requirements' => ['MICROBIOLOGY + CHEMISTRY'],
            ],
            $wasteWater,
            null,
            false
        );

        $this->assertSame('waste_water', $data['variant']);
        $this->assertStringContainsString('LWS/036', $data['documentRef']);
        $this->assertSame('WW-456', $data['wasteWaterFields']['sample_number']);
        $this->assertTrue($data['wasteWaterFields']['field_data_requirement_checks']['MICROBIOLOGY']);
        $this->assertTrue($data['wasteWaterFields']['field_data_requirement_checks']['CHEMISTRY']);
        $this->assertSame(['GRAB', 'COMPOSITE', 'OTHER'], $data['collectionGrid']['technique_ordered_keys']);
    }

    public function test_field_data_requirement_checks_maps_combined_option(): void
    {
        $checks = TestRequestFormReportDataBuilder::fieldDataRequirementChecks([
            'MICROBIOLOGY' => false,
            'CHEMISTRY' => false,
            'MICROBIOLOGY + CHEMISTRY' => true,
        ]);

        $this->assertTrue($checks['MICROBIOLOGY']);
        $this->assertTrue($checks['CHEMISTRY']);
    }
}
