<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class QARMF01FormSeeder extends Seeder
{
    public function run()
    {
        $targetName = 'SAMPLE REJECTION FORM';
        $targetCode = 'QARM/F/01';

        $formByName = SubmissionForm::where('name', $targetName)->first();
        $formByCode = SubmissionForm::where('document_code', $targetCode)->first();

        $form = null;

        if ($formByName && $formByCode && $formByName->id !== $formByCode->id) {
            foreach ([$formByName, $formByCode] as $f) {
                if ($f->instances()->count() > 0) {
                    $f->update([
                        'name' => $f->name . ' (Archived ' . now()->timestamp . ')',
                        'document_code' => $f->document_code . '-OLD-' . now()->timestamp,
                        'is_active' => false,
                        'is_published' => false,
                    ]);
                } else {
                    $f->delete();
                }
            }
            $form = new SubmissionForm();
        } else {
            $existing = $formByName ?: $formByCode;
            if ($existing) {
                if ($existing->instances()->count() > 0) {
                    $existing->update([
                        'name' => $existing->name . ' (Archived ' . now()->timestamp . ')',
                        'document_code' => $existing->document_code . '-OLD-' . now()->timestamp,
                        'is_active' => false,
                        'is_published' => false,
                    ]);
                    $form = new SubmissionForm();
                } else {
                    $form = $existing;
                }
            } else {
                $form = new SubmissionForm();
            }
        }

        $form->fill([
            'name' => $targetName,
            'document_code' => $targetCode,
            'description' => 'GOVERNMENT CHEMIST LABORATORY AUTHORITY - SAMPLE REJECTION FORM',
            'naming_convention_prefix' => 'QARM-F01',
            'naming_convention_format' => 'QARM-F01-{YYYY}{MM}-{0000}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => false,
            'form_type' => 'template',
            'placement_slot' => ['admin_portal'],
        ]);
        $form->save();

        try {
            $stages = \App\SampleAnalysisStage::pluck('id')->toArray();
            $form->sampleAnalysisStages()->sync($stages);
        } catch (\Exception $e) {
            $this->command->warn('Could not sync stages: ' . $e->getMessage());
        }

        $form->sections()->each(function($section) {
            $section->elementHolders()->each(function($holder) {
                $holder->elements()->delete();
                $holder->delete();
            });
            $section->delete();
        });

        $this->createFormSections($form);

        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Exception $e) {
            $this->command->warn('Failed to clear cache: ' . $e->getMessage());
        }

        $this->command->info('QARM/F/01 Form seeded/updated successfully.');
    }

    private function createFormSections($form)
    {
        // Section: General Info
        $infoSection = $form->sections()->create([
            'title' => 'General Information',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $infoHolder = $infoSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $infoFields = [
            ['label' => 'Sample ID', 'name' => 'sample_id', 'type' => 'text'],
            ['label' => 'Name /Client', 'name' => 'client_name', 'type' => 'text'],
            ['label' => 'Date sample Received/collected', 'name' => 'date_received', 'type' => 'date'],
            ['label' => 'Type of sample(s)', 'name' => 'sample_type', 'type' => 'text'],
            ['label' => 'Number of samples/Received', 'name' => 'number_of_samples', 'type' => 'text'],
        ];

        foreach ($infoFields as $index => $field) {
            $infoHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section: Rejection Reasons
        $reasonSection = $form->sections()->create([
            'title' => 'Rejection Details',
            'description' => 'The above sample has not met the criteria of sample integrity for the test(s) requested. Analysis of such sample will yield unreliable results. Therefore, we cannot receive the sample(s) for further analysis.',
            'section_type' => 'rows_section', // Allows adding multiple reasons if needed, or we can use regular with all fields
            'sort_order' => 2,
        ]);
        $reasonHolder = $reasonSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        
        // As a rows_section, each row can just be one reason + explanation
        $reasonHolder->elements()->create([
            'element_type' => 'select',
            'label' => 'Reason',
            'name' => 'rejection_reason',
            'options' => [
                ['value' => 'Sample was collected in improper container', 'label' => 'Sample was collected in improper container'],
                ['value' => 'Sample not properly sealed/was leaking', 'label' => 'Sample not properly sealed/was leaking'],
                ['value' => 'Sample was stored inappropriate storage conditions', 'label' => 'Sample was stored inappropriate storage conditions'],
                ['value' => 'Sample was inappropriate treated after sampling prior analysis', 'label' => 'Sample was inappropriate treated after sampling prior analysis'],
                ['value' => 'Sample was improperly labeled and date of collection was not clear', 'label' => 'Sample was improperly labeled and date of collection was not clear'],
                ['value' => 'Sample material was inappropriate for the test(s) requested', 'label' => 'Sample material was inappropriate for the test(s) requested'],
                ['value' => 'Sample volume /weight was inappropriate for the test(s) requested', 'label' => 'Sample volume /weight was inappropriate for the test(s) requested'],
                ['value' => 'Sample was not accompanied by a request form/sample could not be related to a request form', 'label' => 'Sample was not accompanied by a request form/sample could not be related to a request form'],
                ['value' => 'Sample name/date of collection on request form did not match the same details on the sample label', 'label' => 'Sample name/date of collection on request form did not match the same details on the sample label'],
                ['value' => 'Other, namely', 'label' => 'Other, namely']
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);

        $reasonHolder->elements()->create([
            'element_type' => 'textarea',
            'label' => 'Explanation',
            'name' => 'rejection_explanation',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        // Section: Sign-off
        $signoffSection = $form->sections()->create([
            'title' => 'Sign-off',
            'description' => 'You are advised to collect another sample from the source (if still possible) and resend this to us. We apologize for any inconvenience this has caused.',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);
        $signoffHolder = $signoffSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 3,
            'sort_order' => 1,
        ]);

        $signoffFields = [
            ['label' => 'LABORATORY STAFF', 'name' => 'lab_staff_name', 'type' => 'text'],
            ['label' => 'SIGNATURE', 'name' => 'staff_signature', 'type' => 'signature'],
            ['label' => 'DATE', 'name' => 'signoff_date', 'type' => 'date'],
        ];

        foreach ($signoffFields as $index => $field) {
            $signoffHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
