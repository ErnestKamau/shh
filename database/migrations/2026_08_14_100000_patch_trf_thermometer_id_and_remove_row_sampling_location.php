<?php

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Patch Food, Swab, and Water TRFs:
     * - Thermometer ID as free-text field (not apparatus checkbox)
     * - Remove Sampling Location (CRM select) from Test & sample information rows
     * - Keep Sampling Point free-text field on per-sample rows
     */
    public function up(): void
    {
        $patcher = new class
        {
            use BuildsSubmissionFormTrfSections;

            public function run(): void
            {
                $collectionPatches = [
                    'TRF-FOOD-019' => [['value' => 'air_sampler', 'label' => 'Air sampler']],
                    'TRF-WATER-020' => [],
                    'TRF-SWAB-022' => [],
                ];

                foreach ($collectionPatches as $documentCode => $extraApparatus) {
                    $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    $this->patchCollectionDataSection($form, true, $extraApparatus);
                }

                $rowPatches = [
                    'TRF-FOOD-019' => $this->foodTrfRowFields(),
                    'TRF-WATER-020' => $this->waterTrfRowFields(),
                    'TRF-SWAB-022' => $this->swabTrfRowFields(),
                ];

                foreach ($rowPatches as $documentCode => $rowFields) {
                    $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    $this->patchSampleRowsSection($form, $rowFields);
                    $this->removeRowElementsByName($form, [
                        'sampling_point',
                        'location',
                        'sampling_point_other',
                        'sampling_point_others',
                        'other_sampling_point',
                    ]);
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
