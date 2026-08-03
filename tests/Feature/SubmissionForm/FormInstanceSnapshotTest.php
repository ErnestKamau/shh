<?php

namespace Tests\Feature\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormInstanceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_instance_creation_captures_an_immutable_form_structure_snapshot(): void
    {
        $form = SubmissionForm::query()->create([
            'name' => 'TRF Snapshot',
            'document_code' => 'TRF-SNAPSHOT',
            'naming_convention_prefix' => 'TRF',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'version' => '2.3',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);
        $section = SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Original section',
            'section_type' => 'regular',
            'sort_order' => 1,
            'is_hidden' => false,
        ]);
        $holder = SubmissionFormElementHolder::query()->create([
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 1,
            'max_elements' => 10,
        ]);
        $element = SubmissionFormElement::query()->create([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Original label',
            'name' => 'original_field',
            'is_required' => true,
            'is_hidden' => false,
            'conditional_logic' => [
                ['field' => 'toggle', 'operator' => 'equals', 'value' => '1'],
            ],
            'sort_order' => 1,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Snapshot instance',
            'status' => 'draft',
            'priority' => 'normal',
        ])->fresh();

        $this->assertSame('2.3', $instance->template_version);
        $this->assertNotNull($instance->structure_snapshot_at);
        $this->assertSame(
            'Original section',
            $instance->structure_snapshot['sections'][0]['title']
        );
        $this->assertSame(
            $element->conditional_logic,
            $instance->structure_snapshot['sections'][0]['holders'][0]['elements'][0]['conditional_logic']
        );

        $section->update(['title' => 'Changed section']);
        $form->update(['version' => '3.0']);
        $instance->refresh();

        $this->assertSame('2.3', $instance->template_version);
        $this->assertSame(
            'Original section',
            $instance->structure_snapshot['sections'][0]['title']
        );
    }
}
