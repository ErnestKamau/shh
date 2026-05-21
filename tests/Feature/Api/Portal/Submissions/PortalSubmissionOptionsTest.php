<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalSubmissionOptionsTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_returns_client_units_scoped_to_portal_customer(): void
    {
        CRMCustomer::query()->create([
            'id' => self::CUSTOMER_ID,
            'name' => 'Portal Customer',
            'code' => 'PC001',
            'active' => 1,
        ]);

        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid7(),
            'crm_customer_id' => self::CUSTOMER_ID,
            'company_id' => (string) Str::uuid7(),
            'name' => 'Main Site',
            'active' => 1,
        ]);

        $this->portalGet('/api/v1/portal/submissions/options?element_type=client_unit_select')
            ->assertOk()
            ->assertJsonPath('options.0.value', $unit->id)
            ->assertJsonPath('options.0.label', 'Main Site');
    }

    public function test_rejects_invalid_element_type(): void
    {
        $this->portalGet('/api/v1/portal/submissions/options?element_type=not_a_real_type')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_client_select_returns_single_customer_for_portal_scope(): void
    {
        CRMCustomer::query()->create([
            'id' => self::CUSTOMER_ID,
            'name' => 'Scoped Customer',
            'code' => 'SC001',
            'active' => 1,
        ]);

        $response = $this->portalGet('/api/v1/portal/submissions/options?element_type=client_select');

        $response->assertOk()
            ->assertJsonPath('options.0.value', self::CUSTOMER_ID)
            ->assertJsonPath('options.0.label', 'Scoped Customer')
            ->assertJsonStructure(['options', 'pagination']);
    }

    private function portalGet(string $uri): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->getJson($uri);
    }
}
