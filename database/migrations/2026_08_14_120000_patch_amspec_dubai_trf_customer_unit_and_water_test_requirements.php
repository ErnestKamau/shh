<?php

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Patch live AmSpec Dubai TRFs:
     * - company_unit_id on customer details (all three active TRFs)
     * - test_requirements checkbox group on Water sample rows
     * - RFT catalog: unhide Food/Water/WW, hide Swab standalone + legacy unified form
     */
    public function up(): void
    {
        $patcher = new class
        {
            use BuildsSubmissionFormTrfSections;

            public function run(): void
            {
                $activeDocumentCodes = [
                    'TRF-FOOD-019',
                    'TRF-WATER-020',
                    'TRF-WASTE-036',
                ];

                foreach ($activeDocumentCodes as $documentCode) {
                    $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    $form->is_hidden_from_rft = false;
                    $form->save();

                    $this->patchCustomerDetailsSection($form);

                    if ($documentCode === 'TRF-WATER-020') {
                        $this->patchSampleRowsSection($form, $this->waterTrfRowFields());
                    }
                }

                foreach (['TRF-SWAB-022', 'TRF-AMSPEC-001'] as $hiddenCode) {
                    $form = SubmissionForm::query()->where('document_code', $hiddenCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    $form->is_hidden_from_rft = true;
                    $form->save();
                    $form->sampleTypeCategories()->sync([]);
                }

                $this->clearCaches();
            }
        };

        $patcher->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible: restores prior field layout only via re-seeding.
    }
};
