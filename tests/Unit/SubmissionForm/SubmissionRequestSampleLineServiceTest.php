<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionRequestSampleLineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_rows_section_into_sample_lines(): void
    {
        $form = $this->createTemplateForm();
        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Samples',
            'section_type' => 'rows_section',
            'sort_order' => 0,
        ]);
        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'rows',
            'sort_order' => 0,
        ]);

        $sampleTypeEl = $this->createElement($holder, 'sample_type_select', 'sample_type', 0);
        $analysisTypeEl = $this->createElement($holder, 'analysis_type_select', 'analysis_type', 1);
        $analysisElementEl = $this->createElement($holder, 'analysis_elements_select', 'analysis_element', 2);
        $customerIdEl = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Customer sample ID',
            'name' => 'customer_sample_id',
            'sort_order' => 3,
        ]);

        $sampleTypeId = (string) Str::uuid7();
        $analysisTypeId = (string) Str::uuid7();
        $elementId = (string) Str::uuid7();

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Row instance',
            'form_number' => 'CR100',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $sampleTypeEl, $sampleTypeId, 0);
        $this->createValue($instance, $analysisTypeEl, $analysisTypeId, 0);
        $this->createValue($instance, $analysisElementEl, $elementId, 0);
        $this->createValue($instance, $customerIdEl, 'CUST-42', 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame(0, $lines[0]['row_index']);
        $this->assertSame('CUST-42', $lines[0]['customer_sample_id']);
        $this->assertSame($sampleTypeId, $lines[0]['sample_type_id']);
        $this->assertSame($analysisTypeId, $lines[0]['analysis_type_id']);
        $this->assertSame($elementId, $lines[0]['analysis_element_id']);
    }

    private function createTemplateForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Sample Line Form',
            'document_code' => 'SL/TEST',
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);
    }

    private function createElement(
        SubmissionFormElementHolder $holder,
        string $type,
        string $name,
        int $sortOrder
    ): SubmissionFormElement {
        return SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => $type,
            'label' => $name,
            'name' => $name,
            'sort_order' => $sortOrder,
        ]);
    }

    private function createValue(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        string $value,
        int $arrayIndex
    ): void {
        SubmissionFormInstanceValue::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $element->id,
            'value' => $value,
            'array_index' => $arrayIndex,
        ]);
    }
}
