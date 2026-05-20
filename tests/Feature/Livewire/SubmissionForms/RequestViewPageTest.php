<?php

namespace Tests\Feature\Livewire\SubmissionForms;

use App\Livewire\SubmissionForms\RequestViewPage;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerNotification;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceNote;
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
            ->assertSee('Samples')
            ->assertSee('Notes')
            ->assertSee('Attachments')
            ->assertSee('Chain of custody');
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
