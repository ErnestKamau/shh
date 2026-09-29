<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfRowsSectionPatchSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $patches = [
            'TRF-FOOD-019' => function (SubmissionForm $form): void {
                $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
                $this->removeRowElementsByName($form, ['sampling_point']);
                $this->promoteCollectionFieldsToSampleRows($form, [
                    ['value' => 'air_sampler', 'label' => 'Air sampler'],
                ]);
            },
            'TRF-FOOD-FEED-021' => function (SubmissionForm $form): void {
                $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
                $this->removeRowElementsByName($form, ['sampling_point']);
                $this->promoteCollectionFieldsToSampleRows($form, [
                    ['value' => 'air_sampler', 'label' => 'Air sampler'],
                ]);
            },
            'TRF-WATER-020' => function (SubmissionForm $form): void {
                $this->patchSampleRowsSection($form, $this->waterTrfRowFields());
                $this->removeRowElementsByName($form, [
                    'location',
                    'sampling_point',
                    'sampling_point_other',
                    'sampling_point_others',
                    'other_sampling_point',
                ]);
                $this->promoteCollectionFieldsToSampleRows($form);
            },
            'TRF-SWAB-022' => function (SubmissionForm $form): void {
                $this->patchSampleRowsSection($form, $this->swabTrfRowFields());
                $this->removeRowElementsByName($form, ['sampling_point']);
                $this->promoteCollectionFieldsToSampleRows($form);
            },
            'TRF-WASTEWATER-036' => function (SubmissionForm $form): void {
                $this->patchWasteWaterTrfSections($form);
            },
            // Legacy code still patched if rename has not run yet.
            'TRF-WASTE-036' => function (SubmissionForm $form): void {
                $this->patchWasteWaterTrfSections($form);
            },
        ];

        foreach ($patches as $documentCode => $patch) {
            $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
            if ($form === null) {
                $this->command?->warn("TRF form {$documentCode} not found; skipping rows patch.");

                continue;
            }

            $patch($form);
            $this->command?->info("Patched rows section for {$form->name}.");
        }

        $this->clearCaches();
    }
}
