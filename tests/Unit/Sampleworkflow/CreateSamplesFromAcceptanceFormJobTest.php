<?php

namespace Tests\Unit\Sampleworkflow;

use App\AnalysisType;
use App\Invoice;
use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Lab;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Billing\InvoiceNumberGenerator;
use App\Models\Billing\Pricelist;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\User;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\Services\Sampleworkflow\SampleDetailCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreateSamplesFromAcceptanceFormJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_one_detail_per_sample_type_with_matching_sample_type_id(): void
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

        $customer = CRMCustomer::query()->create([
            'name' => 'Job Test Customer',
            'code' => 'JTC001',
        ]);

        $pricelist = Pricelist::query()->create([
            'description' => 'Test',
            'active' => 1,
            'currency_id' => (string) Str::uuid(),
        ]);

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();

        $sampleTypeA = SampleType::query()->create(['name' => 'Water', 'code' => 'WAT', 'company_id' => $companyId]);
        $sampleTypeB = SampleType::query()->create(['name' => 'Soil', 'code' => 'SOL', 'company_id' => $companyId]);

        $analysisTypeA = AnalysisType::query()->create([
            'name' => 'Analysis A',
            'code' => 'ANA',
            'sample_type_id' => $sampleTypeA->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $analysisTypeB = AnalysisType::query()->create([
            'name' => 'Analysis B',
            'code' => 'ANB',
            'sample_type_id' => $sampleTypeB->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $lab = Lab::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'JOB',
            'name' => 'Job Lab',
            'phone1' => '000',
            'active' => true,
        ]);

        $sro = User::create([
            'name' => 'Job SRO',
            'email' => 'job-sro@example.test',
            'password' => bcrypt('password'),
            'lab_id' => $lab->id,
        ]);

        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Job Unit',
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        $contact = CustomerContact::query()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Job',
            'last_name' => 'Contact',
            'email' => 'job-contact@example.test',
            'telephone' => '1',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'company_id' => $companyId,
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'active' => true,
        ]);

        $submitter = User::create([
            'name' => 'Job Submitter',
            'email' => 'job-submitter@example.test',
            'password' => bcrypt('password'),
            'crm_contact_id' => $contact->id,
        ]);

        $submissionForm = SubmissionForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Job Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'submission_form_id' => $submissionForm->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'submitted_by' => $submitter->id,
            'reviewed_by' => $sro->id,
            'reviewed_at' => '2026-05-22 09:00:00',
            'receiving_lab_id' => $lab->id,
        ]);

        $form = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'submission_form_instance_id' => $instance->id,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Job Test Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'date_of_sampling' => '2026-05-21',
            'request_date' => '2026-05-20',
            'customer_signer_name' => 'Job Signer',
            'total_amount' => 200,
            'pricelist_id' => $pricelist->id,
            'receipt_notification_payload' => [
                'sample_receiving_date' => '2026-05-22',
            ],
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $form->id,
            'line_no' => 1,
            'sample_type_id' => $sampleTypeA->id,
            'analysis_type_id' => $analysisTypeA->id,
            'parameter_label' => 'Lead in water',
            'unit_amount' => 100,
            'number_of_samples' => 1,
            'is_approved' => true,
            'sort_order' => 0,
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $form->id,
            'line_no' => 2,
            'sample_type_id' => $sampleTypeB->id,
            'analysis_type_id' => $analysisTypeB->id,
            'parameter_label' => 'pH in soil',
            'unit_amount' => 100,
            'number_of_samples' => 1,
            'is_approved' => true,
            'sort_order' => 1,
        ]);

        (new CreateSamplesFromAcceptanceFormJob((string) $form->id))->handle(
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService::class),
            app(SampleAnalysisSetupService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormPricingService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleConfigService::class),
            app(InvoiceNumberGenerator::class),
            app(SampleDetailCreationService::class),
            app(JobSampleNumberingService::class),
        );

        $form->refresh();
        $this->assertNotNull($form->sample_header_id);
        $this->assertNotNull($form->invoice_id);

        $details = SampleDetails::query()
            ->where('sample_header_id', $form->sample_header_id)
            ->orderBy('sample_code')
            ->get();

        $this->assertCount(2, $details);
        $this->assertEqualsCanonicalizing(
            [$sampleTypeA->id, $sampleTypeB->id],
            $details->pluck('sample_type_id')->all()
        );

        $detailForTypeA = $details->firstWhere('sample_type_id', $sampleTypeA->id);
        $this->assertSame((string) $analysisTypeA->id, $detailForTypeA->analysis_type_id);

        $detailForTypeB = $details->firstWhere('sample_type_id', $sampleTypeB->id);
        $this->assertSame((string) $analysisTypeB->id, $detailForTypeB->analysis_type_id);

        $header = SampleHeader::query()->find($form->sample_header_id);
        $this->assertNotNull($header);
        $this->assertMatchesRegularExpression('/^\d{9}$/', (string) $header->batch_code);
        foreach ($details as $detail) {
            $this->assertMatchesRegularExpression(
                '/^' . preg_quote((string) $header->batch_code, '/') . '-[MLC]\d{3}$/',
                (string) $detail->sample_code,
            );
            $this->assertSame($header->batch_code . '-R01', $detail->report_number);
        }
        $this->assertSame('Samples Request Review', $header->status);
        $this->assertSame($form->invoice_id, $header->invoice_id);
        $this->assertSame($lab->id, $header->lab_id);
        $this->assertSame($contact->id, $header->crm_contact_id);
        $this->assertSame('job-contact@example.test', $header->schedule_customer_email);
        $this->assertSame($unit->id, $header->crm_unit_id);
        $this->assertSame('2026-05-21', $header->date_collected);
        $this->assertSame('2026-05-22', $header->receipt_date);
        $this->assertSame('Job Signer', $header->payment_done_by);
        $this->assertSame($sro->id, $header->receiving_officer);
        $this->assertSame(1, (int) $header->lab_capable);
        $this->assertSame(1, (int) $header->client_instruction_clear);

        $invoice = Invoice::query()->find($form->invoice_id);
        $this->assertNotNull($invoice);
        $this->assertMatchesRegularExpression('/^INV\d{4}$/', (string) $invoice->invoice_number);
    }

    public function test_job_repeats_details_for_number_of_samples_when_single_sample_type(): void
    {
        $this->mock(SampleAnalysisSetupService::class, function ($mock): void {
            $mock->shouldReceive('syncAnalysisRelations')->andReturnNull();
            $mock->shouldReceive('createCapturedResultsForAnalysisType')->andReturnNull();
        });

        SampleAnalysisStage::query()->create([
            'name' => 'Request Review',
            'code' => 'SRR2',
            'active' => 1,
            'sample_workflow' => 'Samples Request Review',
            'level' => 1,
        ]);

        $customer = CRMCustomer::query()->create([
            'name' => 'Multi Sample Customer',
            'code' => 'MSC001',
        ]);

        Pricelist::query()->create([
            'description' => 'Test',
            'active' => 1,
            'currency_id' => (string) Str::uuid(),
        ]);

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();

        $sampleType = SampleType::query()->create(['name' => 'Water', 'code' => 'W2', 'company_id' => $companyId]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Microbial count',
            'code' => 'AN1',
            'sample_type_id' => $sampleType->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $form = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Multi Sample Customer',
            'number_of_samples' => 3,
            'mode_of_work' => 'Normal',
            'total_amount' => 50,
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $form->id,
            'line_no' => 1,
            'sample_type_id' => $sampleType->id,
            'analysis_type_id' => $analysisType->id,
            'parameter_label' => 'Test',
            'unit_amount' => 50,
            'is_approved' => true,
        ]);

        (new CreateSamplesFromAcceptanceFormJob((string) $form->id))->handle(
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService::class),
            app(SampleAnalysisSetupService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormPricingService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleConfigService::class),
            app(InvoiceNumberGenerator::class),
            app(SampleDetailCreationService::class),
            app(JobSampleNumberingService::class),
        );

        $details = SampleDetails::query()->where('sample_header_id', $form->fresh()->sample_header_id)->get();
        $this->assertCount(3, $details);
        $this->assertTrue($details->every(fn (SampleDetails $d) => (string) $d->sample_type_id === (string) $sampleType->id));
        $jobNumber = (string) SampleHeader::query()->find($form->fresh()->sample_header_id)?->batch_code;
        $this->assertMatchesRegularExpression('/^\d{9}$/', $jobNumber);
        $this->assertSame(
            ['-M001', '-M002', '-M003'],
            $details->sortBy('sample_code')->pluck('sample_code')->map(
                fn (string $code) => substr($code, strlen($jobNumber))
            )->all(),
        );
    }

    public function test_job_uses_trf_category_prefix_and_customer_sample_id_from_config(): void
    {
        $this->mock(SampleAnalysisSetupService::class, function ($mock): void {
            $mock->shouldReceive('syncAnalysisRelations')->andReturnNull();
            $mock->shouldReceive('createCapturedResultsForAnalysisType')->andReturnNull();
        });

        SampleAnalysisStage::query()->create([
            'name' => 'Request Review',
            'code' => 'SRR3',
            'active' => 1,
            'sample_workflow' => 'Samples Request Review',
            'level' => 1,
        ]);

        $customer = CRMCustomer::query()->create([
            'name' => 'TRF Customer',
            'code' => 'TRF001',
        ]);

        Pricelist::query()->create([
            'description' => 'Test',
            'active' => 1,
            'currency_id' => (string) Str::uuid(),
        ]);

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();

        $sampleType = SampleType::query()->create(['name' => 'Water', 'code' => 'W3', 'company_id' => $companyId]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Microbiology panel',
            'code' => 'MIC',
            'sample_type_id' => $sampleType->id,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $form = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'TRF Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 75,
            'sample_configuration_payload' => [
                [
                    'sample_type_id' => $sampleType->id,
                    'analysis_type_id' => $analysisType->id,
                    'number_of_samples' => 1,
                    'sample_code_prefix' => JobSampleNumberingService::PREFIX_MICROBIOLOGY,
                    'instances' => [
                        ['customer_sample_id' => 'CUST-SAMPLE-99', 'sample_marking' => ''],
                    ],
                ],
            ],
        ]);

        AnalysisAcceptanceFormLine::query()->create([
            'analysis_acceptance_form_id' => $form->id,
            'line_no' => 1,
            'sample_type_id' => $sampleType->id,
            'analysis_type_id' => $analysisType->id,
            'parameter_label' => 'Coliform',
            'unit_amount' => 75,
            'is_approved' => true,
        ]);

        (new CreateSamplesFromAcceptanceFormJob((string) $form->id))->handle(
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService::class),
            app(SampleAnalysisSetupService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormPricingService::class),
            app(\App\Services\Sampleworkflow\AcceptanceFormSampleConfigService::class),
            app(InvoiceNumberGenerator::class),
            app(SampleDetailCreationService::class),
            app(JobSampleNumberingService::class),
        );

        $header = SampleHeader::query()->find($form->fresh()->sample_header_id);
        $detail = SampleDetails::query()->where('sample_header_id', $header->id)->sole();

        $this->assertSame($header->batch_code . '-M001', $detail->sample_code);
        $this->assertSame('CUST-SAMPLE-99', $detail->customer_sample_id);
        $this->assertSame($header->batch_code . '-R01', $detail->report_number);
    }
}
