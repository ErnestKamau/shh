<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfRemoveStorageElementSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        foreach (['TRF-FOOD-019', 'TRF-WATER-020', 'TRF-WASTE-036'] as $documentCode) {
            $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
            if ($form === null) {
                continue;
            }

            $this->removeTrfStorageElements($form);
            $this->command?->info("Removed TRF storage elements from {$form->name}.");
        }
    }
}
