<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\MoveToIntray;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceIntray;
use App\Services\SubmissionForm\SubmissionFormIntrayService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionFormIntrayTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::create([
            'name' => 'Actor User',
            'email' => 'actor.intray@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        $this->assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee.intray@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);
    }

    public function test_assign_creates_pending_intray_and_audit_log(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        $intray = app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $this->assignee->id,
            'Please review',
            $this->actor,
        );

        $this->assertSame(SubmissionFormInstanceIntray::STATUS_PENDING, $intray->status);
        $this->assertDatabaseHas('submission_form_instance_intrays', [
            'submission_form_instance_id' => $instance->id,
            'to_user_id' => $this->assignee->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('submission_form_audit_logs', [
            'submission_form_instance_id' => $instance->id,
            'action' => 'intray_assigned',
        ]);
    }

    public function test_second_assign_auto_completes_first_pending(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        $thirdUser = User::create([
            'name' => 'Third User',
            'email' => 'third.intray@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        $first = app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $this->assignee->id,
            null,
            $this->actor,
        );

        app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $thirdUser->id,
            'Reassigned',
            $this->actor,
        );

        $this->assertSame(SubmissionFormInstanceIntray::STATUS_COMPLETED, $first->fresh()->status);
        $this->assertSame(1, SubmissionFormInstanceIntray::query()
            ->where('submission_form_instance_id', $instance->id)
            ->pending()
            ->count());
    }

    public function test_complete_marks_pending_completed_but_latest_holder_remains_visible(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $this->assignee->id,
            null,
            $this->actor,
        );

        app(SubmissionFormIntrayService::class)->completePending($instance->id, $this->assignee);

        $instance->load('latestIntray.toUser', 'activePendingIntray');

        $this->assertNull($instance->activePendingIntray);
        $this->assertSame((string) $this->assignee->id, (string) $instance->latestIntray->to_user_id);
        $this->assertSame(SubmissionFormInstanceIntray::STATUS_COMPLETED, $instance->latestIntray->status);
    }

    public function test_complete_pending_rejects_non_assignee(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $this->assignee->id,
            null,
            $this->actor,
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(SubmissionFormIntrayService::class)->completePending($instance->id, $this->actor);
    }

    public function test_move_to_intray_livewire_bulk_assigns(): void
    {
        $form = $this->createTemplateForm();
        $first = $this->createInstance($form, ['status' => 'submitted', 'form_number' => 'CR001']);
        $second = $this->createInstance($form, ['status' => 'received', 'form_number' => 'CR002']);

        Livewire::actingAs($this->actor)
            ->test(MoveToIntray::class, [
                'selectedFormInstanceIds' => [$first->id, $second->id],
                'assignableUsers' => [
                    ['id' => (string) $this->assignee->id, 'name' => $this->assignee->name],
                ],
            ])
            ->set('assigneeUserId', (string) $this->assignee->id)
            ->set('comment', 'Bulk handoff')
            ->call('confirmMove')
            ->assertDispatched('intray-move-completed');

        $this->assertSame(2, SubmissionFormInstanceIntray::query()
            ->where('to_user_id', $this->assignee->id)
            ->pending()
            ->count());
    }

    public function test_workflow_board_my_pending_count(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        app(SubmissionFormIntrayService::class)->assignToUser(
            $instance->id,
            (string) $this->assignee->id,
            null,
            $this->actor,
        );

        Livewire::actingAs($this->assignee)
            ->test(WorkflowBoard::class, ['status' => 'Samples Receiving'])
            ->assertSet('myPendingIntrayCount', 1);
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Intray Test Form',
            'document_code' => 'INTRAY/TEST',
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

    private function createInstance(SubmissionForm $form, array $overrides = []): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Intray instance',
            'form_number' => 'CR999',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $this->actor->id,
            'priority' => 'normal',
        ], $overrides));
    }
}
