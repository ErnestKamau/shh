<?php

namespace Tests\Feature\Api\Portal\Crm;

use App\Models\CRM\CustomerFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalFeedbackListTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    private const OTHER_CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7f';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_lists_submitted_feedback_for_customer(): void
    {
        CustomerFeedback::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'code' => 'FB001',
            'service_type' => 'Testing',
            'service_reference_no' => 'CR001/26',
            'specific_feedback' => 'Great service',
            'rating_overall' => 4,
            'feedback' => 'Great service',
            'received_from' => 'Portal',
            'registered_by' => 'Portal',
            'user_type' => 'customer',
            'date' => now(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        CustomerFeedback::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::OTHER_CUSTOMER_ID,
            'feedback' => 'Other',
            'received_from' => 'Portal',
            'registered_by' => 'Portal',
            'user_type' => 'customer',
            'date' => now(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->getJson('/api/v1/portal/'.self::CUSTOMER_ID.'/feedback')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.feedback.0.service_reference_no', 'CR001/26')
            ->assertJsonPath('data.feedback.0.specific_feedback', 'Great service')
            ->assertJsonCount(1, 'data.feedback');
    }
}
