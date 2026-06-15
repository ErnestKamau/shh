<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class GCLA01FormSeeder extends Seeder
{
    public function run()
    {
        $targetName = 'SAMPLE RECEIPT NOTIFICATION';
        $targetCode = 'AMSPEC 01';

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
            'description' => 'AmSpec Middle East - Sample Receipt Notification',
            'naming_convention_prefix' => 'AMSPEC01',
            'naming_convention_format' => 'AMSPEC01-{YYYY}{MM}-{0000}',
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

        $this->command->info('AMSPEC 01 Form seeded/updated successfully.');
    }

    private function createFormSections($form)
    {
        $section1 = $form->sections()->create([
            'title' => 'Sample Receipt Notification',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $holder1 = $section1->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        
        $fields = [
            ['label' => '1. Name of the client or submitting authority', 'name' => 'client_name', 'type' => 'textarea'],
            ['label' => '2. Description of sample(s)', 'name' => 'sample_description', 'type' => 'textarea'],
            ['label' => '3. Name of the person submitting the sample or exhibit', 'name' => 'submitter_name', 'type' => 'text'],
            ['label' => 'Designation', 'name' => 'submitter_designation', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'submitter_signature', 'type' => 'signature'],
            ['label' => '4. Laboratory identification number (Lab. No.)', 'name' => 'lab_id_number', 'type' => 'text'],
            ['label' => '5. Number of samples', 'name' => 'number_of_samples', 'type' => 'number'],
            ['label' => '6. Name of the receiving person', 'name' => 'receiver_name', 'type' => 'text'],
            ['label' => 'Designation', 'name' => 'receiver_designation', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'receiver_signature', 'type' => 'signature'],
            ['label' => '7. Sample receiving date', 'name' => 'sample_receiving_date', 'type' => 'date'],
        ];

        foreach ($fields as $index => $field) {
            $holder1->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }
        
        // Stamp section
        $section2 = $form->sections()->create([
            'title' => 'Official Use',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $holder2 = $section2->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
        $holder2->elements()->create([
            'element_type' => 'checkbox',
            'label' => '(Official Stamp / Seal)',
            'name' => 'official_stamp_applied',
            'options' => [
                ['value' => 'Applied', 'label' => 'Stamp / Seal Applied']
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);
    }
}
