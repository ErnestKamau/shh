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
        // Create or update the form with a distinct name to avoid confusion with existing ones
        $form = SubmissionForm::updateOrCreate(
            ['name' => 'Laboratory Service Request Form'],
            [
                'document_code' => 'LSR-001',
                'description' => 'Standard Laboratory Service Request Form for sample submissions.',
                'naming_convention_prefix' => 'LSR',
                'naming_convention_format' => 'LSR-{YYYY}{MM}-{0000}',
                'is_published' => true,
                'is_active' => true,
                'is_customer_portal_form' => true,
                'form_type' => 'template',
                'placement_slot' => ['customer_portal', 'admin_portal'],
            ]
        );

        // Always recreate sections to ensure the seeded structure is applied
        $this->command->info('Creating sections for Laboratory Service Request Form...');
        
        // Clear existing sections if updating
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
        // Section 1: Customer Information
        $customerSection = $form->sections()->create([
            'title' => 'Customer Information',
            'description' => 'Details about the customer submitting the sample.',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);

        $customerRow1 = $customerSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $customerRow1->elements()->create([
            'element_type' => 'client_select',
            'label' => 'Customer',
            'name' => 'crm_customer_id',
            'is_required' => true,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'crm_customer_id',
        ]);

        $customerRow1->elements()->create([
            'element_type' => 'client_contact_select',
            'label' => 'Contact Person',
            'name' => 'crm_contact_id',
            'is_required' => false,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'crm_contact_id',
        ]);

        // Section 2: Sample Header Information
        $headerSection = $form->sections()->create([
            'title' => 'Request Details',
            'description' => 'General information about the sample request.',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);

        $headerRow1 = $headerSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);

        $headerRow1->elements()->create([
            'element_type' => 'date',
            'label' => 'Date Collected',
            'name' => 'date_collected',
            'is_required' => true,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'date_collected',
        ]);

        $headerRow1->elements()->create([
            'element_type' => 'sample_type_select',
            'label' => 'Sample Type',
            'name' => 'sample_type_id',
            'is_required' => true,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'sample_type_id',
        ]);

        $headerRow2 = $headerSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 2,
        ]);

        $headerRow2->elements()->create([
            'element_type' => 'text',
            'label' => 'Reference Number',
            'name' => 'reference_number',
            'is_required' => false,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'reference_number',
        ]);

        $headerRow2->elements()->create([
            'element_type' => 'textarea',
            'label' => 'Description / Remarks',
            'name' => 'description',
            'is_required' => false,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_headers',
            'mapping_field' => 'description',
        ]);

        // Section 3: Sample Details (Rows Section)
        $sampleDetailsSection = $form->sections()->create([
            'title' => 'Sample Details',
            'description' => 'Individual sample information.',
            'section_type' => 'rows_section', // Allow adding multiple samples
            'sort_order' => 3,
        ]);

        $sampleRowTemplate = $sampleDetailsSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 4,
            'sort_order' => 1,
        ]);

        $sampleRowTemplate->elements()->create([
            'element_type' => 'text',
            'label' => 'Sample Code / Batch No',
            'name' => 'sample_code',
            'is_required' => true,
            'sort_order' => 1,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'sample_code',
        ]);

        $sampleRowTemplate->elements()->create([
            'element_type' => 'analysis_type_select',
            'label' => 'Analysis Type',
            'name' => 'analysis_type_id',
            'is_required' => true,
            'sort_order' => 2,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'analysis_type_id',
        ]);

        $sampleRowTemplate->elements()->create([
            'element_type' => 'number',
            'label' => 'Quantity',
            'name' => 'quantity',
            'is_required' => true,
            'sort_order' => 3,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'quantity',
        ]);

        $sampleRowTemplate->elements()->create([
            'element_type' => 'sample_condition_select',
            'label' => 'Sample Condition',
            'name' => 'sample_condition_id',
            'is_required' => false,
            'sort_order' => 4,
            'is_mapped' => true,
            'mapping_table' => 'sample_details',
            'mapping_field' => 'sample_condition_id',
        ]);
    }
}
