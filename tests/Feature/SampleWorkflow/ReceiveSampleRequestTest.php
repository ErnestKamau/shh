<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\CRM\CRMCustomer;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use App\Models\Workflow\Approval;
use App\Models\Workflow\ApprovalLog;
use App\Models\Workflow\ChecklistResponse;
use App\QuotationHeader;
use App\Services\WorkflowService;
use App\Models\System\SystemConfiguration;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceiveSampleRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Approval $approval;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::create([
            'name' => 'Receiving Officer',
            'email' => 'receiving.officer@example.test',
            'password' => bcrypt('password'),
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $this->user->assignRole($adminRole);

        SystemConfiguration::query()->create([
            'key' => 'Samples En-Route',
            'value' => 'Samples En-Route',
            'status' => 1,
        ]);

        $workflowService = app(WorkflowService::class);
        $this->approval = $workflowService->saveApproval([
            'stage_name' => 'Samples Receiving',
            'code' => 'sro_receiving_sample',
            'name' => 'SRO Receiving Sample',
            'order' => 1,
            'is_active' => true,
        ]);

        $workflowService->saveChecklistItem($this->approval->id, [
            'label' => 'Sample labels verified',
            'type' => 'checkbox',
            'is_required' => true,
            'order' => 1,
        ]);

        $workflowService->saveChecklistItem($this->approval->id, [
            'label' => 'Chain of custody received',
            'type' => 'checkbox',
            'is_required' => true,
            'order' => 2,
        ]);
    }

    public function test_confirm_receive_marks_submitted_instances_as_received_with_checklist(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $sampleType = $this->createSampleType('Water', 'SMP-WTR');

        $itemIds = $this->approval->fresh('checklistItems')->checklistItems->pluck('id')->all();
        $responses = [];
        foreach ($itemIds as $itemId) {
            $responses[$itemId] = true;
        }

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$instance->id],
                'selectedFormSummaries' => [
                    ['id' => $instance->id, 'label' => 'CR001', 'customer' => 'Acme'],
                ],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Acme',
                'sampling_date' => now()->toDateString(),
                'sample_rows' => [[
                    'sample_no' => '1',
                    'sample_description' => 'Tap Water',
                ]],
            ])
            ->set('responses', $responses)
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $instance->refresh();
        $this->assertSame('received', $instance->status);

        $this->assertDatabaseHas('workflow_approval_logs', [
            'submission_form_instance_id' => $instance->id,
            'approval_id' => $this->approval->id,
            'status' => 'approved',
        ]);

        $this->assertSame(
            count($itemIds),
            ChecklistResponse::query()
                ->where('submission_form_instance_id', $instance->id)
                ->where('approval_id', $this->approval->id)
                ->count()
        );
    }

    public function test_confirm_receive_blocks_commercial_trf_without_accepted_quotation(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_QUOTATION_SENT);

        $itemIds = $this->approval->fresh('checklistItems')->checklistItems->pluck('id')->all();
        $responses = array_fill_keys($itemIds, true);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('responses', $responses)
            ->call('confirmReceive')
            ->assertHasErrors(['selection']);

        $this->assertSame('submitted', $instance->fresh()->status);
    }

    public function test_confirm_receive_allows_commercial_trf_when_ready_for_reception(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260609-001',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
        ]);
        $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $quotation->id);

        $itemIds = $this->approval->fresh('checklistItems')->checklistItems->pluck('id')->all();
        $responses = array_fill_keys($itemIds, true);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('responses', $responses)
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $this->assertSame('received', $instance->fresh()->status);
    }

    public function test_samples_receiving_review_quotation_action_opens_modal_for_under_review_requests(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260629-001',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
        ]);
        $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW, $quotation->id);

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('openReviewQuotationFromInstances', [$instance->id])
            ->assertDispatched('process-enquiry-open');
    }

    public function test_workflow_board_po_modal_requires_po_number_for_credit_customers(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $creditStatus = SystemConfiguration::query()->create([
            'key' => 'Account holder - credit',
            'value' => 'Account holder credit',
            'status' => 1,
        ]);
        $customer = CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Credit Customer',
            'code' => 'CR-CREDIT',
            'active' => 1,
            'account_status' => $creditStatus->id,
        ]);

        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260629-002',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
        ]);

        $enquiry = $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, $quotation->id, $customer->id);

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('openPoCaptureModal', $enquiry->id)
            ->assertSet('poRequiresPo', true)
            ->set('clientPoNumber', '')
            ->call('submitPoAndReadyForReception')
            ->assertHasErrors(['client_po_number']);

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, $enquiry->fresh()->status);
    }

    public function test_walk_in_capture_creates_trfi_with_normalized_form_data(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Walk-in Customer',
                'customer_phone' => '555-0100',
                'mobile_number' => '555-0200',
                'sampling_date' => '2026-06-18',
                'sample_rows' => [[
                    'sample_no' => '1',
                    'sample_description' => 'Tap Water',
                ]],
            ])
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $trfi = \App\Models\TestRequestFormInstance::query()->first();
        $this->assertNotNull($trfi);
        $this->assertSame(\App\Models\TestRequestFormInstance::CHANNEL_WALK_IN, $trfi->source_channel);
        $this->assertSame('Walk-in Customer', $trfi->form_data['customer_name']);
        $this->assertSame('555-0100', $trfi->form_data['customer_phone']);
        $this->assertSame('555-0200', $trfi->form_data['mobile_number']);
        $this->assertSame('2026-06-18', $trfi->form_data['sampling_date']);
        $this->assertSame('Tap Water', $trfi->form_data['sample_rows'][0]['sample_description']);

        $this->assertDatabaseHas('sample_submission_requests', [
            'test_request_form_instance_id' => $trfi->id,
            'source_channel' => 'walk_in',
        ]);
    }

    public function test_walk_in_capture_succeeds_without_sampling_date(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Walk-in Customer',
                'sample_rows' => [[
                    'sample_description' => 'Tap Water',
                    'test_category' => 'chemistry',
                ]],
            ])
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $trfi = \App\Models\TestRequestFormInstance::query()->first();
        $this->assertNotNull($trfi);
        $this->assertArrayNotHasKey('sampling_date', $trfi->form_data);
    }

    public function test_walk_in_capture_persists_sample_quantity_and_unit(): void
    {
        $sampleType = $this->createSampleType('Food', 'SMP-FOOD');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Walk-in Customer',
                'sample_rows' => [[
                    'sample_description' => 'Chicken',
                    'sample_quantity' => '2',
                    'sample_quantity_unit' => 'kg',
                    'test_category' => 'microbiology',
                ]],
            ])
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $trfi = \App\Models\TestRequestFormInstance::query()->first();
        $this->assertSame('2', $trfi->form_data['sample_rows'][0]['sample_quantity']);
        $this->assertSame('kg', $trfi->form_data['sample_rows'][0]['sample_quantity_unit']);
    }

    public function test_walk_in_capture_requires_customer_name(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR-REQ');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'sample_description' => ['Tap Water'],
            ])
            ->call('confirmReceive')
            ->assertHasErrors(['formData.customer_name'])
            ->assertDispatched('notify', type: 'error', message: 'Customer name is required in Customer details.')
            ->assertNotDispatched('receive-completed');
    }

    public function test_walk_in_capture_requires_at_least_one_sample_row(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR-ROW');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $portalForm->id,
            'title' => 'Test & sample information',
            'section_type' => 'rows_section',
            'sort_order' => 0,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'rows',
            'sort_order' => 0,
        ]);

        SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'rich_text',
            'label' => 'Sample description',
            'name' => 'sample_description',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Walk-in Customer',
                'sample_description' => [''],
            ])
            ->call('confirmReceive')
            ->assertHasErrors(['formData.sample_description.0'])
            ->assertDispatched('notify')
            ->assertNotDispatched('receive-completed');
    }

    public function test_physical_check_in_persists_trf_metadata_on_linked_trfi(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260609-002',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
        ]);
        $enquiry = $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $quotation->id);

        $trfi = \App\Models\TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => TestRequestForm::query()->create([
                'id' => (string) Str::uuid7(),
                'name' => 'TRF',
                'code' => 'TRF-TEST',
                'sample_type_id' => $this->createSampleType('Water', 'WTR-2')->id,
                'form_fields' => ['sections' => []],
                'is_active' => true,
            ])->id,
            'submission_form_instance_id' => $instance->id,
            'sample_submission_request_id' => $enquiry->id,
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [],
        ]);

        $instance->update(['test_request_form_instance_id' => $trfi->id]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('checkInTrfFields.'.$instance->id.'.statement_of_conformity', 'YES')
            ->set('checkInTrfFields.'.$instance->id.'.sampled_by', 'John Doe / E123')
            ->set('checkInTrfFields.'.$instance->id.'.customer_rep_contact', '+971 4 000 0000')
            ->set('checkInTrfFields.'.$instance->id.'.remarks', 'Checked at reception')
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $trfi->refresh();
        $enquiry->refresh();

        $this->assertSame('YES', $trfi->form_data['statement_of_conformity']);
        $this->assertSame('John Doe / E123', $trfi->form_data['sampled_by']);
        $this->assertSame('Checked at reception', $trfi->form_data['remarks']);
        $this->assertSame('YES', $enquiry->statement_of_conformity);
    }

    public function test_confirm_receive_applies_same_checklist_to_multiple_instances(): void
    {
        $form = $this->createTemplateForm();
        $first = $this->createSubmittedInstance($form, ['form_number' => 'CR001']);
        $second = $this->createSubmittedInstance($form, ['form_number' => 'CR002']);
        $sampleType = $this->createSampleType('Water', 'SMP-WTR');

        $itemIds = $this->approval->fresh('checklistItems')->checklistItems->pluck('id')->all();
        $responses = array_fill_keys($itemIds, true);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$first->id, $second->id],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData', [
                'customer_name' => 'Acme',
                'sampling_date' => now()->toDateString(),
                'sample_rows' => [[
                    'sample_no' => '1',
                    'sample_description' => 'Tap Water',
                ]],
            ])
            ->set('responses', $responses)
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');

        $this->assertSame('received', $first->fresh()->status);
        $this->assertSame('received', $second->fresh()->status);

        $this->assertSame(2, ApprovalLog::query()
            ->where('approval_id', $this->approval->id)
            ->whereIn('submission_form_instance_id', [$first->id, $second->id])
            ->count());
    }

    public function test_mark_as_in_review_moves_received_instance_with_audit_log(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form, ['status' => 'received']);

        $this->assertTrue($instance->markAsInReview($this->user, 'Send to analyst queue.'));

        $instance->refresh();

        $this->assertSame('in_review', $instance->status);
        $this->assertSame('Send to analyst queue.', $instance->review_notes);

        $this->assertDatabaseHas('submission_form_audit_logs', [
            'submission_form_instance_id' => $instance->id,
            'action' => 'sent_for_analyst_review',
            'user_id' => $this->user->id,
        ]);
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer Request Template',
            'document_code' => 'TEST/RECV',
            'description' => 'Receiving test form',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);
    }

    private function createCommercialTrfForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form — Water',
            'document_code' => 'TRF-WATER',
            'description' => 'Commercial TRF',
            'naming_convention_prefix' => 'TRF',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);
    }

    private function createSubmittedInstance(SubmissionForm $form, array $overrides = []): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Receiving test instance',
            'form_number' => 'CR100',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'priority' => 'normal',
        ], $overrides));
    }

    private function createEnquiryForInstance(
        SubmissionFormInstance $instance,
        string $status,
        ?string $acceptedQuotationId = null,
        ?string $crmCustomerId = null,
    ): SampleSubmissionRequest {
        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => $status,
            'source_channel' => 'portal',
            'accepted_quotation_header_id' => $acceptedQuotationId,
            'current_quotation_header_id' => $acceptedQuotationId,
            'crm_customer_id' => $crmCustomerId,
            'quotation_accepted_at' => in_array($status, [
                SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
                SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            ], true) ? now() : null,
            'number_of_samples' => 1,
        ]);
    }

    private function createSampleType(string $name, string $code): \App\SampleType
    {
        TestRequestForm::seedDefaults();

        return \App\SampleType::query()->firstOrCreate(
            ['code' => $code],
            [
                'id' => (string) Str::uuid7(),
                'name' => $name,
                'active' => true,
            ]
        );
    }
}
