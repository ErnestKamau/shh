<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\AcceptanceFormWizard;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AcceptanceFormWizardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::create([
            'name' => 'Lab Receiver',
            'email' => 'lab.receiver@example.test',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_open_wizard_loads_customer_contacts_and_shows_modal(): void
    {
        [$instance, $contact] = $this->createInReviewInstance();

        $this->mockAcceptancePrefill($instance);

        Livewire::actingAs($this->user)
            ->test(AcceptanceFormWizard::class)
            ->dispatch('open-acceptance-wizard', submissionFormInstanceId: $instance->id)
            ->assertSet('showModal', true)
            ->assertSet('selectedCustomerContactId', (string) $contact->id)
            ->assertCount('customerContactOptions', 1);
    }

    public function test_submit_dual_accept_calls_service_and_dispatches_completion(): void
    {
        [$instance] = $this->createInReviewInstance();

        $this->mockAcceptancePrefill($instance);

        $completedForm = new AnalysisAcceptanceForm([
            'id' => (string) Str::uuid(),
            'status' => AnalysisAcceptanceForm::STATUS_COMPLETED,
            'sample_header_id' => (string) Str::uuid(),
        ]);
        $completedForm->exists = true;

        $this->mock(AcceptanceFormService::class, function ($mock) use ($completedForm): void {
            $mock->shouldReceive('acceptWithDualSignatures')
                ->once()
                ->andReturn($completedForm);
        });

        Livewire::actingAs($this->user)
            ->test(AcceptanceFormWizard::class)
            ->dispatch('open-acceptance-wizard', submissionFormInstanceId: $instance->id)
            ->set('receivingPersonSignature', 'data:image/png;base64,receiving')
            ->set('customerSignature', 'data:image/png;base64,customer')
            ->call('submitDualAccept')
            ->assertDispatched('acceptance-form-completed')
            ->assertSet('showModal', false);
    }

    /**
     * @return array{0: SubmissionFormInstance, 1: CustomerContact}
     */
    private function createInReviewInstance(): array
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Wizard Customer',
            'code' => 'WIZ001',
        ]);

        $companyId = (string) Str::uuid();
        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Wizard Unit',
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        $contact = CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Jane',
            'last_name' => 'Client',
            'email' => 'jane@example.test',
            'telephone' => '1',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Wizard TRF',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_id' => $form->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'submitted_by' => $this->user->id,
        ]);

        return [$instance, $contact];
    }

    private function mockAcceptancePrefill(SubmissionFormInstance $instance): void
    {
        $this->mock(AcceptanceFormPricingService::class, function ($mock) use ($instance): void {
            $mock->shouldReceive('buildPrefillFromSelection')->andReturn([
                'customer_id' => (string) $instance->crm_customer_id,
                'customer_name' => 'Wizard Customer',
                'number_of_samples' => 1,
                'mode_of_work' => 'Normal',
                'request_date' => '2026-06-23',
                'date_of_sampling' => null,
                'quotation_locked' => false,
                'lines' => [[
                    'line_no' => 1,
                    'sample_type_id' => null,
                    'analysis_type_id' => null,
                    'parameter_label' => 'Test',
                    'unit_amount' => 50,
                    'number_of_samples' => 1,
                    'is_approved' => true,
                    'sort_order' => 0,
                ]],
            ]);
            $mock->shouldReceive('deduplicateRedundantAnalysisTypeLines')
                ->andReturnUsing(fn (array $lines) => $lines);
        });
    }
}
