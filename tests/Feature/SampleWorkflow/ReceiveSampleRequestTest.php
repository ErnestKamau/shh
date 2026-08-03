<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
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
            ->assertSet('showQuotationAcceptanceModal', true)
            ->assertSet('quotationAcceptancePoOnly', true)
            ->assertSet('poRequiresPo', true)
            ->set('clientPoNumber', '')
            ->call('submitPoAndReadyForReception')
            ->assertHasErrors(['client_po_number']);

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, $enquiry->fresh()->status);
    }

    public function test_walk_in_capture_creates_sfi_with_normalized_form_data(): void
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

        $sfi = \App\Models\SubmissionFormInstance::query()->latest('created_at')->first();
        $this->assertNotNull($sfi);
        $this->assertSame('walk_in', $sfi->source_channel);

        $values = app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)
            ->valuesMapFromInstance($sfi);

        $this->assertSame('Walk-in Customer', $values['customer_name'] ?? null);
        $this->assertSame('555-0100', $values['customer_phone'] ?? null);
        $this->assertSame('555-0200', $values['mobile_number'] ?? null);
        $this->assertSame('2026-06-18', $values['sampling_date'] ?? null);
        $this->assertSame('Tap Water', $values['sample_rows'][0]['sample_description'] ?? null);

        $this->assertDatabaseHas('sample_submission_requests', [
            'submission_form_instance_id' => $sfi->id,
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

        $sfi = \App\Models\SubmissionFormInstance::query()->latest('created_at')->first();
        $this->assertNotNull($sfi);
        $values = app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)
            ->valuesMapFromInstance($sfi);
        $this->assertArrayNotHasKey('sampling_date', $values);
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

        $sfi = \App\Models\SubmissionFormInstance::query()->latest('created_at')->first();
        $this->assertNotNull($sfi);
        $values = app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)
            ->valuesMapFromInstance($sfi);
        $this->assertSame('2', $values['sample_rows'][0]['sample_quantity'] ?? null);
        $this->assertSame('kg', $values['sample_rows'][0]['sample_quantity_unit'] ?? null);
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

    public function test_walk_in_next_step_clears_customer_error_after_selection(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR-STEP');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);
        $this->createWalkInCustomerDetailsSection($portalForm);
        $this->createWalkInCollectionSection($portalForm);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->call('nextWalkInStep')
            ->assertHasErrors(['formData.customer_name'])
            ->set('formData.customer_name', 'Walk-in Customer')
            ->call('nextWalkInStep')
            ->assertHasNoErrors()
            ->assertSet('walkInActiveStepIndex', 1);
    }

    public function test_walk_in_customer_selection_by_id_resolves_even_when_name_has_trailing_whitespace(): void
    {
        $sampleType = $this->createSampleType('Food', 'SMP-FOOD-CUST');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);
        $section = $this->createWalkInCustomerDetailsSection($portalForm);
        $holderId = $section->elementHolders()->value('id');

        foreach ([
            ['customer_address', 'Address', 'textarea'],
            ['customer_phone', 'Tel / Fax no.', 'text'],
            ['customer_email', 'Email', 'text'],
            ['contact_person', 'Contact person', 'client_contact_select'],
        ] as $index => [$name, $label, $type]) {
            SubmissionFormElement::query()->create([
                'id' => (string) Str::uuid7(),
                'submission_form_element_holder_id' => $holderId,
                'element_type' => $type,
                'label' => $label,
                'name' => $name,
                'sort_order' => $index + 1,
            ]);
        }

        $customer = CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Nuvemite ',
            'code' => 'NUV-WS',
            'active' => 1,
            'physical_address' => '2588',
            'telephone1' => '0712345678',
            'email' => 'nuvemiteprojects@gmail.com',
        ]);

        CustomerContact::query()->create([
            'id' => (string) Str::uuid7(),
            'crm_customer_id' => $customer->id,
            'company_id' => (string) Str::uuid7(),
            'first_name' => 'Alex',
            'last_name' => 'Contact',
            'email' => 'alex@example.test',
            'telephone' => '0700000000',
            'active' => 1,
            'receive_price_list' => 0,
            'receive_invoice' => 0,
            'receive_report' => 0,
        ]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
                'pageMode' => true,
                'wizardOnly' => true,
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('selectedCrmCustomerId', $customer->id)
            ->assertSet('selectedCrmCustomerId', $customer->id)
            ->assertSet('formData.customer_name', 'Nuvemite')
            ->assertSet('formData.customer_address', '2588')
            ->assertSet('formData.customer_email', 'nuvemiteprojects@gmail.com')
            ->call('openWalkInAddContactModal')
            ->assertHasNoErrors(['formData.customer_name'])
            ->assertSet('showWalkInAddContactModal', true);
    }

    public function test_walk_in_customer_name_with_trailing_whitespace_still_resolves_for_contacts(): void
    {
        $sampleType = $this->createSampleType('Food', 'SMP-FOOD-NAME');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);
        $this->createWalkInCustomerDetailsSection($portalForm);

        CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Nuvemite ',
            'code' => 'NUV-NAME',
            'active' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData.customer_name', 'Nuvemite ')
            ->call('openWalkInAddContactModal')
            ->assertHasNoErrors(['formData.customer_name'])
            ->assertSet('showWalkInAddContactModal', true);
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

    public function test_physical_check_in_completes_without_signature_metadata_fields(): void
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
        $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $quotation->id);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('checkInTrfFields.'.$instance->id.'.sampling_location', 'Site A')
            ->call('confirmReceive')
            ->assertDispatched('receive-completed');
    }

    public function test_handle_receive_modal_open_with_instances_opens_livewire_physical_confirm(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);
        $this->createEnquiryForInstance($instance, SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class)
            ->call('handleReceiveModalOpen', [$instance->id], [[
                'id' => $instance->id,
                'label' => 'TRF001',
                'customer' => 'Acme',
            ]])
            ->assertSet('showPhysicalConfirmModal', true)
            ->assertSet('selectedFormInstanceIds', [$instance->id])
            ->assertSee('Move to In Review')
            ->assertSee('Yes, move to In Review')
            ->assertNotDispatched('show-receive-sample-modal');
    }

    public function test_handle_receive_modal_open_without_instances_opens_bootstrap_walk_in_shell(): void
    {
        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class)
            ->call('handleReceiveModalOpen', [], [])
            ->assertSet('showPhysicalConfirmModal', false)
            ->assertDispatched('show-receive-sample-modal');
    }

    public function test_close_physical_confirm_modal_clears_selection(): void
    {
        $form = $this->createCommercialTrfForm();
        $instance = $this->createSubmittedInstance($form);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class)
            ->call('handleReceiveModalOpen', [$instance->id], [[
                'id' => $instance->id,
                'label' => 'TRF001',
                'customer' => 'Acme',
            ]])
            ->assertSet('showPhysicalConfirmModal', true)
            ->call('closePhysicalConfirmModal')
            ->assertSet('showPhysicalConfirmModal', false)
            ->assertSet('selectedFormInstanceIds', []);
    }

    public function test_walk_in_modal_open_preserves_wizard_progress_for_duplicate_walk_in_open(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR-PRESERVE');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);
        $this->createWalkInCustomerDetailsSection($portalForm);
        $this->createWalkInCollectionSection($portalForm);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData.customer_name', 'Walk-in Customer')
            ->call('nextWalkInStep')
            ->assertSet('walkInActiveStepIndex', 1)
            ->call('handleReceiveModalOpen', [], [])
            ->assertSet('walkInActiveStepIndex', 1)
            ->assertSet('selectedSampleTypeId', $sampleType->id)
            ->assertSet('formData.customer_name', 'Walk-in Customer');
    }

    public function test_walk_in_sample_type_reselect_with_same_value_does_not_reset_step(): void
    {
        $sampleType = $this->createSampleType('Water', 'SMP-WTR-SAME');
        $portalForm = $this->createCommercialTrfForm();
        $portalForm->sampleTypes()->sync([$sampleType->id]);
        $this->createWalkInCustomerDetailsSection($portalForm);
        $this->createWalkInCollectionSection($portalForm);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [],
            ])
            ->set('selectedSampleTypeId', $sampleType->id)
            ->set('formData.customer_name', 'Walk-in Customer')
            ->call('nextWalkInStep')
            ->assertSet('walkInActiveStepIndex', 1)
            ->set('selectedSampleTypeId', $sampleType->id)
            ->assertSet('walkInActiveStepIndex', 1)
            ->assertSet('formData.customer_name', 'Walk-in Customer');
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

    public function test_set_walk_in_analysis_types_preserves_still_valid_parameters(): void
    {
        $suffix = Str::upper(Str::random(4));

        $sampleType = \App\SampleType::query()->create([
            'name' => 'Water Param Preserve '.$suffix,
            'code' => 'WTR-PP-'.$suffix,
            'active' => 1,
        ]);

        $analysisA = \App\AnalysisType::query()->create([
            'name' => 'Microbiology '.$suffix,
            'code' => 'MICRO-'.$suffix,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);
        $analysisB = \App\AnalysisType::query()->create([
            'name' => 'Chemistry '.$suffix,
            'code' => 'CHEM-'.$suffix,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $sharedAnalyte = \App\Analyte::query()->create([
            'name' => 'Chloride '.$suffix,
            'code' => 'CL-'.$suffix,
            'active' => 1,
        ]);
        $chemOnlyAnalyte = \App\Analyte::query()->create([
            'name' => 'Nitrate '.$suffix,
            'code' => 'NO3-'.$suffix,
            'active' => 1,
        ]);

        foreach ([$analysisA, $analysisB] as $analysisType) {
            \App\AnalysisElements::query()->create([
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => $sharedAnalyte->id,
                'active' => 1,
                'level' => 1,
            ]);
        }

        \App\AnalysisElements::query()->create([
            'analysis_type_id' => $analysisB->id,
            'analyte_id' => $chemOnlyAnalyte->id,
            'active' => 1,
            'level' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class)
            ->set('selectedSampleTypeId', (string) $sampleType->id)
            ->set('formData', [
                'analysis_type_id' => [
                    0 => [(string) $analysisA->id],
                ],
                'parameters' => [
                    0 => [(string) $sharedAnalyte->name],
                ],
            ])
            ->call(
                'setWalkInAnalysisTypes',
                'formData.analysis_type_id.0',
                [(string) $analysisA->id, (string) $analysisB->id]
            )
            ->assertSet('formData.parameters.0', [(string) $sharedAnalyte->name])
            ->assertDispatched(
                'walk-in-params-row-reset',
                rowIndex: 0,
                selected: [(string) $sharedAnalyte->name],
            );

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class)
            ->set('selectedSampleTypeId', (string) $sampleType->id)
            ->set('formData', [
                'analysis_type_id' => [
                    0 => [(string) $analysisA->id, (string) $analysisB->id],
                ],
                'parameters' => [
                    0 => [(string) $sharedAnalyte->name, (string) $chemOnlyAnalyte->name],
                ],
            ])
            ->call(
                'setWalkInAnalysisTypes',
                'formData.analysis_type_id.0',
                [(string) $analysisA->id]
            )
            ->assertSet('formData.parameters.0', [(string) $sharedAnalyte->name]);
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

    private function createWalkInCustomerDetailsSection(SubmissionForm $form): SubmissionFormSection
    {
        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Customer details',
            'section_type' => 'regular',
            'sort_order' => 0,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Name',
            'name' => 'customer_name',
            'sort_order' => 0,
        ]);

        return $section;
    }

    private function createWalkInCollectionSection(SubmissionForm $form): SubmissionFormSection
    {
        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Sampling location',
            'name' => 'sampling_location',
            'sort_order' => 0,
        ]);

        return $section;
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
