<?php

namespace Tests\Feature\SampleWorkflow;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\Commercial\CommercialEnquiryFromTrfService;
use App\Services\Commercial\ContractCustomerService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduledContractEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_trf_sync_marks_enquiry_ready_without_pricelist_assignment(): void
    {
        $user = User::create([
            'name' => 'Scheduler',
            'email' => 'scheduler@example.test',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $customerId = (string) Str::uuid();
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $template = TestRequestForm::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'TRF Scheduled',
            'code' => 'TRF-SCH',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid(),
            'test_request_form_id' => $template->id,
            'crm_customer_id' => $customerId,
            'source_channel' => TestRequestFormInstance::CHANNEL_SCHEDULED,
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [
                'sample_rows' => [
                    ['sample_description' => 'Scheduled sample', 'qty' => 1],
                ],
            ],
        ]);

        $enquiry = app(CommercialEnquiryFromTrfService::class)->syncFromTrfi($trfi);

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $enquiry->status);
        $this->assertTrue((bool) $enquiry->po_skipped);
        $this->assertNotNull($enquiry->quotation_accepted_at);
        $this->assertNotNull($enquiry->accepted_quotation_header_id);
        $this->assertSame('sampling_contract', $enquiry->pricing_source);

        $contractService = app(ContractCustomerService::class);
        $this->assertTrue($contractService->isScheduledEnquiry($enquiry));

        $readiness = app(EnquiryReceptionReadinessService::class);
        $this->assertTrue($readiness->isEligibleForPhysicalReceive($enquiry->fresh()));
    }

    public function test_walk_in_pricelist_customer_still_requires_quotation_pipeline(): void
    {
        $customerId = (string) Str::uuid();

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'walk_in',
            'pricing_source' => 'contract',
        ]);

        $contractService = app(ContractCustomerService::class);
        $this->assertFalse($contractService->isScheduledEnquiry($enquiry));
        $this->assertFalse($contractService->bypassesCommercialQuotationGate($enquiry));

        $readiness = app(EnquiryReceptionReadinessService::class);
        $this->assertFalse($readiness->isEligibleForPhysicalReceive($enquiry));
    }

    public function test_walk_in_customer_with_active_crm_sampling_contract_bypasses_reception_gate(): void
    {
        $customerId = (string) Str::uuid();

        CRMCustomer::query()->create([
            'id' => $customerId,
            'name' => 'Sampling Contract Co',
            'code' => 'SCC001',
            'contract_valid_from' => Carbon::today()->subWeek(),
            'contract_valid_to' => Carbon::today()->addWeek(),
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'walk_in',
        ]);

        $contractService = app(ContractCustomerService::class);
        $this->assertTrue($contractService->hasActiveSamplingContract($customerId));
        $this->assertTrue($contractService->bypassesCommercialQuotationGate($enquiry));

        $readiness = app(EnquiryReceptionReadinessService::class);
        $this->assertTrue($readiness->isEligibleForPhysicalReceive($enquiry));
    }
}
