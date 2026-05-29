<?php

namespace Tests\Unit\Sampleworkflow;

use App\Lab;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcceptanceFormSampleHeaderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_create_attributes_maps_request_contact_lab_and_dates(): void
    {
        SampleAnalysisStage::query()->create([
            'name' => 'Request Review',
            'code' => 'SRR-HDR',
            'active' => 1,
            'sample_workflow' => 'Samples Request Review',
            'level' => 1,
        ]);

        $lab = Lab::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'DNA',
            'name' => 'DNA Lab',
            'phone1' => '000',
            'active' => true,
        ]);

        $sro = User::create([
            'name' => 'Receiving SRO',
            'email' => 'sro@example.test',
            'password' => bcrypt('password'),
            'lab_id' => $lab->id,
        ]);

        $customer = CRMCustomer::query()->create([
            'name' => 'Header Test Customer',
            'code' => 'HTC001',
        ]);

        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Main Site',
            'company_id' => (string) Str::uuid(),
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        $contact = CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Jane',
            'last_name' => 'Requester',
            'email' => 'jane@example.test',
            'telephone' => '123',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => (string) Str::uuid(),
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
        ]);

        $submitter = User::create([
            'name' => 'Portal Submitter',
            'email' => 'submitter@example.test',
            'password' => bcrypt('password'),
            'crm_contact_id' => $contact->id,
        ]);

        $formTemplate = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_id' => $formTemplate->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'submitted_by' => $submitter->id,
            'reviewed_by' => $sro->id,
            'reviewed_at' => '2026-05-20 10:00:00',
            'receiving_lab_id' => $lab->id,
        ]);

        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'submission_form_instance_id' => $instance->id,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Header Test Customer',
            'request_date' => '2026-05-18',
            'date_of_sampling' => '2026-05-19',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'customer_signer_name' => 'Signer Name',
            'receipt_notification_payload' => [
                'sample_receiving_date' => '2026-05-21',
                'submitter_name' => 'Portal Submitter',
            ],
        ]);

        $service = app(AcceptanceFormSampleHeaderService::class);

        $attributes = $service->buildCreateAttributes(
            $acceptanceForm,
            (string) Str::uuid(),
            null,
            null,
        );

        $this->assertSame($customer->id, $attributes['crm_customer_id']);
        $this->assertSame($contact->id, $attributes['crm_contact_id']);
        $this->assertSame('jane@example.test', $attributes['schedule_customer_email']);
        $this->assertSame($unit->id, $attributes['crm_unit_id']);
        $this->assertSame('Main Site', $attributes['crm_unit_name']);
        $this->assertSame($lab->id, $attributes['lab_id']);
        $this->assertSame('2026-05-19', $attributes['date_collected']);
        $this->assertSame('2026-05-21', $attributes['receipt_date']);
        $this->assertSame('Signer Name', $attributes['payment_done_by']);
        $this->assertSame('Portal Submitter', $attributes['submit_by']);
        $this->assertSame($sro->id, $attributes['receiving_officer']);
        $this->assertSame('Receiving SRO', $attributes['receiving_officer_name']);
    }
}
