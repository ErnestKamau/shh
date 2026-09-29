<?php

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Move Sample collection data onto per-sample rows and hide the Collection wizard step
     * for Food, Food & Feed, Water, Swab, and Waste Water TRFs.
     */
    public function up(): void
    {
        $patcher = new class
        {
            use BuildsSubmissionFormTrfSections;

            public function run(): void
            {
                $forms = [
                    'TRF-FOOD-019' => [['value' => 'air_sampler', 'label' => 'Air sampler']],
                    'TRF-FOOD-FEED-021' => [['value' => 'air_sampler', 'label' => 'Air sampler']],
                    'TRF-WATER-020' => [],
                    'TRF-SWAB-022' => [],
                    'TRF-WASTEWATER-036' => [],
                    'TRF-WASTE-036' => [],
                ];

                foreach ($forms as $documentCode => $extraApparatus) {
                    $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    if (
                        $documentCode === 'TRF-WASTEWATER-036'
                        || $documentCode === 'TRF-WASTE-036'
                    ) {
                        $this->patchWasteWaterTrfSections($form);

                        continue;
                    }

                    if ($documentCode === 'TRF-WATER-020') {
                        $this->patchCollectionDataSection($form, true);
                        $this->patchSampleRowsSection($form, $this->waterTrfRowFields());
                    } elseif ($documentCode === 'TRF-SWAB-022') {
                        $this->patchCollectionDataSection($form, true);
                        $this->patchSampleRowsSection($form, $this->swabTrfRowFields());
                    } else {
                        $this->patchCollectionDataSection($form, true, $extraApparatus);
                        $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
                    }

                    $this->promoteCollectionFieldsToSampleRows($form, $extraApparatus);
                }

                $this->clearCaches();
            }
        };

        $patcher->run();
    }

    public function down(): void
    {
        // Non-reversible: restore prior layout via TRF seeders if needed.
    }
};
