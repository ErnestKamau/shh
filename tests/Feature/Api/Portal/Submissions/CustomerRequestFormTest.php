<?php

namespace Tests\Feature\Api\Portal\Submissions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRequestFormTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_customer_request_endpoint_is_deprecated(): void
    {
        $response = $this->portalGet('/api/v1/portal/submissions/forms/customer-request');

        $response->assertStatus(410)
            ->assertJsonPath('error.code', 'customer_request_form_deprecated')
            ->assertJsonFragment([
                'GET /api/v1/portal/submissions/forms/test-request-templates',
            ]);
    }

    private function portalGet(string $uri)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
        ])->getJson($uri);
    }
}
