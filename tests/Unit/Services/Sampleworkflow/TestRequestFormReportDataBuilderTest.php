<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\TestRequestForm;
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

        $this->assertSame('food', TestRequestForm::resolveReportVariant($sampleType));
    }

    public function test_resolve_report_variant_returns_water_for_water_and_other_types(): void
    {
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'SMP-WTR',
            'active' => true,
        ]);

        $wasteWater = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Waste Water',
            'code' => 'SMP-WWTR',
            'active' => true,
        ]);

        $other = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Soil',
            'code' => 'SMP-SOIL',
            'active' => true,
        ]);

        $this->assertSame('water', TestRequestForm::resolveReportVariant($water));
        $this->assertSame('water', TestRequestForm::resolveReportVariant($wasteWater));
        $this->assertSame('water', TestRequestForm::resolveReportVariant($other));
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
                'sample_rows' => [
                    [
                        'sample_no' => '1',
                        'sample_description' => 'Chicken Salad',
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
        $this->assertTrue($data['sampleRows'][0]['state_of_sample']['SS']);
    }
}
