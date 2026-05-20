<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_returns_latest_customer_request_form_with_schema(): void
    {
        $older = $this->createPortalForm([
            'name' => 'Older Customer Request',
            'is_customer_request_form' => true,
            'updated_at' => now()->subDay(),
        ]);

        $newer = $this->createPortalForm([
            'name' => 'Newer Customer Request',
            'is_customer_request_form' => true,
            'updated_at' => now(),
        ]);

        $response = $this->portalGet('/api/v1/portal/submissions/forms/customer-request');

        $response->assertOk()
            ->assertJsonPath('data.form.id', $newer->id)
            ->assertJsonPath('data.form.is_customer_request_form', true)
            ->assertJsonStructure([
                'data' => ['form', 'sections', 'submission_payload'],
                'meta' => ['crm_customer_id', 'resolved_at'],
            ]);

        $this->assertNotSame($older->id, $response->json('data.form.id'));
    }

    public function test_returns_not_found_when_no_customer_request_form_exists(): void
    {
        $this->createPortalForm([
            'name' => 'Portal Only',
            'is_customer_request_form' => false,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/customer-request')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'customer_request_form_not_found');
    }

    public function test_excludes_unpublished_customer_request_form(): void
    {
        $this->createPortalForm([
            'name' => 'Draft Customer Request',
            'is_customer_request_form' => true,
            'is_published' => false,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/customer-request')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'customer_request_form_not_found');
    }

    public function test_excludes_inactive_customer_request_form(): void
    {
        $this->createPortalForm([
            'name' => 'Inactive Customer Request',
            'is_customer_request_form' => true,
            'is_active' => false,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/customer-request')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'customer_request_form_not_found');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPortalForm(array $overrides = []): SubmissionForm
    {
        return SubmissionForm::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Form '.Str::random(8),
            'document_code' => 'TEST/'.Str::upper(Str::random(4)),
            'description' => 'Test form',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'is_customer_request_form' => false,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ], $overrides));
    }

    private function portalGet(string $uri)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
        ])->getJson($uri);
    }
}
