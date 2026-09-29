<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfFoodAndFeedSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Food & Feed',
            'document_code' => 'TRF-FOOD-FEED-021',
            'description' => 'AmSpec test request form for Food & Feed samples.',
            'naming_convention_prefix' => 'TRFFF',
            'naming_convention_format' => 'TRFFF-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
        ]);

        $this->syncSampleTypeCategoriesByNames($form, ['Food & Feed', 'Food and Feed']);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Food & Feed structure already exists; patching fields.');
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
            $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
            $this->patchMiscellaneousSection($form);
            $this->patchSubmitAndSignSection($form);
            $this->promoteCollectionFieldsToSampleRows($form, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);

            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->foodTrfRowFields());
            $this->createMiscellaneousSection($form, 4);

            $this->createSubmitAndSignSection($form, 5);
            $this->promoteCollectionFieldsToSampleRows($form, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Food & Feed seeded successfully.');
    }
}
