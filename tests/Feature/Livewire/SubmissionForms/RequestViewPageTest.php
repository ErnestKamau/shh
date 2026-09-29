<?php

namespace Tests\Feature\Livewire\SubmissionForms;

use App\Livewire\SubmissionForms\RequestViewPage;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerNotification;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceNote;
use App\Models\System\SystemConfiguration;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class RequestViewPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Lab Reviewer',
            'email' => 'request.view@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        Gate::before(fn () => true);
    }

    public function test_show_route_renders_request_view_with_tabs(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        $this->actingAs($this->user)
            ->get(route('submission-forms.instances.show', [$form, $instance]))
            ->assertOk()
            ->assertSee('Request Info')
            ->assertSee('Tests')
            ->assertSee('Actions')
            ->assertSee('Notes')
            ->assertSee('Attachments')
            ->assertSee('Chain of custody')
            ->assertDontSee('Captured request details')
            ->assertSee('x-data="{ open: false }"', false)
            ->assertSee('id="receive-sample-modal"', false);
    }

    public function test_show_route_keeps_walk_in_capture_inside_hidden_modal(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        $html = $this->actingAs($this->user)
            ->get(route('submission-forms.instances.show', [$form, $instance]))
            ->assertOk()
            ->getContent();

        $modalPos = strpos($html, 'id="receive-sample-modal"');
        $walkInPos = strpos($html, 'Submit walk-in request');

        $this->assertNotFalse($modalPos);
        $this->assertNotFalse($walkInPos);
        $this->assertGreaterThan($modalPos, $walkInPos);
        $this->assertStringNotContainsString('class="modal fade show', $html);
    }

    public function test_ready_for_reception_view_shows_accept_sample_action(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        \App\Models\SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => \App\Models\SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'walk_in',
            'request_number' => 7,
        ]);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->assertSeeHtml('wire:click="openAcceptSampleWizard"')
            ->assertDontSeeHtml('wire:click="openPhysicalReceiveModal"')
            ->assertDontSee('Receive physical samples')
            ->assertDontSee('Open on receiving board')
            ->assertSee('Ready for Reception')
            ->assertDontSee('Process enquiry')
            ->assertDontSee('Record PO');
    }

    public function test_open_accept_sample_wizard_from_ready_for_reception_dispatches_wizard(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        $enquiry = \App\Models\SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => \App\Models\SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'walk_in',
            'request_number' => 8,
        ]);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->call('openAcceptSampleWizard')
            ->assertDispatched('open-acceptance-wizard', function ($eventName, $params) use ($instance, $enquiry): bool {
                return ($params['submissionFormInstanceId'] ?? null) === $instance->id
                    && ($params['submissionRequestId'] ?? null) === $enquiry->id
                    && ($params['mode'] ?? null) === 'receive_only';
            });
    }

    public function test_workflow_board_breadcrumb_omits_tab_query(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        $receivingUrl = route('sample-workflow', ['status' => 'Samples Receiving']);

        $this->actingAs($this->user)
            ->get(route('submission-forms.instances.show', [$form, $instance]))
            ->assertOk()
            ->assertSee('href="'.$receivingUrl.'"', false)
            ->assertDontSee('tab=in_review');
    }

    public function test_add_internal_note_does_not_notify_customer(): void
    {
        [$form, $instance] = $this->createFormAndInstance(withCustomer: true);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->set('noteBody', 'Internal lab comment')
            ->set('noteVisibility', SubmissionFormInstanceNote::VISIBILITY_INTERNAL)
            ->call('addNote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submission_form_instance_notes', [
            'submission_form_instance_id' => $instance->id,
            'visibility' => 'internal',
        ]);
        $this->assertDatabaseCount('customer_notifications', 0);
    }

    public function test_add_public_note_creates_customer_notification(): void
    {
        [$form, $instance] = $this->createFormAndInstance(withCustomer: true);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->set('noteBody', 'Your samples are being reviewed.')
            ->set('noteVisibility', SubmissionFormInstanceNote::VISIBILITY_PUBLIC)
            ->call('addNote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $instance->crm_customer_id,
            'entity_type' => SubmissionFormInstance::class,
            'entity_id' => $instance->id,
            'notification_type' => CustomerNotification::TYPE_REQUEST_NOTE,
        ]);
    }

    public function test_po_modal_requires_po_number_for_credit_customers(): void
    {
        [$form, $instance] = $this->createFormAndInstance(withCustomer: true);
        $creditStatus = SystemConfiguration::query()->create([
            'key' => 'Account holder - credit',
            'value' => 'Account holder credit',
            'status' => 1,
        ]);

        $customer = CRMCustomer::query()->findOrFail($instance->crm_customer_id);
        $customer->account_status = $creditStatus->id;
        $customer->save();

        $quotation = \App\QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ-RV-PO-001',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
            'crm_customer_id' => $customer->id,
        ]);

        \App\Models\SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            'source_channel' => 'walk_in',
            'crm_customer_id' => $customer->id,
            'current_quotation_header_id' => $quotation->id,
            'accepted_quotation_header_id' => $quotation->id,
            'number_of_samples' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->call('openPoCaptureModal')
            ->assertSet('showQuotationAcceptanceModal', true)
            ->assertSet('quotationAcceptancePoOnly', true)
            ->assertSet('poRequiresPo', true)
            ->set('clientPoNumber', '')
            ->call('submitPoAndReadyForReception')
            ->assertHasErrors(['client_po_number']);

        $this->assertSame('submitted', $instance->fresh()->status);
    }

    public function test_default_tab_is_tests_when_quotation_is_ready_to_send(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        $quotation = \App\QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ-TAB-001',
            'quote_date' => now()->toDateString(),
            'is_approved' => 1,
            'is_complete' => 1,
            'is_draft' => 0,
            'status' => 'Quote Complete',
            'from_enquiry' => true,
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        \App\Models\SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
            'source_channel' => 'walk_in',
            'crm_customer_id' => $quotation->crm_customer_id,
            'current_quotation_header_id' => $quotation->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->assertSet('activeTab', 'tests')
            ->assertSet('quotationApprovedReadyToSend', true);
    }

    public function test_additional_details_render_as_label_value_rows_in_edit_and_view_modals(): void
    {
        [$form, $instance] = $this->createFormInstanceWithAdditionalDetails([
            ['label' => 'TestField', 'value' => 'Value 1'],
            ['label' => 'TestField2', 'value' => 'Value2'],
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->call('openTrfEditor', 'samples', 0)
            ->assertSet('showTrfEditModal', true)
            ->assertSet('editingRowFields.additional_details.0.label', 'TestField')
            ->assertSet('editingRowFields.additional_details.0.value', 'Value 1')
            ->assertSet('editingRowFields.additional_details.1.label', 'TestField2')
            ->assertSet('editingRowFields.additional_details.1.value', 'Value2')
            ->assertSeeHtml('wire:model.defer="editingRowFields.additional_details.0.label"')
            ->assertSeeHtml('wire:model.defer="editingRowFields.additional_details.0.value"')
            ->assertDontSee('[object Object]');

        $component
            ->call('addEditingRowAdditionalDetail')
            ->assertCount('editingRowFields.additional_details', 3)
            ->set('editingRowFields.additional_details.2.label', 'New label')
            ->set('editingRowFields.additional_details.2.value', 'New value')
            ->call('removeEditingRowAdditionalDetail', 1)
            ->assertCount('editingRowFields.additional_details', 2)
            ->assertSet('editingRowFields.additional_details.1.label', 'New label')
            ->assertSet('editingRowFields.additional_details.1.value', 'New value');

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->call('openTrfViewer', 'samples', 0)
            ->assertSet('showTrfViewModal', true)
            ->assertSee('TestField')
            ->assertSee('Value 1')
            ->assertSee('TestField2')
            ->assertSee('Value2')
            ->assertDontSee('[object Object]');
    }

    /**
     * @param  list<array{label: string, value: string}>  $details
     * @return array{0: SubmissionForm, 1: SubmissionFormInstance}
     */
    private function createFormInstanceWithAdditionalDetails(array $details): array
    {
        [$form, $instance] = $this->createFormAndInstance();

        $section = \App\Models\SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test & sample information',
            'section_type' => 'rows_section',
            'sort_order' => 0,
        ]);

        $holder = \App\Models\SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'rows',
            'sort_order' => 0,
        ]);

        $descriptionElement = \App\Models\SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'rich_text',
            'label' => 'Sample description',
            'name' => 'sample_description',
            'sort_order' => 0,
        ]);

        $additionalDetailsElement = \App\Models\SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'textarea',
            'label' => 'Additional details',
            'name' => 'additional_details',
            'sort_order' => 1,
        ]);

        \App\Models\SubmissionFormInstanceValue::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $descriptionElement->id,
            'array_index' => 0,
            'value' => 'Milk sample',
        ]);

        \App\Models\SubmissionFormInstanceValue::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $additionalDetailsElement->id,
            'array_index' => 0,
            'value' => json_encode($details),
        ]);

        return [$form, $instance->fresh(['values.element', 'submissionForm'])];
    }

    /**
     * @return array{0: SubmissionForm, 1: SubmissionFormInstance}
     */
    private function createFormAndInstance(bool $withCustomer = false): array
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Request View Form',
            'document_code' => 'RV/TEST',
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

        $customerId = null;
        if ($withCustomer) {
            $customerId = (string) Str::uuid7();
            CRMCustomer::query()->create([
                'id' => $customerId,
                'name' => 'Portal Customer',
                'code' => 'CUST-RV',
                'active' => 1,
            ]);
        }

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Portal request',
            'form_number' => 'CR200',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'crm_customer_id' => $customerId,
            'priority' => 'normal',
        ]);

        return [$form, $instance];
    }
}
