<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class DCEA001FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $targetName = 'FORENSIC LABORATORY SUBMISSION FORM';
        $targetCode = 'DCEA 001';

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
            'description' => 'For submission of biological samples/ substances suspected to be drug or precursor chemicals/substances with drug related effects',
            'naming_convention_prefix' => 'DCEA',
            'naming_convention_format' => 'DCEA-{YYYY}{MM}-{0000}',
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

        $this->command->info('Creating sections for DCEA 001 Form...');
        
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
            $this->command->info('Server cache cleared successfully.');
        } catch (\Exception $e) {
            $this->command->warn('Failed to clear server cache: ' . $e->getMessage());
        }

        $this->command->info('DCEA 001 Form seeded/updated successfully.');
    }

    private function createFormSections($form)
    {
        // Section 1: Submission Type
        $submissionTypeSection = $form->sections()->create([
            'title' => 'Submission Type',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $submissionTypeHolder = $submissionTypeSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
        $submissionTypeHolder->elements()->create([
            'element_type' => 'radio',
            'label' => 'Submission Type',
            'name' => 'submission_type',
            'options' => [
                ['value' => 'New Submission', 'label' => 'New Submission'],
                ['value' => 'Resubmission', 'label' => 'Resubmission'],
                ['value' => 'Additional Submission', 'label' => 'Additional Submission']
            ],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        // Section 2: Contact Person Information
        $contactSection = $form->sections()->create([
            'title' => 'Contact Person Information',
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);
        $contactHolder = $contactSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $contactFields = [
            ['label' => 'Submitting Agency', 'name' => 'submitting_agency', 'type' => 'text'],
            ['label' => 'Submitting Officer: Full Name', 'name' => 'submitting_officer_full_name', 'type' => 'text'],
            ['label' => 'Title', 'name' => 'submitting_officer_title', 'type' => 'text'],
            ['label' => 'Physical Address', 'name' => 'submitting_agency_physical_address', 'type' => 'textarea'],
        ];
        foreach ($contactFields as $index => $field) {
            $contactHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 3: Case Information
        $caseSection = $form->sections()->create([
            'title' => 'Case Information',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);
        $caseHolder = $caseSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $caseFields = [
            ['label' => 'Region', 'name' => 'case_region', 'type' => 'text'],
            ['label' => 'District', 'name' => 'case_district', 'type' => 'text'],
            ['label' => 'Working Station', 'name' => 'case_working_station', 'type' => 'text'],
            ['label' => 'Office Telephone No', 'name' => 'case_office_telephone', 'type' => 'text'],
            ['label' => 'Mobile Telephone No', 'name' => 'case_mobile_telephone', 'type' => 'text'],
            ['label' => 'Fax', 'name' => 'case_fax', 'type' => 'text'],
            ['label' => 'E-mail', 'name' => 'case_email', 'type' => 'email'],
            ['label' => 'Case No.', 'name' => 'case_no', 'type' => 'text'],
            ['label' => 'Offence', 'name' => 'case_offence', 'type' => 'text'],
            ['label' => 'Date of Seizure', 'name' => 'case_date_of_seizure', 'type' => 'date'],
            ['label' => 'Area of Seizure: Region', 'name' => 'seizure_region', 'type' => 'text'],
            ['label' => 'Area of Seizure: District', 'name' => 'seizure_district', 'type' => 'text'],
            ['label' => 'Area of Seizure: Ward', 'name' => 'seizure_ward', 'type' => 'text'],
            ['label' => 'Village/Street', 'name' => 'seizure_village_street', 'type' => 'text'],
        ];
        foreach ($caseFields as $index => $field) {
            $caseHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 4: Suspect Information
        $suspectSection = $form->sections()->create([
            'title' => 'Suspect Information',
            'section_type' => 'rows_section',
            'sort_order' => 4,
        ]);
        $suspectHolder = $suspectSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 5,
            'sort_order' => 1,
        ]);
        $suspectFields = [
            ['label' => 'Suspect Name (First, Middle, Last)', 'name' => 'suspect_name', 'type' => 'text'],
            ['label' => 'Sex (F/M)', 'name' => 'suspect_sex', 'type' => 'text'],
            ['label' => 'Date of Birth', 'name' => 'suspect_dob', 'type' => 'date'],
            ['label' => 'Nationality', 'name' => 'suspect_nationality', 'type' => 'text'],
            ['label' => 'ID No./Passport No.', 'name' => 'suspect_id_no', 'type' => 'text'],
        ];
        foreach ($suspectFields as $index => $field) {
            $suspectHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 5: Description of Exhibit Submitted
        $exhibitSection = $form->sections()->create([
            'title' => 'Description of Exhibit Submitted',
            'section_type' => 'rows_section',
            'sort_order' => 5,
        ]);
        $exhibitHolder = $exhibitSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $exhibitFields = [
            ['label' => 'No of Items and its Description', 'name' => 'exhibit_description', 'type' => 'text'],
            ['label' => 'Suspected Drug, chemical or item', 'name' => 'suspected_drug', 'type' => 'text'],
        ];
        foreach ($exhibitFields as $index => $field) {
            $exhibitHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 6: Request
        $requestSection = $form->sections()->create([
            'title' => 'Request',
            'section_type' => 'regular',
            'sort_order' => 6,
        ]);
        $requestHolder = $requestSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);
        $requestHolder->elements()->create([
            'element_type' => 'checkbox',
            'label' => 'Requested analysis of:',
            'name' => 'requested_analysis',
            'options' => [
                ['value' => 'Sample identity', 'label' => '(1) Sample identity'],
                ['value' => 'Drug type', 'label' => '(2) Drug type'],
                ['value' => 'Weight of drug', 'label' => '(3) Weight of drug'],
                ['value' => 'Effects of the identified drug to human being', 'label' => '(4) Effects of the identified drug to human being']
            ],
            'is_required' => false,
            'sort_order' => 1,
        ]);

        // Section 7: Submitted By
        $submittedSection = $form->sections()->create([
            'title' => 'Submitted By',
            'section_type' => 'regular',
            'sort_order' => 7,
        ]);
        $submittedHolder = $submittedSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $submittedFields = [
            ['label' => 'Full Name of Submitting Officer', 'name' => 'submitted_by_name', 'type' => 'text'],
            ['label' => 'Title', 'name' => 'submitted_by_title', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'submitted_by_signature', 'type' => 'signature'],
            ['label' => 'Date', 'name' => 'submitted_by_date', 'type' => 'date'],
            ['label' => 'Time', 'name' => 'submitted_by_time', 'type' => 'text'], // could be time but text is safer if time element_type doesn't exist
        ];
        foreach ($submittedFields as $index => $field) {
            $submittedHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 8: Received by
        $receivedSection = $form->sections()->create([
            'title' => 'Received by',
            'section_type' => 'regular',
            'sort_order' => 8,
        ]);
        $receivedHolder = $receivedSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $receivedFields = [
            ['label' => 'Full Name of Receiving Officer', 'name' => 'received_by_name', 'type' => 'text'],
            ['label' => 'Title', 'name' => 'received_by_title', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'received_by_signature', 'type' => 'signature'],
            ['label' => 'Date', 'name' => 'received_by_date', 'type' => 'date'],
            ['label' => 'Time', 'name' => 'received_by_time', 'type' => 'text'],
        ];
        foreach ($receivedFields as $index => $field) {
            $receivedHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
