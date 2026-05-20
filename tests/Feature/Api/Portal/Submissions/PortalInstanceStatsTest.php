<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalInstanceStatsTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_returns_instance_counts_by_status_and_form_type(): void
    {
        $template = $this->createPortalForm(['form_type' => 'template']);
        $attachment = $this->createPortalForm(['form_type' => 'attachment', 'name' => 'Attach']);

        $this->createInstance($template, ['status' => 'draft']);
        $this->createInstance($template, ['status' => 'submitted', 'submitted_at' => now()]);
        $this->createInstance($attachment, ['status' => 'submitted', 'submitted_at' => now()]);

        $this->portalGet('/api/v1/portal/submissions/instances/stats')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('by_status.draft', 1)
            ->assertJsonPath('by_status.submitted', 2)
            ->assertJsonPath('by_form_type.template', 2)
            ->assertJsonPath('by_form_type.attachment', 1);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPortalForm(array $overrides = []): SubmissionForm
    {
        return SubmissionForm::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Form '.Str::random(6),
            'document_code' => 'TEST/'.Str::upper(Str::random(4)),
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => [],
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
            'title' => 'Instance',
            'status' => 'draft',
            'crm_customer_id' => self::CUSTOMER_ID,
            'priority' => 'normal',
        ], $overrides));
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
