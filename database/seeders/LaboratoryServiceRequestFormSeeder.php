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
            'is_published' => false,
            'is_active' => false,
            'is_customer_portal_form' => false,
            'is_customer_request_form' => false,
            'form_type' => 'template',
            'placement_slot' => ['customer_portal', 'admin_portal', 'samples_receiving'],
        ]);
        $form->save();

        // Ensure visibility by linking to all lab sections and sample types
        try {
            $stages = \App\SampleAnalysisStage::pluck('id')->toArray();
            $form->sampleAnalysisStages()->sync($stages);
            
            $sampleTypes = \App\SampleType::pluck('id')->toArray();
            $form->sampleTypes()->sync($sampleTypes);
        } catch (\Exception $e) {
            $this->command->warn('Could not sync stages/types: ' . $e->getMessage());
        }

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

        // Bust the cache to ensure changes appear immediately on the server
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            $this->command->info('Server cache cleared successfully.');
        } catch (\Exception $e) {
            $this->command->warn('Failed to clear server cache: ' . $e->getMessage());
        }

        $this->command->info('Laboratory Service Request Form seeded/updated successfully.');
    }


    /**
     * Create sections and elements for the form
     */
    private function createFormSections($form)
    {
        $samplingVisible = [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        // 1. Customer details
        $customerSection = $form->sections()->create([
            'title' => 'Customer details',
            'description' => 'Customer information for this service request.',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $customerHolder = $customerSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $customerHolder->elements()->create([
            'element_type' => 'client_select',
            'label' => 'Customer',
            'name' => 'crm_customer_id',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $customerHolder->elements()->create([
            'element_type' => 'pricelist_viewer',
            'label' => 'Contract pricelist',
            'name' => 'pricelist',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        // 2. Request header
        $headerSection = $form->sections()->create([
            'title' => 'Request header',
            'description' => 'Reporting language, service date, and service options.',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $headerHolder = $headerSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 8,
            'sort_order' => 1,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'Reporting Language',
            'name' => 'reporting_language',
            'options' => [
                ['value' => 'english', 'label' => 'English'],
                ['value' => 'swahili', 'label' => 'Swahili'],
            ],
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'date',
            'label' => 'Request date of service',
            'name' => 'request_date_of_service',
            'is_required' => true,
            'sort_order' => 2,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'zone_select',
            'label' => 'Lab zone/location',
            'name' => 'lab_zone_location',
            'is_required' => false,
            'sort_order' => 3,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'checkbox',
            'label' => 'Request for Sampling',
            'name' => 'request_for_sampling',
            'is_required' => false,
            'sort_order' => 4,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'select',
            'label' => 'Mode of Service',
            'name' => 'mode_of_service',
            'options' => [
                ['value' => 'express', 'label' => 'Express'],
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'confidential', 'label' => 'Confidential'],
            ],
            'is_required' => false,
            'sort_order' => 5,
        ]);
        $headerHolder->elements()->create([
            'element_type' => 'select',
            'label' => 'Mode of payment',
            'name' => 'mode_of_payment',
            'options' => [
                ['value' => 'pre_paid', 'label' => 'Pre-paid'],
                ['value' => 'post_paid', 'label' => 'Post-paid'],
            ],
            'is_required' => false,
            'sort_order' => 6,
        ]);

        // 3. Sample collection data (conditional)
        $collectionSection = $form->sections()->create([
            'title' => 'Sample collection data',
            'description' => 'Required when sampling is requested by the laboratory.',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);
        $collectionHolder = $collectionSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 8,
            'sort_order' => 1,
        ]);
        foreach ([
            ['date', 'Sampling date', 'sampling_date', 1],
            ['text', 'Sampling time', 'sampling_time', 2],
            ['text', 'Sampling location', 'sampling_location', 3],
            ['text', 'Sampling apparatus', 'sampling_apparatus', 4],
            ['text', 'Thermometer ID', 'thermometer_id', 5],
            ['text', 'Method of sampling', 'method_of_sampling', 6],
            ['select', 'Reason of collection', 'reason_of_collection', 7, [
                ['value' => 'contract', 'label' => 'Contract'],
                ['value' => 'non_contract', 'label' => 'Non-contract'],
                ['value' => 'haccp', 'label' => 'HACCP'],
                ['value' => 'disputed', 'label' => 'Disputed'],
            ]],
            ['select', 'Transport condition', 'transport_condition', 8, [
                ['value' => 'chiller', 'label' => 'Chiller'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
        ] as $field) {
            $payload = [
                'element_type' => $field[0],
                'label' => $field[1],
                'name' => $field[2],
                'is_required' => false,
                'sort_order' => $field[3],
                'conditional_logic' => $samplingVisible,
            ];
            if ($field[0] === 'select' && isset($field[4])) {
                $payload['options'] = $field[4];
            }
            $collectionHolder->elements()->create($payload);
        }

        // 4. Test & sample information
        $samplesSection = $form->sections()->create([
            'title' => 'Test & sample information',
            'description' => 'Sample lines with parameters requested.',
            'section_type' => 'rows_section',
            'sort_order' => 4,
        ]);
        $samplesHolder = $samplesSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 14,
            'sort_order' => 1,
        ]);
        $rowFields = [
            ['text', 'Sample description', 'sample_description', 1],
            ['text', 'Sample Id', 'sample_id', 2],
            ['sample_type_select', 'Type of Sample', 'sample_type_id', 3],
            ['analysis_type_select', 'Matrix', 'analysis_type_id', 4],
            ['analysis_elements_select', 'Parameters', 'parameters', 5],
            ['text', 'Parameter category', 'parameter_category', 6],
            ['text', 'Sampling point / location', 'sampling_point', 7],
            ['number', 'Qty', 'number_of_samples', 8],
            ['text', 'Sample condition', 'sample_condition', 9],
            ['date', 'Production date', 'production_date', 10],
            ['date', 'Expiration date', 'expiration_date', 11],
            ['text', 'Batch number', 'batch_number', 12],
            ['select', 'State of sample', 'state_of_sample', 13, [
                ['value' => 'L', 'label' => 'L'],
                ['value' => 'SS', 'label' => 'SS'],
                ['value' => 'S', 'label' => 'S'],
            ]],
            ['camera_photo', 'Picture of sample(s)', 'picture_of_samples', 14],
        ];
        foreach ($rowFields as $field) {
            $payload = [
                'element_type' => $field[0],
                'label' => $field[1],
                'name' => $field[2],
                'is_required' => in_array($field[2], ['sample_id', 'sample_type_id', 'analysis_type_id'], true),
                'sort_order' => $field[3],
            ];
            if ($field[0] === 'analysis_type_select') {
                $payload['depends_on_type'] = 'sample_type_select';
                $payload['depends_on_field'] = 'sample_type_id';
            }
            if ($field[0] === 'analysis_elements_select') {
                $payload['depends_on_type'] = 'analysis_type_select';
                $payload['depends_on_field'] = 'analysis_type_id';
            }
            if ($field[0] === 'select' && isset($field[4])) {
                $payload['options'] = $field[4];
            }
            $samplesHolder->elements()->create($payload);
        }

        // 5. Reporting & conformity
        $reportingSection = $form->sections()->create([
            'title' => 'Reporting & conformity',
            'description' => 'Statement of conformity preference.',
            'section_type' => 'regular',
            'sort_order' => 5,
        ]);
        $reportingHolder = $reportingSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $reportingHolder->elements()->create([
            'element_type' => 'select',
            'label' => 'Statement of conformity in reports',
            'name' => 'statement_of_conformity',
            'options' => [
                ['value' => 'yes', 'label' => 'Yes'],
                ['value' => 'no', 'label' => 'No'],
                ['value' => 'as_per_contract', 'label' => 'As per contract'],
                ['value' => 'as_per_email', 'label' => 'As per email'],
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);
        $reportingHolder->elements()->create([
            'element_type' => 'textarea',
            'label' => 'Purpose',
            'name' => 'purpose',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        // 6. Attachments
        $attachmentsSection = $form->sections()->create([
            'title' => 'Attachments',
            'description' => 'Supporting documents for the submission.',
            'section_type' => 'rows_section',
            'sort_order' => 6,
        ]);
        $attachmentsHolder = $attachmentsSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 3,
            'sort_order' => 1,
        ]);
        $attachmentsHolder->elements()->create([
            'element_type' => 'file',
            'label' => 'Attachment',
            'name' => 'attachments',
            'is_required' => false,
            'sort_order' => 1,
        ]);
        $attachmentsHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Attachment Type',
            'name' => 'attachment_type',
            'is_required' => false,
            'sort_order' => 2,
        ]);
        $attachmentsHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Attachment Description',
            'name' => 'attachment_heading',
            'is_required' => false,
            'sort_order' => 3,
        ]);

        // 7. Declaration
        $declarationSection = $form->sections()->create([
            'title' => 'Declaration',
            'description' => 'Submitted by signature block.',
            'section_type' => 'regular',
            'sort_order' => 7,
        ]);
        $declarationHolder = $declarationSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 4,
            'sort_order' => 1,
        ]);
        $declarationHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Submitted by (name)',
            'name' => 'submitted_by_full_name',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $declarationHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Title',
            'name' => 'submitted_by_title',
            'is_required' => false,
            'sort_order' => 2,
        ]);
        $declarationHolder->elements()->create([
            'element_type' => 'text',
            'label' => 'Signature',
            'name' => 'submitted_by_signature',
            'is_required' => false,
            'sort_order' => 3,
        ]);
        $declarationHolder->elements()->create([
            'element_type' => 'date',
            'label' => 'Date',
            'name' => 'submitted_by_date',
            'is_required' => true,
            'sort_order' => 4,
        ]);
    }

}
