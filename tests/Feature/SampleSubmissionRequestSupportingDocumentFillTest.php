<?php

namespace Tests\Feature;

use App\Livewire\Sampleworkflow\SubmissionSupportingDocumentFill;
use App\Models\SampleSubmissionRequest;
use App\Models\SupportingDocumentElement;
use App\Models\SupportingDocumentInstance;
use App\Models\SupportingDocumentSection;
use App\Models\SupportingDocumentTemplate;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class SampleSubmissionRequestSupportingDocumentFillTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_can_submit_supporting_document_values_for_submission_request(): void
    {
        if (! Schema::hasTable('supporting_document_instances') || ! Schema::hasColumn('supporting_document_instances', 'sample_submission_request_id')) {
            $this->markTestSkipped('Supporting document instance migration not applied.');
        }

        $user = User::create([
            'name' => 'Lab User',
            'email' => 'lab.supporting.doc@example.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $submissionRequest = SampleSubmissionRequest::create([
            'crm_customer_id' => null,
            'status' => 'Submitted',
        ]);

        $template = SupportingDocumentTemplate::create([
            'document_code' => 'LAB-SDOC-1',
            'title' => 'Chain of custody',
            'subtitle' => null,
            'description' => null,
            'version' => 1,
            'is_published' => true,
            'is_active' => true,
            'company_id' => null,
            'created_by' => $user->id,
        ]);

        $section = SupportingDocumentSection::create([
            'supporting_document_template_id' => $template->id,
            'title' => 'Details',
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

        $instance = SupportingDocumentInstance::create([
            'supporting_document_template_id' => $template->id,
            'template_version' => 1,
            'sample_submission_request_id' => $submissionRequest->id,
            'sample_header_id' => 0,
            'status' => 'draft',
            'submitted_at' => null,
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(SubmissionSupportingDocumentFill::class, [
                'submissionRequestId' => $submissionRequest->id,
                'instanceId' => $instance->id,
            ])
            ->set('values.' . $field->id, 'Officer A')
            ->call('submitDocument')
            ->assertRedirect(route('sample-submission-requests.show', $submissionRequest));

        $instance->refresh();
        $this->assertSame('submitted', $instance->status);
        $this->assertNotNull($instance->submitted_at);
        $this->assertSame('Officer A', $instance->values()->where('supporting_document_element_id', $field->id)->value('value'));
    }
}
