<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class LaboratoryServiceRequestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // To avoid unique constraint violations and foreign key errors, we check for existing forms.
        $targetName = 'Laboratory Service Request Form';
        $targetCode = 'LSR-001';

        $formByName = SubmissionForm::where('name', $targetName)->first();
        $formByCode = SubmissionForm::where('document_code', $targetCode)->first();

        // Determine if we should use an existing form or create a new one
        $form = null;

        if ($formByName && $formByCode && $formByName->id !== $formByCode->id) {
            // Conflict between two records. Archive both if they have data, or delete if they don't.
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
                    // Existing form has data. Archive it and start fresh to avoid FK errors.
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
            'description' => 'Standard Laboratory Service Request Form for sample submissions.',
            'naming_convention_prefix' => 'LSR',
            'naming_convention_format' => 'LSR-{YYYY}{MM}-{0000}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'form_type' => 'template',
            'placement_slot' => ['customer_portal', 'admin_portal'],
        ]);
        $form->save();

        // Always recreate sections to ensure the seeded structure is applied
        $this->command->info('Creating sections for Laboratory Service Request Form...');
        
        // Clear existing sections only if they belong to a fresh or instance-less form
        $form->sections()->each(function($section) {
            $section->elementHolders()->each(function($holder) {
                $holder->elements()->delete();
                $holder->delete();
            });
            $section->delete();
        });

        $this->createFormSections($form);

        $this->command->info('Laboratory Service Request Form seeded/updated successfully.');
    }


    /**
     * Create sections and elements for the form
     */
    private function createFormSections($form)
    {
        // Section 1: Head
        $this->command->info('Creating Head section...');
        $headSection = $form->sections()->create([
            'title' => 'Head',
            'description' => 'General request header information.',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);

        $headHolder = $headSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 3,
            'sort_order' => 1,
        ]);

        $headHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'Reporting Language',
            'name' => 'reporting_language',
            'options' => [
                ['value' => 'english', 'label' => 'English'],
                ['value' => 'swahili', 'label' => 'Swahili']
            ],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $headHolder->elements()->create([
            'element_type' => 'date',
            'label' => 'Request date of service',
            'name' => 'request_date_of_service',
            'is_required' => true,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'date_collected',
        ]);

        $headHolder->elements()->create([
            'element_type' => 'select',
            'label' => 'Lab zone/location',
            'name' => 'lab_zone_location',
            'is_required' => false,
            'sort_order' => 3,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'lab_id',
        ]);

        // Section 2: Samples (Rows Section)
        $this->command->info('Creating Samples section...');
        $samplesSection = $form->sections()->create([
            'title' => 'Samples',
            'description' => 'Individual sample details.',
            'section_type' => 'rows_section',
            'sort_order' => 2,
        ]);

        $samplesHolder = $samplesSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 8,
            'sort_order' => 1,
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Sample Id',
            'name' => 'sample_id',
            'is_required' => true,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'sample_code',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'sample_type_select',
            'label' => 'Type of Sample',
            'name' => 'sample_type_id',
            'is_required' => true,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'sample_type_id',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'analysis_type_select',
            'label' => 'Matrix',
            'name' => 'analysis_type_id',
            'is_required' => true,
            'sort_order' => 3,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'analysis_type_id',
            'depends_on_type' => 'sample_type_select',
            'depends_on_field' => 'sample_type_id',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'analysis_elements_select',
            'label' => 'Parameters',
            'name' => 'parameters',
            'is_required' => false,
            'sort_order' => 4,
            'depends_on_type' => 'analysis_type_select',
            'depends_on_field' => 'analysis_type_id',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'number',
            'label' => 'Number of samples',
            'name' => 'number_of_samples',
            'is_required' => true,
            'sort_order' => 5,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'quantity',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Nature of Sample',
            'name' => 'nature_of_sample',
            'is_required' => false,
            'sort_order' => 6,
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'camera_photo',
            'label' => 'Picture of sample(s)',
            'name' => 'picture_of_samples',
            'is_required' => false,
            'sort_order' => 7,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'photo_url',
        ]);

        $samplesHolder->elements()->create([
            'element_type' => 'checkbox',
            'label' => 'Request for Sampling',
            'name' => 'request_for_sampling',
            'is_required' => false,
            'sort_order' => 8,
        ]);

        // Section 3: Additional Details
        $this->command->info('Creating Additional Details section...');
        $additionalSection = $form->sections()->create([
            'title' => 'Additional Details',
            'description' => 'Further information about the submission.',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);

        $additionalHolder = $additionalSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 8,
            'sort_order' => 1,
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Submitting Personnel',
            'name' => 'submitting_personnel',
            'is_required' => false,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'submit_by',
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'date',
            'label' => 'Date of submission',
            'name' => 'date_of_submission',
            'is_required' => false,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'receipt_date',
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Purpose',
            'name' => 'purpose',
            'is_required' => false,
            'sort_order' => 3,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'reason_for_submission',
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Safety Precautions',
            'name' => 'safety_precautions',
            'is_required' => false,
            'sort_order' => 4,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'batch_instructions',
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Unique Identification',
            'name' => 'unique_identification',
            'is_required' => false,
            'sort_order' => 5,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'reference_number',
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'checkbox',
            'label' => 'Mode of payment',
            'name' => 'mode_of_payment',
            'is_required' => false,
            'sort_order' => 6,
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Further Request',
            'name' => 'further_request',
            'is_required' => false,
            'sort_order' => 7,
        ]);

        $additionalHolder->elements()->create([
            'element_type' => 'checkbox',
            'label' => 'Mode of Service',
            'name' => 'mode_of_service',
            'is_required' => false,
            'sort_order' => 8,
        ]);
    }

}
