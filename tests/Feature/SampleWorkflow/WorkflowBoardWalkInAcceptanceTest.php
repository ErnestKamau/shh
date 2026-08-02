<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use App\Services\Commercial\QuotationAcceptanceTatService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class WorkflowBoardWalkInAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Receiving User',
            'email' => 'receiving.walkin@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_walk_in_acceptance_without_selection_notifies_error(): void
    {
        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [])
            ->assertDispatched('notify', type: 'error');
    }

    public function test_record_walk_in_acceptance_opens_signature_modal(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $enquiryId = (string) Str::uuid7();

        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260624-001',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
            'sample_submission_request_id' => $enquiryId,
        ]);

        SampleSubmissionRequest::query()->create([
            'id' => $enquiryId,
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'current_quotation_header_id' => $quotation->id,
            'number_of_samples' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [$instance->id])
            ->assertSet('showQuotationAcceptanceModal', true)
            ->assertSet('quotationAcceptanceEnquiryId', $enquiryId)
            ->assertSet('quotationAcceptancePoOnly', false);

        $this->assertDatabaseHas('sample_submission_requests', [
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
        ]);
    }

    public function test_submit_quotation_acceptance_signature_accepts_and_marks_ready(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $enquiryId = (string) Str::uuid7();
        $customerId = (string) Str::uuid();
        $contactId = (string) Str::uuid();

        \App\Models\CRM\CRMCustomer::query()->create([
            'id' => $customerId,
            'name' => 'Walk-in Customer',
            'code' => 'WALK-001',
            'active' => 1,
        ]);

        \App\Models\CRM\CustomerContact::query()->create([
            'id' => $contactId,
            'crm_customer_id' => $customerId,
            'first_name' => 'Walk',
            'last_name' => 'In Client',
            'active' => 1,
        ]);

        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260624-002',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
            'sample_submission_request_id' => $enquiryId,
            'crm_customer_id' => $customerId,
        ]);

        SampleSubmissionRequest::query()->create([
            'id' => $enquiryId,
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'current_quotation_header_id' => $quotation->id,
            'crm_customer_id' => $customerId,
            'crm_customer_contact_id' => $contactId,
            'number_of_samples' => 1,
        ]);

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')->once()->andReturn($quotation);
        });
        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->set('showQuotationAcceptanceModal', true)
            ->set('quotationAcceptanceEnquiryId', $enquiryId)
            ->set('quotationAcceptanceContactId', $contactId)
            ->set('quotationAcceptanceSignerName', 'Walk In Client')
            ->set('quotationAcceptanceSignature', 'data:image/png;base64,walkinsign')
            ->call('submitQuotationAcceptanceSignature')
            ->assertSet('showQuotationAcceptanceModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sample_submission_requests', [
            'id' => $enquiryId,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
        ]);

        $quotation->refresh();
        $this->assertSame('data:image/png;base64,walkinsign', $quotation->customer_acceptance_signature);
        $this->assertSame('Walk In Client', $quotation->customer_acceptance_signer_name);
    }

    public function test_submit_quotation_acceptance_accepts_signature_passed_from_the_pad(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $enquiryId = (string) Str::uuid7();
        $customerId = (string) Str::uuid();
        $contactId = (string) Str::uuid();

        $quotation = $this->createSentQuotationEnquiry($instance, $enquiryId, $customerId, $contactId, 'AMSQ260624-003');

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')->once()->andReturn($quotation);
        });
        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [$instance->id])
            ->call('submitQuotationAcceptanceSignature', 'data:image/png;base64,padsign')
            ->assertSet('showQuotationAcceptanceModal', false)
            ->assertHasNoErrors();

        $quotation->refresh();
        $this->assertSame('data:image/png;base64,padsign', $quotation->customer_acceptance_signature);
    }

    public function test_resending_the_same_contact_does_not_clear_the_captured_signature(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $enquiryId = (string) Str::uuid7();
        $customerId = (string) Str::uuid();
        $contactId = (string) Str::uuid();

        $this->createSentQuotationEnquiry($instance, $enquiryId, $customerId, $contactId, 'AMSQ260624-004');

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [$instance->id])
            ->set('quotationAcceptanceSignature', 'data:image/png;base64,drawnsign')
            ->set('quotationAcceptanceContactId', $contactId)
            ->assertSet('quotationAcceptanceSignature', 'data:image/png;base64,drawnsign')
            ->assertNotDispatched('quotation-acceptance-signature-changed');
    }

    private function createSentQuotationEnquiry(
        SubmissionFormInstance $instance,
        string $enquiryId,
        string $customerId,
        string $contactId,
        string $quoteNumber
    ): QuotationHeader {
        \App\Models\CRM\CRMCustomer::query()->create([
            'id' => $customerId,
            'name' => 'Walk-in Customer',
            'code' => 'WALK-'.substr($quoteNumber, -3),
            'active' => 1,
        ]);

        \App\Models\CRM\CustomerContact::query()->create([
            'id' => $contactId,
            'crm_customer_id' => $customerId,
            'first_name' => 'Walk',
            'last_name' => 'In Client',
            'active' => 1,
        ]);

        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => $quoteNumber,
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
            'sample_submission_request_id' => $enquiryId,
            'crm_customer_id' => $customerId,
        ]);

        SampleSubmissionRequest::query()->create([
            'id' => $enquiryId,
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'current_quotation_header_id' => $quotation->id,
            'crm_customer_id' => $customerId,
            'crm_customer_contact_id' => $contactId,
            'number_of_samples' => 1,
        ]);

        return $quotation;
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Walk-in acceptance form',
            'document_code' => 'WALKIN/TEST',
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);
    }

    private function createSubmittedInstance(SubmissionForm $form): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Walk-in instance',
            'form_number' => 'CR888',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'priority' => 'normal',
        ]);
    }
}
