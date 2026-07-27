<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\ContractCustomerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContractCustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_scheduled_enquiry_matches_scheduled_channel_only(): void
    {
        $service = app(ContractCustomerService::class);

        $scheduled = SampleSubmissionRequest::query()->make([
            'source_channel' => 'scheduled',
        ]);
        $walkIn = SampleSubmissionRequest::query()->make([
            'source_channel' => 'walk_in',
        ]);

        $this->assertTrue($service->isScheduledEnquiry($scheduled));
        $this->assertFalse($service->isScheduledEnquiry($walkIn));
    }

    public function test_has_active_sampling_contract_uses_crm_validity_window(): void
    {
        $customerId = (string) Str::uuid();

        CRMCustomer::query()->create([
            'id' => $customerId,
            'name' => 'Contract Customer',
            'code' => 'CC001',
            'contract_valid_from' => Carbon::today()->subMonth(),
            'contract_valid_to' => Carbon::today()->addMonth(),
        ]);

        $service = app(ContractCustomerService::class);

        $this->assertTrue($service->hasActiveSamplingContract($customerId));
        $this->assertFalse($service->hasActiveSamplingContract($customerId, Carbon::today()->addMonths(2)));
    }

    public function test_bypasses_commercial_quotation_gate_for_scheduled_or_active_crm_contract(): void
    {
        $service = app(ContractCustomerService::class);

        $scheduled = SampleSubmissionRequest::query()->make([
            'source_channel' => 'scheduled',
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        $this->assertTrue($service->bypassesCommercialQuotationGate($scheduled));

        $customerId = (string) Str::uuid();
        CRMCustomer::query()->create([
            'id' => $customerId,
            'name' => 'CRM Contract',
            'code' => 'CRM1',
            'contract_valid_from' => Carbon::today()->subDay(),
            'contract_valid_to' => Carbon::today()->addDay(),
        ]);

        $walkInWithContract = SampleSubmissionRequest::query()->make([
            'source_channel' => 'walk_in',
            'crm_customer_id' => $customerId,
        ]);

        $this->assertTrue($service->bypassesCommercialQuotationGate($walkInWithContract));
    }

    public function test_has_assigned_pricelist_detects_customer_assignment(): void
    {
        $customerId = (string) Str::uuid();
        $pricelist = \App\Models\Billing\Pricelist::query()->create([
            'name' => 'Assigned',
            'active' => true,
        ]);
        \App\Models\Billing\PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $service = app(ContractCustomerService::class);

        $this->assertTrue($service->hasAssignedPricelist($customerId));
        $this->assertTrue($service->hasContractPricelist($customerId));
        $this->assertFalse($service->hasAssignedPricelist((string) Str::uuid()));
    }
}
