<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\AnalysisType;
use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Lab;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\Billing\Pricelist;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\SampleType;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\CustomerContactVerificationService;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcceptanceFormDualSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_contacts_for_customer_includes_non_portal_contacts(): void
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Dual Sig Customer',
            'code' => 'DSC001',
        ]);

        $companyId = (string) Str::uuid();
        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Unit',
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Portal',
            'last_name' => 'User',
            'email' => 'portal@example.test',
            'telephone' => '1',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
            'can_login' => true,
        ]);

        CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Walk',
            'last_name' => 'In',
            'email' => 'walkin@example.test',
            'telephone' => '2',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
            'can_login' => false,
        ]);

        $portalOnly = app(CustomerContactVerificationService::class)
            ->portalContactsForCustomer((string) $customer->id);
        $active = app(CustomerContactVerificationService::class)
            ->activeContactsForCustomer((string) $customer->id);

        $this->assertCount(1, $portalOnly);
        $this->assertCount(2, $active);
    }

    public function test_accept_with_dual_signatures_creates_completed_form_and_moves_batch_to_samples_in_lab(): void
    {
        $this->mock(SampleAnalysisSetupService::class, function ($mock): void {
            $mock->shouldReceive('syncAnalysisRelations')->andReturnNull();
            $mock->shouldReceive('createCapturedResultsForAnalysisType')->andReturnNull();
        });

        SampleAnalysisStage::query()->create([
            'name' => 'Request Review',
            'code' => 'SRR',
            'active' => 1,
            'sample_workflow' => 'Samples Request Review',
            'level' => 1,
        ]);

        SampleAnalysisStage::query()->create([
            'name' => 'In Lab',
            'code' => 'SIL',
            'active' => 1,
            'sample_workflow' => 'Samples In Lab',
            'level' => 1,
        ]);

        $customer = CRMCustomer::query()->create([
            'name' => 'Dual Accept Customer',
            'code' => 'DAC001',
        ]);

        $pricelist = Pricelist::query()->create([
            'description' => 'Dual Test',
            'active' => 1,
            'currency_id' => (string) Str::uuid(),
        ]);

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();

        $sampleType = SampleType::query()->create(['name' => 'Water', 'code' => 'WAT', 'company_id' => $companyId]);

        $analysisType = AnalysisType::query()->create([
            'name' => 'Analysis A',
            'code' => 'ANA',
            'sample_type_id' => $sampleType->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $lab = Lab::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'DUAL',
            'name' => 'Dual Lab',
            'phone1' => '000',
            'active' => true,
        ]);

        $user = User::create([
            'name' => 'Receiving Officer',
            'email' => 'receiving@example.test',
            'password' => bcrypt('password'),
            'lab_id' => $lab->id,
        ]);

        Auth::login($user);

        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Dual Unit',
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        $contact = CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Client',
            'last_name' => 'Signer',
            'email' => 'client@example.test',
            'telephone' => '1',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
        ]);

        $submissionForm = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Dual Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_id' => $submissionForm->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'submitted_by' => $user->id,
            'receiving_lab_id' => $lab->id,
        ]);

        $lines = [[
            'line_no' => 1,
            'sample_type_id' => $sampleType->id,
            'analysis_type_id' => $analysisType->id,
            'parameter_label' => 'Lead',
            'unit_amount' => 100,
            'number_of_samples' => 1,
            'is_approved' => true,
            'sort_order' => 0,
        ]];

        $this->mock(AcceptanceFormPricingService::class, function ($mock) use ($customer, $pricelist, $lines): void {
            $mock->shouldReceive('buildPrefillFromSelection')->andReturn([
                'customer_id' => (string) $customer->id,
                'customer_name' => $customer->name,
                'number_of_samples' => 1,
                'mode_of_work' => 'Normal',
                'request_date' => '2026-06-23',
                'date_of_sampling' => null,
                'lines' => $lines,
                'pricelist' => $pricelist,
            ]);
            $mock->shouldReceive('resolveLinePrice')->andReturn(100.0);
        });

        $form = app(AcceptanceFormService::class)->acceptWithDualSignatures(
            (string) $instance->id,
            null,
            [
                'crm_customer_id' => (string) $customer->id,
                'customer_name' => $customer->name,
                'request_date' => '2026-06-23',
                'number_of_samples' => 1,
                'mode_of_work' => 'Normal',
                'sample_configuration_payload' => [],
            ],
            $lines,
            'Receiving Officer',
            'data:image/png;base64,receiving',
            '2026-06-23',
            (string) $contact->id,
            'Client Signer',
            'data:image/png;base64,customer',
            '2026-06-23',
            (string) $user->id,
        );

        $this->assertSame(AnalysisAcceptanceForm::STATUS_COMPLETED, $form->status);
        $this->assertNotNull($form->sample_header_id);
        $this->assertSame('Client Signer', $form->customer_signer_name);
        $this->assertSame('Receiving Officer', $form->manager_signer_name);

        $batch = SampleHeader::query()->find($form->sample_header_id);
        $this->assertNotNull($batch);
        $this->assertSame('Samples In Lab', $batch->status);

        $instance->refresh();
        $this->assertSame('approved', $instance->status);
    }
}
