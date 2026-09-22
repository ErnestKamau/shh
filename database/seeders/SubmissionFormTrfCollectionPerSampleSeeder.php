<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

/**
 * Idempotent: move Sample collection data onto per-sample rows and hide the Collection step.
 * Safe to re-run on new or existing databases after TRF forms are seeded.
 */
class SubmissionFormTrfCollectionPerSampleSeeder extends Seeder
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
                $this->command?->warn("TRF form {$documentCode} not found; skipping collection promotion.");

                continue;
            }

            if (
                $documentCode === 'TRF-WASTEWATER-036'
                || $documentCode === 'TRF-WASTE-036'
            ) {
                $this->patchWasteWaterTrfSections($form);
                $this->command?->info("Promoted collection fields for {$documentCode}.");

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
            $this->command?->info("Promoted collection fields for {$documentCode}.");
        }

        $this->clearCaches();
    }
}
