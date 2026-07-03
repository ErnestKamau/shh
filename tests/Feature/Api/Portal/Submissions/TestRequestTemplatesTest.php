<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestRequestTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_lists_published_trf_portal_templates(): void
    {
        $water = $this->createPortalForm([
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
        ]);

        $food = $this->createPortalForm([
            'name' => 'Test Request Form - Food',
            'document_code' => 'TRF-FOOD-019',
        ]);

        $this->createPortalForm([
            'name' => 'Generic Portal Form',
            'document_code' => 'GEN-001',
        ]);

        $response = $this->portalGet('/api/v1/portal/submissions/forms/test-request-templates');

        $response->assertOk()
            ->assertJsonPath('meta.count', 2)
            ->assertJsonFragment(['document_code' => 'TRF-WATER-020'])
            ->assertJsonFragment(['document_code' => 'TRF-FOOD-019']);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($water->id, $ids);
        $this->assertContains($food->id, $ids);
    }

    public function test_resolve_test_request_by_sample_type_id(): void
    {
        $foodType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $waterType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $foodForm = $this->createPortalForm([
            'name' => 'Test Request Form - Food',
            'document_code' => 'TRF-FOOD-019',
        ]);
        $foodForm->sampleTypes()->sync([$foodType->id]);

        $this->createPortalForm([
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
        ])->sampleTypes()->sync([$waterType->id]);

        $this->portalGet('/api/v1/portal/submissions/forms/resolve-test-request?sample_type_id='.$foodType->id)
            ->assertOk()
            ->assertJsonPath('data.document_code', 'TRF-FOOD-019')
            ->assertJsonPath('meta.sample_type_id', $foodType->id);
    }

    public function test_excludes_unpublished_trf_templates(): void
    {
        $this->createPortalForm([
            'name' => 'Draft Water TRF',
            'document_code' => 'TRF-WATER-DRAFT',
            'is_published' => false,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/test-request-templates')
            ->assertOk()
            ->assertJsonPath('meta.count', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPortalForm(array $overrides = []): SubmissionForm
    {
        return SubmissionForm::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Form '.Str::random(8),
            'document_code' => 'TRF/'.Str::upper(Str::random(4)),
            'description' => 'Test form',
            'naming_convention_prefix' => 'TRF',
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
            'placement_slot' => ['customer_portal', 'admin_portal', 'samples_receiving'],
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
