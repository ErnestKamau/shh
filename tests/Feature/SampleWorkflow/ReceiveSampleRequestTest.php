<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
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

    public function test_confirm_receive_applies_same_checklist_to_multiple_instances(): void
    {
        $form = $this->createTemplateForm();
        $first = $this->createSubmittedInstance($form, ['form_number' => 'CR001']);
        $second = $this->createSubmittedInstance($form, ['form_number' => 'CR002']);

        $itemIds = $this->approval->fresh('checklistItems')->checklistItems->pluck('id')->all();
        $responses = array_fill_keys($itemIds, true);

        Livewire::actingAs($this->user)
            ->test(ReceiveSampleRequest::class, [
                'selectedFormInstanceIds' => [$first->id, $second->id],
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
    ): SampleSubmissionRequest {
        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => $status,
            'source_channel' => 'portal',
            'accepted_quotation_header_id' => $acceptedQuotationId,
            'current_quotation_header_id' => $acceptedQuotationId,
            'quotation_accepted_at' => in_array($status, [
                SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
                SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            ], true) ? now() : null,
            'number_of_samples' => 1,
        ]);
    }
}
