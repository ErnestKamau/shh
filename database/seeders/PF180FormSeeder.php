<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class PF180FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // To avoid unique constraint violations and foreign key errors, we check for existing forms.
        $targetName = 'PF180 Sample Analysis Request Form';
        $targetCode = 'HQ/F/01';

        $formByName = SubmissionForm::where('name', $targetName)->first();
        $formByCode = SubmissionForm::where('document_code', $targetCode)->first();

        // Determine if we should use an existing form or create a new one
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
            'description' => 'SAMPLE ANALYSIS REQUEST FORM (PF 180)',
            'naming_convention_prefix' => 'PF180',
            'naming_convention_format' => 'PF180-{YYYY}{MM}-{0000}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => false,
            'form_type' => 'attachment',
            'placement_slot' => ['admin_portal'],
        ]);
        $form->save();

        // Ensure visibility by linking to all lab sections
        try {
            $stages = \App\SampleAnalysisStage::pluck('id')->toArray();
            $form->sampleAnalysisStages()->sync($stages);
        } catch (\Exception $e) {
            $this->command->warn('Could not sync stages: ' . $e->getMessage());
        }

        $this->command->info('Creating sections for PF180 Form...');
        
        // Clear existing sections only if they belong to a fresh or instance-less form
        $form->sections()->each(function($section) {
            $section->elementHolders()->each(function($holder) {
                $holder->elements()->delete();
                $holder->delete();
            });
            $section->delete();
        });

        $this->createFormSections($form);

        // Bust the cache
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            $this->command->info('Server cache cleared successfully.');
        } catch (\Exception $e) {
            $this->command->warn('Failed to clear server cache: ' . $e->getMessage());
        }

        $this->command->info('PF180 Form seeded/updated successfully.');
    }

    /**
     * Create sections and elements for the form
     */
    private function createFormSections($form)
    {
        // Section 1: RESPONSIBLE OFFICER (SRO SAMPLE RECEIVING DETAILS)
        $this->command->info('Creating SRO section...');
        $sroSection = $form->sections()->create([
            'title' => 'RESPONSIBLE OFFICER (SRO SAMPLE RECEIVING DETAILS)',
            'description' => 'Details about the sample receiving and responsible officer.',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);

        $sroHolder = $sroSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $sroFields = [
            ['label' => 'S/N', 'name' => 'sn', 'type' => 'text'],
            ['label' => 'LAB NO.', 'name' => 'lab_no', 'type' => 'text'],
            ['label' => 'DATE IN', 'name' => 'date_in', 'type' => 'date'],
            ['label' => 'FILE NO.', 'name' => 'file_no', 'type' => 'text'],
            ['label' => 'NAME OF CLIENT', 'name' => 'name_of_client', 'type' => 'text'],
            ['label' => 'NAME OF SUBMITTING OFFICER', 'name' => 'name_of_submitting_officer', 'type' => 'text'],
            ['label' => 'DESIGNATION OF SUBMITTING OFFICER', 'name' => 'designation_of_submitting_officer', 'type' => 'text'],
            ['label' => 'PHONE NO. SUBMITTING OFFICER', 'name' => 'phone_no_submitting_officer', 'type' => 'text'],
            ['label' => 'ADDRESS AND EMAIL SUBMITTING OFFICER', 'name' => 'address_email_submitting_officer', 'type' => 'textarea'],
            ['label' => 'DESCRIPTION OF SAMPLE(S)', 'name' => 'description_of_samples', 'type' => 'textarea'],
            ['label' => 'NO. OF SAMPLES RECEIVED', 'name' => 'no_of_samples_received', 'type' => 'number'],
            ['label' => 'DATE SIGNED BY SUBMITTING OFFICER', 'name' => 'date_signed_by_submitting_officer', 'type' => 'date'],
            ['label' => 'REF. NO. OF SUBMITTING LETTER', 'name' => 'ref_no_submitting_letter', 'type' => 'text'],
            ['label' => 'REQUESTED ANALYSIS/SERVICES', 'name' => 'requested_analysis_services', 'type' => 'textarea'],
            ['label' => 'NAME OF SRO', 'name' => 'name_of_sro', 'type' => 'text'],
            ['label' => 'DESIGNATION OF SRO', 'name' => 'designation_of_sro', 'type' => 'text'],
            ['label' => 'SAMPLE RECEIVING DATE', 'name' => 'sample_receiving_date', 'type' => 'date'],
        ];

        foreach ($sroFields as $index => $field) {
            $sroHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 2: SAMPLE SUBMISSION TO LAB. MANAGER
        $this->command->info('Creating Submission section...');
        $submissionSection = $form->sections()->create([
            'title' => 'SAMPLE SUBMISSION TO LAB. MANAGER',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);

        $submissionHolder = $submissionSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $submissionFields = [
            ['label' => 'NAME OF SRO SUBMITTING SAMPLE TO MANAGER', 'name' => 'sro_name_submitting_to_manager', 'type' => 'text'],
            ['label' => 'DATE SRO SUBMITTING SAMPLE TO MANAGER', 'name' => 'sro_date_submitting_to_manager', 'type' => 'date'],
            ['label' => 'MANAGER TO RECEIVE SAMPLES', 'name' => 'manager_to_receive_samples', 'type' => 'text'],
        ];

        foreach ($submissionFields as $index => $field) {
            $submissionHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 3: CHIEF GOVERNMENT CHEMIST\'S INSTRUCTIONS
        $this->command->info('Creating CGC section...');
        $cgcSection = $form->sections()->create([
            'title' => 'CHIEF GOVERNMENT CHEMIST\'S INSTRUCTIONS',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);

        $cgcHolder = $cgcSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $cgcFields = [
            ['label' => 'DATE SRAF RECEIVED BY SECRETARY', 'name' => 'date_sraf_received_by_secretary', 'type' => 'date'],
            ['label' => 'DATE SRF SUBMITTED TO CGC', 'name' => 'date_srf_submitted_to_cgc', 'type' => 'date'],
            ['label' => 'NAME OF CGC\'S SECRETARY', 'name' => 'name_of_cgc_secretary', 'type' => 'text'],
            ['label' => 'INSTRUCTIONS BY CGC', 'name' => 'instructions_by_cgc', 'type' => 'textarea'],
            ['label' => 'DATE RELEASED BY CGC', 'name' => 'date_released_by_cgc', 'type' => 'date'],
            ['label' => 'DATE SUBMITTED TO DIRECTOR', 'name' => 'date_submitted_to_director', 'type' => 'date'],
        ];

        foreach ($cgcFields as $index => $field) {
            $cgcHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 4: DIRECTOR\'S INSTRUCTIONS
        $this->command->info('Creating Director section...');
        $directorSection = $form->sections()->create([
            'title' => 'DIRECTOR\'S INSTRUCTIONS',
            'section_type' => 'regular',
            'sort_order' => 4,
        ]);

        $directorHolder = $directorSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $directorFields = [
            ['label' => 'NAME OF CGC\'S SECRETARY', 'name' => 'name_of_cgc_secretary_for_director', 'type' => 'text'],
            ['label' => 'NAME OF MANAGER ASSIGNED BY DIRECTOR', 'name' => 'name_of_manager_assigned_by_director', 'type' => 'text'],
            ['label' => 'INSTRUCTIONS TO MANAGER', 'name' => 'instructions_to_manager', 'type' => 'textarea'],
            ['label' => 'DATE INSTRUCTIONS RELEASED BY DIRECTOR', 'name' => 'date_instructions_released_by_director', 'type' => 'date'],
        ];

        foreach ($directorFields as $index => $field) {
            $directorHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
