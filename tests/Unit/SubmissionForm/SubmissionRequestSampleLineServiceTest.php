<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\SamplePoint;
use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\SampleType;
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

    public function test_deduplicates_identical_rows_from_duplicate_rows_sections(): void
    {
        $form = $this->createTemplateForm();
        $sampleTypeId = (string) Str::uuid7();
        $analysisTypeId = (string) Str::uuid7();
        $elementId = (string) Str::uuid7();

        $elementsBySection = [];

        foreach ([1, 2] as $ordinal) {
            $section = SubmissionFormSection::query()->create([
                'id' => (string) Str::uuid7(),
                'submission_form_id' => $form->id,
                'title' => 'Test & sample information',
                'section_type' => 'rows_section',
                'sort_order' => 3,
            ]);
            $holder = SubmissionFormElementHolder::query()->create([
                'id' => (string) Str::uuid7(),
                'submission_form_section_id' => $section->id,
                'holder_type' => 'rows',
                'sort_order' => 0,
            ]);

            $elementsBySection[] = [
                $this->createElement($holder, 'sample_type_select', 'sample_type', 0),
                $this->createElement($holder, 'analysis_type_select', 'analysis_type', 1),
                $this->createElement($holder, 'analysis_elements_select', 'analysis_element', 2),
            ];
        }

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Duplicate sections instance',
            'form_number' => 'CR-DUP',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        foreach ($elementsBySection as $elements) {
            $this->createValue($instance, $elements[0], $sampleTypeId, 0);
            $this->createValue($instance, $elements[1], $analysisTypeId, 0);
            $this->createValue($instance, $elements[2], $elementId, 0);
        }

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame($analysisTypeId, $lines[0]['analysis_type_id']);
        $this->assertSame($elementId, $lines[0]['analysis_element_id']);
    }

    public function test_food_test_category_maps_to_parameter_category_and_prefix(): void
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
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        $descriptionEl = $this->createElement($holder, 'textarea', 'sample_description', 0);
        $categoryEl = $this->createElement($holder, 'radio', 'test_category', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Food row instance',
            'form_number' => 'CR-FOOD',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $descriptionEl, 'Chicken', 0);
        $this->createValue($instance, $categoryEl, 'microbiology', 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame('microbiology', $lines[0]['parameter_category'] ?? null);
        $this->assertSame('M', $lines[0]['sample_code_prefix'] ?? null);
    }

    public function test_water_test_requirements_map_to_sample_code_prefix(): void
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
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        $descriptionEl = $this->createElement($holder, 'textarea', 'sample_description', 0);
        $requirementsEl = $this->createElement($holder, 'checkbox', 'test_requirements', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Water row instance',
            'form_number' => 'CR-WATER',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $descriptionEl, 'Tap water', 0);
        $this->createValue($instance, $requirementsEl, json_encode(['legionella' => true]), 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame('legionella', $lines[0]['parameter_category'] ?? null);
        $this->assertSame('L', $lines[0]['sample_code_prefix'] ?? null);
    }

    public function test_parses_multiple_comma_separated_parameters_into_element_ids(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food & Feed',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food Chemistry',
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $elementIds = [];
        foreach (['Moisture', 'Energy'] as $analyteName) {
            $analyte = Analyte::query()->create([
                'id' => (string) Str::uuid7(),
                'name' => $analyteName,
                'code' => strtoupper(substr($analyteName, 0, 3)),
                'active' => 1,
            ]);

            $elementIds[] = (string) AnalysisElements::query()->create([
                'id' => (string) Str::uuid7(),
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => $analyte->id,
                'active' => 1,
            ])->id;
        }

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

        $analysisTypeEl = $this->createElement($holder, 'analysis_type_select', 'analysis_type_id', 0);
        $parametersEl = $this->createElement($holder, 'analysis_elements_select', 'parameters', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Multi-parameter instance',
            'form_number' => 'CR-MULTI',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $analysisTypeEl, $analysisType->id, 0);
        $this->createValue($instance, $parametersEl, implode(',', $elementIds), 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame($elementIds[0], $lines[0]['analysis_element_id']);
        $this->assertSame($elementIds, $lines[0]['attributes']['analysis_element_ids'] ?? []);
        $this->assertStringContainsString('Moisture', (string) ($lines[0]['parameter_label'] ?? ''));
        $this->assertStringContainsString('Energy', (string) ($lines[0]['parameter_label'] ?? ''));
    }

    public function test_scalar_water_test_requirements_build_parameter_label(): void
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
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        $descriptionEl = $this->createElement($holder, 'textarea', 'sample_description', 0);
        $requirementsEl = $this->createElement($holder, 'radio', 'test_requirements', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Water scalar requirements',
            'form_number' => 'CR-WATER-SCALAR',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $descriptionEl, 'Tap water', 0);
        $this->createValue($instance, $requirementsEl, 'chemistry', 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame('Chemistry', $lines[0]['parameter_label'] ?? null);
    }

    public function test_food_cooked_uuid_keeps_header_sample_type_when_resolving_parameters(): void
    {
        $foodFeed = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food & Feed',
            'code' => 'FOOD FEED',
            'active' => 1,
        ]);

        $food = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $cooked = AnalysisType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Cooked',
            'sample_type_id' => $foodFeed->id,
            'active' => 1,
        ]);

        $foodAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food Analysis',
            'sample_type_id' => $food->id,
            'active' => 1,
        ]);

        $correctAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Moisture',
            'code' => 'MOI',
            'active' => 1,
        ]);

        $wrongAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Moisture',
            'code' => 'MOI2',
            'active' => 1,
        ]);

        $correctElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid7(),
            'analysis_type_id' => $cooked->id,
            'analyte_id' => $correctAnalyte->id,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => (string) Str::uuid7(),
            'analysis_type_id' => $foodAnalysis->id,
            'analyte_id' => $wrongAnalyte->id,
            'active' => 1,
        ]);

        $form = $this->createTemplateForm();
        $form->sampleTypes()->attach($foodFeed->id);

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test & sample information',
            'section_type' => 'rows_section',
            'sort_order' => 0,
        ]);
        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'rows',
            'sort_order' => 0,
        ]);

        $analysisTypeEl = $this->createElement($holder, 'analysis_type_select', 'analysis_type_id', 0);
        $parametersEl = $this->createElement($holder, 'analysis_elements_select', 'parameters', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Food cooked instance',
            'form_number' => 'CR-COOKED',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $analysisTypeEl, (string) $cooked->id, 0);
        $this->createValue($instance, $parametersEl, 'Moisture', 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame((string) $foodFeed->id, $lines[0]['sample_type_id']);
        $this->assertSame((string) $cooked->id, $lines[0]['analysis_type_id']);
        $this->assertSame((string) $correctElement->id, $lines[0]['analysis_element_id']);
        $this->assertSame('Cooked', $lines[0]['attributes']['food_sample_type'] ?? null);
    }

    public function test_resolves_sampling_point_uuid_to_sample_point_name(): void
    {
        $customer = CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Location Customer',
            'code' => 'LOC001',
        ]);
        $unit = CRMCompanyUnit::query()->create([
            'id' => (string) Str::uuid7(),
            'crm_customer_id' => $customer->id,
            'company_id' => (string) Str::uuid7(),
            'name' => 'Main Unit',
            'active' => 1,
        ]);
        $samplePoint = SamplePoint::query()->create([
            'id' => (string) Str::uuid7(),
            'crm_customer_id' => $customer->id,
            'crm_company_unit_id' => $unit->id,
            'name' => 'Ruiru Gate',
            'active' => 1,
        ]);

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

        $descriptionEl = $this->createElement($holder, 'text', 'sample_description', 0);
        $samplingPointEl = $this->createElement($holder, 'customer_sample_point_select', 'sampling_point', 1);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sampling point instance',
            'form_number' => 'CR-POINT',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $this->createValue($instance, $descriptionEl, 'Chicken', 0);
        $this->createValue($instance, $samplingPointEl, (string) $samplePoint->id, 0);

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);

        $this->assertCount(1, $lines);
        $this->assertSame('Ruiru Gate', $lines[0]['sampling_point']);
        $this->assertNotSame((string) $samplePoint->id, $lines[0]['sampling_point']);
    }

    public function test_it_keeps_identical_physical_samples_on_distinct_row_indexes(): void
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

        $sampleTypeEl = $this->createElement($holder, 'sample_type_select', 'sample_type_id', 0);
        $analysisTypeEl = $this->createElement($holder, 'analysis_type_select', 'analysis_type_id', 1);
        $parametersEl = $this->createElement($holder, 'analysis_elements_select', 'parameters', 2);

        $sampleTypeId = (string) Str::uuid7();
        $analysisTypeId = (string) Str::uuid7();
        $parameterIds = implode(',', [
            (string) Str::uuid7(),
            (string) Str::uuid7(),
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Multi-sample instance',
            'form_number' => 'CR400',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        foreach ([0, 1, 2, 3] as $rowIndex) {
            $this->createValue($instance, $sampleTypeEl, $sampleTypeId, $rowIndex);
            $this->createValue($instance, $analysisTypeEl, $analysisTypeId, $rowIndex);
            $this->createValue($instance, $parametersEl, $parameterIds, $rowIndex);
        }

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance(
            $instance->fresh(['values.element', 'submissionForm.sections.elementHolders.elements']),
        );

        $this->assertCount(4, $lines);
        $this->assertSame([0, 1, 2, 3], array_column($lines, 'row_index'));
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
