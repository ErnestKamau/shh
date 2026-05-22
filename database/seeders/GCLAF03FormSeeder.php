<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class GCLAF03FormSeeder extends Seeder
{
    public function run()
    {
        $targetName = 'Laboratory Analysis Acceptance Form';
        $targetCode = 'GCLA/F/03';

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
            'description' => 'Laboratory Analysis Acceptance Form',
            'naming_convention_prefix' => 'GCLA-F03',
            'naming_convention_format' => 'GCLA-F03-{YYYY}{MM}-{0000}',
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

        $this->command->info('GCLA/F/03 Form seeded/updated successfully.');
    }

    private function createFormSections($form)
    {
        // Section: Official Use Only
        $officialSection = $form->sections()->create([
            'title' => 'Official Use Only',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $officialHolder = $officialSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
        $officialHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Lab. No.',
            'name' => 'official_lab_no',
            'is_required' => false,
            'sort_order' => 1,
        ]);

        // Section: PART A: SAMPLE DETAILS
        $partASection = $form->sections()->create([
            'title' => 'PART A: SAMPLE DETAILS',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $partAHolder = $partASection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $partAFields = [
            ['label' => '1. Name of customer', 'name' => 'customer_name', 'type' => 'text'],
            ['label' => 'Date', 'name' => 'customer_date', 'type' => 'date'],
            ['label' => '2. Address', 'name' => 'customer_address', 'type' => 'textarea'],
            ['label' => 'Tel', 'name' => 'customer_tel', 'type' => 'text'],
            ['label' => 'Email', 'name' => 'customer_email', 'type' => 'email'],
            ['label' => '4. Number of samples', 'name' => 'number_of_samples', 'type' => 'number'],
            ['label' => 'Mode of work', 'name' => 'mode_of_work', 'type' => 'radio', 'options' => [
                ['value' => 'Normal', 'label' => 'Normal'],
                ['value' => 'Express', 'label' => 'Express']
            ]],
            ['label' => '5. Type of samples', 'name' => 'type_of_samples', 'type' => 'text'],
            ['label' => '6. Date of sampling (If Applicable)', 'name' => 'date_of_sampling', 'type' => 'date'],
        ];

        foreach ($partAFields as $index => $field) {
            $element = [
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ];
            if (isset($field['options'])) {
                $element['options'] = $field['options'];
            }
            $partAHolder->elements()->create($element);
        }

        // Section: Parameters Table
        $parametersSection = $form->sections()->create([
            'title' => 'Parameters Requested',
            'section_type' => 'rows_section',
            'sort_order' => 3,
        ]);
        $parametersHolder = $parametersSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 4,
            'sort_order' => 1,
        ]);
        $parametersFields = [
            ['label' => 'Parameter(s) Requested', 'name' => 'parameter_requested', 'type' => 'text'],
            ['label' => 'Amount $ usd', 'name' => 'amount_usd', 'type' => 'number'],
            ['label' => 'Accept (v)', 'name' => 'accept', 'type' => 'checkbox'],
            ['label' => 'Reject (x)', 'name' => 'reject', 'type' => 'checkbox'],
        ];
        foreach ($parametersFields as $index => $field) {
            $parametersHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section: Total & Deviations
        $deviationSection = $form->sections()->create([
            'title' => 'Total and Deviations',
            'section_type' => 'regular',
            'sort_order' => 4,
        ]);
        $deviationHolder = $deviationSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $deviationHolder->elements()->create([
            'element_type' => 'number',
            'label' => 'TOTAL Amount',
            'name' => 'total_amount',
            'is_required' => false,
            'sort_order' => 1,
        ]);
        $deviationHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'Any deviation from specified conditions',
            'name' => 'deviation_conditions',
            'options' => [
                ['value' => 'Yes', 'label' => 'Yes'],
                ['value' => 'No', 'label' => 'No']
            ],
            'is_required' => false,
            'sort_order' => 2,
        ]);

        // Section: PART B: CUSTOMER CERTIFIED
        $partBSection = $form->sections()->create([
            'title' => 'PART B: CUSTOMER CERTIFIED',
            'description' => 'I certified that the above request is Correct.',
            'section_type' => 'regular',
            'sort_order' => 5,
        ]);
        $partBHolder = $partBSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 3,
            'sort_order' => 1,
        ]);
        $partBFields = [
            ['label' => 'Customer\'s Name', 'name' => 'part_b_customer_name', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'part_b_signature', 'type' => 'signature'],
            ['label' => 'Date', 'name' => 'part_b_date', 'type' => 'date'],
        ];
        foreach ($partBFields as $index => $field) {
            $partBHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section: PART C: CONFORMITY ASSESSMENT
        $partCSection = $form->sections()->create([
            'title' => 'PART C: CONFORMITY ASSESSMENT',
            'section_type' => 'regular',
            'sort_order' => 6,
        ]);
        $partCHolder = $partCSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
        $partCHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'Customer requests / not requested a statement of conformity to a specification or standard',
            'name' => 'conformity_statement_request',
            'options' => [
                ['value' => 'Requested', 'label' => 'Requested'],
                ['value' => 'Not Requested', 'label' => 'Not Requested']
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);

        // Section: PART D: LABORATORY MANAGER
        $partDSection = $form->sections()->create([
            'title' => 'PART D: LABORATORY MANAGER',
            'section_type' => 'regular',
            'sort_order' => 7,
        ]);
        $partDHolder = $partDSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        
        $partDHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'I certify that the laboratory',
            'name' => 'laboratory_capability',
            'options' => [
                ['value' => 'has capability', 'label' => 'has capability and resources to meet customer requirements'],
                ['value' => 'has not capability', 'label' => 'has not capability and resources to meet customer requirements']
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);

        $partDFields = [
            ['label' => 'Laboratory', 'name' => 'laboratory_name', 'type' => 'text'],
            ['label' => 'Laboratory Manager name', 'name' => 'lab_manager_name', 'type' => 'text'],
            ['label' => 'Manager\'s Signature', 'name' => 'manager_signature', 'type' => 'signature'],
            ['label' => 'Date', 'name' => 'manager_date', 'type' => 'date'],
        ];
        foreach ($partDFields as $index => $field) {
            $partDHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 2,
            ]);
        }
    }
}
