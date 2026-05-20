<?php

namespace Tests\Feature\Sampleworkflow;

use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Livewire\Sampleworkflow\CustomerAcceptanceSignModal;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerAcceptanceSignModalTest extends TestCase
{
    use RefreshDatabase;

    private User $labUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->labUser = User::query()->create([
            'name' => 'Lab Reviewer',
            'email' => 'lab.reviewer@example.test',
            'password' => Hash::make('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        Gate::before(fn () => true);
    }

    public function test_verify_identity_advances_to_sign_step(): void
    {
        [$acceptanceForm, $contact] = $this->createAwaitingCustomerSignFixture('correct-password');

        Livewire::actingAs($this->labUser)
            ->test(CustomerAcceptanceSignModal::class)
            ->call('openModal', $acceptanceForm->id)
            ->assertSet('showModal', true)
            ->assertSet('currentStep', 1)
            ->set('selectedContactId', $contact->id)
            ->set('password', 'correct-password')
            ->call('verifyIdentity')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 2)
            ->assertSet('verifiedContactId', $contact->id)
            ->assertSet('customerSignerName', 'Jane Customer');
    }

    public function test_wrong_password_stays_on_step_one(): void
    {
        [$acceptanceForm, $contact] = $this->createAwaitingCustomerSignFixture('correct-password');

        Livewire::actingAs($this->labUser)
            ->test(CustomerAcceptanceSignModal::class)
            ->call('openModal', $acceptanceForm->id)
            ->set('selectedContactId', $contact->id)
            ->set('password', 'wrong-password')
            ->call('verifyIdentity')
            ->assertHasErrors(['password'])
            ->assertSet('currentStep', 1)
            ->assertSet('verifiedContactId', null);
    }

    public function test_submit_customer_sign_dispatches_sample_creation_job(): void
    {
        config(['sampleworkflow.acceptance_form.dispatch_sample_creation_sync' => false]);
        Bus::fake();

        [$acceptanceForm, $contact] = $this->createAwaitingCustomerSignFixture('correct-password');

        Livewire::actingAs($this->labUser)
            ->test(CustomerAcceptanceSignModal::class)
            ->call('openModal', $acceptanceForm->id)
            ->set('selectedContactId', $contact->id)
            ->set('password', 'correct-password')
            ->call('verifyIdentity')
            ->set('customerSignature', 'data:image/png;base64,abc')
            ->call('submitCustomerSign')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $acceptanceForm->refresh();
        $this->assertSame(AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN, $acceptanceForm->status);
        $this->assertSame('Jane Customer', $acceptanceForm->customer_signer_name);

        Bus::assertDispatched(CreateSamplesFromAcceptanceFormJob::class, function ($job) use ($acceptanceForm) {
            return $job->acceptanceFormId === $acceptanceForm->id;
        });
    }

    public function test_cannot_open_modal_when_not_awaiting_customer_sign(): void
    {
        $customer = $this->createCustomer();
        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 10,
        ]);

        Livewire::actingAs($this->labUser)
            ->test(CustomerAcceptanceSignModal::class)
            ->call('openModal', $acceptanceForm->id)
            ->assertSet('showModal', false);
    }

    public function test_contact_from_another_customer_is_rejected(): void
    {
        [$acceptanceForm] = $this->createAwaitingCustomerSignFixture('correct-password');
        $otherCustomer = $this->createCustomer('Other Customer', 'OC001');
        $otherContact = $this->createPortalContact($otherCustomer, 'other@example.test', 'correct-password');

        Livewire::actingAs($this->labUser)
            ->test(CustomerAcceptanceSignModal::class)
            ->call('openModal', $acceptanceForm->id)
            ->set('selectedContactId', $otherContact->id)
            ->set('password', 'correct-password')
            ->call('verifyIdentity')
            ->assertHasErrors(['password'])
            ->assertSet('currentStep', 1);
    }

    /**
     * @return array{0: AnalysisAcceptanceForm, 1: CustomerContact}
     */
    private function createAwaitingCustomerSignFixture(string $portalPassword): array
    {
        $customer = $this->createCustomer();
        $contact = $this->createPortalContact($customer, 'jane@example.test', $portalPassword);

        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'request_date' => now()->toDateString(),
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 50,
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $acceptanceForm->id,
            'line_no' => 1,
            'parameter_label' => 'Lead',
            'unit_amount' => 50,
            'number_of_samples' => 1,
            'is_approved' => true,
        ]);

        return [$acceptanceForm, $contact];
    }

    private function createCustomer(string $name = 'Test Customer', string $code = 'TC001'): CRMCustomer
    {
        return CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => $name,
            'code' => $code,
            'active' => 1,
        ]);
    }

    private function createPortalContact(CRMCustomer $customer, string $email, string $portalPassword): CustomerContact
    {
        $companyId = (string) Str::uuid7();

        $contact = CustomerContact::query()->create([
            'id' => (string) Str::uuid7(),
            'first_name' => 'Jane',
            'last_name' => 'Customer',
            'email' => $email,
            'telephone' => '0700000000',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'active' => 1,
            'can_login' => 1,
        ]);

        User::query()->create([
            'name' => 'Jane Customer',
            'email' => $email,
            'password' => Hash::make($portalPassword),
            'active' => 1,
            'is_client' => 1,
            'client_id' => $customer->id,
            'crm_contact_id' => $contact->id,
        ]);

        return $contact;
    }
}
