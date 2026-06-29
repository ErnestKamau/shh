<?php

namespace Tests\Unit\SubmissionForm;

use App\SampleType;
use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class TrfDocumentCodeForSampleTypeTest extends TestCase
{
    private TrfDocumentCodeForSampleType $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new TrfDocumentCodeForSampleType();
    }

    public function test_resolves_food_document_code(): void
    {
        $sampleType = new SampleType([
            'name' => 'Food',
            'code' => 'FOOD',
        ]);

        $this->assertSame(TrfDocumentCodeForSampleType::FOOD, $this->resolver->resolve($sampleType));
    }

    public function test_resolves_water_document_code_for_wtr_code(): void
    {
        $sampleType = new SampleType([
            'name' => 'Water',
            'code' => 'WTR',
        ]);

        $this->assertSame(TrfDocumentCodeForSampleType::WATER, $this->resolver->resolve($sampleType));
    }

    public function test_resolves_waste_water_before_plain_water(): void
    {
        $sampleType = new SampleType([
            'name' => 'Waste Water',
            'code' => 'SMP WWTR',
        ]);

        $this->assertSame(TrfDocumentCodeForSampleType::WASTE_WATER, $this->resolver->resolve($sampleType));
    }

    public function test_food_sample_type_labels_include_ready_to_eat(): void
    {
        $this->assertTrue($this->resolver->isFoodSampleTypeLabel('Ready To Eat'));
        $this->assertFalse($this->resolver->isFoodSampleTypeLabel((string) Str::uuid()));
    }
}
