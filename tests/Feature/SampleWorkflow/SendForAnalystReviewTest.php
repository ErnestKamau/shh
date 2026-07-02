<?php

namespace Tests\Feature\SampleWorkflow;

use App\Lab;
use App\Livewire\Sampleworkflow\SendForAnalystReview;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormAuditLog;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SendForAnalystReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Lab $lab;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->lab = Lab::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'RCV',
            'name' => 'Receiving Lab',
            'phone1' => '000',
            'active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Receiving Officer',
            'email' => 'receiving.officer@example.test',
            'password' => bcrypt('password'),
            'lab_id' => $this->lab->id,
        ]);
    }

    public function test_confirm_moves_received_instance_to_in_review_with_audit_log(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'received']);

        Livewire::actingAs($this->user)
            ->test(SendForAnalystReview::class, [
                'selectedFormInstanceIds' => [$instance->id],
                'selectedFormSummaries' => [
                    ['id' => $instance->id, 'label' => 'CR001', 'customer' => 'Acme'],
                ],
            ])
            ->set('comment', 'Ready for analyst review.')
            ->call('confirmSendForAnalystReview')
            ->assertDispatched('analyst-review-completed');

        $instance->refresh();

        $this->assertSame('in_review', $instance->status);
        $this->assertSame($this->user->id, $instance->reviewed_by);
        $this->assertSame('Ready for analyst review.', $instance->review_notes);
        $this->assertNull($instance->receiving_lab_id);

        $this->assertDatabaseHas('submission_form_audit_logs', [
            'submission_form_instance_id' => $instance->id,
            'action' => 'sent_for_analyst_review',
            'user_id' => $this->user->id,
        ]);

        $auditLog = SubmissionFormAuditLog::query()
            ->where('submission_form_instance_id', $instance->id)
            ->where('action', 'sent_for_analyst_review')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('received', $auditLog->field_changes['status']['from'] ?? null);
        $this->assertSame('in_review', $auditLog->field_changes['status']['to'] ?? null);
    }

    public function test_confirm_moves_submitted_instance_to_in_review(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        Livewire::actingAs($this->user)
            ->test(SendForAnalystReview::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->call('confirmSendForAnalystReview')
            ->assertDispatched('analyst-review-completed');

        $this->assertSame('in_review', $instance->fresh()->status);
    }

    public function test_confirm_does_not_require_lab_selection(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'received']);

        Livewire::actingAs($this->user)
            ->test(SendForAnalystReview::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->call('confirmSendForAnalystReview')
            ->assertHasNoErrors()
            ->assertDispatched('analyst-review-completed');

        $this->assertSame('in_review', $instance->fresh()->status);
    }

    public function test_confirm_skips_non_eligible_instances(): void
    {
        $form = $this->createTemplateForm();
        $received = $this->createInstance($form, ['status' => 'received', 'form_number' => 'CR001']);
        $inReview = $this->createInstance($form, ['status' => 'in_review', 'form_number' => 'CR002']);

        Livewire::actingAs($this->user)
            ->test(SendForAnalystReview::class, [
                'selectedFormInstanceIds' => [$received->id, $inReview->id],
            ])
            ->call('confirmSendForAnalystReview')
            ->assertDispatched('analyst-review-completed');

        $this->assertSame('in_review', $received->fresh()->status);
        $this->assertSame('in_review', $inReview->fresh()->status);
    }

    public function test_mark_as_in_review_rejects_non_eligible_status(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'approved']);

        $this->assertFalse($instance->markAsInReview($this->user, 'Note'));
        $this->assertSame('approved', $instance->fresh()->status);
    }

    public function test_confirm_allows_empty_comment(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'received']);

        Livewire::actingAs($this->user)
            ->test(SendForAnalystReview::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('comment', '')
            ->call('confirmSendForAnalystReview')
            ->assertDispatched('analyst-review-completed');

        $instance->refresh();
        $this->assertSame('in_review', $instance->status);
        $this->assertNull($instance->review_notes);
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer Request Template',
            'document_code' => 'TEST/REVIEW',
            'description' => 'Analyst review test form',
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

    private function createInstance(SubmissionForm $form, array $overrides = []): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Analyst review test instance',
            'form_number' => 'CR100',
            'status' => 'received',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'priority' => 'normal',
        ], $overrides));
    }
}
