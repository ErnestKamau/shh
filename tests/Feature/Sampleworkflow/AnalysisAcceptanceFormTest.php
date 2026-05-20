<?php

namespace Tests\Feature\Sampleworkflow;

use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerNotification;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\Models\SubmissionForm;
use App\SampleAnalysisStage;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalysisAcceptanceFormTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    public function test_step_one_creates_form_and_customer_notification(): void
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Test Customer',
            'code' => 'TC001',
        ]);

        $form = SubmissionForm::query()->create([
            'name' => 'Portal Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'title' => 'Request 1',
        ]);

        $acceptanceForm = app(AcceptanceFormService::class)->createFromStep1(
            (string) $instance->id,
            null,
            [
                'crm_customer_id' => $customer->id,
                'customer_name' => 'Test Customer',
                'request_date' => now()->format('Y-m-d'),
                'number_of_samples' => 2,
                'mode_of_work' => 'Normal',
                'date_of_sampling' => now()->format('Y-m-d'),
            ],
            [
                [
                    'line_no' => 1,
                    'sample_type_id' => (string) Str::uuid(),
                    'analysis_type_id' => (string) Str::uuid(),
                    'parameter_label' => 'Lead',
                    'unit_amount' => 50,
                    'number_of_samples' => 1,
                    'is_approved' => true,
                    'sort_order' => 0,
                ],
            ],
            (string) Str::uuid()
        );

        $this->assertSame(AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN, $acceptanceForm->status);
        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->id,
            'entity_type' => AnalysisAcceptanceForm::class,
            'entity_id' => $acceptanceForm->id,
            'notification_type' => CustomerNotification::TYPE_ACCEPTANCE_FORM_SIGNING,
        ]);
    }

    public function test_customer_sign_dispatches_sample_creation_job(): void
    {
        config(['sampleworkflow.acceptance_form.dispatch_sample_creation_sync' => false]);
        Bus::fake();

        $customer = CRMCustomer::query()->create([
            'name' => 'Test Customer',
            'code' => 'TC002',
        ]);

        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Test Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 100,
        ]);

        app(AcceptanceFormService::class)->recordCustomerSignature(
            $acceptanceForm,
            'Jane Client',
            'data:image/png;base64,abc',
            now()->format('Y-m-d')
        );

        $acceptanceForm->refresh();
        $this->assertSame(AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN, $acceptanceForm->status);

        Bus::assertDispatched(CreateSamplesFromAcceptanceFormJob::class, function ($job) use ($acceptanceForm) {
            return $job->acceptanceFormId === $acceptanceForm->id;
        });
    }

    public function test_customer_sign_runs_sample_creation_sync_by_default(): void
    {
        config(['sampleworkflow.acceptance_form.dispatch_sample_creation_sync' => true]);

        $customer = CRMCustomer::query()->create([
            'name' => 'Sync Customer',
            'code' => 'SC001',
        ]);

        SampleAnalysisStage::query()->create([
            'name' => 'Request Review',
            'code' => 'SRRS',
            'active' => 1,
            'sample_workflow' => 'Samples Request Review',
            'level' => 1,
        ]);

        \App\Models\Billing\Pricelist::query()->create([
            'description' => 'Default',
            'active' => 1,
            'currency_id' => (string) Str::uuid(),
        ]);

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();

        $sampleType = \App\SampleType::query()->create(['name' => 'Type', 'code' => 'T1', 'company_id' => $companyId]);
        $analysisType = \App\AnalysisType::query()->create([
            'name' => 'AT',
            'code' => 'AT1',
            'sample_type_id' => $sampleType->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Sync Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 25,
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $acceptanceForm->id,
            'line_no' => 1,
            'sample_type_id' => $sampleType->id,
            'analysis_type_id' => $analysisType->id,
            'parameter_label' => 'Param',
            'unit_amount' => 25,
            'is_approved' => true,
        ]);

        $this->mock(\App\Services\Sampleworkflow\SampleAnalysisSetupService::class, function ($mock): void {
            $mock->shouldReceive('syncAnalysisRelations')->andReturnNull();
            $mock->shouldReceive('createCapturedResultsForAnalysisType')->andReturnNull();
        });

        app(AcceptanceFormService::class)->recordCustomerSignature(
            $acceptanceForm,
            'Jane Client',
            'data:image/png;base64,abc'
        );

        $acceptanceForm->refresh();
        $this->assertNotNull($acceptanceForm->sample_header_id);
        $this->assertNotNull($acceptanceForm->invoice_id);
        $this->assertDatabaseHas('sample_details', [
            'sample_header_id' => $acceptanceForm->sample_header_id,
            'sample_type_id' => $sampleType->id,
        ]);
    }

    public function test_portal_sign_endpoint_requires_gateway_and_customer_header(): void
    {
        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);

        $customer = CRMCustomer::query()->create([
            'name' => 'Portal Customer',
            'code' => 'PC001',
        ]);

        $acceptanceForm = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Portal Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 10,
        ]);

        $this->postJson('/api/v1/portal/submissions/acceptance-forms/' . $acceptanceForm->id . '/sign', [
            'customer_signer_name' => 'Portal Customer',
            'customer_signature' => 'data:image/png;base64,abc',
        ], [
            'Authorization' => 'Bearer ' . self::GATEWAY_KEY,
            'X-CRM-Customer-Id' => $customer->id,
        ])->assertOk()
            ->assertJsonPath('data.status', AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN);
    }
}
