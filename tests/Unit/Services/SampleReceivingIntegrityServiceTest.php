<?php

namespace Tests\Unit\Services;

use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\Workflow\Approval;
use App\Models\Workflow\ChecklistItem;
use App\Models\Workflow\ChecklistResponse;
use App\Services\Sampleworkflow\SampleReceivingIntegrityService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleReceivingIntegrityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_incomplete_when_required_checkbox_not_checked(): void
    {
        $approval = $this->seedReceivingApproval();
        $instance = $this->makeFormInstance();

        ChecklistResponse::query()->create([
            'submission_form_instance_id' => $instance->id,
            'approval_id' => $approval->id,
            'checklist_item_id' => $approval->checklistItems->first()->id,
            'value' => false,
        ]);

        $result = app(SampleReceivingIntegrityService::class)->assessFormInstance((string) $instance->id);

        $this->assertTrue($result['incomplete']);
        $this->assertCount(1, $result['items']);
    }

    public function test_reports_complete_when_all_required_items_satisfied(): void
    {
        $approval = $this->seedReceivingApproval();
        $instance = $this->makeFormInstance();

        foreach ($approval->checklistItems as $item) {
            ChecklistResponse::query()->create([
                'submission_form_instance_id' => $instance->id,
                'approval_id' => $approval->id,
                'checklist_item_id' => $item->id,
                'value' => $item->type === 'checkbox' ? true : 'ok',
            ]);
        }

        $result = app(SampleReceivingIntegrityService::class)->assessFormInstance((string) $instance->id);

        $this->assertFalse($result['incomplete']);
        $this->assertSame([], $result['items']);
    }

    private function seedReceivingApproval(): Approval
    {
        $approval = app(WorkflowService::class)->saveApproval([
            'stage_name' => ReceiveSampleRequest::STAGE_NAME,
            'code' => ReceiveSampleRequest::APPROVAL_CODE,
            'name' => 'SRO Receiving Sample Test',
            'order' => 1,
            'is_active' => true,
        ]);

        app(WorkflowService::class)->saveChecklistItem($approval->id, [
            'label' => 'Sample labels verified',
            'type' => 'checkbox',
            'is_required' => true,
            'order' => 1,
        ]);

        return $approval->fresh(['checklistItems']);
    }

    private function makeFormInstance(): SubmissionFormInstance
    {
        $form = SubmissionForm::query()->create([
            'name' => 'Test Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        return SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'status' => 'submitted',
            'title' => 'Request',
        ]);
    }
}
