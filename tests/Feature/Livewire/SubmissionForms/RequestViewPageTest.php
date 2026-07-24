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
            ->assertDontSee('Captured request details');
    }

    public function test_ready_for_reception_view_hides_process_enquiry_action(): void
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
            ->assertSeeHtml('wire:click="openPhysicalReceiveModal"')
            ->assertDontSee('Receive physical samples')
            ->assertDontSee('Open on receiving board')
            ->assertSee('Ready for Reception')
            ->assertDontSee('Process enquiry')
            ->assertDontSee('Record PO');
    }

    public function test_open_physical_receive_modal_dispatches_to_receive_sample_request(): void
    {
        [$form, $instance] = $this->createFormAndInstance();

        \App\Models\SampleSubmissionRequest::query()->create([
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
            ->call('openPhysicalReceiveModal')
            ->assertDispatched('receive-modal-open');
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

        $instance->refresh();

        Livewire::actingAs($this->user)
            ->test(RequestViewPage::class, [
                'submissionFormId' => $form->id,
                'instanceId' => $instance->id,
            ])
            ->call('openPoCaptureModal')
            ->assertSet('poRequiresPo', true)
            ->set('clientPoNumber', '')
            ->set('poSkipped', false)
            ->call('submitPoAndReadyForReception')
            ->assertHasErrors(['client_po_number']);

        $this->assertSame('submitted', $instance->fresh()->status);
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
