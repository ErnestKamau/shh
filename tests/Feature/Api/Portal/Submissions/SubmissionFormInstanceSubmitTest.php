<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionFormInstanceSubmitTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';
    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';
    private const PORTAL_ACCOUNT_ID = '019e310c-103b-7289-b904-8aa59f4c90b6';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_submits_draft_instance_when_fields_are_provided_as_name_value_objects(): void
    {
        $form = $this->createPortalForm();
        $section = $this->createSection($form);
        $holder = $this->createHolder($section);

        $this->createElement($holder, [
            'element_type' => 'text',
            'name' => 'sample_id',
            'label' => 'Sample ID',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $instance = $this->createInstance($form);

        $response = $this->portalRequest('PUT', '/api/v1/portal/submissions/instances/'.$instance->id, [
            'action' => 'submit',
            'fields' => [
                ['name' => 'sample_id', 'value' => 'SAMPLE-123'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('submission_form_instance_values', [
            'submission_form_instance_id' => $instance->id,
            'value' => 'SAMPLE-123',
        ]);
    }

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

    private function createSection(SubmissionForm $form): SubmissionFormSection
    {
        return SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Section 1',
            'description' => 'Section description',
            'section_type' => 'regular',
            'section_alignment' => 'left',
            'sort_order' => 1,
        ]);
    }

    private function createHolder(SubmissionFormSection $section): SubmissionFormElementHolder
    {
        return SubmissionFormElementHolder::query()->create([
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
    }

    private function createElement(SubmissionFormElementHolder $holder, array $attributes): SubmissionFormElement
    {
        return SubmissionFormElement::query()->create(array_merge([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Field',
            'name' => 'field1',
            'placeholder' => null,
            'help_text' => null,
            'is_required' => false,
            'is_readonly' => false,
            'default_value' => null,
            'validation_rules' => [],
            'options' => null,
            'calculation_formula' => null,
            'conditional_logic' => null,
            'sort_order' => 1,
            'mapping_table' => null,
            'mapping_field' => null,
            'is_mapped' => false,
            'depends_on_type' => null,
            'depends_on_field' => null,
            'source_table' => null,
            'source_field' => null,
        ], $attributes));
    }

    private function createInstance(SubmissionForm $form): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Instance '.Str::random(6),
            'status' => 'draft',
            'crm_customer_id' => self::CUSTOMER_ID,
            'portal_account_id' => self::PORTAL_ACCOUNT_ID,
            'priority' => 'normal',
        ]);
    }

    private function portalRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
            'X-Portal-Account-Id' => self::PORTAL_ACCOUNT_ID,
        ], []))->json($method, $uri, $data);
    }
}
