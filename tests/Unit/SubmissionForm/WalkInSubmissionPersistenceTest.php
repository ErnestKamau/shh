<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use App\SampleType;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalkInSubmissionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_walk_in_submission_must_include_indexed_row_fields_to_persist_tests(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Walk-in TRF',
            'document_code' => 'TRF/FOOD',
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

        $form->sampleTypes()->attach($sampleType->id);

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

        $analysisTypeElement = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'analysis_type_select',
            'label' => 'Sample Type',
            'name' => 'analysis_type_id',
            'sort_order' => 0,
        ]);

        $parametersElement = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'analysis_elements_select',
            'label' => 'Parameters',
            'name' => 'parameters',
            'sort_order' => 1,
        ]);

        $analysisTypeId = (string) Str::uuid7();
        $parameterId = (string) Str::uuid7();

        $formData = [
            'analysis_type_id' => [0 => $analysisTypeId],
            'parameters' => [0 => [$parameterId]],
        ];

        $normalizer = app(SubmissionFormValueNormalizer::class);
        $normalizedOnly = $normalizer->toRequestPayload($formData);
        $this->assertArrayHasKey('sample_rows', $normalizedOnly);
        $this->assertArrayNotHasKey('parameters', $normalizedOnly);

        $submissionService = app(SubmissionFormSubmissionService::class);

        $normalizedInstance = $submissionService->submitWalkInInstance(
            $form,
            $normalizedOnly,
            null,
            $sampleType->id,
            CommercialEnquirySyncService::SOURCE_WALK_IN,
        );

        $normalizedRowValues = SubmissionFormInstanceValue::query()
            ->where('submission_form_instance_id', $normalizedInstance->id)
            ->whereIn('submission_form_element_id', [$analysisTypeElement->id, $parametersElement->id])
            ->count();

        $this->assertSame(0, $normalizedRowValues);

        $mergedPayload = array_merge($formData, $normalizedOnly);

        $mergedInstance = $submissionService->submitWalkInInstance(
            $form,
            $mergedPayload,
            null,
            $sampleType->id,
            CommercialEnquirySyncService::SOURCE_WALK_IN,
        );

        $mergedRowValues = SubmissionFormInstanceValue::query()
            ->where('submission_form_instance_id', $mergedInstance->id)
            ->whereIn('submission_form_element_id', [$analysisTypeElement->id, $parametersElement->id])
            ->orderBy('submission_form_element_id')
            ->orderBy('array_index')
            ->get();

        $this->assertCount(2, $mergedRowValues);
        $this->assertSame($analysisTypeId, $mergedRowValues[0]->value);
        $this->assertSame($parameterId, $mergedRowValues[1]->value);
        $this->assertSame($sampleType->id, $mergedInstance->selected_sample_type_id);
        $this->assertSame(['Food'], $mergedInstance->getResolvedSampleTypeNames());
    }

    public function test_walk_in_submission_persists_multiple_parameters_per_row_as_comma_separated_value(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Walk-in TRF',
            'document_code' => 'TRF/FOOD',
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

        $form->sampleTypes()->attach($sampleType->id);

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

        SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'analysis_elements_select',
            'label' => 'Parameters',
            'name' => 'parameters',
            'sort_order' => 0,
        ]);

        $parameterA = (string) Str::uuid7();
        $parameterB = (string) Str::uuid7();

        $formData = [
            'parameters' => [0 => [$parameterA, $parameterB]],
        ];

        $normalizer = app(SubmissionFormValueNormalizer::class);
        $payload = array_merge($formData, $normalizer->toRequestPayload($formData));

        $instance = app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
            $form,
            $payload,
            null,
            $sampleType->id,
            CommercialEnquirySyncService::SOURCE_WALK_IN,
        );

        $stored = SubmissionFormInstanceValue::query()
            ->where('submission_form_instance_id', $instance->id)
            ->where('array_index', 0)
            ->value('value');

        $this->assertSame($parameterA.','.$parameterB, $stored);
    }
}
