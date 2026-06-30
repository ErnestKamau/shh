<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrfPortalSubmitCreatesCanonicalTrfiTest extends TestCase
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
    }

    public function test_portal_trf_submit_creates_trfi_with_canonical_form_data(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $trfModel = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Water',
            'code' => 'TRF-WATER',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'description' => 'Portal TRF',
            'naming_convention_prefix' => 'TRFW',
            'naming_convention_format' => 'TRFW-{YYYY}{MM}-{0000}',
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
        ]);
        $form->sampleTypes()->sync([$sampleType->id]);

        $headerSection = SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Customer',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $headerHolder = SubmissionFormElementHolder::query()->create([
            'submission_form_section_id' => $headerSection->id,
            'holder_type' => 'field',
            'max_elements' => 5,
            'sort_order' => 1,
        ]);

        $customerName = $this->createElement($headerHolder, 'customer_name', 1);
        $this->createElement($headerHolder, 'customer_tel_fax', 2);
        $this->createElement($headerHolder, 'customer_mobile', 3);
        $this->createElement($headerHolder, 'sampling_date', 4);

        $rowsSection = SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Samples',
            'section_type' => 'rows_section',
            'sort_order' => 2,
        ]);
        $rowsHolder = SubmissionFormElementHolder::query()->create([
            'submission_form_section_id' => $rowsSection->id,
            'holder_type' => 'field',
            'max_elements' => 3,
            'sort_order' => 1,
        ]);
        $sampleDescription = $this->createElement($rowsHolder, 'sample_description', 1);
        $this->createElement($rowsHolder, 'sample_type_id', 2);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'crm_customer_id' => self::CUSTOMER_ID,
            'status' => 'draft',
        ]);

        $response = $this->portalRequest('PUT', '/api/v1/portal/submissions/instances/'.$instance->id, [
            'action' => 'submit',
            'fields' => [
                'customer_name' => 'Portal Customer',
                'customer_tel_fax' => '111-2222',
                'customer_mobile' => '333-4444',
                'sampling_date' => '2026-06-16',
                'sample_description' => ['Sample A'],
                'sample_type_id' => [$sampleType->id],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $trfi = TestRequestFormInstance::query()
            ->where('submission_form_instance_id', $instance->id)
            ->first();

        $this->assertNotNull($trfi);
        $this->assertSame($trfModel->id, $trfi->test_request_form_id);

        $formData = $trfi->form_data;
        $this->assertSame('Portal Customer', $formData['customer_name']);
        $this->assertSame('111-2222', $formData['customer_phone']);
        $this->assertSame('333-4444', $formData['mobile_number']);
        $this->assertSame('2026-06-16', $formData['sampling_date']);
        $this->assertSame('Sample A', $formData['sample_rows'][0]['sample_description']);
        $this->assertSame($sampleType->id, $formData['sample_rows'][0]['sample_type_id']);

        $this->assertDatabaseHas('submission_form_instance_values', [
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $customerName->id,
            'value' => 'Portal Customer',
        ]);

        $this->assertNotNull($trfi->form_number);
        $this->assertSame(self::CUSTOMER_ID, $trfi->crm_customer_id);
        $this->assertSame(TestRequestFormInstance::CHANNEL_PORTAL, $trfi->source_channel);

        $this->assertDatabaseHas('sample_submission_requests', [
            'submission_form_instance_id' => $instance->id,
            'test_request_form_instance_id' => $trfi->id,
            'crm_customer_id' => self::CUSTOMER_ID,
        ]);
    }

    private function createElement(SubmissionFormElementHolder $holder, string $name, int $sortOrder): SubmissionFormElement
    {
        return SubmissionFormElement::query()->create([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => $name,
            'name' => $name,
            'is_required' => false,
            'is_readonly' => false,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function portalRequest(string $method, string $uri, array $payload = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->json($method, $uri, $payload);
    }
}
