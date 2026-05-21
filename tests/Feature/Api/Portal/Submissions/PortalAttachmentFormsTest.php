<?php

namespace Tests\Feature\Api\Portal\Submissions;

use App\Models\SubmissionForm;
use App\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalAttachmentFormsTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.portal_gateway.api_key' => self::GATEWAY_KEY]);
    }

    public function test_lists_linked_attachment_forms_for_template(): void
    {
        $template = $this->createPortalForm(['name' => 'Main Request']);
        $attachment = $this->createPortalForm([
            'name' => 'Supporting Docs',
            'form_type' => 'attachment',
        ]);

        DB::table('submission_form_template_links')->insert([
            'template_form_id' => $template->id,
            'attachment_form_id' => $attachment->id,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/'.$template->id.'/attachment-forms')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $attachment->id)
            ->assertJsonPath('data.0.name', 'Supporting Docs');
    }

    public function test_filters_attachment_forms_by_sample_type(): void
    {
        $sampleTypeA = SampleType::query()->create(['name' => 'Water']);
        $sampleTypeB = SampleType::query()->create(['name' => 'Soil']);

        $template = $this->createPortalForm();
        $matching = $this->createPortalForm([
            'name' => 'Water attachment',
            'form_type' => 'attachment',
        ]);
        $other = $this->createPortalForm([
            'name' => 'Soil attachment',
            'form_type' => 'attachment',
        ]);

        DB::table('submission_form_template_links')->insert([
            ['template_form_id' => $template->id, 'attachment_form_id' => $matching->id],
            ['template_form_id' => $template->id, 'attachment_form_id' => $other->id],
        ]);

        DB::table('submission_form_sample_types')->insert([
            'submission_form_id' => $matching->id,
            'sample_type_id' => $sampleTypeA->id,
        ]);
        DB::table('submission_form_sample_types')->insert([
            'submission_form_id' => $other->id,
            'sample_type_id' => $sampleTypeB->id,
        ]);

        $this->portalGet('/api/v1/portal/submissions/forms/'.$template->id.'/attachment-forms?sample_type_id='.$sampleTypeA->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
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

    private function portalGet(string $uri): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->getJson($uri);
    }
}
