<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionFormInstanceListTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    private const OTHER_CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7f';

    private const PORTAL_ACCOUNT_ID = '019e310c-103b-7289-b904-8aa59f4c90b6';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_lists_instances_grouped_by_status_for_customer(): void
    {
        $form = $this->createPortalForm();

        $draft = $this->createInstance($form, ['status' => 'draft', 'title' => 'Draft one']);
        $submitted = $this->createInstance($form, [
            'status' => 'submitted',
            'title' => 'Submitted one',
            'form_number' => 'CR001/26',
            'sequence_number' => 1,
            'submitted_at' => now(),
        ]);

        $this->createInstance($form, [
            'status' => 'draft',
            'crm_customer_id' => self::OTHER_CUSTOMER_ID,
            'title' => 'Other customer',
        ]);

        $response = $this->portalRequest('GET', '/api/v1/portal/submissions/instances');

        $response->assertOk()
            ->assertJsonPath('meta.crm_customer_id', self::CUSTOMER_ID)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.counts.draft', 1)
            ->assertJsonPath('meta.counts.submitted', 1)
            ->assertJsonPath('data.draft.0.id', $draft->id)
            ->assertJsonPath('data.draft.0.can_delete', true)
            ->assertJsonPath('data.submitted.0.id', $submitted->id)
            ->assertJsonPath('data.submitted.0.can_delete', true)
            ->assertJsonStructure([
                'data' => [
                    'draft',
                    'submitted',
                    'in_review',
                    'approved',
                    'rejected',
                    'cancelled',
                ],
                'meta' => ['total', 'counts', 'status_groups', 'deletable_statuses'],
            ]);
    }

    public function test_filters_instances_by_status_query(): void
    {
        $form = $this->createPortalForm();
        $this->createInstance($form, ['status' => 'draft']);
        $this->createInstance($form, ['status' => 'submitted', 'submitted_at' => now()]);

        $this->portalRequest('GET', '/api/v1/portal/submissions/instances?status=draft')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.draft.0.status', 'draft')
            ->assertJsonPath('data.submitted', []);
    }

    public function test_lists_all_customer_instances_when_portal_account_header_differs(): void
    {
        $form = $this->createPortalForm();

        $otherAccountInstance = $this->createInstance($form, [
            'portal_account_id' => '019e3592-0841-717c-b3af-6c5f5b64478d',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $nullAccountInstance = $this->createInstance($form, [
            'portal_account_id' => null,
            'status' => 'draft',
        ]);

        $this->portalRequest('GET', '/api/v1/portal/submissions/instances', [
            'X-Portal-Account-Id' => '019e36c3-d430-7185-a6bd-a43447436bae',
        ])
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.submitted.0.id', $otherAccountInstance->id)
            ->assertJsonPath('data.draft.0.id', $nullAccountInstance->id);
    }

    public function test_filters_by_portal_account_id_query_param(): void
    {
        $form = $this->createPortalForm();

        $this->createInstance($form, [
            'portal_account_id' => self::PORTAL_ACCOUNT_ID,
            'status' => 'draft',
        ]);

        $this->createInstance($form, [
            'portal_account_id' => null,
            'status' => 'draft',
        ]);

        $this->portalRequest('GET', '/api/v1/portal/submissions/instances?portal_account_id='.self::PORTAL_ACCOUNT_ID)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.draft.0.portal_account_id', self::PORTAL_ACCOUNT_ID);
    }

    public function test_requires_crm_customer_id_header(): void
    {
        $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/portal/submissions/instances')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_deletes_draft_instance(): void
    {
        $form = $this->createPortalForm();
        $instance = $this->createInstance($form, ['status' => 'draft']);

        $this->portalRequest('DELETE', '/api/v1/portal/submissions/instances/'.$instance->id)
            ->assertOk()
            ->assertJsonPath('message', 'Submission deleted successfully.');

        $this->assertDatabaseMissing('submission_form_instances', ['id' => $instance->id]);
    }

    public function test_deletes_submitted_instance(): void
    {
        $form = $this->createPortalForm();
        $instance = $this->createInstance($form, [
            'status' => 'submitted',
            'submitted_at' => now(),
            'form_number' => 'CR002/26',
            'sequence_number' => 2,
        ]);

        $this->portalRequest('DELETE', '/api/v1/portal/submissions/instances/'.$instance->id)
            ->assertOk();

        $this->assertDatabaseMissing('submission_form_instances', ['id' => $instance->id]);
    }

    public function test_rejects_delete_for_in_review_instance(): void
    {
        $form = $this->createPortalForm();
        $instance = $this->createInstance($form, ['status' => 'in_review']);

        $this->portalRequest('DELETE', '/api/v1/portal/submissions/instances/'.$instance->id)
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'instance_not_deletable');

        $this->assertDatabaseHas('submission_form_instances', ['id' => $instance->id]);
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createInstance(SubmissionForm $form, array $overrides = []): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Instance '.Str::random(6),
            'status' => 'draft',
            'crm_customer_id' => self::CUSTOMER_ID,
            'portal_account_id' => self::PORTAL_ACCOUNT_ID,
            'priority' => 'normal',
        ], $overrides));
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function portalRequest(string $method, string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
            'X-Portal-Account-Id' => self::PORTAL_ACCOUNT_ID,
        ], $headers))->json($method, $uri);
    }
}
