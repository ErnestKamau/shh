<?php

namespace Tests\Feature\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormInstanceDraftSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_lims_draft_save_persists_partial_values_without_finalizing(): void
    {
        Gate::before(fn () => true);
        $user = User::query()->create([
            'name' => 'TRF Administrator',
            'email' => 'trf-admin@example.test',
            'password' => bcrypt('password'),
        ]);
        $role = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $user->assignRole($role);

        $form = SubmissionForm::query()->create([
            'name' => 'TRF Draft',
            'document_code' => 'TRF-DRAFT',
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
        $section = SubmissionFormSection::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Details',
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
        $optionalElement = SubmissionFormElement::query()->create([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Draft note',
            'name' => 'draft_note',
            'is_required' => false,
            'is_hidden' => false,
            'sort_order' => 1,
        ]);
        SubmissionFormElement::query()->create([
            'submission_form_element_holder_id' => $holder->id,
            'element_type' => 'text',
            'label' => 'Required on submit',
            'name' => 'required_on_submit',
            'is_required' => true,
            'is_hidden' => false,
            'sort_order' => 2,
        ]);
        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'title' => 'Draft instance',
            'submitted_by' => $user->id,
            'status' => 'draft',
            'priority' => 'normal',
        ]);

        $response = $this->actingAs($user)->put(
            route('submission-forms.instances.update', [$form, $instance]),
            [
                'action' => 'draft',
                'draft_note' => 'Saved before completion',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Draft saved successfully.');
        $this->assertSame('draft', $instance->fresh()->status);
        $this->assertNull($instance->fresh()->form_number);
        $this->assertDatabaseHas('submission_form_instance_values', [
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $optionalElement->id,
            'value' => 'Saved before completion',
        ]);
    }
}
