<?php

namespace Tests\Unit\Services\CRM;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Services\CRM\CustomerPurgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPurgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_for_company_removes_customers_and_direct_dependents(): void
    {
        $companyId = (string) Str::uuid();
        $otherCompanyId = (string) Str::uuid();

        $customer = CRMCustomer::query()->create([
            'name' => 'Purge Customer',
            'code' => 'PURGE-001',
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $otherCustomer = CRMCustomer::query()->create([
            'name' => 'Other Company Customer',
            'code' => 'OTHER-001',
            'company_id' => $otherCompanyId,
            'active' => 1,
        ]);

        $unit = CRMCompanyUnit::query()->create([
            'name' => 'Main Unit',
            'crm_customer_id' => $customer->id,
            'company_id' => $companyId,
            'active' => 1,
        ]);

        CustomerContact::query()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'crm_customer_id' => $customer->id,
            'active' => 1,
        ]);

        $summary = app(CustomerPurgeService::class)->purgeForCompany($companyId);

        $this->assertSame(1, $summary['customers']);
        $this->assertSame(1, $summary['units']);
        $this->assertSame(1, $summary['contacts']);
        $this->assertSame(0, CRMCustomer::query()->where('company_id', $companyId)->count());
        $this->assertSame(0, CRMCompanyUnit::query()->where('crm_customer_id', $customer->id)->count());
        $this->assertSame(0, CustomerContact::query()->where('crm_customer_id', $customer->id)->count());
        $this->assertDatabaseHas('crm_customers', ['id' => $otherCustomer->id]);
        $this->assertDatabaseMissing('crm_company_units', ['id' => $unit->id]);
    }

    public function test_purge_all_removes_customers_across_companies(): void
    {
        $companyA = (string) Str::uuid();
        $companyB = (string) Str::uuid();

        CRMCustomer::query()->create([
            'name' => 'Company A Customer',
            'code' => 'A-001',
            'company_id' => $companyA,
            'active' => 1,
        ]);

        CRMCustomer::query()->create([
            'name' => 'Company B Customer',
            'code' => 'B-001',
            'company_id' => $companyB,
            'active' => 1,
        ]);

        $summary = app(CustomerPurgeService::class)->purgeAll();

        $this->assertSame(2, $summary['customers']);
        $this->assertSame(0, CRMCustomer::query()->count());
    }

    public function test_purge_for_company_returns_zero_counts_when_no_customers_exist(): void
    {
        $summary = app(CustomerPurgeService::class)->purgeForCompany((string) Str::uuid());

        $this->assertSame(0, $summary['customers']);
        $this->assertSame(0, $summary['units']);
        $this->assertSame(0, $summary['contacts']);
    }
}
