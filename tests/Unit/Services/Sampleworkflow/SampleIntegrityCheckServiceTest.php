<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
use App\Services\Sampleworkflow\SubcontractingAssignmentService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SampleIntegrityCheckServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function persist_keeps_shared_section_analysts_when_subcontracted_row_has_none(): void
    {
        $configId = (string) Str::uuid();
        $sectionId = (string) Str::uuid();
        $analystA = (string) Str::uuid();
        $analystB = (string) Str::uuid();
        $inHouseA = (string) Str::uuid();
        $inHouseB = (string) Str::uuid();
        $subcontracted = (string) Str::uuid();

        $enquiry = $this->makeEnquiryWithConfig([
            [
                'id' => $configId,
                'parameter_keys' => [$inHouseA, $inHouseB, $subcontracted],
                'parameter_lab_sections' => [
                    $inHouseA => [$sectionId],
                    $inHouseB => [$sectionId],
                    $subcontracted => [$sectionId],
                ],
                'analysts_by_lab_section' => [
                    $sectionId => [],
                ],
            ],
        ]);

        $this->bindConfigServicePassthrough();

        $service = app(SampleIntegrityCheckService::class);

        $service->persistIntegrityAssignments($enquiry, [
            [
                'config_id' => $configId,
                'element_id' => $inHouseA,
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => [$analystA, $analystB]],
                'subcontracted' => false,
            ],
            [
                'config_id' => $configId,
                'element_id' => $inHouseB,
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => [$analystA, $analystB]],
                'subcontracted' => false,
            ],
            [
                'config_id' => $configId,
                'element_id' => $subcontracted,
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => []],
                'subcontracted' => true,
            ],
        ]);

        $enquiry->refresh();
        $saved = $enquiry->enquiry_sample_configuration[0] ?? [];

        $this->assertSame(
            [$analystA, $analystB],
            array_values($saved['analysts_by_lab_section'][$sectionId] ?? [])
        );
    }

    #[Test]
    public function persist_prefers_non_empty_analysts_across_shared_section_rows(): void
    {
        $configId = (string) Str::uuid();
        $sectionId = (string) Str::uuid();
        $analystId = (string) Str::uuid();
        $elementAssigned = (string) Str::uuid();
        $elementEmpty = (string) Str::uuid();

        $enquiry = $this->makeEnquiryWithConfig([
            [
                'id' => $configId,
                'parameter_keys' => [$elementAssigned, $elementEmpty],
                'parameter_lab_sections' => [
                    $elementAssigned => [$sectionId],
                    $elementEmpty => [$sectionId],
                ],
                'analysts_by_lab_section' => [
                    $sectionId => [],
                ],
            ],
        ]);

        $this->bindConfigServicePassthrough();

        app(SampleIntegrityCheckService::class)->persistIntegrityAssignments($enquiry, [
            [
                'config_id' => $configId,
                'element_id' => $elementAssigned,
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => [$analystId]],
                'subcontracted' => false,
            ],
            [
                'config_id' => $configId,
                'element_id' => $elementEmpty,
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => []],
                'subcontracted' => false,
            ],
        ]);

        $enquiry->refresh();
        $saved = $enquiry->enquiry_sample_configuration[0] ?? [];

        $this->assertSame(
            [$analystId],
            array_values($saved['analysts_by_lab_section'][$sectionId] ?? [])
        );
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     */
    private function makeEnquiryWithConfig(array $configs): SampleSubmissionRequest
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Integrity Persist Customer',
            'code' => 'INTPERSIST',
        ]);

        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customer->id,
            'status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
            'source_channel' => 'walk_in',
            'request_number' => random_int(1000, 9999),
            'enquiry_sample_configuration' => $configs,
        ]);
    }

    private function bindConfigServicePassthrough(): void
    {
        $configService = Mockery::mock(AcceptanceFormSampleConfigService::class);
        $configService->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
            if (! is_array($value)) {
                $value = $value !== null && $value !== '' ? [(string) $value] : [];
            }

            return array_values(array_filter(array_map('strval', $value), static fn (string $id): bool => $id !== ''));
        });
        $configService->shouldReceive('flattenToPerSampleConfigs')->andReturnUsing(fn (array $configs) => $configs);
        $configService->shouldReceive('normalizeConfigsAnalysisTypeIds')->andReturnUsing(fn (array $configs) => $configs);
        $configService->shouldReceive('syncParameterLabSectionsForConfigs')->andReturnUsing(fn (array $configs) => $configs);
        $configService->shouldReceive('normalizeConfigsForStorage')->andReturnUsing(fn (array $configs) => $configs);
        $this->app->instance(AcceptanceFormSampleConfigService::class, $configService);

        $this->app->instance(AcceptanceFormPricingService::class, Mockery::mock(AcceptanceFormPricingService::class));

        $readiness = Mockery::mock(EnquiryReceptionReadinessService::class);
        $readiness->shouldReceive('resolveAcceptedQuotation')->andReturn(null);
        $this->app->instance(EnquiryReceptionReadinessService::class, $readiness);

        $this->app->instance(SubcontractingAssignmentService::class, Mockery::mock(SubcontractingAssignmentService::class));
        $this->app->instance(SubmissionRequestSampleLineService::class, Mockery::mock(SubmissionRequestSampleLineService::class));
    }
}
