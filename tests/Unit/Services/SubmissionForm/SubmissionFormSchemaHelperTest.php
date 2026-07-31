<?php

namespace Tests\Unit\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionFormSchemaHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_sections_prefers_richer_duplicate_section(): void
    {
        $form = $this->createForm();

        $sparseSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'standard',
            'sort_order' => 2,
        ]);

        $richSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'standard',
            'sort_order' => 2,
        ]);

        $this->createFieldHolder($sparseSection, 'sampling_date');
        $this->createFieldHolder($richSection, 'sampling_date');
        $this->createFieldHolder($richSection, 'packaging');

        $unique = app(SubmissionFormSchemaHelper::class)->uniqueSections($form->fresh());

        $this->assertCount(1, $unique);
        $this->assertSame($richSection->id, $unique->first()->id);
    }

    public function test_unique_elements_prefers_later_section_for_same_field_name(): void
    {
        $form = $this->createForm();

        $collectionSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'standard',
            'sort_order' => 2,
        ]);

        $miscSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Miscellaneous',
            'section_type' => 'standard',
            'sort_order' => 4,
        ]);

        $collectionPackaging = $this->createFieldHolder($collectionSection, 'packaging');
        $miscPackaging = $this->createFieldHolder($miscSection, 'packaging');

        $unique = app(SubmissionFormSchemaHelper::class)->uniqueElements($form->fresh());
        $packaging = $unique->firstWhere('name', 'packaging');

        $this->assertNotNull($packaging);
        $this->assertSame($miscPackaging->id, $packaging->id);
        $this->assertNotSame($collectionPackaging->id, $packaging->id);
    }

    public function test_form_data_for_display_reads_values_from_duplicate_element_ids_by_name(): void
    {
        $form = $this->createForm();

        $displaySection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'standard',
            'sort_order' => 2,
        ]);

        $legacySection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'standard',
            'sort_order' => 2,
        ]);

        $displayElement = $this->createFieldHolder($displaySection, 'sampling_date');
        $legacyElement = $this->createFieldHolder($legacySection, 'sampling_date');

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test instance',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        SubmissionFormInstanceValue::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $legacyElement->id,
            'value' => '2026-07-03',
            'array_index' => 0,
        ]);

        $formData = $instance->fresh()->getFormDataForDisplay();
        $section = collect($formData['sections'])->firstWhere('title', 'Sample collection data');
        $element = collect($section['element_holders'][0]['elements'] ?? [])
            ->firstWhere('name', 'sampling_date');

        $this->assertSame('2026-07-03', $element['saved_values'][0]['value'] ?? null);
        $this->assertNotSame($displayElement->id, $legacyElement->id);
    }

    private function createForm(): SubmissionForm
    {
        return SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Food',
            'document_code' => 'TRF-FOOD-TEST',
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

    public function test_unique_sections_prefers_collection_section_without_misc_fields(): void
    {
        $form = $this->createForm();

        $legacySection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $this->createFieldHolder($legacySection, 'sampling_date');
        $this->createFieldHolder($legacySection, 'packaging');

        $cleanSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Sample collection data',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $this->createFieldHolder($cleanSection, 'sampling_date');

        $unique = app(SubmissionFormSchemaHelper::class)->uniqueSections($form->fresh());

        $this->assertCount(1, $unique);
        $this->assertSame($cleanSection->id, $unique->first()->id);
    }

    public function test_form_data_for_display_hides_internal_customer_fields(): void
    {
        $form = $this->createForm();

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Customer details',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);

        $this->createFieldHolder($section, 'customer_name');
        $this->createFieldHolder($section, 'crm_contact_id');
        $this->createFieldHolder($section, 'customer_tax_id');

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test instance',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $formData = $instance->fresh()->getFormDataForDisplay();
        $customerSection = collect($formData['sections'])->firstWhere('title', 'Customer details');
        $names = collect($customerSection['element_holders'])
            ->flatMap(fn (array $holder) => collect($holder['elements'])->pluck('name'))
            ->all();

        $this->assertContains('customer_name', $names);
        $this->assertNotContains('crm_contact_id', $names);
        $this->assertNotContains('customer_tax_id', $names);
    }

    public function test_resolve_display_value_strips_html_from_sample_description(): void
    {
        $form = $this->createForm();

        $section = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test & sample information',
            'section_type' => 'rows_section',
            'sort_order' => 3,
        ]);

        $element = $this->createFieldHolder($section, 'sample_description');
        $element->update(['element_type' => 'textarea', 'label' => 'Sample description']);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test instance',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        $display = $instance->resolveDisplayValue($element->fresh(), '<p>chicken</p>');

        $this->assertSame('chicken', $display);
    }

    public function test_should_hide_superseded_number_of_samples_when_sample_quantity_exists(): void
    {
        $form = $this->createForm();

        $rowsSection = SubmissionFormSection::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test & sample information',
            'section_type' => 'rows_section',
            'sort_order' => 3,
        ]);

        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $rowsSection->id,
            'holder_type' => 'rows',
            'sort_order' => 0,
        ]);

        $legacyQty = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'number',
            'label' => 'Qty',
            'name' => 'number_of_samples',
            'sort_order' => 1,
        ]);

        $canonicalQty = SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'number',
            'label' => 'Qty',
            'name' => 'sample_quantity',
            'sort_order' => 2,
        ]);

        $this->assertTrue(
            SubmissionFormSchemaHelper::shouldHideSupersededRowField($legacyQty, $holder->elements()->get()),
        );
        $this->assertFalse(
            SubmissionFormSchemaHelper::shouldHideSupersededRowField($canonicalQty, $holder->elements()->get()),
        );
    }

    public function test_selected_checkbox_keys_returns_truthy_option_keys(): void
    {
        $this->assertSame(
            ['microbiology', 'chemistry'],
            SubmissionFormSchemaHelper::selectedCheckboxKeys([
                'microbiology' => true,
                'legionella' => false,
                'chemistry' => true,
            ]),
        );

        $this->assertSame([], SubmissionFormSchemaHelper::selectedCheckboxKeys([
            'microbiology' => false,
        ]));
    }

    public function test_selected_checkbox_keys_ignores_non_checkbox_maps(): void
    {
        // Flat token lists (e.g. analysis-type CSV pickers) must not be treated as checkbox maps.
        $this->assertNull(SubmissionFormSchemaHelper::selectedCheckboxKeys(['uuid-a', 'uuid-b']));

        // Nested per-row multi-selects are not checkbox maps either.
        $this->assertNull(SubmissionFormSchemaHelper::selectedCheckboxKeys([0 => ['a', 'b']]));

        // Value maps (key === value) are selection lists, not booleans.
        $this->assertNull(SubmissionFormSchemaHelper::selectedCheckboxKeys(['grab' => 'grab']));
    }

    public function test_test_category_tokens_drops_legacy_stringified_booleans(): void
    {
        $this->assertSame([], SubmissionFormSchemaHelper::testCategoryTokens('1,1'));
        $this->assertSame([], SubmissionFormSchemaHelper::testCategoryTokens('1,'));
    }

    public function test_test_category_tokens_canonicalizes_and_dedupes(): void
    {
        $this->assertSame(
            ['microbiology', 'chemistry'],
            SubmissionFormSchemaHelper::testCategoryTokens('microbiology, chemical_analysis'),
        );

        $this->assertSame(
            ['chemistry'],
            SubmissionFormSchemaHelper::testCategoryTokens('chemical,chemistry'),
        );
    }

    public function test_test_category_tokens_accepts_checkbox_map(): void
    {
        $this->assertSame(
            ['microbiology', 'legionella'],
            SubmissionFormSchemaHelper::testCategoryTokens([
                'microbiology' => true,
                'legionella' => true,
                'chemistry' => false,
            ]),
        );
    }

    public function test_test_category_label_humanizes_tokens(): void
    {
        $this->assertSame(
            'Microbiology, Chemistry',
            SubmissionFormSchemaHelper::testCategoryLabel('microbiology,chemistry'),
        );

        $this->assertSame('', SubmissionFormSchemaHelper::testCategoryLabel('1,1'));

        // Unknown categories are preserved (title-cased) rather than dropped.
        $this->assertSame('Routine', SubmissionFormSchemaHelper::testCategoryLabel('routine'));
    }

    private function createFieldHolder(SubmissionFormSection $section, string $name): SubmissionFormElement
    {
        $holder = SubmissionFormElementHolder::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => 0,
        ]);

        return SubmissionFormElement::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => ucfirst(str_replace('_', ' ', $name)),
            'name' => $name,
            'sort_order' => 0,
        ]);
    }
}
