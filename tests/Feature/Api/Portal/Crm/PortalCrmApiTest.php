<?php

namespace Tests\Feature\Api\Portal\Crm;

use App\Invoice;
use App\Models\CRM\Complaint;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\EvaluationMetric;
use App\Models\CRM\FeedbackRequest;
use App\Models\CRM\CRMCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalCrmApiTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    private const OTHER_CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7f';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.portal_gateway.api_key' => self::GATEWAY_KEY,
            'portal_crm.cache.store' => 'array',
        ]);
    }

    public function test_creates_complaint_for_customer(): void
    {
        $this->portalRequest('POST', '/api/v1/portal/'.self::CUSTOMER_ID.'/complaints', [
            'description' => 'Delayed turnaround',
            'priority' => 'High',
            'type' => 'Service',
            'organization_name' => 'Acme Ltd',
            'contact_name' => 'Jane Client',
            'date' => '2026-05-18',
            'is_lab_related' => true,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.resolution_status', 'open');

        $this->assertDatabaseHas('complaints', [
            'client_id' => self::CUSTOMER_ID,
            'description' => 'Delayed turnaround',
        ]);
    }

    public function test_lists_complaints_for_customer(): void
    {
        Complaint::query()->create([
            'id' => (string) Str::uuid7(),
            'complaint_id' => 'COMP/1',
            'description' => 'Test complaint',
            'priority' => 'Low',
            'type' => 'Service',
            'client_id' => self::CUSTOMER_ID,
            'complaint_workflow' => 1,
            'date' => now(),
            'is_closed' => false,
        ]);

        $this->portalRequest('GET', '/api/v1/portal/'.self::CUSTOMER_ID.'/complaints')
            ->assertOk()
            ->assertJsonPath('data.complaints.0.title', 'Test complaint');
    }

    public function test_rejects_customer_scope_mismatch(): void
    {
        $this->portalRequest('GET', '/api/v1/portal/'.self::OTHER_CUSTOMER_ID.'/complaints')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_returns_feedback_metrics(): void
    {
        EvaluationMetric::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Communication',
            'max_rating' => 5,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->portalRequest('GET', '/api/v1/portal/feedback/metrics')
            ->assertOk()
            ->assertJsonPath('data.metrics.0.name', 'Communication');
    }

    public function test_submits_spontaneous_feedback(): void
    {
        $customer = $this->createCustomer();
        $contact = $this->createContact($customer);
        $metric = EvaluationMetric::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Quality',
            'max_rating' => 5,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->portalRequest('POST', '/api/v1/portal/'.self::CUSTOMER_ID.'/feedback', [
            'contact_id' => $contact->id,
            'service_type' => 'Testing',
            'service_reference_no' => 'CR001/26',
            'ratings' => [
                ['evaluation_metric_id' => $metric->id, 'rating' => 4],
            ],
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'code', 'rating_overall']]);
    }

    public function test_completes_token_feedback_request(): void
    {
        $customer = $this->createCustomer();
        $contact = $this->createContact($customer);
        $metric = EvaluationMetric::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Turnaround',
            'max_rating' => 5,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $feedback = CustomerFeedback::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'contact_id' => $contact->id,
            'status' => CustomerFeedback::STATUS_PENDING,
            'is_submitted' => false,
        ]);

        $token = (string) Str::uuid7();
        FeedbackRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'contact_id' => $contact->id,
            'feedback_id' => $feedback->id,
            'token' => $token,
            'status' => FeedbackRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
        ]);

        $this->portalRequest('POST', '/api/v1/portal/'.self::CUSTOMER_ID.'/feedback/complete', [
            'token' => $token,
            'contact_id' => $contact->id,
            'service_type' => 'Testing',
            'service_reference_no' => 'CR002/26',
            'ratings' => [
                ['evaluation_metric_id' => $metric->id, 'rating' => 5],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.id', $feedback->id);
    }

    public function test_lists_invoices_with_payment_summary(): void
    {
        $invoice = Invoice::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'invoice_number' => 'INV-TEST-001',
            'total' => 1000,
            'total_tax' => 0,
            'pricelist_id' => (string) Str::uuid7(),
        ]);

        $this->portalRequest('GET', '/api/v1/portal/'.self::CUSTOMER_ID.'/invoices')
            ->assertOk()
            ->assertJsonPath('data.invoices.0.id', $invoice->id)
            ->assertJsonPath('data.invoices.0.payment_status', 'unpaid');
    }

    public function test_shows_invoice_detail(): void
    {
        $invoice = Invoice::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'invoice_number' => 'INV-TEST-002',
            'total' => 500,
            'total_tax' => 50,
            'pricelist_id' => (string) Str::uuid7(),
        ]);

        $this->portalRequest('GET', '/api/v1/portal/'.self::CUSTOMER_ID.'/invoices/'.$invoice->id)
            ->assertOk()
            ->assertJsonPath('data.invoice_number', 'INV-TEST-002')
            ->assertJsonStructure(['data' => ['line_items', 'payments']]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function portalRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->json($method, $uri, $data);
    }

    private function createCustomer(): CRMCustomer
    {
        return CRMCustomer::query()->create([
            'id' => self::CUSTOMER_ID,
            'code' => 'ACME',
            'name' => 'Acme Ltd',
            'active' => 1,
        ]);
    }

    private function createContact(CRMCustomer $customer): CustomerContact
    {
        return CustomerContact::query()->create([
            'id' => (string) Str::uuid7(),
            'crm_customer_id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Client',
        ]);
    }
}
