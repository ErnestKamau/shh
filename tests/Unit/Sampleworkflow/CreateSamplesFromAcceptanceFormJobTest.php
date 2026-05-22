<?php

namespace Tests\Unit\Sampleworkflow;

use App\AnalysisType;
use App\Invoice;
use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Services\Billing\InvoiceNumberGenerator;
use App\Models\Billing\Pricelist;
use App\Models\CRM\CRMCustomer;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
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

        $form = AnalysisAcceptanceForm::query()->create([
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'crm_customer_id' => $customer->id,
            'customer_name' => 'Job Test Customer',
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'total_amount' => 200,
            'pricelist_id' => $pricelist->id,
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
            app(InvoiceNumberGenerator::class)
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
        $this->assertSame('Samples Request Review', $header->status);
        $this->assertSame($form->invoice_id, $header->invoice_id);

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
            'name' => 'Analysis',
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
            app(InvoiceNumberGenerator::class)
        );

        $details = SampleDetails::query()->where('sample_header_id', $form->fresh()->sample_header_id)->get();
        $this->assertCount(3, $details);
        $this->assertTrue($details->every(fn (SampleDetails $d) => (string) $d->sample_type_id === (string) $sampleType->id));
    }
}
