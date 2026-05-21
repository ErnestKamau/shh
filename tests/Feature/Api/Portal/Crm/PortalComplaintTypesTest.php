<?php

namespace Tests\Feature\Api\Portal\Crm;

use App\Models\CRM\Complaint_Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalComplaintTypesTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_returns_active_complaint_types(): void
    {
        Complaint_Type::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Service',
            'description' => 'Service complaint',
            'status' => 1,
        ]);

        Complaint_Type::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Archived',
            'status' => 'archived',
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->getJson('/api/v1/portal/complaints/types')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.types.0.name', 'Service')
            ->assertJsonCount(1, 'data.types');
    }
}
