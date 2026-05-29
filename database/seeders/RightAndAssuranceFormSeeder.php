<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;

class RightAndAssuranceFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $targetName = 'RIGHT AND ASSURANCE FORM';
        $targetCode = 'DNA-002'; // Giving it a generic code since one isn't explicitly defined other than SECOND SCHEDULE

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
            'description' => 'SECOND SCHEDULE [Made under section 28(1)] of the Human DNA Regulation Act No 8, 2009',
            'naming_convention_prefix' => 'DNA-RAF',
            'naming_convention_format' => 'DNA-RAF-{YYYY}{MM}-{0000}',
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

        $this->command->info('Creating sections for Right and Assurance Form...');
        
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

        $this->command->info('Right and Assurance Form seeded/updated successfully.');
    }

    private function createFormSections($form)
    {
        // Section 1: General Information
        $infoSection = $form->sections()->create([
            'title' => 'General Information',
            'description' => '(To be read or cause to be read to the sample source or sample source\'s representative before collection of sample for Human DNA for analysis)',
            'section_type' => 'regular',
            'sort_order' => 1,
        ]);
        $infoHolder = $infoSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        $infoFields = [
            ['label' => '1. Name of Sample source', 'name' => 'sample_source_name', 'type' => 'text'],
            ['label' => '2. Name of Sample source\'s representative', 'name' => 'sample_source_representative', 'type' => 'text'],
            ['label' => '3. Address', 'name' => 'address', 'type' => 'textarea'],
            ['label' => 'Telephone', 'name' => 'telephone', 'type' => 'text'],
            ['label' => 'Email', 'name' => 'email', 'type' => 'email'],
            ['label' => 'Mobile', 'name' => 'mobile', 'type' => 'text'],
            ['label' => '4. Date of birth', 'name' => 'date_of_birth', 'type' => 'date'],
            ['label' => '5. Nationality', 'name' => 'nationality', 'type' => 'text'],
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

        // Section 2: Rights and Assurances
        $rightsSection = $form->sections()->create([
            'title' => 'Rights and Assurances',
            'description' => "The right and assurances of the sample source/ sample source's representatives shall include the following information:",
            'section_type' => 'regular',
            'sort_order' => 2,
        ]);

        $rightsHolder = $rightsSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 1,
            'sort_order' => 1,
        ]);

        $rightsStatements = [
            'i. that the samples for Human DNA shall only be used as authorized in the written authorization;',
            'ii. that the sample for Human DNA is the property of the sample source or sample source\'s representative;',
            'iii. Unless specifically prohibited by the sample source or sample source\'s representative, researchers may be granted access to sample for Human DNA that cannot be linked to individual identification;',
            'iv. that the sample source or the sample source\'s representative has the right to order the destruction of the sample for Human DNA, genetic processed material at any time;',
            'v. that the sample for Human DNA shall be destroyed on completion of the analysis unless the sample source or sample source\'s representative has previously directed otherwise in writing;',
            'vi. that the sample source may designate another individual as the person authorized to make decisions regarding the sample for Human DNA after the death of the sample source; and if any person is so designated, the sample source shall notify the facility in which the sample source for Human DNA is stored;',
            'vii. save for samples collected from criminal suspects the sample source\'s representative has the right to examine the records containing private genetic information, to obtain copies of such records;',
            'viii. the sample source or sample source\'s representative or criminal investigation authority may request for necessary corrections or amendments, if any, of the personal particulars of the sample source;',
            'ix. except for samples of Human DNA collected from dead bodies, criminal investigations and in compliance with court order, the sample source or sample source\'s representative have the right to refuse the collection of sample for Human DNA from him if the mode of collection is non intimacy or discovers that the consent is obtained through undue influence;',
            'x. the right to have copy of written authorization; and',
            'xi. Counseling services may be available.',
        ];

        foreach ($rightsStatements as $index => $statement) {
            $rightsHolder->elements()->create([
                'element_type' => 'static_text',
                'label' => mb_substr($statement, 0, 200),
                'name' => 'rights_statement_' . ($index + 1),
                'default_value' => $statement,
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

// Section 3: Declarations
        $declarationSection = $form->sections()->create([
            'title' => 'Declarations',
            'section_type' => 'regular',
            'sort_order' => 3,
        ]);
        $declarationHolder = $declarationSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        
        $declarationFields = [
            ['label' => 'I (Name)', 'name' => 'declaration_name', 'type' => 'text'],
            ['label' => 'P.O.BOX', 'name' => 'declaration_pobox', 'type' => 'text'],
            ['label' => 'Written authorization by', 'name' => 'declaration_written_authorization_by', 'type' => 'text'],
            ['label' => 'Signature', 'name' => 'declaration_signature', 'type' => 'signature'],
            ['label' => 'Date', 'name' => 'declaration_date', 'type' => 'date'],
            ['label' => 'I HEREBY CERTIFY that the rights and assurances were read and understood by', 'name' => 'certify_read_by', 'type' => 'text'],
            ['label' => 'in my presence this day of', 'name' => 'certify_day_of', 'type' => 'text'],
            ['label' => 'Year 20...', 'name' => 'certify_year', 'type' => 'text'],
            ['label' => 'Certifier Signature', 'name' => 'certifier_signature', 'type' => 'signature'],
            ['label' => 'Seal', 'name' => 'certifier_seal', 'type' => 'text'],
            ['label' => 'Qualification', 'name' => 'certifier_qualification', 'type' => 'text'],
        ];

        foreach ($declarationFields as $index => $field) {
            $declarationHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }

        // Section 4: FOR OFFICIAL USE ONLY
        $officialSection = $form->sections()->create([
            'title' => 'FOR OFFICIAL USE ONLY',
            'section_type' => 'regular',
            'sort_order' => 4,
        ]);
        $officialHolder = $officialSection->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 2,
            'sort_order' => 1,
        ]);
        
        $officialFields = [
            ['label' => 'Received/Rejected by', 'name' => 'official_received_by', 'type' => 'text'],
            ['label' => 'of (Department/Institution)', 'name' => 'official_of', 'type' => 'text'],
            ['label' => 'this day of', 'name' => 'official_day_of', 'type' => 'text'],
            ['label' => 'Year 20...', 'name' => 'official_year', 'type' => 'text'],
            ['label' => 'Opinion of Receiving officer', 'name' => 'official_opinion', 'type' => 'textarea'],
            ['label' => 'Signature', 'name' => 'official_signature', 'type' => 'signature'],
            ['label' => 'Qualification', 'name' => 'official_qualification', 'type' => 'text'],
            ['label' => 'Seal', 'name' => 'official_seal', 'type' => 'text'],
        ];

        foreach ($officialFields as $index => $field) {
            $officialHolder->elements()->create([
                'element_type' => $field['type'],
                'label' => $field['label'],
                'name' => $field['name'],
                'is_required' => false,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
