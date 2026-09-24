<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfWaterSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'description' => 'AmSpec LWS-020 test request form for water samples.',
            'naming_convention_prefix' => 'TRFW',
            'naming_convention_format' => 'TRFW-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-water',
        ]);

        $this->syncSampleTypeCategoriesByNames($form, ['Water']);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Water structure already exists; patching fields.');
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true);
            $this->patchSampleRowsSection($form, $this->waterTrfRowFields());
            $this->patchMiscellaneousSection($form);
            $this->patchSubmitAndSignSection($form);
            $this->promoteCollectionFieldsToSampleRows($form);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true);

            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->waterTrfRowFields());
            $this->createMiscellaneousSection($form, 4);

            $this->createSubmitAndSignSection($form, 5);
            $this->promoteCollectionFieldsToSampleRows($form);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Water seeded successfully.');
    }
}
