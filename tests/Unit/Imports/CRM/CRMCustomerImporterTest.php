<?php

namespace Tests\Unit\Imports\CRM;

use App\Country;
use App\Imports\CRM\CRMCustomerImporter;
use App\Models\BulkImportBatch;
use App\Models\CRM\CRMCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CRMCustomerImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_customer_code_preserves_underscores_and_strips_excel_floats(): void
    {
        $importer = $this->makeImporter();

        $this->assertSame('CLIENT_001', $this->callProtected($importer, 'normalizeCustomerCode', ['CLIENT_001']));
        $this->assertSame('1001', $this->callProtected($importer, 'normalizeCustomerCode', [1001.0]));
        $this->assertSame('CUST-001', $this->callProtected($importer, 'normalizeCustomerCode', ['  CUST-001  ']));
    }

    public function test_import_row_stores_customer_code_so_it_can_be_read_back(): void
    {
        $companyId = (string) Str::uuid();
        Country::query()->create([
            'name' => 'Brazil',
            'iso_code_2' => 'BR',
            'iso_code_3' => 'BRA',
            'address_format' => '{firstname} {lastname}',
            'postcode_required' => 0,
            'status' => 1,
        ]);

        $importer = $this->makeImporter($companyId);
        $originalRow = [
            'name' => 'Acme Corporation',
            'customer_code' => 'CLIENT_001',
            'email' => 'contact@acme.com',
            'phone1' => '+1234567890',
            'country_code' => 'BR',
            'currency_code' => 'USD',
        ];

        $transformed = $this->callProtected($importer, 'transformRow', [$originalRow]);
        $this->callProtected($importer, 'importRow', [$transformed, $originalRow]);

        $customer = CRMCustomer::query()->where('company_id', $companyId)->first();

        $this->assertNotNull($customer);
        $this->assertSame('CLIENT_001', $customer->code);
    }

    public function test_import_row_updates_existing_customer_by_code(): void
    {
        $companyId = (string) Str::uuid();
        Country::query()->create([
            'name' => 'Brazil',
            'iso_code_2' => 'BR',
            'iso_code_3' => 'BRA',
            'address_format' => '{firstname} {lastname}',
            'postcode_required' => 0,
            'status' => 1,
        ]);

        CRMCustomer::query()->create([
            'name' => 'Old Name',
            'code' => 'CLIENT_001',
            'email' => 'old@example.com',
            'telephone1' => '000',
            'company_id' => $companyId,
            'country_id' => Country::query()->value('id'),
            'active' => 1,
        ]);

        $importer = $this->makeImporter($companyId);
        $originalRow = [
            'name' => 'Updated Name',
            'customer_code' => 'CLIENT_001',
            'email' => 'new@example.com',
            'phone1' => '+1234567890',
            'country_code' => 'BR',
            'currency_code' => 'USD',
        ];

        $transformed = $this->callProtected($importer, 'transformRow', [$originalRow]);
        $this->callProtected($importer, 'importRow', [$transformed, $originalRow]);

        $this->assertSame(1, CRMCustomer::query()->where('company_id', $companyId)->count());
        $customer = CRMCustomer::query()->where('company_id', $companyId)->first();
        $this->assertSame('Updated Name', $customer->name);
        $this->assertSame('CLIENT_001', $customer->code);
    }

    private function makeImporter(?string $companyId = null): CRMCustomerImporter
    {
        $batch = BulkImportBatch::query()->create([
            'company_id' => $companyId ?? (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'module' => 'crm',
            'form_type' => 'customer',
            'status' => 'processing',
        ]);

        return new CRMCustomerImporter($batch);
    }

    private function callProtected(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object, ...$arguments);
    }
}
