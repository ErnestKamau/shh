<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestRequestFormPortalPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    private const GATEWAY_KEY = 'test-portal-gateway-key';
    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
        Storage::fake('public');
    }

    public function test_portal_can_download_test_request_form_pdf(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'SMP-WTR',
            'active' => true,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Portal Form',
            'document_code' => 'TEST/PORTAL',
            'description' => 'Portal form',
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
            'lims_destination_pages' => ['sample-workflow'],
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Portal instance',
            'form_number' => '2500001',
            'status' => 'submitted',
            'crm_customer_id' => self::CUSTOMER_ID,
            'priority' => 'normal',
        ]);

        $trf = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water Test Request Form',
            'code' => 'TRF-WTR',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $trf->id,
            'submission_form_instance_id' => $instance->id,
            'form_data' => [
                'customer_name' => 'ABC COMPANY',
                'sample_rows' => [[
                    'sample_no' => '1',
                    'sample_description' => 'Tap Water',
                ]],
            ],
            'status' => 'submitted',
        ]);

        app(TestRequestFormPdfService::class)->generateAndStore($trfi);

        $response = $this->portalRequest(
            'GET',
            '/api/v1/portal/submissions/instances/' . $instance->id . '/test-request-form/pdf'
        );

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    private function portalRequest(string $method, string $uri, array $payload = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . self::GATEWAY_KEY,
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
            'Accept' => 'application/json',
        ])->json($method, $uri, $payload);
    }
}
