<?php

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use BuildsSubmissionFormTrfSections;

    public function up(): void
    {
        SubmissionForm::query()
            ->whereIn('document_code', [
                'TRF-FOOD-019',
                'TRF-WATER-020',
                'TRF-WASTE-036',
            ])
            ->each(function (SubmissionForm $form): void {
                $this->patchCollectionDataSection($form, true);
                $this->patchMiscellaneousSection($form);
            });
    }

    public function down(): void
    {
        // Structure-only migration; no down migration.
    }
};
