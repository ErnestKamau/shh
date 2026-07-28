<?php

namespace Tests\Feature\SubmissionForm;

use App\Http\Controllers\FormBuilderController;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use App\Services\SubmissionForm\FormSchemaBuilder;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class BuilderHiddenSectionElementTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_hidden_flags_are_detected_by_schema_helper(): void
    {
        [$form, $section, $element] = $this->createFormWithField('customer_name');

        $this->assertFalse(SubmissionFormSchemaHelper::isBuilderHiddenSection($section));
        $this->assertFalse(SubmissionFormSchemaHelper::isBuilderHiddenElement($element));

        $section->update(['is_hidden' => true]);
        $section->refresh();
        $element->load('holder.section');

        $this->assertTrue(SubmissionFormSchemaHelper::isBuilderHiddenSection($section));
        $this->assertTrue(SubmissionFormSchemaHelper::isBuilderHiddenElement($element));
    }

    public function test_form_schema_builder_omits_hidden_sections_and_elements(): void
    {
        [$form, $section, $visible] = $this->createFormWithField('customer_name');

        $hidden = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $visible->submission_form_element_holder_id,
            'element_type' => 'text',
            'label' => 'Legacy field',
            'name' => 'legacy_field',
            'is_hidden' => true,
            'sort_order' => 1,
        ]);

        $hiddenSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Deprecated section',
            'section_type' => 'regular',
            'is_hidden' => true,
            'sort_order' => 2,
        ]);

        $schema = app(FormSchemaBuilder::class)->buildSections($form->fresh(['sections.elementHolders.elements']));
        $titles = collect($schema)->pluck('title')->all();
        $names = collect($schema)
            ->flatMap(fn (array $s) => collect($s['holders'])->flatMap(fn (array $h) => collect($h['elements'])->pluck('name')))
            ->all();

        $this->assertContains('Customer details', $titles);
        $this->assertNotContains('Deprecated section', $titles);
        $this->assertContains('customer_name', $names);
        $this->assertNotContains('legacy_field', $names);
        $this->assertTrue($hidden->is_hidden);
        $this->assertTrue($hiddenSection->is_hidden);
    }

    public function test_validation_skips_required_rules_for_hidden_elements(): void
    {
        [, , $element] = $this->createFormWithField('customer_name', required: true);
        $element->update(['is_hidden' => true]);
        $element->load('holder.section');

        $service = app(SubmissionFormSubmissionService::class);
        $rules = $service->buildValidationRules(
            collect([$element]),
            Request::create('/fake', 'POST', []),
            requireRequired: true,
        );

        $this->assertSame(['nullable'], $rules['customer_name']);
    }

    public function test_toggle_section_and_element_hidden_endpoints(): void
    {
        [, $section, $element] = $this->createFormWithField('customer_name');
        $controller = app(FormBuilderController::class);

        $sectionResponse = $controller->toggleSectionHidden($section)->getData(true);
        $this->assertTrue($sectionResponse['success']);
        $this->assertTrue($section->fresh()->is_hidden);

        $sectionResponse = $controller->toggleSectionHidden($section->fresh())->getData(true);
        $this->assertFalse($section->fresh()->is_hidden);

        $elementResponse = $controller->toggleElementHidden($element)->getData(true);
        $this->assertTrue($elementResponse['success']);
        $this->assertTrue($element->fresh()->is_hidden);
    }

    /**
     * @return array{0: SubmissionForm, 1: SubmissionFormSection, 2: SubmissionFormElement}
     */
    private function createFormWithField(string $name, bool $required = false): array
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Food',
            'document_code' => 'TRF-FOOD-HIDE',
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

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Customer details',
            'section_type' => 'regular',
            'sort_order' => 1,
            'is_hidden' => false,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 0,
            'max_elements' => 10,
        ]);

        $element = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Customer name',
            'name' => $name,
            'is_required' => $required,
            'is_hidden' => false,
            'sort_order' => 0,
        ]);

        return [$form, $section, $element];
    }
}
