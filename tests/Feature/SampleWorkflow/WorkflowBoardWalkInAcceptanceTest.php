<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\QuotationHeader;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
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

    public function test_record_walk_in_acceptance_without_selection_notifies_error(): void
    {
        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [])
            ->assertDispatched('notify', type: 'error', message: 'Select a walk-in request with a sent quotation to record acceptance.');
    }

    public function test_record_walk_in_acceptance_accepts_sent_quotation_and_opens_po_modal(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createSubmittedInstance($form);
        $quotation = QuotationHeader::query()->create([
            'id' => (string) Str::uuid7(),
            'quote_number' => 'AMSQ260624-001',
            'quote_date' => now()->toDateString(),
            'sent_to_customer_at' => now(),
            'status' => 'Quote Complete',
        ]);

        SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'current_quotation_header_id' => $quotation->id,
            'number_of_samples' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->call('recordWalkInAcceptanceFromInstances', [$instance->id])
            ->assertDispatched('notify', type: 'success')
            ->assertSet('showPoCaptureModal', true);

        $this->assertDatabaseHas('sample_submission_requests', [
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
        ]);
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
