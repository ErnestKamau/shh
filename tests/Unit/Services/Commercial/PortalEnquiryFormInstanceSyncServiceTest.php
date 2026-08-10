<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\SubmissionForm;
use App\SampleType;
use App\Services\Commercial\PortalEnquiryFormInstanceSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalEnquiryFormInstanceSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_resolves_water_trf_by_document_code_without_sample_type_pivot(): void
    {
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $resolved = app(PortalEnquiryFormInstanceSyncService::class)
            ->resolveSubmissionFormForSampleType((string) $water->id);

        $this->assertNotNull($resolved);
        $this->assertSame('TRF-WATER-020', $resolved->document_code);
    }

    #[Test]
    public function it_resolves_canonical_water_trf_when_multiple_portal_trfs_are_linked(): void
    {
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $waterTrf = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $genericTrf = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Request Form',
            'document_code' => 'TRF-',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $waterTrf->sampleTypes()->attach($water->id);
        $genericTrf->sampleTypes()->attach($water->id);

        $resolved = app(PortalEnquiryFormInstanceSyncService::class)
            ->resolveSubmissionFormForSampleType((string) $water->id);

        $this->assertNotNull($resolved);
        $this->assertSame('TRF-WATER-020', $resolved->document_code);
    }
}
