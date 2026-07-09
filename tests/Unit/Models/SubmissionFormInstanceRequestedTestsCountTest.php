<?php

namespace Tests\Unit\Models;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionFormInstanceRequestedTestsCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_form_relation_uses_uuid_safe_existence_query(): void
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Commercial TRF',
            'document_code' => 'TRF-REL',
            'description' => 'Test',
            'naming_convention_prefix' => 'TRF',
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

        SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Relation test instance',
            'form_number' => 'TRF-REL-001',
            'status' => 'in_review',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->assertSame(1, SubmissionFormInstance::query()
            ->whereHas('submissionForm', fn ($query) => $query->where('form_type', 'template'))
            ->count());
    }

    public function test_sidebar_receiving_request_count_can_count_template_instances_without_batches(): void
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Receiving TRF',
            'document_code' => 'TRF-COUNT',
            'description' => 'Test',
            'naming_convention_prefix' => 'TRF',
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

        SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Receiving count instance',
            'form_number' => 'TRF-COUNT-001',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->assertSame(1, WorkflowBoard::sidebarReceivingRequestCount());
    }

    public function test_requested_tests_count_uses_enquiry_sample_configuration_when_form_values_empty(): void
    {
        if (! Schema::hasColumn('sample_submission_requests', 'enquiry_sample_configuration')) {
            $this->markTestSkipped('enquiry_sample_configuration column not present.');
        }

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Commercial TRF',
            'document_code' => 'TRF-TEST',
            'description' => 'Test',
            'naming_convention_prefix' => 'TRF',
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
            'title' => 'Process enquiry instance',
            'form_number' => 'TRF-001',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'source_channel' => 'scheduled',
            'number_of_samples' => 2,
            'enquiry_sample_configuration' => [
                [
                    'id' => (string) Str::uuid7(),
                    'sample_type_id' => '1',
                    'analysis_type_id' => '10',
                    'number_of_samples' => 2,
                    'parameter_keys' => ['101', '102', '103'],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'sample_type_id' => '2',
                    'analysis_type_id' => '20',
                    'number_of_samples' => 1,
                    'parameter_keys' => ['201'],
                ],
            ],
        ]);

        $this->assertSame(4, $instance->fresh()->requested_tests_count);
    }
}
