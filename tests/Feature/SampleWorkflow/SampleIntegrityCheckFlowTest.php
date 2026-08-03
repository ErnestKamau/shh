<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\AcceptanceFormWizard;
use App\Livewire\Sampleworkflow\SampleIntegrityCheckPage;
use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\ContractCustomerService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SampleIntegrityCheckFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::create([
            'name' => 'Integrity Tester',
            'email' => 'integrity.tester@example.test',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_receive_only_handoff_sets_integrity_status_without_creating_job(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();

        $this->mockCommercialForm($instance);
        $this->mockAcceptancePrefill($instance);

        $configService = Mockery::mock(AcceptanceFormSampleConfigService::class)->makePartial();
        $configService->shouldReceive('normalizeConfigsForStorage')->andReturn([
            [
                'id' => (string) Str::uuid(),
                'parameter_keys' => [(string) Str::uuid()],
                'parameter_lab_sections' => [],
                'analysts_by_lab_section' => [],
            ],
        ]);
        $configService->shouldReceive('syncParameterKeysFromQuotationLines')->andReturnUsing(fn ($configs) => $configs);
        $configService->shouldReceive('resolveElementIdsForAnalysisTypes')->andReturn([]);
        $configService->shouldReceive('analysisTypeIdsFromConfig')->andReturn([]);
        $configService->shouldReceive('flattenToPerSampleConfigs')->andReturnUsing(fn ($configs) => $configs);
        $configService->shouldReceive('normalizeConfigsAnalysisTypeIds')->andReturnUsing(fn ($configs) => $configs);
        $configService->shouldReceive('syncParameterLabSections')->andReturnUsing(fn ($config) => $config);
        $configService->shouldReceive('syncParameterLabSectionsForConfigs')->andReturnUsing(fn ($configs) => $configs);
        $configService->shouldReceive('validateReceptionConfigs')->andReturnNull();
        $this->app->instance(AcceptanceFormSampleConfigService::class, $configService);

        $acceptanceService = Mockery::mock(AcceptanceFormService::class);
        $acceptanceService->shouldNotReceive('acceptWithDualSignatures');
        $this->app->instance(AcceptanceFormService::class, $acceptanceService);

        Livewire::actingAs($this->user)
            ->test(AcceptanceFormWizard::class)
            ->dispatch(
                'open-acceptance-wizard',
                submissionFormInstanceId: $instance->id,
                submissionRequestId: $enquiry->id,
                mode: 'receive_only',
            )
            ->assertSet('showModal', true)
            ->assertSet('wizardMode', 'receive_only')
            ->assertSet('showParametersOnConfig', false)
            ->assertSet('showParameterLabSectionsOnConfig', false)
            ->assertSet('showSectionAnalystsOnConfig', false)
            ->set('modeOfWork', 'Normal')
            ->set('lines', [[
                'line_no' => 1,
                'parameter_label' => 'Test',
                'unit_amount' => 10,
            ]])
            ->call('submitReceiveSamples')
            ->assertDispatched('acceptance-form-completed')
            ->assertSet('showModal', false);

        $enquiry->refresh();
        $this->assertSame(SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK, $enquiry->status);
        $this->assertNull($enquiry->sample_header_id);
        $this->assertNotEmpty($enquiry->enquiry_sample_configuration);
    }

    public function test_integrity_confirm_accept_creates_job_without_wizard(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();
        $enquiry->update(['status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK]);

        $configId = (string) Str::uuid();
        $elementId = (string) Str::uuid();
        $rowKey = $configId.'|'.$elementId;

        $integrityService = Mockery::mock(SampleIntegrityCheckService::class);
        $integrityService->shouldReceive('buildTestRows')->andReturn([[
            'row_key' => $rowKey,
            'config_id' => $configId,
            'sample_label' => 'S1',
            'element_id' => $elementId,
            'test_label' => 'E. coli',
            'lab_section_ids' => [(string) Str::uuid()],
            'analysts_by_lab_section' => [],
            'subcontracted' => false,
        ]]);
        $integrityService->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $integrityService->shouldReceive('persistIntegrityAssignments')
            ->twice()
            ->andReturnUsing(fn (SampleSubmissionRequest $e) => $e);
        $this->app->instance(SampleIntegrityCheckService::class, $integrityService);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values(array_map('strval', $value)) : [(string) $value];
            });
        });

        $completedForm = new AnalysisAcceptanceForm([
            'id' => (string) Str::uuid(),
            'status' => AnalysisAcceptanceForm::STATUS_COMPLETED,
            'sample_header_id' => (string) Str::uuid(),
            'crm_customer_id' => null,
            'sample_configuration_payload' => [],
        ]);
        $completedForm->exists = true;

        $this->mock(AcceptanceFormService::class, function ($mock) use ($completedForm, $instance, $enquiry): void {
            $mock->shouldReceive('acceptFromIntegrityCheck')
                ->once()
                ->withArgs(function (string $instanceId, string $enquiryId) use ($instance, $enquiry): bool {
                    return $instanceId === (string) $instance->id
                        && $enquiryId === (string) $enquiry->id;
                })
                ->andReturn($completedForm);
            $mock->shouldNotReceive('acceptWithDualSignatures');
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->call('openAcceptConfirm')
            ->assertSet('showAcceptConfirmModal', true)
            ->call('confirmAcceptSamples')
            ->assertSet('showAcceptConfirmModal', false)
            ->assertDispatched('acceptance-form-completed');
    }

    public function test_accept_allowed_when_subcontract_dispatch_pending(): void
    {
        $integrity = Mockery::mock(SampleSubmissionRequest::class)->makePartial();
        $integrity->status = SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK;
        $integrity->shouldReceive('needsSubcontractDispatch')->andReturn(true);

        $ready = Mockery::mock(SampleSubmissionRequest::class)->makePartial();
        $ready->status = SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION;
        $ready->shouldReceive('needsSubcontractDispatch')->andReturn(true);

        $service = new EnquiryReceptionReadinessService(
            Mockery::mock(ContractCustomerService::class)
        );

        $this->assertTrue($service->isEligibleForSampleAcceptance($integrity));
        $this->assertTrue($service->isEligibleForReceiveHandoff($ready));
    }

    public function test_integrity_page_persists_multi_section_and_subcontract_flags(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();
        $enquiry->update(['status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK]);

        $elementId = (string) Str::uuid();
        $sectionA = (string) Str::uuid();
        $sectionB = (string) Str::uuid();
        $configId = (string) Str::uuid();
        $rowKey = $configId.'|'.$elementId;

        $service = Mockery::mock(SampleIntegrityCheckService::class);
        $service->shouldReceive('buildTestRows')->andReturn([[
            'row_key' => $rowKey,
            'config_id' => $configId,
            'sample_label' => 'S1',
            'element_id' => $elementId,
            'test_label' => 'E. coli',
            'lab_section_ids' => [$sectionA],
            'analysts_by_lab_section' => [$sectionA => []],
            'subcontracted' => false,
        ]]);
        $service->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $service->shouldReceive('persistIntegrityAssignments')
            ->once()
            ->withArgs(function (SampleSubmissionRequest $saved, array $rows) use ($elementId, $sectionA, $sectionB): bool {
                $row = $rows[0] ?? [];

                return (string) ($row['element_id'] ?? '') === $elementId
                    && ($row['lab_section_ids'] ?? []) === [$sectionA, $sectionB]
                    && ! empty($row['subcontracted']);
            })
            ->andReturnUsing(fn (SampleSubmissionRequest $e) => $e);
        $this->app->instance(SampleIntegrityCheckService::class, $service);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock) use ($sectionA, $sectionB): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([
                ['id' => $sectionA, 'name' => 'Microbiology'],
                ['id' => $sectionB, 'name' => 'Chemical'],
            ]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values($value) : [(string) $value];
            });
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->set('testRows.0.lab_section_ids', [$sectionA, $sectionB])
            ->call('toggleSubcontracted', $rowKey)
            ->call('saveAssignments')
            ->assertSet('flashMessage', 'Integrity assignments saved.');
    }

    public function test_set_row_lab_sections_replaces_selection_and_prunes_dropped_analysts(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();
        $enquiry->update(['status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK]);

        $elementId = (string) Str::uuid();
        $sectionA = (string) Str::uuid();
        $sectionB = (string) Str::uuid();
        $analystId = (string) Str::uuid();
        $configId = (string) Str::uuid();
        $rowKey = $configId.'|'.$elementId;

        $service = Mockery::mock(SampleIntegrityCheckService::class);
        $service->shouldReceive('buildTestRows')->andReturn([[
            'row_key' => $rowKey,
            'config_id' => $configId,
            'sample_label' => 'S1',
            'element_id' => $elementId,
            'test_label' => 'E. coli',
            'lab_section_ids' => [$sectionA],
            'analysts_by_lab_section' => [$sectionA => [$analystId]],
            'subcontracted' => false,
        ]]);
        $service->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $this->app->instance(SampleIntegrityCheckService::class, $service);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock) use ($sectionA, $sectionB): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([
                ['id' => $sectionA, 'name' => 'Microbiology'],
                ['id' => $sectionB, 'name' => 'Chemical'],
            ]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values(array_map('strval', $value)) : [(string) $value];
            });
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->assertSet('selectedSampleKey', $configId)
            ->call('setRowLabSections', $rowKey, [$sectionB])
            ->assertSet('testRows.0.lab_section_ids', [$sectionB])
            ->assertSet('testRows.0.analysts_by_lab_section', [$sectionB => []]);
    }

    public function test_master_detail_bulk_lab_sections_apply_to_selected_rows_only(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();
        $enquiry->update(['status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK]);

        $configA = (string) Str::uuid();
        $configB = (string) Str::uuid();
        $elementA = (string) Str::uuid();
        $elementB = (string) Str::uuid();
        $elementC = (string) Str::uuid();
        $sectionA = (string) Str::uuid();
        $sectionB = (string) Str::uuid();
        $rowA = $configA.'|'.$elementA;
        $rowB = $configA.'|'.$elementB;
        $rowC = $configB.'|'.$elementC;

        $service = Mockery::mock(SampleIntegrityCheckService::class);
        $service->shouldReceive('buildTestRows')->andReturn([
            [
                'row_key' => $rowA,
                'config_id' => $configA,
                'sample_label' => 'S1',
                'element_id' => $elementA,
                'test_label' => 'Aflatoxin',
                'lab_section_ids' => [],
                'analysts_by_lab_section' => [],
                'subcontracted' => false,
            ],
            [
                'row_key' => $rowB,
                'config_id' => $configA,
                'sample_label' => 'S1',
                'element_id' => $elementB,
                'test_label' => 'Coliforms',
                'lab_section_ids' => [$sectionA],
                'analysts_by_lab_section' => [$sectionA => []],
                'subcontracted' => false,
            ],
            [
                'row_key' => $rowC,
                'config_id' => $configB,
                'sample_label' => 'S2',
                'element_id' => $elementC,
                'test_label' => 'Moisture',
                'lab_section_ids' => [],
                'analysts_by_lab_section' => [],
                'subcontracted' => false,
            ],
        ]);
        $service->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $this->app->instance(SampleIntegrityCheckService::class, $service);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock) use ($sectionA, $sectionB): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([
                ['id' => $sectionA, 'name' => 'Microbiology'],
                ['id' => $sectionB, 'name' => 'Chemistry'],
            ]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values(array_map('strval', $value)) : [(string) $value];
            });
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->assertSet('selectedSampleKey', $configA)
            ->call('selectSample', $configB)
            ->assertSet('selectedSampleKey', $configB)
            ->call('selectSample', $configA)
            ->call('toggleRowSelection', $rowA)
            ->call('toggleRowSelection', $rowB)
            ->call('applyBulkLabSections', [$sectionB])
            ->assertSet('testRows.0.lab_section_ids', [$sectionB])
            ->assertSet('testRows.1.lab_section_ids', [$sectionA, $sectionB])
            ->assertSet('testRows.1.analysts_by_lab_section', [
                $sectionA => [],
                $sectionB => [],
            ])
            ->assertSet('testRows.2.lab_section_ids', [])
            ->assertSet('selectedRowKeys', [$rowA, $rowB])
            ->assertSet('flashMessage', '2 test(s) updated with lab section(s).');
    }

    public function test_copy_assignments_from_first_selected_updates_siblings(): void
    {
        [$instance, $enquiry] = $this->createReadyForReceptionPair();
        $enquiry->update(['status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK]);

        $configId = (string) Str::uuid();
        $elementA = (string) Str::uuid();
        $elementB = (string) Str::uuid();
        $sectionId = (string) Str::uuid();
        $analystId = (string) Str::uuid();
        $rowA = $configId.'|'.$elementA;
        $rowB = $configId.'|'.$elementB;

        $service = Mockery::mock(SampleIntegrityCheckService::class);
        $service->shouldReceive('buildTestRows')->andReturn([
            [
                'row_key' => $rowA,
                'config_id' => $configId,
                'sample_label' => 'S1',
                'element_id' => $elementA,
                'test_label' => 'Test A',
                'lab_section_ids' => [$sectionId],
                'analysts_by_lab_section' => [$sectionId => [$analystId]],
                'subcontracted' => true,
            ],
            [
                'row_key' => $rowB,
                'config_id' => $configId,
                'sample_label' => 'S1',
                'element_id' => $elementB,
                'test_label' => 'Test B',
                'lab_section_ids' => [],
                'analysts_by_lab_section' => [],
                'subcontracted' => false,
            ],
        ]);
        $service->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $this->app->instance(SampleIntegrityCheckService::class, $service);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock) use ($sectionId): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([
                ['id' => $sectionId, 'name' => 'Chemistry'],
            ]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values(array_map('strval', $value)) : [(string) $value];
            });
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->call('toggleRowSelection', $rowA)
            ->set('bulkAnalystLabSectionIds', [$sectionId])
            ->assertSet('bulkAnalystIdsBySection', [$sectionId => [$analystId]])
            ->call('toggleRowSelection', $rowB)
            ->call('copyAssignmentsFromFirstSelected')
            ->assertSet('testRows.1.lab_section_ids', [$sectionId])
            ->assertSet('testRows.1.analysts_by_lab_section', [$sectionId => [$analystId]])
            ->assertSet('testRows.1.subcontracted', true)
            ->assertSet('selectedRowKeys', []);
    }

    public function test_apply_bulk_analysts_assigns_section_and_analysts_to_selected_rows(): void
    {
        [$instance] = $this->createReadyForReceptionPair();

        $configId = (string) Str::uuid();
        $elementA = (string) Str::uuid();
        $elementB = (string) Str::uuid();
        $elementC = (string) Str::uuid();
        $sectionChem = (string) Str::uuid();
        $sectionBio = (string) Str::uuid();
        $analystA = (string) Str::uuid();
        $analystB = (string) Str::uuid();
        $rowA = $configId.'|'.$elementA;
        $rowB = $configId.'|'.$elementB;
        $rowC = $configId.'|'.$elementC;

        $service = Mockery::mock(SampleIntegrityCheckService::class);
        $service->shouldReceive('buildTestRows')->andReturn([
            [
                'row_key' => $rowA,
                'config_id' => $configId,
                'sample_label' => 'S1',
                'element_id' => $elementA,
                'test_label' => 'Test A',
                'lab_section_ids' => [$sectionChem, $sectionBio],
                'analysts_by_lab_section' => [
                    $sectionChem => [],
                    $sectionBio => [],
                ],
                'subcontracted' => false,
            ],
            [
                'row_key' => $rowB,
                'config_id' => $configId,
                'sample_label' => 'S1',
                'element_id' => $elementB,
                'test_label' => 'Test B',
                'lab_section_ids' => [$sectionChem],
                'analysts_by_lab_section' => [$sectionChem => []],
                'subcontracted' => false,
            ],
            [
                'row_key' => $rowC,
                'config_id' => $configId,
                'sample_label' => 'S1',
                'element_id' => $elementC,
                'test_label' => 'Test C',
                'lab_section_ids' => [],
                'analysts_by_lab_section' => [],
                'subcontracted' => false,
            ],
        ]);
        $service->shouldReceive('requestInfoCard')->andReturn(['fields' => [], 'remarks' => null]);
        $this->app->instance(SampleIntegrityCheckService::class, $service);

        $this->mock(AcceptanceFormSampleConfigService::class, function ($mock) use ($sectionChem, $sectionBio, $analystA, $analystB): void {
            $mock->shouldReceive('labSectionsForPicker')->andReturn([
                ['id' => $sectionChem, 'name' => 'Chemistry'],
                ['id' => $sectionBio, 'name' => 'Biological'],
            ]);
            $mock->shouldReceive('analystsForLabSectionPicker')
                ->with($sectionChem)
                ->andReturn([
                    ['id' => $analystA, 'name' => 'Alice'],
                ]);
            $mock->shouldReceive('analystsForLabSectionPicker')
                ->with($sectionBio)
                ->andReturn([
                    ['id' => $analystB, 'name' => 'Bob'],
                ]);
            $mock->shouldReceive('analystsForLabSectionPicker')->andReturn([]);
            $mock->shouldReceive('normalizeLabSectionIds')->andReturnUsing(function ($value) {
                return is_array($value) ? array_values(array_map('strval', $value)) : [(string) $value];
            });
        });

        Livewire::actingAs($this->user)
            ->test(SampleIntegrityCheckPage::class, [
                'submissionFormId' => $instance->submission_form_id,
                'instanceId' => $instance->id,
            ])
            ->call('toggleRowSelection', $rowA)
            ->call('toggleRowSelection', $rowB)
            ->set('bulkAnalystLabSectionIds', [$sectionChem, $sectionBio])
            ->call('toggleBulkAnalyst', $sectionChem, $analystA)
            ->call('toggleBulkAnalyst', $sectionBio, $analystB)
            ->call('applyBulkAnalysts')
            ->assertSet('testRows.0.analysts_by_lab_section', [
                $sectionChem => [$analystA],
                $sectionBio => [$analystB],
            ])
            ->assertSet('testRows.1.analysts_by_lab_section', [
                $sectionChem => [$analystA],
            ])
            ->assertSet('testRows.2.lab_section_ids', [])
            ->assertSet('testRows.2.analysts_by_lab_section', [])
            ->assertSet('selectedRowKeys', [])
            ->assertSet('bulkAnalystLabSectionIds', [])
            ->assertSet('flashMessage', '2 test(s) updated with analyst(s).');
    }

    public function test_process_enquiry_step_three_no_longer_exposes_subcontract_column(): void
    {
        $blade = File::get(resource_path('views/livewire/sampleworkflow/process-enquiry-wizard.blade.php'));

        $this->assertStringNotContainsString('>Subcontract</th>', $blade);
        $this->assertStringNotContainsString('lines.{{ $index }}.subcontracted', $blade);
    }

    public function test_receiving_tabs_include_sample_integrity_check(): void
    {
        $tabs = \App\Livewire\Sampleworkflow\WorkflowBoard::receivingRequestTabs();

        $this->assertArrayHasKey('sample_integrity_check', $tabs);
        $this->assertSame('Sample Integrity & Acceptance Check', $tabs['sample_integrity_check']);
    }

    public function test_open_sample_integrity_check_page_redirects_for_selected_instance(): void
    {
        [$instance] = $this->createReadyForReceptionPair();

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Sampleworkflow\WorkflowBoard::class, [
                'status' => 'Samples Receiving',
            ])
            ->set('workflowSubTab', 'sample_integrity_check')
            ->call('openSampleIntegrityCheckPageFromSelection', [$instance->id])
            ->assertRedirect(route('submission-forms.instances.sample-integrity-check', [
                'submissionForm' => $instance->submission_form_id,
                'instance' => $instance->id,
            ]));
    }

    /**
     * @return array{0: SubmissionFormInstance, 1: SampleSubmissionRequest}
     */
    private function createReadyForReceptionPair(): array
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Integrity Customer',
            'code' => 'INT001',
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Integrity TRF',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_id' => $form->id,
            'crm_customer_id' => $customer->id,
            'status' => 'submitted',
            'submitted_by' => $this->user->id,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_instance_id' => $instance->id,
            'crm_customer_id' => $customer->id,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'walk_in',
            'request_number' => random_int(1000, 9999),
            'quotation_accepted_at' => now(),
        ]);

        return [$instance, $enquiry];
    }

    private function mockCommercialForm(SubmissionFormInstance $instance): void
    {
        $this->mock(CommercialEnquiryFromFormService::class, function ($mock) use ($instance): void {
            $mock->shouldReceive('isCommercialTestRequestForm')
                ->andReturnUsing(fn ($candidate) => (string) $candidate->id === (string) $instance->id);
        });
    }

    private function mockAcceptancePrefill(SubmissionFormInstance $instance): void
    {
        $this->mock(AcceptanceFormPricingService::class, function ($mock) use ($instance): void {
            $mock->shouldReceive('buildPrefillFromSelection')->andReturn([
                'customer_id' => (string) $instance->crm_customer_id,
                'customer_name' => 'Integrity Customer',
                'number_of_samples' => 1,
                'mode_of_work' => 'Normal',
                'request_date' => '2026-07-30',
                'date_of_sampling' => null,
                'quotation_locked' => false,
                'lines' => [[
                    'line_no' => 1,
                    'row_index' => 0,
                    'sample_type_id' => (string) Str::uuid(),
                    'analysis_type_id' => (string) Str::uuid(),
                    'parameter_label' => 'Test',
                    'unit_amount' => 50,
                    'number_of_samples' => 1,
                    'is_approved' => true,
                    'sort_order' => 0,
                    'analysis_element_id' => (string) Str::uuid(),
                ]],
            ]);
            $mock->shouldReceive('deduplicateRedundantAnalysisTypeLines')
                ->andReturnUsing(fn (array $lines) => $lines);
        });
    }
}
