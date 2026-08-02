<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\ContractCustomerService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EnquiryReceptionReadinessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_receive_handoff_and_sample_acceptance_ignore_pending_subcontract_dispatch(): void
    {
        $enquiry = $this->makeEnquiry(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);
        $enquiry = Mockery::mock($enquiry)->makePartial();
        $enquiry->shouldReceive('needsSubcontractDispatch')->andReturn(true);

        $service = new EnquiryReceptionReadinessService(
            Mockery::mock(ContractCustomerService::class)
        );

        $this->assertTrue($service->isEligibleForReceiveHandoff($enquiry));
        $this->assertTrue($service->isEligibleForSampleAcceptance($enquiry));
    }

    public function test_sample_acceptance_allows_integrity_status(): void
    {
        $enquiry = $this->makeEnquiry(SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK);

        $service = new EnquiryReceptionReadinessService(
            Mockery::mock(ContractCustomerService::class)
        );

        $this->assertTrue($service->isEligibleForSampleAcceptance($enquiry));
    }

    public function test_mark_sample_integrity_check_updates_status(): void
    {
        $enquiry = $this->makeEnquiry(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);

        $service = new EnquiryReceptionReadinessService(
            Mockery::mock(ContractCustomerService::class)
        );

        $updated = $service->markSampleIntegrityCheck($enquiry);

        $this->assertSame(
            SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
            $updated->status
        );
    }

    public function test_physical_receive_eligibility_does_not_require_subcontract_dispatch(): void
    {
        $enquiry = $this->makeEnquiry(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);
        $enquiry->quotation_accepted_at = now();
        $enquiry->save();

        $enquiry = Mockery::mock($enquiry->fresh())->makePartial();
        $enquiry->shouldReceive('needsSubcontractDispatch')->andReturn(true);

        $contract = Mockery::mock(ContractCustomerService::class);
        $contract->shouldReceive('bypassesCommercialQuotationGate')->andReturn(false);

        $service = new EnquiryReceptionReadinessService($contract);

        $this->assertTrue($service->isEligibleForPhysicalReceive($enquiry));
    }

    private function makeEnquiry(string $status): SampleSubmissionRequest
    {
        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid(),
            'status' => $status,
            'source_channel' => 'walk_in',
            'request_number' => random_int(100, 9999),
            'quotation_accepted_at' => now(),
        ]);
    }
}
