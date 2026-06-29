<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionForm;
use App\SampleType;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalSubmissionFormAccessWaterTrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_water_trf_document_code_when_sample_type_not_linked(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form - Water',
            'document_code' => TrfDocumentCodeForSampleType::WATER,
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $resolved = app(PortalSubmissionFormAccess::class)->testRequestFormForSampleType((string) $sampleType->id);

        $this->assertNotNull($resolved);
        $this->assertSame($form->id, $resolved->id);
    }
}
