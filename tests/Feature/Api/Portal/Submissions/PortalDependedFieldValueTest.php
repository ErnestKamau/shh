<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\CRM\CRMCustomer;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalDependedFieldValueTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_resolves_depended_field_with_explicit_depends_on_value(): void
    {
        CRMCustomer::query()->create([
            'id' => self::CUSTOMER_ID,
            'name' => 'Acme Laboratory',
            'code' => 'ACME',
            'active' => 1,
        ]);

        [$form, $dependedElement] = $this->createFormWithDependedField();
        $instance = $this->createInstance($form);

        $this->portalGet(
            '/api/v1/portal/submissions/instances/'.$instance->id.'/depended-field-value'
            .'?element_name='.$dependedElement->name
            .'&depends_on_value='.self::CUSTOMER_ID
        )
            ->assertOk()
            ->assertJsonPath('value', 'Acme Laboratory');
    }

    public function test_infers_parent_value_from_draft_instance(): void
    {
        CRMCustomer::query()->create([
            'id' => self::CUSTOMER_ID,
            'name' => 'Inferred Customer',
            'code' => 'INF001',
            'active' => 1,
        ]);

        [$form, $dependedElement, $parentElement] = $this->createFormWithDependedField(includeParent: true);
        $instance = $this->createInstance($form);

        SubmissionFormInstanceValue::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $parentElement->id,
            'value' => self::CUSTOMER_ID,
        ]);

        $this->portalGet(
            '/api/v1/portal/submissions/instances/'.$instance->id.'/depended-field-value'
            .'?element_name='.$dependedElement->name
        )
            ->assertOk()
            ->assertJsonPath('value', 'Inferred Customer');
    }

    /**
     * @return array{0: SubmissionForm, 1: SubmissionFormElement, 2?: SubmissionFormElement}
     */
    private function createFormWithDependedField(bool $includeParent = false): array
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Depended Field Form',
            'document_code' => 'DEP/001',
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
        ]);

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Section',
            'section_type' => 'regular',
            'sort_order' => 0,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        $parentElement = null;
        if ($includeParent) {
            $parentElement = SubmissionFormElement::query()->create([
                'id' => (string) Str::uuid7(),
                'submission_form_element_holder_id' => $holder->id,
                'element_type' => 'client_select',
                'label' => 'Customer',
                'name' => 'customer_id',
                'sort_order' => 0,
            ]);
        }

        $dependedElement = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'depended_field',
            'label' => 'Customer name',
            'name' => 'customer_name',
            'depends_on_field' => $includeParent ? 'customer_id' : null,
            'source_table' => 'crm_customers',
            'source_field' => 'name',
            'sort_order' => 1,
        ]);

        return $includeParent
            ? [$form, $dependedElement, $parentElement]
            : [$form, $dependedElement];
    }

    private function createInstance(SubmissionForm $form): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Draft',
            'status' => 'draft',
            'crm_customer_id' => self::CUSTOMER_ID,
            'priority' => 'normal',
        ]);
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
