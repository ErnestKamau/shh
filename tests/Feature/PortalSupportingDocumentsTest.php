<?php

namespace Tests\Feature;

use App\Models\SupportingDocumentElement;
use App\Models\SupportingDocumentSection;
use App\Models\SupportingDocumentTemplate;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PortalSupportingDocumentsTest extends TestCase
{
    public function test_portal_can_list_templates_and_fetch_structure(): void
    {
        if (! Schema::hasTable('supporting_document_templates')) {
            $this->markTestSkipped('Supporting document tables not migrated in this environment.');
        }

        $user = User::create([
            'name' => 'Portal User',
            'email' => 'portal.user@example.test',
            'password' => null,
            'company_id' => 1,
            'is_client' => 1,
            'client_id' => '123',
        ]);

        Sanctum::actingAs($user);

        $template = SupportingDocumentTemplate::create([
            'document_code' => 'DCEA 002',
            'title' => 'CERTIFICATE OF PHOTOGRAPH/MOVING PICTURE',
            'subtitle' => 'Made under section 51(5)',
            'description' => null,
            'version' => 2,
            'is_published' => true,
            'is_active' => true,
            'company_id' => 1,
            'created_by' => $user->id,
        ]);

        $section = SupportingDocumentSection::create([
            'supporting_document_template_id' => $template->id,
            'title' => 'Body',
            'sort_order' => 1,
        ]);

        SupportingDocumentElement::create([
            'supporting_document_section_id' => $section->id,
            'element_type' => 'static_text',
            'label' => null,
            'name' => null,
            'placeholder' => null,
            'help_text' => null,
            'is_required' => false,
            'is_readonly' => false,
            'default_value' => 'I, __________ do hereby certify ...',
            'validation_rules' => null,
            'options' => null,
            'conditional_logic' => null,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/portal/supporting-document-templates')
            ->assertOk()
            ->assertJsonFragment(['id' => $template->id]);

        $this->getJson('/api/portal/supporting-document-templates/' . $template->id)
            ->assertOk()
            ->assertJsonFragment(['document_code' => 'DCEA 002'])
            ->assertJsonStructure([
                'data' => [
                    'sections' => [
                        '*' => [
                            'elements' => [
                                '*' => ['element_type', 'default_value'],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_portal_can_submit_supporting_document_for_test_request(): void
    {
        if (! Schema::hasTable('supporting_document_instances') || ! Schema::hasTable('sample_headers')) {
            $this->markTestSkipped('Required tables not migrated in this environment.');
        }

        $user = User::create([
            'name' => 'Portal User 2',
            'email' => 'portal.user2@example.test',
            'password' => null,
            'company_id' => 1,
            'is_client' => 1,
            'client_id' => '555',
        ]);

        Sanctum::actingAs($user);

        $batch = SampleHeader::create([
            'batch_code' => 'BA0001',
            'receipt_date' => null,
            'date_collected' => '2026-01-01',
            'crm_customer_id' => 555,
            'crm_unit_name' => 'Unit',
            'sample_type_id' => 1,
            'reference_number' => null,
            'status' => 'Samples En-Route',
            'is_routine' => 0,
            'routine_frequency' => 0,
            'date_expected' => '2026-01-02',
        ]);

        $template = SupportingDocumentTemplate::create([
            'document_code' => 'DCEA 002',
            'title' => 'CERTIFICATE',
            'subtitle' => null,
            'description' => null,
            'version' => 3,
            'is_published' => true,
            'is_active' => true,
            'company_id' => 1,
            'created_by' => $user->id,
        ]);

        $section = SupportingDocumentSection::create([
            'supporting_document_template_id' => $template->id,
            'title' => 'Fields',
            'sort_order' => 1,
        ]);

        $field = SupportingDocumentElement::create([
            'supporting_document_section_id' => $section->id,
            'element_type' => 'text',
            'label' => 'Recording officer',
            'name' => 'recording_officer',
            'placeholder' => null,
            'help_text' => null,
            'is_required' => true,
            'is_readonly' => false,
            'default_value' => null,
            'validation_rules' => null,
            'options' => null,
            'conditional_logic' => null,
            'sort_order' => 1,
        ]);

        $this->postJson('/api/portal/test-requests/' . $batch->id . '/supporting-documents/submit', [
            'template_id' => $template->id,
            'values' => [
                $field->id => 'Officer Name',
            ],
        ])->assertCreated()
            ->assertJsonFragment([
                'supporting_document_template_id' => $template->id,
                'template_version' => 3,
                'sample_header_id' => $batch->id,
                'status' => 'submitted',
            ]);
    }
}

