<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
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
        $this->assertSame(
            [$analystA, $analystB],
            array_values($saved['analysts_by_element'][$inHouseA][$sectionId] ?? [])
        );
        $this->assertSame(
            [],
            array_values($saved['analysts_by_element'][$subcontracted][$sectionId] ?? [])
        );
        $this->assertSame(
            [$subcontracted],
            array_values($saved['subcontracted_parameter_keys'] ?? [])
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
        $this->assertSame(
            [$analystId],
            array_values($saved['analysts_by_element'][$elementAssigned][$sectionId] ?? [])
        );
        $this->assertSame(
            [],
            array_values($saved['analysts_by_element'][$elementEmpty][$sectionId] ?? [])
        );
    }

    #[Test]
    public function build_test_rows_auto_picks_element_operator_when_test_has_no_analysts(): void
    {
        $configId = (string) Str::uuid();
        $sectionId = (string) Str::uuid();
        $otherAnalystId = (string) Str::uuid();
        $elementEmpty = (string) Str::uuid();
        $elementAssigned = (string) Str::uuid();
        $elementSibling = (string) Str::uuid();

        $operator = \App\User::query()->create([
            'name' => 'Default Operator',
            'email' => 'operator-'.Str::uuid().'@example.test',
            'password' => bcrypt('secret'),
        ]);
        $operatorId = (string) $operator->id;

        \App\AnalysisElements::query()->create([
            'id' => $elementEmpty,
            'operator_id' => $operatorId,
            'lab_section_id' => $sectionId,
            'method' => 'Empty save',
            'level' => 1,
        ]);
        \App\AnalysisElements::query()->create([
            'id' => $elementAssigned,
            'operator_id' => $operatorId,
            'lab_section_id' => $sectionId,
            'method' => 'Already assigned',
            'level' => 1,
        ]);
        \App\AnalysisElements::query()->create([
            'id' => $elementSibling,
            'operator_id' => $operatorId,
            'lab_section_id' => $sectionId,
            'method' => 'Sibling',
            'level' => 1,
        ]);

        $enquiry = $this->makeEnquiryWithConfig([
            [
                'id' => $configId,
                'parameter_keys' => [$elementEmpty, $elementAssigned, $elementSibling],
                'parameter_lab_sections' => [
                    $elementEmpty => [$sectionId],
                    $elementAssigned => [$sectionId],
                    $elementSibling => [$sectionId],
                ],
                // Section rollup must not bleed onto tests without their own analysts.
                'analysts_by_lab_section' => [
                    $sectionId => [$otherAnalystId],
                ],
                'analysts_by_element' => [
                    $elementEmpty => [$sectionId => []],
                    $elementAssigned => [$sectionId => [$otherAnalystId]],
                ],
            ],
        ]);

        $this->bindConfigServiceForBuildRows();

        $rows = collect(app(SampleIntegrityCheckService::class)->buildTestRows($enquiry))
            ->keyBy('element_id');

        $this->assertSame(
            [$operatorId],
            array_values($rows[$elementEmpty]['analysts_by_lab_section'][$sectionId] ?? [])
        );
        $this->assertSame(
            [$otherAnalystId],
            array_values($rows[$elementAssigned]['analysts_by_lab_section'][$sectionId] ?? [])
        );
        $this->assertSame(
            [$operatorId],
            array_values($rows[$elementSibling]['analysts_by_lab_section'][$sectionId] ?? [])
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

    private function bindConfigServiceForBuildRows(): void
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
        $configService->shouldReceive('syncParameterLabSections')->andReturnUsing(fn (array $config) => $config);
        $configService->shouldReceive('normalizeSubcontractedParameterKeys')->andReturnUsing(function ($value, $parameterKeys = []) {
            $ids = is_array($value) ? array_values(array_filter(array_map('strval', $value))) : [];
            if ($parameterKeys === []) {
                return $ids;
            }
            $allowed = array_flip(array_map('strval', $parameterKeys));

            return array_values(array_filter($ids, static fn (string $id): bool => isset($allowed[$id])));
        });
        $this->app->instance(AcceptanceFormSampleConfigService::class, $configService);

        $this->app->instance(AcceptanceFormPricingService::class, Mockery::mock(AcceptanceFormPricingService::class));

        $subcontracting = Mockery::mock(SubcontractingAssignmentService::class);
        $subcontracting->shouldReceive('resolveSubcontractedElementIds')->andReturn([]);
        $this->app->instance(SubcontractingAssignmentService::class, $subcontracting);

        $this->app->instance(SubmissionRequestSampleLineService::class, Mockery::mock(SubmissionRequestSampleLineService::class));
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
        $configService->shouldReceive('normalizeSubcontractedParameterKeys')->andReturnUsing(function ($value, $parameterKeys = []) {
            $ids = is_array($value) ? array_values(array_filter(array_map('strval', $value))) : [];
            if ($parameterKeys === []) {
                return $ids;
            }
            $allowed = array_flip(array_map('strval', $parameterKeys));

            return array_values(array_filter($ids, static fn (string $id): bool => isset($allowed[$id])));
        });
        $this->app->instance(AcceptanceFormSampleConfigService::class, $configService);

        $this->app->instance(AcceptanceFormPricingService::class, Mockery::mock(AcceptanceFormPricingService::class));

        $this->app->instance(SubcontractingAssignmentService::class, Mockery::mock(SubcontractingAssignmentService::class));
        $this->app->instance(SubmissionRequestSampleLineService::class, Mockery::mock(SubmissionRequestSampleLineService::class));
    }
}
