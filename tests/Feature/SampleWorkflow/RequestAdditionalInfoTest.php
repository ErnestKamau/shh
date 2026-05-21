<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\RequestAdditionalInfo;
use App\Models\CRM\CRMCustomer;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionFormAdditionalInfoService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class RequestAdditionalInfoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::create([
            'name' => 'Receiving Officer',
            'email' => 'receiving.officer@example.test',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_confirm_moves_received_instances_to_in_additional_info_with_audit_log(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'received']);

        Livewire::actingAs($this->user)
            ->test(RequestAdditionalInfo::class, [
                'selectedFormInstanceIds' => [$instance->id],
                'selectedFormSummaries' => [
                    ['id' => $instance->id, 'label' => 'CR001', 'customer' => 'Acme'],
                ],
            ])
            ->set('remarks', 'Please provide batch records and COA.')
            ->set('notifyCustomer', false)
            ->call('confirmRequestAdditionalInfo')
            ->assertDispatched('request-additional-info-completed');

        $instance->refresh();

        $this->assertSame('in_additional_info', $instance->status);
        $this->assertSame($this->user->id, $instance->reviewed_by);
        $this->assertSame('Please provide batch records and COA.', $instance->review_notes);

        $this->assertDatabaseHas('submission_form_audit_logs', [
            'submission_form_instance_id' => $instance->id,
            'action' => 'additional_info_requested',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_confirm_skips_non_received_instances(): void
    {
        $form = $this->createTemplateForm();
        $received = $this->createInstance($form, ['status' => 'received', 'form_number' => 'CR001']);
        $submitted = $this->createInstance($form, ['status' => 'submitted', 'form_number' => 'CR002']);

        Livewire::actingAs($this->user)
            ->test(RequestAdditionalInfo::class, [
                'selectedFormInstanceIds' => [$received->id, $submitted->id],
            ])
            ->set('remarks', 'Need more documentation from customer.')
            ->set('notifyCustomer', false)
            ->call('confirmRequestAdditionalInfo')
            ->assertDispatched('request-additional-info-completed');

        $this->assertSame('in_additional_info', $received->fresh()->status);
        $this->assertSame('submitted', $submitted->fresh()->status);
    }

    public function test_mark_as_in_additional_info_rejects_non_received_status(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, ['status' => 'submitted']);

        $this->assertFalse($instance->markAsInAdditionalInfo($this->user, 'Need more info', false));
        $this->assertSame('submitted', $instance->fresh()->status);
    }

    public function test_confirm_sends_notification_when_customer_email_is_available(): void
    {
        $customer = $this->createCustomer('customer.with.email@example.test');
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, [
            'status' => 'received',
            'crm_customer_id' => $customer->id,
        ]);

        $this->mock(SubmissionFormAdditionalInfoService::class, function ($mock) use ($instance): void {
            $mock->shouldReceive('notifyCustomer')
                ->once()
                ->withArgs(function ($inst, string $message, User $actor) use ($instance): bool {
                    return $inst->id === $instance->id
                        && str_contains($message, 'safety data sheet')
                        && $actor->id === $this->user->id;
                })
                ->andReturn(true);
        });

        Livewire::actingAs($this->user)
            ->test(RequestAdditionalInfo::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('remarks', 'Please upload the missing safety data sheet.')
            ->set('notifyCustomer', true)
            ->call('confirmRequestAdditionalInfo')
            ->assertDispatched('request-additional-info-completed');

        $this->assertSame('in_additional_info', $instance->fresh()->status);
    }

    public function test_confirm_updates_status_when_customer_email_is_missing(): void
    {
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, [
            'status' => 'received',
            'crm_customer_id' => null,
            'submitted_by' => null,
        ]);

        $this->mock(SubmissionFormAdditionalInfoService::class, function ($mock): void {
            $mock->shouldReceive('notifyCustomer')
                ->once()
                ->andReturn(false);
        });

        Livewire::actingAs($this->user)
            ->test(RequestAdditionalInfo::class, [
                'selectedFormInstanceIds' => [$instance->id],
            ])
            ->set('remarks', 'Please clarify sample identifiers.')
            ->set('notifyCustomer', true)
            ->call('confirmRequestAdditionalInfo')
            ->assertDispatched('request-additional-info-completed');

        $this->assertSame('in_additional_info', $instance->fresh()->status);
    }

    public function test_additional_info_service_resolves_email_from_crm_customer(): void
    {
        $customer = $this->createCustomer('resolved@example.test');
        $form = $this->createTemplateForm();
        $instance = $this->createInstance($form, [
            'status' => 'received',
            'crm_customer_id' => $customer->id,
        ]);

        $service = app(SubmissionFormAdditionalInfoService::class);

        $this->assertSame('resolved@example.test', $service->resolveCustomerEmail($instance));
    }

    private function createCustomer(string $email): CRMCustomer
    {
        return CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Customer',
            'code' => 'TCI'.Str::random(4),
            'company_id' => (string) Str::uuid7(),
            'active' => 1,
            'email' => $email,
            'telephone1' => '1234567890',
            'telephone2' => '',
            'website' => 'https://example.com',
            'country_id' => (string) Str::uuid7(),
            'postal_address' => null,
            'physical_address' => null,
            'fax' => null,
        ]);
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer Request Template',
            'document_code' => 'TEST/ADDINFO',
            'description' => 'Additional info test form',
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
            'title' => 'Additional info test instance',
            'form_number' => 'CR100',
            'status' => 'received',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'priority' => 'normal',
        ], $overrides));
    }
}
