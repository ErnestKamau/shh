<?php

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Patch Food TRFs: add test_category, sample_condition, and sample_temp row fields.
     */
    public function up(): void
    {
        $patcher = new class
        {
            use BuildsSubmissionFormTrfSections;

            public function run(): void
            {
                $documentCodes = [
                    'TRF-FOOD-019',
                    'TRF-FOOD-FEED-021',
                ];

                foreach ($documentCodes as $documentCode) {
                    $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
                    if ($form === null) {
                        continue;
                    }

                    $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
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
