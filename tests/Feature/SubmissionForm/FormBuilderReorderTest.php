<?php

namespace Tests\Feature\SubmissionForm;

use App\Http\Controllers\FormBuilderController;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FormBuilderReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorder_actions_accept_uuid_primary_keys(): void
    {
        $form = SubmissionForm::query()->create([
            'name' => 'TRF Reorder',
            'document_code' => 'TRF-REORDER',
            'naming_convention_prefix' => 'TRF',
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

        $firstSection = $this->createSection($form, 'First', 1);
        $secondSection = $this->createSection($form, 'Second', 2);
        $firstHolder = $this->createHolder($firstSection, 1);
        $secondHolder = $this->createHolder($firstSection, 2);
        $firstElement = $this->createElement($firstHolder, 'first_field', 1);
        $secondElement = $this->createElement($firstHolder, 'second_field', 2);

        $controller = app(FormBuilderController::class);

        $controller->reorderSections(
            Request::create('/reorder', 'POST', [
                'section_ids' => [$secondSection->id, $firstSection->id],
            ]),
            $form
        );
        $controller->reorderElementHolders(
            Request::create('/reorder', 'POST', [
                'holder_ids' => [$secondHolder->id, $firstHolder->id],
            ]),
            $firstSection
        );
        $controller->reorderElements(
            Request::create('/reorder', 'POST', [
                'element_ids' => [$secondElement->id, $firstElement->id],
            ]),
            $firstHolder
        );

        $this->assertSame(1, $secondSection->fresh()->sort_order);
        $this->assertSame(2, $firstSection->fresh()->sort_order);
        $this->assertSame(1, $secondHolder->fresh()->sort_order);
        $this->assertSame(2, $firstHolder->fresh()->sort_order);
        $this->assertSame(1, $secondElement->fresh()->sort_order);
        $this->assertSame(2, $firstElement->fresh()->sort_order);
    }

    private function createSection(
        SubmissionForm $form,
        string $title,
        int $sortOrder
    ): SubmissionFormSection {
        return SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => $title,
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
            'is_hidden' => false,
        ]);
    }

    private function createHolder(
        SubmissionFormSection $section,
        int $sortOrder
    ): SubmissionFormElementHolder {
        return SubmissionFormElementHolder::query()->create([
            'submission_form_section_id' => $section->id,
            'holder_type' => 'field',
            'sort_order' => $sortOrder,
            'max_elements' => 10,
        ]);
    }

    private function createElement(
        SubmissionFormElementHolder $holder,
        string $name,
        int $sortOrder
    ): SubmissionFormElement {
        return SubmissionFormElement::query()->create([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => str_replace('_', ' ', $name),
            'name' => $name,
            'is_required' => false,
            'is_hidden' => false,
            'sort_order' => $sortOrder,
        ]);
    }
}
